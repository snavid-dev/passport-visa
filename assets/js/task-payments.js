/* ============================================================
   task-payments.js — AJAX add/delete payments on the task view.
   Posts to the task payment endpoints; CSRF is appended by main.js.
   On success the page reloads (server flashdata shows the toast).
   ============================================================ */
(function ($) {
  'use strict';
  if (!$) { return; }

  $(function () {
    // ---- Add payment ----
    $('.payment-form').on('submit', function (e) {
      e.preventDefault();
      var $form  = $(this);
      var $err   = $form.find('.payment-error').hide().text('');
      var $btn   = $form.find('[type="submit"]').prop('disabled', true);

      $.ajax({
        url: $form.data('action'),
        type: 'POST',
        data: $form.serialize(),
        dataType: 'json'
      })
        .done(function (res) {
          if (res && res.success) { window.location.reload(); }
          else { $btn.prop('disabled', false); }
        })
        .fail(function (xhr) {
          $btn.prop('disabled', false);
          var msg = 'خطا در ثبت پرداخت.';
          if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
            msg = Object.keys(xhr.responseJSON.errors).map(function (k) {
              return xhr.responseJSON.errors[k];
            }).join(' ');
          }
          $err.text(msg).show();
        });
    });

    // ---- Delete payment ----
    $(document).on('click', '.delete-payment', function () {
      if (!confirm('حذف این پرداخت؟ ثبت دفتر کل برگردانده می‌شود.')) { return; }
      var url = $(this).data('action');
      $.ajax({ url: url, type: 'POST', dataType: 'json' })
        .done(function (res) {
          if (res && res.success) { window.location.reload(); }
          else { alert('حذف ناموفق بود.'); }
        })
        .fail(function () { alert('حذف ناموفق بود.'); });
    });
  });
})(window.jQuery);
