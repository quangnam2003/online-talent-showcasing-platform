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

  /* ── mat khau + nhap lai (form dang ky): kiem tra ngay khi go ───────────
       [data-password-pair] chua input[data-pw] (minlength) va input[data-pw-confirm];
       tich xanh khi du dai / khop, x do khi chua; chan submit va bao "nhap lai"
       thay vi de server tra ve trang moi. */
  document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-password-pair]').forEach(function (pair) {
      var pw = pair.querySelector('[data-pw]');
      var cf = pair.querySelector('[data-pw-confirm]');
      if (!pw || !cf) return;
      var pwWrap = pw.closest('.input-wrap'), cfWrap = cf.closest('.input-wrap');
      var pwHint = pair.querySelector('[data-pw-hint]'), cfHint = pair.querySelector('[data-pw-confirm-hint]');
      var min = parseInt(pw.getAttribute('minlength') || '8', 10) || 8;
      var form = pair.closest('form');
      var touchedCf = false;

      function setState(wrap, hint, state, text) {
        if (wrap) { wrap.classList.remove('is-ok', 'is-bad'); if (state) wrap.classList.add(state); }
        if (hint) { hint.classList.remove('is-ok', 'is-bad'); if (state) hint.classList.add(state); if (text !== undefined) hint.textContent = text; }
      }
      function checkPw() {
        var v = pw.value;
        if (!v) { setState(pwWrap, pwHint, null, 'Ít nhất ' + min + ' ký tự.'); return false; }
        if (v.length < min) { setState(pwWrap, pwHint, 'is-bad', 'Còn thiếu ' + (min - v.length) + ' ký tự (tối thiểu ' + min + ').'); return false; }
        setState(pwWrap, pwHint, 'is-ok', 'Mật khẩu hợp lệ.'); return true;
      }
      function checkCf(force) {
        var v = cf.value;
        if (!v) { setState(cfWrap, cfHint, force ? 'is-bad' : null, force ? 'Vui lòng nhập lại mật khẩu.' : ''); cf.setCustomValidity(force ? 'Vui lòng nhập lại mật khẩu.' : ''); return false; }
        if (v !== pw.value) {
          if (touchedCf || force) setState(cfWrap, cfHint, 'is-bad', 'Mật khẩu chưa khớp — vui lòng nhập lại.');
          cf.setCustomValidity('Mật khẩu chưa khớp — vui lòng nhập lại.');
          return false;
        }
        setState(cfWrap, cfHint, 'is-ok', 'Mật khẩu khớp.'); cf.setCustomValidity(''); return true;
      }
      pw.addEventListener('input', function () { checkPw(); if (cf.value) checkCf(false); });
      cf.addEventListener('input', function () { touchedCf = true; checkCf(false); });
      cf.addEventListener('blur', function () { if (cf.value) { touchedCf = true; checkCf(true); } });
      if (form) form.addEventListener('submit', function (e) {
        var okPw = checkPw(), okCf = checkCf(true);
        if (!okPw || !okCf) {
          e.preventDefault();
          var target = !okPw ? pw : cf;
          target.focus({ preventScroll: false }); target.select && target.select();
          var wrap = target.closest('.input-wrap'); if (wrap) { wrap.classList.remove('shake'); void wrap.offsetWidth; wrap.classList.add('shake'); }
        }
      });
      // khoi phuc trang thai neu trinh duyet tu dien lai
      if (pw.value) checkPw();
    });
  });

  /* ── hop thoai (.dialog-backdrop[data-dialog]): dong bang nut / Esc / bam nen ── */
  window.tsCloseDialog = function (el) {
    var box = el && el.closest ? el.closest('.dialog-backdrop') : el;
    if (!box || box.classList.contains('is-leaving')) return;
    box.classList.add('is-leaving');
    setTimeout(function () { box.remove(); }, 240);
  };
  document.addEventListener('click', function (e) {
    if (e.target.classList && e.target.classList.contains('dialog-backdrop')) window.tsCloseDialog(e.target);
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') document.querySelectorAll('.dialog-backdrop[data-dialog]').forEach(window.tsCloseDialog);
  });
  document.addEventListener('DOMContentLoaded', function () {
    var d = document.querySelector('.dialog-backdrop[data-dialog] .btn'); if (d) d.focus();
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

/* ── thong bao gan thoi gian thuc: polling nhe /notifications/poll ────────
     - Moi 8s hoi server (tam dung khi tab an, hoi ngay khi quay lai)
     - Cap nhat so chua doc tren chuong + sidebar
     - Thong bao moi (tao sau luc mo trang) → hien toast goc duoi phai, bam de mo */
(function () {
  var bell = document.getElementById('ts-bell');
  if (!bell || !bell.dataset.notiPoll) return;

  var url = bell.dataset.notiPoll;
  var since = bell.dataset.notiSince;
  var seen = {};
  var INTERVAL = 8000;
  var timer = null;
  var busy = false;

  var readUrl = bell.dataset.notiRead || '';
  var csrf = (document.querySelector('meta[name="csrf-token"]') || {}).content || '';
  function markRead(id) {
    if (!readUrl || !id) return;
    try {
      // keepalive: request van gui du trang dang chuyen huong
      fetch(readUrl, {
        method: 'POST', keepalive: true, credentials: 'same-origin',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        body: JSON.stringify({ id: id })
      }).catch(function () {});
    } catch (e) {}
  }

  var badges = document.querySelectorAll('[data-noti-badge]');
  function setUnread(n) {
    badges.forEach(function (b) {
      var cap = b.dataset.notiBadge === 'dot' ? 9 : 99;
      b.textContent = n > cap ? cap + '+' : String(n);
      b.hidden = !(n > 0);
    });
    bell.setAttribute('aria-label', n > 0 ? 'Thông báo (' + n + ' chưa đọc)' : 'Thông báo');
  }

  var host = document.getElementById('ts-toasts');
  function toast(item) {
    if (!host) return;
    var a = document.createElement('a');
    a.className = 'ts-toast';
    a.href = item.url || '#';
    a.setAttribute('role', 'status');
    a.innerHTML =
      '<svg class="icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/><path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/></svg>' +
      '<span class="ts-toast-msg"></span>' +
      '<button type="button" class="ts-toast-x" aria-label="Đóng">×</button>';
    a.querySelector('.ts-toast-msg').textContent = item.message || 'Bạn có thông báo mới';
    var close = function (e) {
      if (e) { e.preventDefault(); e.stopPropagation(); }
      a.classList.add('is-leaving');
      setTimeout(function () { a.remove(); }, 260);
    };
    a.querySelector('.ts-toast-x').addEventListener('click', close);
    // Bam toast: danh dau da doc roi di toi noi dung. Neu dich la CHINH trang dang mo
    // (chi khac #hash) thi phai tai lai — noi dung moi (binh luan vua them…) chua co trong DOM.
    a.addEventListener('click', function (e) {
      e.preventDefault();
      var href = a.href;
      markRead(item.id);
      var target = document.createElement('a'); target.href = href;
      var samePage = target.pathname === location.pathname && target.search === location.search;
      if (samePage) {
        location.replace(href);   // cap nhat hash de trinh duyet cuon dung cho sau khi tai lai
        location.reload();
      } else {
        location.assign(href);
      }
    });
    host.appendChild(a);
    requestAnimationFrame(function () { a.classList.add('is-in'); });
    setTimeout(close, 7000);
    // toi da 4 toast cung luc
    while (host.children.length > 4) host.firstElementChild.remove();
  }

  function poll() {
    if (busy || document.hidden) return;
    busy = true;
    fetch(url + '?since=' + encodeURIComponent(since), {
      headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
      credentials: 'same-origin'
    })
      .then(function (r) { if (r.status === 401 || r.status === 403 || r.status === 419) { stop(); return null; } return r.ok ? r.json() : null; })
      .then(function (data) {
        if (!data) return;
        setUnread(data.unread || 0);
        (data.items || []).slice().reverse().forEach(function (item) {
          if (seen[item.id]) return;
          seen[item.id] = true;
          toast(item);
        });
        if (data.now) since = data.now;
      })
      .catch(function () { /* mang chap chon: bo qua, thu lai lan sau */ })
      .finally(function () { busy = false; });
  }

  function start() { if (!timer) timer = setInterval(poll, INTERVAL); }
  function stop() { if (timer) { clearInterval(timer); timer = null; } }

  document.addEventListener('visibilitychange', function () { if (!document.hidden) poll(); });
  start();
})();
