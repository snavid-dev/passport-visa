/* ============================================================
   main.js — global init
   Iran Visa Processing System
   Loads after jQuery, GSAP, AOS (see _layouts/main.php footer).
   ============================================================ */
(function (window, document) {
  'use strict';

  var reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ----------------------------------------------------------
     CSRF — attach CodeIgniter's token to every AJAX request.
     Token name + cookie name come from <meta> tags in the layout.
     With csrf_regenerate = TRUE the cookie updates each request,
     so we read the freshest value from the cookie at send time.
  ---------------------------------------------------------- */
  function readCookie(name) {
    var m = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
    return m ? decodeURIComponent(m.pop()) : null;
  }

  function csrfName() {
    var el = document.querySelector('meta[name="csrf-name"]');
    return el ? el.getAttribute('content') : 'csrf_token';
  }

  function csrfCookieName() {
    var el = document.querySelector('meta[name="csrf-cookie"]');
    return el ? el.getAttribute('content') : 'csrf_cookie';
  }

  function csrfToken() {
    return readCookie(csrfCookieName()) ||
           (document.querySelector('meta[name="csrf-hash"]') || {}).content ||
           null;
  }

  if (window.jQuery) {
    jQuery.ajaxPrefilter(function (options, originalOptions, jqXHR) {
      if (options.type && options.type.toUpperCase() === 'POST') {
        var token = csrfToken();
        if (token) {
          var pair = encodeURIComponent(csrfName()) + '=' + encodeURIComponent(token);
          if (typeof options.data === 'string') {
            options.data += (options.data ? '&' : '') + pair;
          } else if (options.data == null) {
            options.data = pair;
          }
          // FormData objects are handled by the caller appending the field.
        }
      }
    });
  }

  /* ----------------------------------------------------------
     GSAP page entrance — runs once on DOMContentLoaded.
  ---------------------------------------------------------- */
  document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss flash alerts.
    initAlerts();
    // Sidebar (mobile) toggle.
    initSidebar();

    if (reducedMotion || !window.gsap) { return; }

    if (document.querySelector('.app-sidebar')) {
      gsap.from('.app-sidebar', { x: 80, opacity: 0, duration: 0.6, ease: 'power3.out' });
    }
    if (document.querySelector('.app-topbar')) {
      gsap.from('.app-topbar', { y: -40, opacity: 0, duration: 0.5, ease: 'power3.out' });
    }
    if (document.querySelector('.glass-card')) {
      gsap.from('.glass-card', {
        y: 32, opacity: 0, duration: 0.5,
        stagger: 0.08, ease: 'power3.out', delay: 0.1
      });
    }
    if (document.querySelector('.auth-card')) {
      gsap.from('.auth-card', { y: 24, opacity: 0, scale: 0.98, duration: 0.6, ease: 'power3.out' });
    }
  });

  /* ----------------------------------------------------------
     Jalali datepicker — watches inputs with [data-jdp].
     Outputs Shamsi (YYYY/MM/DD); server converts via from_jalali().
  ---------------------------------------------------------- */
  if (window.jalaliDatepicker) {
    jalaliDatepicker.startWatch({
      time: false,
      persianDigit: false,   // Western digits per brief
      showTodayBtn: true,
      showEmptyBtn: true,
      autoHide: true,
      separatorChars: { date: '/' }
    });
  }

  /* ----------------------------------------------------------
     AOS scroll reveals — init once globally.
  ---------------------------------------------------------- */
  if (window.AOS && !reducedMotion) {
    AOS.init({ duration: 500, easing: 'ease-out-cubic', once: true, offset: 40 });
  }

  /* ----------------------------------------------------------
     Flash alerts auto-dismiss
  ---------------------------------------------------------- */
  function initAlerts() {
    var alerts = document.querySelectorAll('.app-alert[data-autodismiss]');
    alerts.forEach(function (el) {
      window.setTimeout(function () { dismissAlert(el); }, 4500);
    });
    document.addEventListener('click', function (e) {
      var btn = e.target.closest('[data-dismiss-alert]');
      if (btn) { dismissAlert(btn.closest('.app-alert')); }
    });
  }

  function dismissAlert(el) {
    if (!el) { return; }
    if (reducedMotion || !window.gsap) { el.remove(); return; }
    gsap.to(el, { opacity: 0, y: -12, duration: 0.3, ease: 'power2.in', onComplete: function () { el.remove(); } });
  }

  /* ----------------------------------------------------------
     Mobile sidebar toggle
  ---------------------------------------------------------- */
  function initSidebar() {
    var toggle   = document.querySelector('[data-sidebar-toggle]');
    var sidebar  = document.querySelector('.app-sidebar');
    var backdrop = document.querySelector('.sidebar-backdrop');
    if (!toggle || !sidebar) { return; }

    function open()  { sidebar.classList.add('is-open'); if (backdrop) backdrop.classList.add('is-open'); }
    function close() { sidebar.classList.remove('is-open'); if (backdrop) backdrop.classList.remove('is-open'); }

    toggle.addEventListener('click', function () {
      sidebar.classList.contains('is-open') ? close() : open();
    });
    if (backdrop) { backdrop.addEventListener('click', close); }
  }

  /* ----------------------------------------------------------
     Reusable row animations (used by task-form.js etc.)
  ---------------------------------------------------------- */
  window.animateRowIn = function (rowElement) {
    if (reducedMotion || !window.gsap || !rowElement) { return; }
    gsap.from(rowElement, { opacity: 0, y: 16, duration: 0.35, ease: 'power3.out' });
  };

  window.animateRowOut = function (rowElement, callback) {
    if (reducedMotion || !window.gsap || !rowElement) {
      if (typeof callback === 'function') { callback(); }
      return;
    }
    gsap.to(rowElement, {
      opacity: 0, height: 0, marginBottom: 0, paddingTop: 0, paddingBottom: 0,
      duration: 0.3, ease: 'power2.in', onComplete: callback
    });
  };

  /* Expose CSRF helpers for FormData-based uploads. */
  window.AppCSRF = { name: csrfName, token: csrfToken };

})(window, document);
