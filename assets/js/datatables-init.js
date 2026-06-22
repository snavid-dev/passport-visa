/* ============================================================
   datatables-init.js — global DataTables config (Persian, RTL)
   Auto-inits any <table class="datatable"> present on load.
   Client-side for < 200 rows; pass data-serverside for large sets.
   ============================================================ */
(function ($) {
  'use strict';
  if (!$ || !$.fn || !$.fn.DataTable) { return; }

  window.DataTablePersian = {
    sEmptyTable:     'داده‌ای موجود نیست',
    sInfo:           'نمایش _START_ تا _END_ از _TOTAL_ مورد',
    sInfoEmpty:      'نمایش 0 تا 0 از 0 مورد',
    sInfoFiltered:   '(فیلتر شده از _MAX_ مورد)',
    sLengthMenu:     'نمایش _MENU_ مورد',
    sLoadingRecords: 'در حال بارگذاری…',
    sProcessing:     'در حال پردازش…',
    sSearch:         'جستجو:',
    sZeroRecords:    'موردی مطابق جستجو یافت نشد',
    oPaginate: {
      sFirst:    'اول',
      sLast:     'آخر',
      sNext:     'بعدی',
      sPrevious: 'قبلی'
    },
    oAria: {
      sSortAscending:  ': فعال‌سازی مرتب‌سازی صعودی',
      sSortDescending: ': فعال‌سازی مرتب‌سازی نزولی'
    }
  };

  window.initDataTable = function (selector, options) {
    options = options || {};
    var base = {
      language: window.DataTablePersian,
      pageLength: 25,
      lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
      order: [],
      autoWidth: false,
      responsive: false
    };
    return $(selector).DataTable($.extend(true, {}, base, options));
  };

  $(function () {
    $('table.datatable').each(function () {
      var $t = $(this);
      if ($.fn.dataTable.isDataTable(this)) { return; }
      window.initDataTable(this, {
        serverSide: !!$t.data('serverside'),
        ajax: $t.data('source') || null
      });
    });
  });
})(window.jQuery);
