(function ($) {
  "use strict";

  var postId = null;
  var currentView = "list";
  var editingKey = null;
  var fieldsData = [];

  function getPostId() {
    try {
      return elementor.documents.getCurrent().id;
    } catch (e) {
      return null;
    }
  }

  function addToggleButton() {
    var $wrapper = $("#elementor-editor-wrapper-v2");
    console.log($wrapper);

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
      '        <button class="gpai-cf-btn-create elementor-button elementor-button-primary elementor-size-xs">+ Nuevo campo</button>' +
      "      </div>" +
      '      <div class="gpai-cf-panel-items"></div>' +
      '      <div class="gpai-cf-panel-empty">No hay campos personalizados. Crea uno nuevo.</div>' +
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
      '        <textarea id="gpai-cf-form-value" placeholder="Valor que se mostrar&aacute; en el frontend" rows="4"></textarea>' +
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
      alert("No se pudo obtener el ID de la página.");
      return;
    }
    createPanelHTML();
    $("#gpai-cf-panel").addClass("gpai-cf-panel-open");
    showList();
  }

  function closePanel() {
    $("#gpai-cf-panel").removeClass("gpai-cf-panel-open");
    currentView = "list";
    editingKey = null;
  }

  function showList() {
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
          .html('<div class="gpai-cf-panel-error">Error de conexión.</div>');
      },
    });
  }

  function renderList() {
    var $items = $("#gpai-cf-panel .gpai-cf-panel-items");
    var $empty = $("#gpai-cf-panel .gpai-cf-panel-empty");

    if (!fieldsData.length) {
      $items.empty();
      $empty.show();
      return;
    }

    $empty.hide();
    $items.empty();

    fieldsData.forEach(function (field) {
      var key = field.key;
      var value = field.value || "";
      var displayValue =
        value.length > 80 ? value.substring(0, 80) + "..." : value;

      var $row = $(
        '<div class="gpai-cf-field-row">' +
          '  <div class="gpai-cf-field-info">' +
          '    <code class="gpai-cf-field-key">{{' +
          key +
          "}}</code>" +
          '    <span class="gpai-cf-field-value">' +
          escapeHtml(displayValue) +
          "</span>" +
          "  </div>" +
          '  <div class="gpai-cf-field-actions">' +
          '    <button class="gpai-cf-field-edit" data-key="' +
          key +
          '" title="Editar">✎</button>' +
          '    <button class="gpai-cf-field-delete" data-key="' +
          key +
          '" title="Eliminar">✕</button>' +
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
        if (confirm("¿Eliminar el campo {{" + k + "}}?")) {
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
        alert("Error de conexión. Intenta de nuevo.");
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
        alert("Error de conexión. Intenta de nuevo.");
      },
    });
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
