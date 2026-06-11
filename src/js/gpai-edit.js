(function ($) {
  "use strict";

  var postId = null;
  var currentView = "list";
  var editingKey = null;
  var fieldsData = [];
  var searchQuery = "";
  var currentTab = "all";
  var templateFieldsData = [];
  var templateFieldsLoaded = false;
  var editingTemplateKey = null;
  var editingTemplateDefault = null;
  var currentElementKeys = [];
  var sectionClickEnabled = false;

  function addToggleButton() {
    if ($(".gpai-edit-toggle-btn").length) return;

    var $btn = $(
      '<div class="gpai-edit-toggle-btn" title="Campos personalizados">' +
        '  <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-7 2h5v5h-5V6zm-2 0v5H6V6h5zM6 14h5v5H6v-5zm7 5v-5h5v5h-5z"/></svg>' +
        "  <span>Campos</span>" +
        "</div>",
    );

    $btn.on("click", function () {
      var $panel = $("#gpai-edit-panel");
      if ($panel.hasClass("gpai-edit-panel-open")) {
        closePanel();
      } else {
        openPanel();
      }
    });

    $("body").append($btn);
  }

  function createPanelHTML() {
    if ($("#gpai-edit-panel").length) return;

    var html =
      '<div id="gpai-edit-panel" class="gpai-edit-panel">' +
      '  <div class="gpai-edit-panel-header">' +
      '    <div class="gpai-edit-panel-header-drag">Campos personalizados</div>' +
      '    <button class="gpai-edit-panel-close" title="Cerrar">&times;</button>' +
      "  </div>" +
      '  <div class="gpai-edit-panel-body">' +
      '    <div class="gpai-edit-panel-list-view">' +
      '      <div class="gpai-edit-panel-toolbar">' +
      '        <input type="text" class="gpai-edit-search" placeholder="Buscar campo..." />' +
      '        <div class="gpai-edit-tabs">' +
      '          <button class="gpai-edit-tab gpai-edit-tab-active" data-tab="all">Todos</button>' +
      '          <button class="gpai-edit-tab" data-tab="section">Secci\u00f3n</button>' +
      '          <button class="gpai-edit-tab" data-tab="templates">Plantillas</button>' +
      "        </div>" +
      '        <button class="gpai-edit-btn-create gpai-edit-btn gpai-edit-btn-primary">+ Nuevo</button>' +
      "      </div>" +
      '      <div class="gpai-edit-panel-items"></div>' +
      '      <div class="gpai-edit-panel-empty">No hay campos personalizados.</div>' +
      "    </div>" +
      '    <div class="gpai-edit-panel-form-view" style="display:none">' +
      '      <div class="gpai-edit-form-group">' +
      "        <label>Clave</label>" +
      '        <div class="gpai-edit-key-input-wrapper">' +
      '          <span class="gpai-edit-brace">{{</span>' +
      '          <input type="text" id="gpai-edit-form-key" placeholder="nombre_del_campo" autocomplete="off" />' +
      '          <span class="gpai-edit-brace">}}</span>' +
      "        </div>" +
      "      </div>" +
      '      <div class="gpai-edit-form-group">' +
      "        <label>Valor</label>" +
      '        <textarea id="gpai-edit-form-value" placeholder="Valor que se mostrar\u00e1 en el frontend" rows="4"></textarea>' +
      "      </div>" +
      '      <div class="gpai-edit-form-actions">' +
      '        <button class="gpai-edit-form-cancel gpai-edit-btn gpai-edit-btn-default">Cancelar</button>' +
      '        <button class="gpai-edit-form-save gpai-edit-btn gpai-edit-btn-primary">Guardar</button>' +
      "      </div>" +
      "    </div>" +
      "  </div>" +
      "</div>";

    $("body").append(html);
  }

  function bindPanelEvents() {
    var $panel = $("#gpai-edit-panel");
    if ($panel.data("gpai-bound")) return;
    $panel.data("gpai-bound", true);

    $panel.on("click", ".gpai-edit-panel-close", closePanel);

    $panel.on("click", ".gpai-edit-btn-create", function () {
      showForm(null, null);
    });

    $panel.on("click", ".gpai-edit-form-cancel", showList);

    $panel.on("click", ".gpai-edit-form-save", handleSave);

    $panel.on("click", ".gpai-edit-template-form-cancel", cancelTemplateForm);

    $panel.on("click", ".gpai-edit-template-form-save", handleTemplateSave);

    $panel.on("input", ".gpai-edit-search", function () {
      searchQuery = $(this).val().toLowerCase().trim();
      renderList();
    });

    $panel.on("click", ".gpai-edit-tab", function () {
      $panel.find(".gpai-edit-tab").removeClass("gpai-edit-tab-active");
      $(this).addClass("gpai-edit-tab-active");
      currentTab = $(this).data("tab");
      renderList();
    });

    $panel.on("click", ".gpai-edit-field-edit", function () {
      var k = $(this).data("key");
      if (currentTab === "templates") {
        var d = $(this).data("default") || "";
        var c = $(this).data("current") || "";
        showTemplateForm(k, d, c);
      } else {
        var v = "";
        fieldsData.forEach(function (f) {
          if (f.key === k) v = f.value;
        });
        showForm(k, v);
      }
    });

    $panel.on("click", ".gpai-edit-field-delete", function () {
      var k = $(this).data("key");
      if (confirm("\u00BFEliminar el campo {{" + k + "}}?")) {
        deleteField(k);
      }
    });

    makeDraggable($panel);

    $(document).on("keydown", "#gpai-edit-form-key, #gpai-edit-form-value", function (e) {
      if (
        e.key === "Enter" &&
        !e.shiftKey &&
        $(this).is("#gpai-edit-form-value")
      ) {
        e.preventDefault();
        handleSave();
      }
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        $("#gpai-edit-form-value").focus();
      }
    });
  }

  function openPanel() {
    if (!postId) {
      alert("No se pudo obtener el ID de la p\u00e1gina.");
      return;
    }
    createPanelHTML();
    bindPanelEvents();
    $("#gpai-edit-panel").addClass("gpai-edit-panel-open");
    templateFieldsLoaded = false;
    showList();
  }

  function closePanel() {
    $("#gpai-edit-panel").removeClass("gpai-edit-panel-open");
    currentView = "list";
    editingKey = null;
  }

  function showList() {
    hideTemplateForm();
    currentView = "list";
    editingKey = null;

    var $panel = $("#gpai-edit-panel");
    $panel.find(".gpai-edit-panel-list-view").show();
    $panel.find(".gpai-edit-panel-form-view").hide();
    $panel
      .find(".gpai-edit-panel-items")
      .html('<div class="gpai-edit-panel-loading">Cargando...</div>');

    $.ajax({
      url: gpaiEdit.ajaxurl,
      type: "POST",
      data: {
        action: "gpai_list_custom_fields",
        post_id: postId,
      },
      success: function (response) {
        if (response.success) {
          fieldsData = response.data || [];
          renderList();
        } else {
          $panel
            .find(".gpai-edit-panel-items")
            .html(
              '<div class="gpai-edit-panel-error">Error al cargar: ' +
                (response.data || "desconocido") +
                "</div>",
            );
        }
      },
      error: function () {
        $panel
          .find(".gpai-edit-panel-items")
          .html('<div class="gpai-edit-panel-error">Error de conexi\u00f3n.</div>');
      },
    });
  }

  function renderList() {
    hideTemplateForm();
    var $items = $("#gpai-edit-panel .gpai-edit-panel-items");
    var $empty = $("#gpai-edit-panel .gpai-edit-panel-empty");

    if (currentTab === "templates") {
      if (!templateFieldsLoaded) {
        loadTemplateFields();
      } else {
        renderTemplateList();
      }
      return;
    }

    var filtered = fieldsData;

    if (currentTab === "section") {
      if (currentElementKeys.length > 0) {
        var existingKeysMap = {};
        filtered = fieldsData.filter(function (f) {
          existingKeysMap[f.key] = true;
          return currentElementKeys.indexOf(f.key) !== -1;
        });
        currentElementKeys.forEach(function (k) {
          if (!existingKeysMap[k]) {
            filtered.push({
              key: k,
              value: "",
              type: "custom",
              _missing: true,
            });
          }
        });
      } else {
        $items.empty();
        if (sectionClickEnabled) {
          $empty.show().text("Haz clic en un elemento de la p\u00e1gina para ver sus campos.");
        } else {
          $empty.show().text("Agrega &amp;GPAI_CUSTOM_FIELDS_DISABLE en la URL para usar esta funci\u00f3n.");
        }
        return;
      }
    }

    if (searchQuery) {
      filtered = filtered.filter(function (f) {
        return f.key.toLowerCase().indexOf(searchQuery) !== -1;
      });
    }

    if (!filtered.length) {
      $items.empty();
      if (searchQuery) {
        $empty
          .show()
          .text(
            'No se encontraron campos con "' + searchQuery + '".',
          );
      } else {
        $empty.show().text("No hay campos personalizados.");
      }
      return;
    }

    $empty.hide();
    $items.empty();

    filtered.forEach(function (field) {
      var key = field.key;
      var value = field.value || "";
      var fieldType = field.type || "custom";
      var displayValue =
        value.length > 80
          ? value.substring(0, 80) + "..."
          : value;

      var displayKey, keyClass;
      if (fieldType === "global") {
        displayKey = "{g{" + key + "}}";
        keyClass = "gpai-edit-field-key gpai-edit-field-key-global";
      } else {
        displayKey = "{{" + key + "}}";
        keyClass = "gpai-edit-field-key";
      }

      var $row = $(
        '<div class="gpai-edit-field-row">' +
          '  <div class="gpai-edit-field-info">' +
          '    <code class="' +
          keyClass +
          '">' +
          escapeHtml(displayKey) +
          "</code>" +
          '    <span class="gpai-edit-field-value">' +
          escapeHtml(displayValue) +
          "</span>" +
          "  </div>" +
          '  <div class="gpai-edit-field-actions">' +
          '    <button class="gpai-edit-field-edit" data-key="' +
          key +
          '" data-type="' +
          fieldType +
          '" title="Editar">\u270E</button>' +
          '    <button class="gpai-edit-field-delete" data-key="' +
          key +
          '" title="Eliminar">\u2715</button>' +
          "  </div>" +
          "</div>",
      );

      $items.append($row);
    });
  }

  function showForm(key, value) {
    currentView = "form";
    editingKey = key || null;

    var $panel = $("#gpai-edit-panel");
    $panel.find(".gpai-edit-panel-list-view").hide();
    $panel.find(".gpai-edit-panel-form-view").show();

    $panel.find(".gpai-edit-form-save").text(key ? "Actualizar" : "Crear");

    if (key) {
      $("#gpai-edit-form-key").val(key);
      $("#gpai-edit-form-value").val(value || "");
    } else {
      $("#gpai-edit-form-key").val("");
      $("#gpai-edit-form-value").val("");
    }

    $("#gpai-edit-form-key").focus();
  }

  function handleSave() {
    var key = $("#gpai-edit-form-key").val().trim();
    var value = $("#gpai-edit-form-value").val().trim();

    key = key.replace(/[{}]/g, "").trim();

    if (!key) {
      alert("Por favor ingresa una clave.");
      $("#gpai-edit-form-key").focus();
      return;
    }
    if (!value) {
      alert("Por favor ingresa un valor.");
      $("#gpai-edit-form-value").focus();
      return;
    }

    var $saveBtn = $("#gpai-edit-panel .gpai-edit-form-save");
    $saveBtn.prop("disabled", true).text("Guardando...");

    $.ajax({
      url: gpaiEdit.ajaxurl,
      type: "POST",
      data: {
        action: "gpai_save_custom_field",
        post_id: postId,
        key: key,
        value: value,
      },
      success: function (response) {
        $saveBtn
          .prop("disabled", false)
          .text(editingKey ? "Actualizar" : "Crear");
        if (response.success) {
          showToast("Campo guardado correctamente", "success");
          showList();
        } else {
          alert("Error: " + (response.data || "No se pudo guardar."));
        }
      },
      error: function () {
        $saveBtn
          .prop("disabled", false)
          .text(editingKey ? "Actualizar" : "Crear");
        alert("Error de conexi\u00f3n. Intenta de nuevo.");
      },
    });
  }

  function deleteField(key) {
    $.ajax({
      url: gpaiEdit.ajaxurl,
      type: "POST",
      data: {
        action: "gpai_delete_custom_field",
        post_id: postId,
        key: key,
      },
      success: function (response) {
        if (response.success) {
          showToast("Campo eliminado", "success");
          showList();
        } else {
          alert("Error: " + (response.data || "No se pudo eliminar."));
        }
      },
      error: function () {
        alert("Error de conexi\u00f3n. Intenta de nuevo.");
      },
    });
  }

  function loadTemplateFields() {
    var $items = $("#gpai-edit-panel .gpai-edit-panel-items");
    var $empty = $("#gpai-edit-panel .gpai-edit-panel-empty");
    $empty.hide();
    $items.html(
      '<div class="gpai-edit-panel-loading">Cargando campos de plantilla...</div>',
    );

    $.ajax({
      url: gpaiEdit.ajaxurl,
      type: "POST",
      data: {
        action: "gpai_list_template_fields",
        post_id: postId,
      },
      success: function (response) {
        if (response.success) {
          templateFieldsData = response.data || [];
          templateFieldsLoaded = true;
          renderTemplateList();
        } else {
          $items.html(
            '<div class="gpai-edit-panel-error">Error: ' +
              (response.data || "desconocido") +
              "</div>",
          );
        }
      },
      error: function () {
        $items.html(
          '<div class="gpai-edit-panel-error">Error de conexi\u00f3n.</div>',
        );
      },
    });
  }

  function renderTemplateList() {
    var $items = $("#gpai-edit-panel .gpai-edit-panel-items");
    var $empty = $("#gpai-edit-panel .gpai-edit-panel-empty");

    var filtered = templateFieldsData;
    if (searchQuery) {
      filtered = filtered.filter(function (f) {
        return f.key.toLowerCase().indexOf(searchQuery) !== -1;
      });
    }

    if (!filtered.length) {
      $items.empty();
      if (searchQuery) {
        $empty
          .show()
          .text(
            'No se encontraron campos con "' + searchQuery + '".',
          );
      } else {
        $empty
          .show()
          .text("No hay campos de plantilla disponibles.");
      }
      return;
    }

    $empty.hide();
    $items.empty();

    filtered.forEach(function (field) {
      var key = field.key;
      var defaultVal = field.default_value || "";
      var currentVal = field.current_value || "";
      var displayVal = currentVal || defaultVal;
      var displayClass = currentVal
        ? "gpai-edit-template-current-value"
        : "gpai-edit-template-current-placeholder";

      var $row = $(
        '<div class="gpai-edit-field-row">' +
          '  <div class="gpai-edit-field-info">' +
          '    <code class="gpai-edit-field-key gpai-edit-field-key-global">{g{' +
          escapeHtml(key) +
          "}}</code>" +
          '    <div class="gpai-edit-template-default"><span class="gpai-edit-template-default-label">Valor plantilla:</span> <span class="gpai-edit-template-default-value">' +
          escapeHtml(defaultVal) +
          "</span></div>" +
          '    <div class="gpai-edit-template-current"><span class="gpai-edit-template-current-label">Valor actual:</span> <span class="' +
          displayClass +
          '">' +
          escapeHtml(displayVal) +
          "</span></div>" +
          "  </div>" +
          '  <div class="gpai-edit-field-actions">' +
          '    <button class="gpai-edit-field-edit" data-key="' +
          key +
          '" data-default="' +
          escapeHtml(defaultVal) +
          '" data-current="' +
          escapeHtml(currentVal) +
          '" title="Editar">\u270E</button>' +
          "  </div>" +
          "</div>",
      );

      $items.append($row);
    });
  }

  function showTemplateForm(key, defaultVal, currentVal) {
    editingTemplateKey = key;
    editingTemplateDefault = defaultVal;

    var $panel = $("#gpai-edit-panel");
    $panel.find(".gpai-edit-panel-list-view").hide();
    $panel.find(".gpai-edit-panel-template-form-view").remove();

    var $form = $(
      '<div class="gpai-edit-panel-template-form-view">' +
        '    <div class="gpai-edit-form-group">' +
        "      <label>Clave</label>" +
        '      <div class="gpai-edit-key-input-wrapper">' +
        '        <span class="gpai-edit-brace">{g{</span>' +
        '        <input type="text" value="' +
        escapeHtml(key) +
        '" readonly class="gpai-edit-template-form-key" />' +
        '        <span class="gpai-edit-brace">}}</span>' +
        "      </div>" +
        "    </div>" +
        '    <div class="gpai-edit-form-group">' +
        "      <label>Valor plantilla</label>" +
        '      <div class="gpai-edit-template-form-default">' +
        escapeHtml(defaultVal) +
        "</div>" +
        "    </div>" +
        '    <div class="gpai-edit-form-group">' +
        "      <label>Valor actual</label>" +
        '      <textarea class="gpai-edit-template-form-value" placeholder="' +
        escapeHtml(defaultVal) +
        '" rows="4">' +
        escapeHtml(currentVal) +
        "</textarea>" +
        "    </div>" +
        '    <div class="gpai-edit-form-actions">' +
        '      <button class="gpai-edit-template-form-cancel gpai-edit-btn gpai-edit-btn-default">Cancelar</button>' +
        '      <button class="gpai-edit-template-form-save gpai-edit-btn gpai-edit-btn-primary">Guardar</button>' +
        "    </div>" +
        "</div>",
    );

    $panel.find(".gpai-edit-panel-body").append($form);

    $form.find(".gpai-edit-template-form-value").on("keydown", function (e) {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        handleTemplateSave();
      }
    });

    $form.find(".gpai-edit-template-form-value").focus();
  }

  function cancelTemplateForm() {
    $("#gpai-edit-panel .gpai-edit-panel-template-form-view").remove();
    editingTemplateKey = null;
    editingTemplateDefault = null;
    $("#gpai-edit-panel .gpai-edit-panel-list-view").show();
    renderTemplateList();
  }

  function handleTemplateSave() {
    var value = $("#gpai-edit-panel .gpai-edit-template-form-value").val().trim();
    var key = editingTemplateKey;

    if (!key) return;

    var $saveBtn = $("#gpai-edit-panel .gpai-edit-template-form-save");
    $saveBtn.prop("disabled", true).text("Guardando...");

    $.ajax({
      url: gpaiEdit.ajaxurl,
      type: "POST",
      data: {
        action: "gpai_save_global_field",
        post_id: postId,
        key: key,
        value: value,
      },
      success: function (response) {
        $saveBtn.prop("disabled", false).text("Guardar");
        if (response.success) {
          templateFieldsData.forEach(function (f) {
            if (f.key === key) {
              f.current_value = value;
            }
          });
          showToast("Campo global guardado", "success");
          cancelTemplateForm();
        } else {
          alert("Error: " + (response.data || "No se pudo guardar."));
        }
      },
      error: function () {
        $saveBtn.prop("disabled", false).text("Guardar");
        alert("Error de conexi\u00f3n. Intenta de nuevo.");
      },
    });
  }

  function hideTemplateForm() {
    $("#gpai-edit-panel .gpai-edit-panel-template-form-view").remove();
    editingTemplateKey = null;
    editingTemplateDefault = null;
  }

  function makeDraggable($el) {
    var $handle = $el.find(".gpai-edit-panel-header-drag");
    var startX, startY, origX, origY;
    var dragging = false;

    $handle.on("mousedown", function (e) {
      if ($(e.target).closest("button").length) return;
      dragging = true;
      var offset = $el.offset();
      startX = e.clientX;
      startY = e.clientY;
      origX = offset.left;
      origY = offset.top;
      $el.addClass("gpai-edit-panel-dragging");
      e.preventDefault();
    });

    $(document).on("mousemove", function (e) {
      if (!dragging) return;
      var dx = e.clientX - startX;
      var dy = e.clientY - startY;
      $el.css({
        left: origX + dx,
        top: origY + dy,
      });
    });

    $(document).on("mouseup", function () {
      if (dragging) {
        dragging = false;
        $el.removeClass("gpai-edit-panel-dragging");
      }
    });
  }

  function showToast(message, type) {
    $(".gpai-edit-toast").remove();
    var $toast = $(
      '<div class="gpai-edit-toast gpai-edit-toast-' +
        type +
        '">' +
        escapeHtml(message) +
        "</div>",
    );
    $("body").append($toast);
    setTimeout(function () {
      $toast.fadeOut(300, function () {
        $toast.remove();
      });
    }, 2000);
  }

  function escapeHtml(str) {
    var div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }

  function findKeysInHtml(html) {
    var result = { custom: {}, global: {} };
    if (!html) return result;

    var match;
    var regex = /\{\{(.*?)\}\}/g;
    while ((match = regex.exec(html)) !== null) {
      var k = match[1].trim();
      if (k) result.custom[k] = true;
    }

    regex = /\{g\{(.*?)\}\}/g;
    while ((match = regex.exec(html)) !== null) {
      var gk = match[1].trim();
      if (gk) result.global[gk] = true;
    }

    return result;
  }

  function setupSectionClickHandler() {
    if (!gpaiEdit.custom_fields_disabled) return;
    sectionClickEnabled = true;

    $(document).on("click", function (e) {
      if ($(e.target).closest("#gpai-edit-panel, .gpai-edit-toggle-btn").length) return;

      var target = e.target;
      if (!target || !target.innerHTML) return;

      var html = target.innerHTML;
      var foundKeys = findKeysInHtml(html);
      currentElementKeys = Object.keys(foundKeys.custom).concat(Object.keys(foundKeys.global));

      $(".gpai-edit-highlight").removeClass("gpai-edit-highlight");
      $(target).addClass("gpai-edit-highlight");

      if (currentTab === "section") {
        renderList();
      }
    });
  }

  function init() {
    if (typeof gpaiEdit === "undefined" || !gpaiEdit.post_id) {
      return;
    }
    postId = gpaiEdit.post_id;
    addToggleButton();
    setupSectionClickHandler();
  }

  $(init);
})(jQuery);
