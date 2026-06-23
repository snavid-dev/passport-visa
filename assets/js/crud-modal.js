/* ============================================================
   crud-modal.js — generic AJAX create/edit modal
   Used by Accounts, Services (and any simple entity).

   Wiring (data-attributes):
     - Trigger to add:    [data-crud-create]
     - Trigger to edit:   [data-crud-edit] [data-id="N"]
     - The <form> inside the modal: [data-crud-form]
         data-create-url       = URL for POST create
         data-update-url-base  = base URL for POST update (id appended)
         data-get-url-base     = base URL for JSON fetch (id appended)
     - Modal element wraps the form (Bootstrap modal).
   Server contract:
     - create/update return JSON {success:true} on success,
       {success:false, errors:{field:msg}} with HTTP 422 on validation fail.
     - get returns {success:true, data:{...}}.
   On success the page reloads (server flashdata shows the toast).
   ============================================================ */
(function ($) {
  'use strict';
  if (!$) { return; }

  function clearErrors($form) {
    $form.find('.is-invalid').removeClass('is-invalid');
    $form.find('.field-error').remove();
  }

  function showErrors($form, errors) {
    clearErrors($form);
    $.each(errors, function (field, msg) {
      var $input = $form.find('[name="' + field + '"]');
      $input.addClass('is-invalid');
      $('<div class="field-error form-error mt-1"></div>').text(msg).insertAfter($input.last());
    });
  }

  function setField($form, name, value) {
    var $el = $form.find('[name="' + name + '"]');
    if (!$el.length) { return; }
    if ($el.attr('type') === 'checkbox') {
      $el.prop('checked', Number(value) === 1 || value === true);
    } else {
      $el.val(value === null ? '' : value);
      if ($el.hasClass('select2')) { $el.trigger('change'); }
    }
  }

  $(function () {
    var $form  = $('[data-crud-form]');
    if (!$form.length) { return; }
    var $modalEl = $form.closest('.modal');
    var modal   = $modalEl.length && window.bootstrap ? new bootstrap.Modal($modalEl[0]) : null;

    var createUrl     = $form.data('create-url');
    var updateUrlBase = $form.data('update-url-base');
    var getUrlBase    = $form.data('get-url-base');
    var $title        = $modalEl.find('[data-crud-title]');

    // ---- Open: create ----
    $(document).on('click', '[data-crud-create]', function () {
      clearErrors($form);
      $form[0].reset();
      // reset select2 widgets to placeholder
      $form.find('select.select2').val('').trigger('change');
      // default the "active" switch on for new records
      $form.find('[name="active"][type="checkbox"]').prop('checked', true);
      $form.attr('action', createUrl);
      if ($title.length) { $title.text($title.data('create-text') || 'افزودن'); }
      if (modal) { modal.show(); }
    });

    // ---- Open: edit ----
    $(document).on('click', '[data-crud-edit]', function () {
      var id = $(this).data('id');
      clearErrors($form);
      $form[0].reset();
      $.ajax({ url: getUrlBase + '/' + id, dataType: 'json' })
        .done(function (res) {
          if (res && res.success) {
            $.each(res.data, function (k, v) { setField($form, k, v); });
            $form.attr('action', updateUrlBase + '/' + id);
            if ($title.length) { $title.text($title.data('edit-text') || 'ویرایش'); }
            if (modal) { modal.show(); }
          }
        });
    });

    // ---- Submit (AJAX) ----
    $form.on('submit', function (e) {
      e.preventDefault();
      var $btn = $form.find('[type="submit"]').prop('disabled', true);
      $.ajax({
        url: $form.attr('action'),
        type: 'POST',
        data: $form.serialize(),
        dataType: 'json'
      })
        .done(function (res) {
          if (res && res.success) {
            window.location.reload();
          } else {
            $btn.prop('disabled', false);
          }
        })
        .fail(function (xhr) {
          $btn.prop('disabled', false);
          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            showErrors($form, xhr.responseJSON.errors);
          } else {
            alert('خطا در ذخیره‌سازی. لطفاً دوباره تلاش کنید.');
          }
        });
    });
  });
})(window.jQuery);
