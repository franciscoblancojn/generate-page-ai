#!/bin/bash
#
# Harness de validación del proyecto Generate Page AI.
#
# Uso:
#   bin/harness.sh                 → Ejecuta todas las validaciones.
#   bin/harness.sh doc             → Lista y valida los docs en doc/.
#   bin/harness.sh doc <Nombre>    → Crea un doc en doc/ si no existe.
#
# Validaciones:
#   1. php -l en todo el plugin (excluye libs/).
#   2. Sintaxis PHP <= 7.0 (no ?->, match, readonly, arrow, named args).
#   3. JavaScript ES5 (sin arrow functions, let/const, template literals).
#   4. Prefijos CSS: gpai- (plugin) o fwue- (librería wordpress_utils).
#   5. Clases requeridas presentes (prefijo GPAI_).
#   6. require_once declarados en cargadores → archivo existe.
#   7. Referencias a documentación → archivo existe en doc/.
#
# NO ejecutar cambios de git. Solo validaciones de lectura + creación de docs.

set -u

PLUGIN_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DOC_DIR="$PLUGIN_DIR/doc"
LIB_DIR="$PLUGIN_DIR/libs"
FAILURES=0
WARNINGS=0

fail() {
    printf '  [FAIL] %s\n' "$*"
    FAILURES=$((FAILURES + 1))
}

warn() {
    printf '  [WARN] %s\n' "$*"
    WARNINGS=$((WARNINGS + 1))
}

ok() {
    printf '  [ OK ] %s\n' "$*"
}

# ---------------------------------------------------------------
# Helpers de detección de sintaxis moderna (PHP 7.1+ / 8.x)
# ---------------------------------------------------------------
has_modern_php_syntax() {
    local file="$1"

    # Nullsafe operator ?-> (PHP 8.0)
    if grep -qE '\?->' "$file"; then
        echo 'operador nullsafe ?-> (PHP 8.0)'
        return 0
    fi
    # Named arguments (PHP 8.0)
    if grep -qE ':[[:space:]]*[a-zA-Z_][a-zA-Z0-9_]*\(\)' "$file"; then
        # Solo aplica cuando hay `name: value` style argument invocation; para
        # evitar falsos positivos se requiere paréntesis después de la clave.
        if grep -qE '[a-zA-Z_][a-zA-Z0-9_]*:[[:space:]]*\$?[a-zA-Z0-9_]+\(' "$file"; then
            echo 'posibles named arguments'
            return 0
        fi
    fi
    # match expression (PHP 8.0)
    if grep -qE '\bmatch[[:space:]]*\(' "$file"; then
        echo 'expresión match (PHP 8.0)'
        return 0
    fi
    # readonly properties (PHP 8.1)
    if grep -qE '\breadonly[[:space:]]+' "$file"; then
        echo 'propiedad readonly (PHP 8.1)'
        return 0
    fi
    # Arrow functions fn() (PHP 7.4)
    if grep -qE '\bfn[[:space:]]*\([^)]*\)[[:space:]]*=>' "$file"; then
        echo 'arrow function fn() (PHP 7.4)'
        return 0
    fi
    # Typed properties (PHP 7.4)
    if grep -qE '(public|private|protected)[[:space:]]+([a-zA-Z_][a-zA-Z0-9_]*|\\?[A-Z][a-zA-Z0-9_]*)+\$' "$file"; then
        echo 'propiedad tipada (PHP 7.4)'
        return 0
    fi
    # Union types (PHP 8.0)
    if grep -qE '\|([a-z_][a-z0-9_]*)[[:space:]]*\$\{(.*)' "$file"; then
        echo 'union type'
        return 0
    fi

    return 1
}

has_modern_js_syntax() {
    local file="$1"

    # Arrow functions => (ES6)
    if grep -qE '\=\>' "$file"; then
        echo 'arrow function o =>'
        return 0
    fi
    # Template literals con backtick
    if grep -q '`' "$file"; then
        echo 'template literal (backtick)'
        return 0
    fi
    # let/const (ES6)
    if grep -qE '\b(let|const)[[:space:]]+[a-zA-Z_$]' "$file"; then
        echo 'let/const (ES6)'
        return 0
    fi

    return 1
}

# ---------------------------------------------------------------
# Comando: crear un doc en doc/
# ---------------------------------------------------------------
create_doc() {
    local name="${1:-}"
    if [ -z "$name" ]; then
        printf 'Uso: bin/harness.sh doc <Nombre>\n'
        printf '     bin/harness.sh doc\n'
        return 1
    fi

    if [ "${name##*.}" != "md" ]; then
        name="$name.md"
    fi

    local file="$DOC_DIR/$name"
    if [ -f "$file" ]; then
        printf 'Ya existe: %s\n' "$file"
        return 0
    fi

    local title="${name%.md}"
    if ! echo "$title" | grep -q '^DOC-'; then
        title="DOC-$title"
    fi

    mkdir -p "$DOC_DIR"
    cat > "$file" <<EOF
# $title.md

> Documentación de Generate Page AI. Se sirve de la carpeta $DOC_DIR.

---

## Descripción

_Escribir aquí._

---

## Uso

_Escribir aquí._
EOF

    printf 'Creado: %s\n' "$file"
    return 0
}

# ---------------------------------------------------------------
# Comando: listar/validar docs en doc/
# ---------------------------------------------------------------
list_docs() {
    printf '\nDocumentación en %s:\n' "$DOC_DIR"

    if [ ! -d "$DOC_DIR" ]; then
        warn "falta la carpeta $DOC_DIR"
        return 1
    fi

    local docs
    docs="$(find "$DOC_DIR" -name '*.md' -type f | sort)"

    if [ -z "$docs" ]; then
        warn "no hay archivos .md en $DOC_DIR"
        return 1
    fi

    printf '%s\n' "$docs" | while read -r d; do
        ok "${d#$PLUGIN_DIR/}"
    done

    return 0
}

# ---------------------------------------------------------------
# Validación de referencias a documentación
# ---------------------------------------------------------------
validate_doc_refs() {
    printf '\n---- Validando referencias a documentación (%s) ----\n' "$DOC_DIR"

    if [ ! -d "$DOC_DIR" ]; then
        fail "no existe la carpeta de documentación: $DOC_DIR"
        return
    fi

    local refs
    refs="$(grep -rhoE 'DOC-[A-Za-z0-9_.-]+\.md' \
        "$PLUGIN_DIR/AGENTS.md" "$PLUGIN_DIR/CONTEXT.md" "$PLUGIN_DIR/HOOKS.md" \
        "$PLUGIN_DIR/README.md" "$PLUGIN_DIR/CHANGELOG.md" "$PLUGIN_DIR/.rules" 2>/dev/null \
        | sort -u)"

    if [ -z "$refs" ]; then
        warn 'no se encontraron referencias a DOC-*.md'
        return
    fi

    printf '%s\n' "$refs" | while read -r ref; do
        if [ -f "$DOC_DIR/$ref" ]; then
            ok "$ref -> existe en doc/"
        else
            fail "$ref referenciado pero no existe en doc/$ref"
        fi
    done
}

# ---------------------------------------------------------------
# Validaciones estándar del proyecto
# ---------------------------------------------------------------
run_lint() {
    printf '\n---- php -l (excluyendo %s) ----\n' "$LIB_DIR"

    local files
    files="$(find "$PLUGIN_DIR/src" "$PLUGIN_DIR/index.php" "$PLUGIN_DIR/composer.json" -name '*.php' -type f 2>/dev/null | sort)"

    if [ -z "$files" ]; then
        fail 'no se encontraron archivos PHP'
        return
    fi

    local err
    while read -r f; do
        if command -v php >/dev/null 2>&1; then
            if ! php -l "$f" >/dev/null 2>&1; then
                php -l "$f"
                fail "$(basename "$f") no compila"
            fi
        else
            warn 'php no está disponible: se omite php -l'
            break
        fi
    done <<< "$files"
}

run_php_syntax() {
    printf '\n---- Sintaxis PHP <= 7.0 ----\n'

    local files
    files="$(find "$PLUGIN_DIR/src" -name '*.php' -type f 2>/dev/null | grep -v "^$PLUGIN_DIR/libs/" | sort)"

    if [ -z "$files" ]; then
        warn 'no se encontraron archivos PHP'
        return
    fi

    while read -r f; do
        if has_modern_php_syntax "$f"; then
            local why
            why="$(has_modern_php_syntax "$f")"
            fail "$(basename "$f"): $why"
        fi
    done <<< "$files"
}

run_js_syntax() {
    printf '\n---- JavaScript ES5 ----\n'

    local files
    files="$(find "$PLUGIN_DIR/src" -name '*.js' -type f 2>/dev/null | grep -v "^$PLUGIN_DIR/libs/" | sort)"

    if [ "$files" != "" ]; then
        while read -r f; do
            if has_modern_js_syntax "$f"; then
                local why
                why="$(has_modern_js_syntax "$f")"
                fail "$(basename "$f"): $why"
            fi
        done <<< "$files"
    fi

    # JS embebido en archivos PHP de páginas/secciones
    files="$(find "$PLUGIN_DIR/src/page" -name '*.php' -type f 2>/dev/null | sort)"
    while read -r f; do
        if has_modern_js_syntax "$f"; then
            local why js
            why="$(has_modern_js_syntax "$f")"
            warn "$(basename "$f"): posible JS moderno en PHP ($why)"
        fi
    done <<< "$files"
}

run_css_prefixes() {
    printf '\n---- Prefijos CSS (gpai- / fwue-) ----\n'

    local files
    files="$(find "$PLUGIN_DIR/src" -name '*.css' -type f 2>/dev/null | sort)"

    if [ -z "$files" ]; then
        warn 'no se encontraron archivos CSS'
        return
    fi

    while read -r f; do
        local bad
        bad="$(grep -nE '\.(btn|input|modal|tooltip|collapse|table|form|panel|title|body|wrap|content|field|label|row|box|text|block|button|icon)([^a-z-]|$)' "$f" | grep -vE '\.gpai-|\.fwue-|/\*|@import' | head -5)"
        if [ -n "$bad" ]; then
            fail "$(basename "$f") tiene selectores sin prefijo gpai-/fwue-"
            printf '%s\n' "$bad" | while read -r b; do warn "    $b"; done
        fi
    done <<< "$files"
}

run_required_classes() {
    printf '\n---- Clases requeridas (prefijo GPAI_) ----\n'

    local required=(
        'GPAI_AI'
        'GPAI_CONTENT'
        'GPAI_PROMPT'
        'GPAI_AI_HARNESS'
        'GPAI_CF'
        'GPAI_YOAST'
        'GPAI_SEO'
        'GPAI_EXPORT_IMPORT'
        'GPAI_SITEMAPS_API'
        'GPAI_IMAGENES'
        'GPAI_ANALISIS'
        'GPAI_REEMPLAZAR'
        'GPAI_API_SEO'
        'GPAI_API_CF'
        'GPAI_API_GF'
        'GPAI_USE_DATA_BASE'
        'GPAI_USE_DATA_CONFIG'
        'GPAI_USE_DATA_DUPLICADOS'
        'GPAI_USE_DATA_SITEMAPS'
        'GPAI_USE_DATA_HTACCESS'
        'GPAI_USE_DATA_GLOBAL_FIELDS'
    )

    local cls
    for cls in "${required[@]}"; do
        if grep -rq "class $cls" "$PLUGIN_DIR/src"; then
            ok "class $cls"
        else
            fail "falta la clase $cls"
        fi
    done
}

run_requires() {
    printf '\n---- require_once en cargadores (src/**/_.php) ----\n'

    local loaders
    loaders="$(find "$PLUGIN_DIR/src" -name '_.php' -type f 2>/dev/null | sort)"

    if [ -z "$loaders" ]; then
        warn 'no se encontraron cargadores _.php'
        return
    fi

    while read -r loader; do
        local targets
        targets="$(grep -oE "require_once[[:space:]]+GPAI_DIR[[:space:]]*.[[:space:]]*'[^']+'" "$loader" | sed -E "s/.*'([^']+)'.*/\1/")"

        if [ -z "$targets" ]; then
            warn "$(basename "$(dirname "$loader")"): sin require_once con GPAI_DIR"
            continue
        fi

        printf '%s\n' "$targets" | while read -r t; do
            if [ -f "$PLUGIN_DIR/$t" ]; then
                ok "${t#$PLUGIN_DIR/}"
            else
                fail "${loader#$PLUGIN_DIR/}: objetivo inexistente ${t#$PLUGIN_DIR/}"
            fi
        done
    done <<< "$loaders"
}

run_composer() {
    printf '\n---- composer validate ----\n'

    if [ -f "$PLUGIN_DIR/composer.json" ] && command -v composer >/dev/null 2>&1; then
        composer validate --no-check-publish "$PLUGIN_DIR/composer.json" >/dev/null 2>&1 && \
            ok 'composer.json válido' || \
            fail 'composer.json inválido'
    else
        warn 'composer no disponible: se omite composer validate'
    fi
}

# ---------------------------------------------------------------
# Modo de ejecución
# ---------------------------------------------------------------
case "${1:-}" in
    doc)
        if [ $# -ge 2 ]; then
            create_doc "$2"
        else
            list_docs
        fi
        ;;
    *)
        run_lint
        run_php_syntax
        run_js_syntax
        run_css_prefixes
        run_required_classes
        run_requires
        run_composer
        validate_doc_refs
        ;;
esac

printf '\n-----------------------------\n'
printf '  FAILURES: %d | WARNINGS: %d\n' "$FAILURES" "$WARNINGS"
printf '-----------------------------\n'

if [ "$FAILURES" -gt 0 ]; then
    exit 1
fi
exit 0