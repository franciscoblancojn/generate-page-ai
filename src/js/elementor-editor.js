(function ($) {
  "use strict";

  var postId = null;
  var currentView = "list";
  var editingKey = null;
  var fieldsData = [];
  var currentElementKeys = [];
  var currentTab = "all";
  var searchQuery = "";
  var templateFieldsData = [];
  var templateFieldsLoaded = false;
  var editingTemplateKey = null;
  var editingTemplateDefault = null;

  function getPostId() {
    try {
      return elementor.documents.getCurrent().id;
    } catch (e) {
      return null;
    }
  }

  function addToggleButton() {
    var $wrapper = $("#elementor-editor-wrapper-v2");

    if (!$wrapper.length) return;
    if ($wrapper.find(".gpai-cf-toggle-btn").length) return;

    var $btn = $(
      '<div class="gpai-cf-toggle-btn" title="Campos personalizados">' +
        '  <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm-7 2h5v5h-5V6zm-2 0v5H6V6h5zM6 14h5v5H6v-5zm7 5v-5h5v5h-5z"/></svg>' +
        "  <span>Campos</span>" +
        "</div>",
    );

    $btn.on("click", function () {
      var $panel = $("#gpai-cf-panel");
      if ($panel.hasClass("gpai-cf-panel-open")) {
        closePanel();
      } else {
        openPanel();
      }
    });

    $wrapper.append($btn);
  }

  function createPanelHTML() {
    if ($("#gpai-cf-panel").length) return;

    var html =
      '<div id="gpai-cf-panel" class="gpai-cf-panel">' +
      '  <div class="gpai-cf-panel-header">' +
      '    <div class="gpai-cf-panel-header-drag">Campos personalizados</div>' +
      '    <button class="gpai-cf-panel-close" title="Cerrar">&times;</button>' +
      "  </div>" +
      '  <div class="gpai-cf-panel-body">' +
      '    <div class="gpai-cf-panel-list-view">' +
      '      <div class="gpai-cf-panel-toolbar">' +
      '        <input type="text" class="gpai-cf-search" placeholder="Buscar campo..." />' +
      '        <div class="gpai-cf-tabs">' +
      '          <button class="gpai-cf-tab gpai-cf-tab-active" data-tab="all">Todos</button>' +
      '          <button class="gpai-cf-tab" data-tab="section">Secci\u00f3n actual</button>' +
      '          <button class="gpai-cf-tab" data-tab="templates">Plantillas</button>' +
      "        </div>" +
      '        <button class="gpai-cf-btn-create elementor-button elementor-button-primary elementor-size-xs">+ Nuevo</button>' +
      "      </div>" +
      '      <div class="gpai-cf-panel-items"></div>' +
      '      <div class="gpai-cf-panel-empty">No hay campos personalizados.</div>' +
      "    </div>" +
      '    <div class="gpai-cf-panel-form-view" style="display:none">' +
      '      <div class="gpai-cf-form-group">' +
      "        <label>Clave</label>" +
      '        <div class="gpai-cf-key-input-wrapper">' +
      '          <span class="gpai-cf-brace">{{</span>' +
      '          <input type="text" id="gpai-cf-form-key" placeholder="nombre_del_campo" autocomplete="off" />' +
      '          <span class="gpai-cf-brace">}}</span>' +
      "        </div>" +
      "      </div>" +
      '      <div class="gpai-cf-form-group">' +
      "        <label>Valor</label>" +
      '        <textarea id="gpai-cf-form-value" placeholder="Valor que se mostrar\u00e1 en el frontend" rows="4"></textarea>' +
      "      </div>" +
      '      <div class="gpai-cf-form-actions">' +
      '        <button class="gpai-cf-form-cancel elementor-button elementor-button-default elementor-size-xs">Cancelar</button>' +
      '        <button class="gpai-cf-form-save elementor-button elementor-button-primary elementor-size-xs">Guardar</button>' +
      "      </div>" +
      "    </div>" +
      "  </div>" +
      "</div>";

    $("body").append(html);

    $("#gpai-cf-panel .gpai-cf-panel-close").on("click", closePanel);
    $("#gpai-cf-panel .gpai-cf-btn-create").on("click", function () {
      showForm(null);
    });
    $("#gpai-cf-panel .gpai-cf-form-cancel").on("click", showList);
    $("#gpai-cf-panel .gpai-cf-form-save").on("click", handleSave);

    $("#gpai-cf-panel .gpai-cf-search").on("input", function () {
      searchQuery = $(this).val().toLowerCase().trim();
      renderList();
    });

    $("#gpai-cf-panel .gpai-cf-tab").on("click", function () {
      $(".gpai-cf-tab").removeClass("gpai-cf-tab-active");
      $(this).addClass("gpai-cf-tab-active");
      currentTab = $(this).data("tab");
      renderList();
    });

    makeDraggable($("#gpai-cf-panel"));

    $("#gpai-cf-form-key, #gpai-cf-form-value").on("keydown", function (e) {
      if (
        e.key === "Enter" &&
        !e.shiftKey &&
        $(this).is("#gpai-cf-form-value")
      ) {
        e.preventDefault();
        handleSave();
      }
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        $("#gpai-cf-form-value").focus();
      }
    });
  }

  function openPanel() {
    postId = getPostId();
    if (!postId) {
      alert("No se pudo obtener el ID de la p\u00e1gina.");
      return;
    }
    createPanelHTML();
    $("#gpai-cf-panel").addClass("gpai-cf-panel-open");
    setupPreviewClickHandler();
    templateFieldsLoaded = false;
    showList();
  }

  function closePanel() {
    $("#gpai-cf-panel").removeClass("gpai-cf-panel-open");
    currentView = "list";
    editingKey = null;
  }

  function showList() {
    hideTemplateForm();
    currentView = "list";
    editingKey = null;

    var $panel = $("#gpai-cf-panel");
    $panel.find(".gpai-cf-panel-list-view").show();
    $panel.find(".gpai-cf-panel-form-view").hide();
    $panel
      .find(".gpai-cf-panel-items")
      .html('<div class="gpai-cf-panel-loading">Cargando...</div>');

    $.ajax({
      url: gpaiEditor.ajaxurl,
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
            .find(".gpai-cf-panel-items")
            .html(
              '<div class="gpai-cf-panel-error">Error al cargar: ' +
                (response.data || "desconocido") +
                "</div>",
            );
        }
      },
      error: function () {
        $panel
          .find(".gpai-cf-panel-items")
          .html('<div class="gpai-cf-panel-error">Error de conexi\u00f3n.</div>');
      },
    });
  }

  function renderList() {
    hideTemplateForm();
    var $items = $("#gpai-cf-panel .gpai-cf-panel-items");
    var $empty = $("#gpai-cf-panel .gpai-cf-panel-empty");

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
        filtered = filtered.filter(function (f) {
          return currentElementKeys.indexOf(f.key) !== -1;
        });
      } else {
        $items.empty();
        $empty
          .show()
          .text(
            "Selecciona un elemento en el editor para ver sus campos.",
          );
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
      if (currentTab === "section") {
        $empty
          .show()
          .text(
            "No hay campos personalizados en esta secci\u00f3n.",
          );
      } else if (searchQuery) {
        $empty
          .show()
          .text(
            "No se encontraron campos con \"" + searchQuery + "\".",
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
        value.length > 80 ? value.substring(0, 80) + "..." : value;

      var displayKey, keyClass;
      if (fieldType === "global") {
        displayKey = "{g{" + key + "}}";
        keyClass = "gpai-cf-field-key gpai-cf-field-key-global";
      } else {
        displayKey = "{{" + key + "}}";
        keyClass = "gpai-cf-field-key";
      }

      var $row = $(
        '<div class="gpai-cf-field-row">' +
          '  <div class="gpai-cf-field-info">' +
          '    <code class="' +
          keyClass +
          '">' +
          escapeHtml(displayKey) +
          "</code>" +
          '    <span class="gpai-cf-field-value">' +
          escapeHtml(displayValue) +
          "</span>" +
          "  </div>" +
          '  <div class="gpai-cf-field-actions">' +
          '    <button class="gpai-cf-field-edit" data-key="' +
          key +
          '" data-type="' +
          fieldType +
          '" title="Editar">\u270E</button>' +
          '    <button class="gpai-cf-field-delete" data-key="' +
          key +
          '" title="Eliminar">\u2715</button>' +
          "  </div>" +
          "</div>",
      );

      $row.find(".gpai-cf-field-edit").on("click", function () {
        var k = $(this).data("key");
        var v = "";
        fieldsData.forEach(function (f) {
          if (f.key === k) v = f.value;
        });
        showForm(k, v);
      });

      $row.find(".gpai-cf-field-delete").on("click", function () {
        var k = $(this).data("key");
        if (confirm("\u00BFEliminar el campo {{" + k + "}}?")) {
          deleteField(k);
        }
      });

      $items.append($row);
    });
  }

  function showForm(key, value) {
    currentView = "form";
    editingKey = key || null;

    var $panel = $("#gpai-cf-panel");
    $panel.find(".gpai-cf-panel-list-view").hide();
    $panel.find(".gpai-cf-panel-form-view").show();

    $panel.find(".gpai-cf-form-save").text(key ? "Actualizar" : "Crear");

    if (key) {
      $("#gpai-cf-form-key").val(key);
      $("#gpai-cf-form-value").val(value || "");
    } else {
      $("#gpai-cf-form-key").val("");
      $("#gpai-cf-form-value").val("");
    }

    $("#gpai-cf-form-key").focus();
  }

  function handleSave() {
    var key = $("#gpai-cf-form-key").val().trim();
    var value = $("#gpai-cf-form-value").val().trim();

    if (!key) {
      alert("Por favor ingresa una clave.");
      $("#gpai-cf-form-key").focus();
      return;
    }
    if (!value) {
      alert("Por favor ingresa un valor.");
      $("#gpai-cf-form-value").focus();
      return;
    }

    var $saveBtn = $("#gpai-cf-panel .gpai-cf-form-save");
    $saveBtn.prop("disabled", true).text("Guardando...");

    $.ajax({
      url: gpaiEditor.ajaxurl,
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
      url: gpaiEditor.ajaxurl,
      type: "POST",
      data: {
        action: "gpai_delete_custom_field",
        post_id: postId,
        key: key,
      },
      success: function (response) {
        if (response.success) {
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
    var $items = $("#gpai-cf-panel .gpai-cf-panel-items");
    var $empty = $("#gpai-cf-panel .gpai-cf-panel-empty");
    $empty.hide();
    $items.html(
      '<div class="gpai-cf-panel-loading">Cargando campos de plantilla...</div>',
    );

    $.ajax({
      url: gpaiEditor.ajaxurl,
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
            '<div class="gpai-cf-panel-error">Error: ' +
              (response.data || "desconocido") +
              "</div>",
          );
        }
      },
      error: function () {
        $items.html(
          '<div class="gpai-cf-panel-error">Error de conexi\u00f3n.</div>',
        );
      },
    });
  }

  function renderTemplateList() {
    var $items = $("#gpai-cf-panel .gpai-cf-panel-items");
    var $empty = $("#gpai-cf-panel .gpai-cf-panel-empty");

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
          .text(
            "No hay campos de plantilla disponibles.",
          );
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
        ? "gpai-cf-template-current-value"
        : "gpai-cf-template-current-placeholder";

      var $row = $(
        '<div class="gpai-cf-field-row gpai-cf-template-row">' +
          '  <div class="gpai-cf-field-info">' +
          '    <code class="gpai-cf-field-key gpai-cf-field-key-global">{g{' +
          escapeHtml(key) +
          "}}</code>" +
          '    <div class="gpai-cf-template-default"><span class="gpai-cf-template-default-label">Valor plantilla:</span> <span class="gpai-cf-template-default-value">' +
          escapeHtml(defaultVal) +
          "</span></div>" +
          '    <div class="gpai-cf-template-current"><span class="gpai-cf-template-current-label">Valor actual:</span> <span class="' +
          displayClass +
          '">' +
          escapeHtml(displayVal) +
          "</span></div>" +
          "  </div>" +
          '  <div class="gpai-cf-field-actions">' +
          '    <button class="gpai-cf-field-edit" data-key="' +
          key +
          '" data-default="' +
          escapeHtml(defaultVal) +
          '" data-current="' +
          escapeHtml(currentVal) +
          '" title="Editar">\u270E</button>' +
          "  </div>" +
          "</div>",
      );

      $row.find(".gpai-cf-field-edit").on("click", function () {
        var k = $(this).data("key");
        var d = $(this).data("default");
        var c = $(this).data("current");
        showTemplateForm(k, d, c);
      });

      $items.append($row);
    });
  }

  function showTemplateForm(key, defaultVal, currentVal) {
    editingTemplateKey = key;
    editingTemplateDefault = defaultVal;

    var $panel = $("#gpai-cf-panel");
    $panel.find(".gpai-cf-panel-list-view").hide();
    $panel.find(".gpai-cf-panel-template-form-view").remove();

    var $form = $(
      '<div class="gpai-cf-panel-template-form-view">' +
        '    <div class="gpai-cf-form-group">' +
        "      <label>Clave</label>" +
        '      <div class="gpai-cf-key-input-wrapper">' +
        '        <span class="gpai-cf-brace">{g{</span>' +
        '        <input type="text" value="' +
        escapeHtml(key) +
        '" readonly class="gpai-cf-template-form-key" />' +
        '        <span class="gpai-cf-brace">}}</span>' +
        "      </div>" +
        "    </div>" +
        '    <div class="gpai-cf-form-group">' +
        "      <label>Valor plantilla</label>" +
        '      <div class="gpai-cf-template-form-default">' +
        escapeHtml(defaultVal) +
        "</div>" +
        "    </div>" +
        '    <div class="gpai-cf-form-group">' +
        "      <label>Valor actual</label>" +
        '      <textarea class="gpai-cf-template-form-value" placeholder="' +
        escapeHtml(defaultVal) +
        '" rows="4">' +
        escapeHtml(currentVal) +
        "</textarea>" +
        "    </div>" +
        '    <div class="gpai-cf-form-actions">' +
        '      <button class="gpai-cf-template-form-cancel elementor-button elementor-button-default elementor-size-xs">Cancelar</button>' +
        '      <button class="gpai-cf-template-form-save elementor-button elementor-button-primary elementor-size-xs">Guardar</button>' +
        "    </div>" +
        "</div>",
    );

    $panel.find(".gpai-cf-panel-body").append($form);

    $form.find(".gpai-cf-template-form-cancel").on("click", cancelTemplateForm);
    $form.find(".gpai-cf-template-form-save").on("click", handleTemplateSave);

    $form.find(".gpai-cf-template-form-value").on("keydown", function (e) {
      if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        handleTemplateSave();
      }
    });

    $form.find(".gpai-cf-template-form-value").focus();
  }

  function cancelTemplateForm() {
    $("#gpai-cf-panel .gpai-cf-panel-template-form-view").remove();
    editingTemplateKey = null;
    editingTemplateDefault = null;
    $("#gpai-cf-panel .gpai-cf-panel-list-view").show();
    renderTemplateList();
  }

  function handleTemplateSave() {
    var value = $("#gpai-cf-panel .gpai-cf-template-form-value").val().trim();
    var key = editingTemplateKey;

    if (!key) return;

    var $saveBtn = $("#gpai-cf-panel .gpai-cf-template-form-save");
    $saveBtn.prop("disabled", true).text("Guardando...");

    $.ajax({
      url: gpaiEditor.ajaxurl,
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
          cancelTemplateForm();
        } else {
          alert(
            "Error: " + (response.data || "No se pudo guardar."),
          );
        }
      },
      error: function () {
        $saveBtn.prop("disabled", false).text("Guardar");
        alert("Error de conexi\u00f3n. Intenta de nuevo.");
      },
    });
  }

  function hideTemplateForm() {
    $("#gpai-cf-panel .gpai-cf-panel-template-form-view").remove();
    editingTemplateKey = null;
    editingTemplateDefault = null;
  }

  function findKeysInHtml(html) {
    var keys = {};
    if (!html) return keys;

    var match;
    var regex = /\{\{(.*?)\}\}/g;
    while ((match = regex.exec(html)) !== null) {
      var k = match[1].trim();
      if (k) keys[k] = true;
    }

    regex = /\{g\{(.*?)\}\}/g;
    while ((match = regex.exec(html)) !== null) {
      var gk = match[1].trim();
      if (gk) keys[gk] = true;
    }

    return keys;
  }

  function setupPreviewClickHandler() {
    function attachListener() {
      try {
        var iframe = document.getElementById("elementor-preview-iframe");
        if (!iframe) return false;

        var doc =
          iframe.contentDocument || iframe.contentWindow.document;
        if (!doc || !doc.body) return false;

        doc.removeEventListener("click", handlePreviewClick, true);
        doc.addEventListener("click", handlePreviewClick, true);
        return true;
      } catch (e) {
        return false;
      }
    }

    if (!attachListener()) {
      var retries = 0;
      var maxRetries = 20;
      var interval = setInterval(function () {
        retries++;
        if (attachListener() || retries >= maxRetries) {
          clearInterval(interval);
        }
      }, 500);
    }
  }

  function handlePreviewClick(e) {
    try {
      var target = e.target;
      var el = target;
      var elementId = null;

      while (el && el.nodeType === 1) {
        var id = el.getAttribute
          ? el.getAttribute("data-id")
          : null;
        if (id) {
          elementId = id;
          break;
        }
        el = el.parentElement;
      }

      if (!elementId) return;

      var iframe = document.getElementById("elementor-preview-iframe");
      if (!iframe) return;

      var doc =
        iframe.contentDocument || iframe.contentWindow.document;
      var targetEl = doc.querySelector(
        '[data-id="' + elementId + '"]',
      );
      if (!targetEl) return;

      var html = targetEl.innerHTML;

      var foundKeys = findKeysInHtml(html);
      currentElementKeys = Object.keys(foundKeys);

      if (currentTab === "section") {
        renderList();
      }
    } catch (e) {}
  }

  function makeDraggable($el) {
    var $handle = $el.find(".gpai-cf-panel-header-drag");
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
      $el.addClass("gpai-cf-panel-dragging");
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
        $el.removeClass("gpai-cf-panel-dragging");
      }
    });
  }

  function escapeHtml(str) {
    var div = document.createElement("div");
    div.textContent = str;
    return div.innerHTML;
  }

  function init() {
    addToggleButton();
  }

  $(window).on("elementor:init", init);
})(jQuery);
