/* ============================================================================
   TalentStage — JS nho cho tuong tac giao dien (khong build step).
   - Drawer sidebar tren mobile (mo/dong, dong khi bam ra ngoai / Esc)
   - Dong flash muot (tsDismiss)
   - Mo/dong panel .reveal muot (tsToggle) — thay cho display:none
   - Menu tai khoan (details.menu), dropzone + the dinh kem + upload XHR co tien trinh (form[data-upload])
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

  /* ── dropzone + the dinh kem + upload co tien trinh ─────────────────────
       Cau truc: <form data-upload> … <div data-attach>
                   <label class="dropzone" data-dropzone><input type=file data-attach-input></label>
                   <div class="attach-card" data-attach-card hidden>…</div>
                 </div>
       - Chon/keo tha tep → kiem tra loai & dung luong → hien the (xem truoc, ten, loai,
         dung luong, thoi luong doc tu metadata → input[name=duration]).
       - Submit → XHR co upload.onprogress: thanh tien trinh + phan tram; JSON 200 → "Đã gửi ✓"
         roi chuyen trang; 422 → loi theo truong; 413 → tep qua lon; mat mang → bao lai. */
  var MB = 1048576;
  var MAX_MEDIA = 100 * MB;
  var VIDEO_EXT = /\.(mp4|m4v|mov|webm)$/i;
  var AUDIO_EXT = /\.(mp3|m4a|aac|wav|ogg|oga|flac)$/i;

  function fmtBytes(n) { return n >= MB ? (n / MB).toFixed(1) + ' MB' : Math.max(1, Math.round(n / 1024)) + ' KB'; }
  function fmtDuration(sec) {
    sec = Math.round(sec); var h = Math.floor(sec / 3600), m = Math.floor((sec % 3600) / 60), s = sec % 60;
    var mm = h ? String(m).padStart(2, '0') : String(m);
    return (h ? h + ':' : '') + mm + ':' + String(s).padStart(2, '0');
  }
  function mediaKind(file) {
    if (/^video\//.test(file.type) || VIDEO_EXT.test(file.name)) return 'video';
    if (/^audio\//.test(file.type) || AUDIO_EXT.test(file.name)) return 'audio';
    return null;
  }

  function initUploadForm(form) {
    var wrap = form.querySelector('[data-attach]');
    var input = form.querySelector('[data-attach-input]');
    var zone = form.querySelector('[data-dropzone]');
    var card = form.querySelector('[data-attach-card]');
    var preview = form.querySelector('[data-attach-preview]');
    var nameEl = form.querySelector('[data-attach-name]');
    var metaEl = form.querySelector('[data-attach-meta]');
    var statusEl = form.querySelector('[data-attach-status]');
    var progress = form.querySelector('[data-progress]');
    var bar = form.querySelector('[data-progress-bar]');
    var removeBtn = form.querySelector('[data-attach-remove]');
    var durationInput = form.querySelector('input[name=duration]');
    var submitBtn = form.querySelector('[data-submit]');
    var submitText = form.querySelector('[data-submit-text]');
    var alertBox = form.querySelector('[data-form-alert]');
    var alertText = form.querySelector('[data-form-alert-text]');
    if (!input || !zone || !card) return;

    var objectUrl = null, uploading = false;

    function fieldError(name, msg) {
      var el = form.querySelector('[data-error-for="' + name + '"]');
      if (!el) return;
      el.textContent = msg || ''; el.hidden = !msg;
      var ctrl = form.querySelector('[name="' + name + '"]');
      if (ctrl && ctrl.classList.contains('input')) ctrl.classList.toggle('is-invalid', !!msg);
    }
    function clearErrors() {
      form.querySelectorAll('[data-error-for]').forEach(function (el) { el.hidden = true; el.textContent = ''; });
      form.querySelectorAll('.input.is-invalid').forEach(function (el) { el.classList.remove('is-invalid'); });
      if (alertBox) alertBox.hidden = true;
    }
    function showAlert(msg) {
      if (!alertBox) return;
      alertText.textContent = msg; alertBox.hidden = false;
      alertBox.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
    }
    function setStatus(msg, cls) {
      statusEl.textContent = msg || '';
      card.classList.remove('is-uploading', 'is-done', 'is-error');
      if (cls) card.classList.add(cls);
    }
    function revoke() { if (objectUrl) { URL.revokeObjectURL(objectUrl); objectUrl = null; } }

    function showCard(file) {
      var kind = mediaKind(file);
      revoke();
      preview.innerHTML = ''; preview.classList.toggle('is-audio', kind === 'audio');
      nameEl.textContent = file.name;
      metaEl.textContent = (kind === 'audio' ? 'Bản thu âm' : 'Video') + ' · ' + fmtBytes(file.size);
      durationInput && (durationInput.value = '');
      setStatus('', null); progress.hidden = true; bar.style.width = '0%';

      objectUrl = URL.createObjectURL(file);
      var media = document.createElement(kind === 'audio' ? 'audio' : 'video');
      media.preload = 'metadata'; media.muted = true; media.playsInline = true; media.src = objectUrl;
      media.addEventListener('loadedmetadata', function () {
        if (isFinite(media.duration) && media.duration > 0) {
          if (durationInput) durationInput.value = Math.round(media.duration);
          metaEl.textContent += ' · ' + fmtDuration(media.duration);
        }
        if (kind === 'video') { try { media.currentTime = Math.min(1, media.duration / 2); } catch (e) {} }
      });
      if (kind === 'video') { preview.appendChild(media); }
      else {
        preview.innerHTML = '<svg class="icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2a3 3 0 0 0-3 3v7a3 3 0 0 0 6 0V5a3 3 0 0 0-3-3Z"/><path d="M19 10v2a7 7 0 0 1-14 0v-2"/><path d="M12 19v3"/></svg>';
        media.load(); // chi de doc thoi luong
      }
      zone.hidden = true; card.hidden = false;
    }

    function clearFile() {
      revoke(); input.value = '';
      if (durationInput) durationInput.value = '';
      card.hidden = true; zone.hidden = false; preview.innerHTML = '';
      fieldError('video', '');
    }

    function acceptFile(file) {
      fieldError('video', '');
      var kind = mediaKind(file);
      if (!kind) {
        clearFile();
        fieldError('video', 'Định dạng chưa hỗ trợ. Chấp nhận video MP4 / MOV / WEBM hoặc âm thanh MP3 / M4A / WAV / OGG / FLAC.');
        return false;
      }
      if (file.size > MAX_MEDIA) {
        clearFile();
        fieldError('video', 'Tệp ' + fmtBytes(file.size) + ' vượt quá giới hạn 100 MB. Hãy nén hoặc cắt ngắn tiết mục rồi thử lại.');
        return false;
      }
      showCard(file);
      return true;
    }

    input.addEventListener('change', function () { if (input.files && input.files[0]) acceptFile(input.files[0]); });
    ['dragenter', 'dragover'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-drag'); }); });
    ['dragleave', 'drop'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('is-drag'); }); });
    zone.addEventListener('drop', function (e) {
      if (!(e.dataTransfer && e.dataTransfer.files.length)) return;
      try { input.files = e.dataTransfer.files; } catch (err) { return; }
      acceptFile(e.dataTransfer.files[0]);
    });
    if (removeBtn) removeBtn.addEventListener('click', function () { if (!uploading) clearFile(); });

    // xem truoc anh bia
    var thumbInput = form.querySelector('[data-thumb-input]');
    var thumbPrev = form.querySelector('[data-thumb-preview]');
    if (thumbInput && thumbPrev) {
      thumbInput.addEventListener('change', function () {
        var f = thumbInput.files && thumbInput.files[0];
        if (f && /^image\//.test(f.type)) { thumbPrev.src = URL.createObjectURL(f); thumbPrev.hidden = false; }
        else { thumbPrev.hidden = true; thumbPrev.removeAttribute('src'); }
      });
    }

    // gui bang XHR de co tien trinh
    form.addEventListener('submit', function (e) {
      if (!window.XMLHttpRequest || !window.FormData || uploading) { if (uploading) e.preventDefault(); return; }
      e.preventDefault();
      clearErrors();
      var file = input.files && input.files[0];
      if (!file) { fieldError('video', 'Bạn chưa chọn tệp video hoặc âm thanh.'); zone.scrollIntoView({ block: 'center', behavior: 'smooth' }); return; }
      if (!form.reportValidity()) return;

      uploading = true;
      var xhr = new XMLHttpRequest();
      var started = Date.now();
      progress.hidden = false; bar.classList.remove('is-indeterminate'); bar.style.width = '0%';
      setStatus('Đang tải lên… 0%', 'is-uploading');
      if (submitBtn) { submitBtn.disabled = true; }
      if (submitText) submitText.textContent = 'Đang gửi…';
      if (removeBtn) removeBtn.disabled = true;
      var guard = function (ev) { ev.preventDefault(); ev.returnValue = ''; };
      window.addEventListener('beforeunload', guard);

      function finish() {
        uploading = false;
        window.removeEventListener('beforeunload', guard);
        if (submitBtn) submitBtn.disabled = false;
        if (submitText) submitText.textContent = 'Gửi duyệt';
        if (removeBtn) removeBtn.disabled = false;
      }
      function fail(msg, fieldErrors) {
        finish();
        progress.hidden = true;
        var fileErr = fieldErrors && fieldErrors.video ? fieldErrors.video[0] : null;
        var onlyFields = fieldErrors && !fileErr; // loi o cac truong khac (tieu de, the loai…) — the dinh kem van binh thuong
        setStatus(fileErr || (onlyFields ? '' : (msg || 'Gửi không thành công.')), onlyFields ? null : 'is-error');
        if (fieldErrors) Object.keys(fieldErrors).forEach(function (k) { fieldError(k, fieldErrors[k][0]); });
        showAlert(msg || 'Chưa thể gửi — vui lòng kiểm tra lại các mục được đánh dấu.');
      }

      xhr.upload.addEventListener('progress', function (ev) {
        if (!ev.lengthComputable) { bar.classList.add('is-indeterminate'); setStatus('Đang tải lên…', 'is-uploading'); return; }
        var pct = Math.min(99, Math.round(ev.loaded / ev.total * 100));
        bar.style.width = pct + '%';
        var secs = (Date.now() - started) / 1000, speed = secs > 0.5 ? ev.loaded / secs : 0;
        var eta = speed > 0 ? Math.max(0, Math.round((ev.total - ev.loaded) / speed)) : null;
        setStatus('Đang tải lên… ' + pct + '% (' + fmtBytes(ev.loaded) + ' / ' + fmtBytes(ev.total) + ')' + (eta !== null && eta > 1 ? ' · còn ~' + fmtDuration(eta) : ''), 'is-uploading');
      });
      xhr.upload.addEventListener('load', function () { bar.style.width = '100%'; setStatus('Đã tải xong, máy chủ đang lưu và gửi duyệt…', 'is-uploading'); });
      xhr.addEventListener('error', function () { fail('Mất kết nối trong lúc tải lên. Kiểm tra mạng rồi thử lại.'); });
      xhr.addEventListener('abort', function () { fail('Đã hủy tải lên.'); });
      xhr.addEventListener('load', function () {
        var data = null; try { data = JSON.parse(xhr.responseText); } catch (err) {}
        if (xhr.status >= 200 && xhr.status < 300 && data && data.ok) {
          finish();
          setStatus('Đã gửi duyệt thành công — đang chuyển tới danh sách tiết mục của bạn…', 'is-done');
          if (submitBtn) submitBtn.disabled = true;
          setTimeout(function () { window.location.href = data.redirect || '/my-videos'; }, 700);
          return;
        }
        if (xhr.status === 422 && data) { fail(data.message && data.errors ? 'Chưa thể gửi — vui lòng kiểm tra lại các mục được đánh dấu.' : data.message, data.errors || null); return; }
        if (xhr.status === 413) { fail((data && data.message) || 'Tệp quá lớn — máy chủ chỉ nhận tối đa 100 MB.', (data && data.errors) || { video: ['Tệp quá lớn — tối đa 100 MB.'] }); return; }
        if (xhr.status === 419 || xhr.status === 401) { fail('Phiên đăng nhập đã hết hạn. Hãy tải lại trang và đăng nhập rồi gửi lại.'); return; }
        fail('Máy chủ gặp lỗi (mã ' + xhr.status + '). Vui lòng thử lại sau ít phút.');
      });

      xhr.open('POST', form.action, true);
      xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
      xhr.setRequestHeader('Accept', 'application/json');
      var token = document.querySelector('meta[name=csrf-token]');
      if (token) xhr.setRequestHeader('X-CSRF-TOKEN', token.content);
      xhr.send(new FormData(form));
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('form[data-upload]').forEach(initUploadForm);
    // dropzone don gian (khong co the dinh kem) — giu tuong thich
    document.querySelectorAll('.dropzone:not([data-dropzone])').forEach(function (zone) {
      var input = zone.querySelector('input[type=file]'); var nameEl = zone.querySelector('.dropzone-name');
      if (!input) return;
      var show = function () { var f = input.files && input.files[0]; zone.classList.toggle('has-file', !!f); if (nameEl) nameEl.textContent = f ? (f.name + ' · ' + fmtBytes(f.size)) : ''; };
      input.addEventListener('change', show);
      ['dragenter', 'dragover'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.add('is-drag'); }); });
      ['dragleave', 'drop'].forEach(function (ev) { zone.addEventListener(ev, function (e) { e.preventDefault(); zone.classList.remove('is-drag'); }); });
      zone.addEventListener('drop', function (e) { if (e.dataTransfer && e.dataTransfer.files.length) { try { input.files = e.dataTransfer.files; } catch (err) { return; } show(); } });
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
