/* ============================================================================
   TalentStage — JS nho cho tuong tac giao dien (khong build step).
   - Drawer sidebar tren mobile (mo/dong, dong khi bam ra ngoai / Esc)
   - Dong flash muot (tsDismiss)
   - Mo/dong panel .reveal muot (tsToggle) — thay cho display:none
   ========================================================================== */
(function () {
  'use strict';

  var body = document.body;

  /* ── drawer ──────────────────────────────────────────────────────────── */
  window.tsToggleNav = function () {
    body.classList.toggle('nav-open');
  };
  document.addEventListener('click', function (e) {
    if (!body.classList.contains('nav-open')) return;
    if (e.target.closest('.ts-sidebar') || e.target.closest('.ts-menu-btn')) return;
    body.classList.remove('nav-open');
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && body.classList.contains('nav-open')) body.classList.remove('nav-open');
  });

  /* ── menu tha xuong (details.menu): dong khi bam ra ngoai / Esc / chon muc ── */
  document.addEventListener('click', function (e) {
    document.querySelectorAll('details.menu[open]').forEach(function (m) {
      if (!m.contains(e.target) || e.target.closest('.menu-item')) m.removeAttribute('open');
    });
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('details.menu[open]').forEach(function (m) { m.removeAttribute('open'); });
  });

  /* ── dropzone: keo tha tep vao <label class="dropzone"> chua input[type=file] ── */
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.dropzone').forEach(function (zone) {
      var input = zone.querySelector('input[type=file]');
      var nameEl = zone.querySelector('.dropzone-name');
      if (!input) return;
      var show = function () {
        var f = input.files && input.files[0];
        zone.classList.toggle('has-file', !!f);
        if (nameEl) nameEl.textContent = f ? (f.name + ' · ' + (f.size / 1048576).toFixed(1) + ' MB') : '';
      };
      input.addEventListener('change', show);
      ['dragenter', 'dragover'].forEach(function (ev) {
        zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-drag'); });
      });
      ['dragleave', 'drop'].forEach(function (ev) {
        zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('is-drag'); });
      });
      zone.addEventListener('drop', function (e) {
        if (e.dataTransfer && e.dataTransfer.files.length) {
          try { input.files = e.dataTransfer.files; } catch (err) { return; }
          show();
        }
      });
    });
  });

  /* ── flash: mo dan roi go khoi DOM ───────────────────────────────────── */
  window.tsDismiss = function (btn) {
    var el = btn.closest('.flash');
    if (!el) return;
    el.classList.add('is-leaving');
    var done = false;
    var remove = function () { if (!done) { done = true; el.remove(); } };
    el.addEventListener('transitionend', remove, { once: true });
    setTimeout(remove, 320); // du phong neu transition bi tat (reduced-motion)
  };

  /* ── reveal: tim panel trong pham vi gan nhat roi bat/tat .is-open ────
       dung: <button onclick="tsToggle(this, '.reply-form')">
             pham vi = to tien gan nhat co [data-reveal-scope] (hoac document) */
  window.tsToggle = function (btn, selector) {
    var scope = btn.closest('[data-reveal-scope]') || document;
    var panel = scope.querySelector(selector);
    if (!panel) return;
    var open = panel.classList.toggle('is-open');
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      var field = panel.querySelector('input:not([type=hidden]), select, textarea');
      if (field) setTimeout(function () { field.focus({ preventScroll: true }); }, 60);
    }
  };
})();
