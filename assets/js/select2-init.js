/* ============================================================
   select2-init.js — global Select2 defaults (RTL, Bootstrap 5)
   Applies to any <select class="select2"> present on load.
   Dynamic rows re-init via initRowSelects() in task-form.js.
   ============================================================ */
(function ($) {
  'use strict';
  if (!$ || !$.fn || !$.fn.select2) { return; }

  window.Select2Defaults = {
    theme: 'bootstrap-5',
    dir: 'rtl',
    width: '100%',
    language: {
      noResults:    function () { return 'موردی یافت نشد'; },
      searching:    function () { return 'در حال جستجو…'; },
      inputTooShort: function () { return 'لطفاً بیشتر تایپ کنید'; }
    }
  };

  $(function () {
    $('select.select2').each(function () {
      var $el = $(this);
      var opts = $.extend({}, window.Select2Defaults);
      if ($el.data('placeholder')) { opts.placeholder = $el.data('placeholder'); }
      // Resolve dropdown parent for use inside Bootstrap modals.
      if ($el.closest('.modal').length) { opts.dropdownParent = $el.closest('.modal'); }
      $el.select2(opts);
    });
  });
})(window.jQuery);
