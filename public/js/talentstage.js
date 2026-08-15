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
