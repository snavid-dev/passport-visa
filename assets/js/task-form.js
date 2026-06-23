/* ============================================================
   task-form.js — task create/edit form behaviour
     - dynamic passport rows (add/remove + reindex)
     - Jalali datepicker re-init on new rows
     - GSAP row in/out animations (via window.animateRowIn/Out)
     - fee auto-calc (service default fee × passport count)
   ============================================================ */
(function ($) {
  'use strict';
  if (!$) { return; }

  $(function () {
    var $container = $('#passport-rows-container');
    if (!$container.length && !$('#task-form').length) { return; }

    var $template = $('#passport-row-template');

    function rowCount() { return $container.children('.passport-row').length; }

    function refreshCount() {
      $('#passport-count').text(rowCount());
      $('#no-passport-hint').toggle(rowCount() === 0);
      recalcFee();
    }

    // Reindex every row's field names to a contiguous 0..n-1 sequence.
    function reindex() {
      $container.children('.passport-row').each(function (i) {
        $(this).find('input, select').each(function () {
          var name = $(this).attr('name');
          if (!name) { return; }
          // passport_rows[N][field]
          name = name.replace(/passport_rows\[\d+\]/, 'passport_rows[' + i + ']');
          // scan_N file input
          name = name.replace(/^scan_\d+$/, 'scan_' + i);
          $(this).attr('name', name);
        });
      });
    }

    function initRowDatepickers() {
      if (window.jalaliDatepicker) {
        try { jalaliDatepicker.startWatch({ time: false, persianDigit: false, autoHide: true, separatorChars: { date: '/' } }); }
        catch (e) { /* already watching */ }
      }
    }

    // ---- Add row ----
    $('#add-passport-row').on('click', function () {
      var html = $template.html();
      var $row = $(html);
      var idx  = rowCount();
      $row.find('input, select').each(function () {
        var name = $(this).attr('name');
        if (!name) { return; }
        name = name.replace(/passport_rows\[\d+\]/, 'passport_rows[' + idx + ']');
        name = name.replace(/^scan_\d+$/, 'scan_' + idx);
        $(this).attr('name', name);
      });
      $container.append($row);
      initRowDatepickers();
      if (window.animateRowIn) { window.animateRowIn($row[0]); }
      refreshCount();
    });

    // ---- Remove row ----
    $(document).on('click', '.remove-passport-row', function () {
      var $row = $(this).closest('.passport-row');
      var done = function () { $row.remove(); reindex(); refreshCount(); };
      if (window.animateRowOut) { window.animateRowOut($row[0], done); }
      else { done(); }
    });

    // ---- Fee auto-calc ----
    var $service = $('#service-select');
    var $fee     = $('#fee-amount');
    var $feeCur  = $('#fee-currency');
    var $visa    = $('#visa-type');

    function perPassengerFee() {
      var opt = $service.length ? $service.find('option:selected') : null;
      if (!opt || !opt.val()) { return null; }
      var f = parseFloat(opt.data('fee'));
      return isNaN(f) ? null : f;
    }

    function recalcFee() {
      var per = perPassengerFee();
      if (per === null) { return; }
      var count = Math.max(rowCount(), 1);
      $fee.val((per * count).toFixed(2));
    }

    $service.on('change', function () {
      var opt = $(this).find('option:selected');
      if (opt.val()) {
        if (opt.data('currency') && $feeCur.length) { $feeCur.val(opt.data('currency')).trigger('change'); }
        if (opt.data('visa') && $visa.length && !$visa.val()) { $visa.val(opt.data('visa')); }
        recalcFee();
      }
    });

    refreshCount();
  });
})(window.jQuery);
