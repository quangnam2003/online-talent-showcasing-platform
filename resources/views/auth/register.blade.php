@extends('layouts.app')

@section('title', 'Đăng ký — TalentStage')

{{-- Khong dung screen-title: the dang ky tu mang tieu de (tab Dang nhap / Dang ky) va duoc can giua trang --}}
@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        @include('auth._cover')

        <div class="auth-panel">
            <div class="auth-panel-inner">
                <div class="auth-tabs" role="tablist" aria-label="Đăng nhập hoặc đăng ký">
                    <a class="auth-tab" role="tab" aria-selected="false" href="{{ route('login') }}">Đăng nhập</a>
                    <span class="auth-tab active" role="tab" aria-selected="true">Đăng ký</span>
                </div>
                <p class="auth-lead">Bước đầu tiên để bước lên sân khấu — miễn phí, chỉ mất một phút.</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <label class="field">
                        <span class="label-up">Họ tên</span>
                        <input class="input @error('name') is-invalid @enderror" type="text" name="name"
                               value="{{ old('name') }}" placeholder="Nguyễn Quang Nam" required autofocus autocomplete="name">
                        @error('name') <span class="err-msg">{{ $message }}</span> @enderror
                    </label>

                    <label class="field">
                        <span class="label-up">Email</span>
                        <input class="input @error('email') is-invalid @enderror" type="email" name="email"
                               value="{{ old('email') }}" placeholder="ban@example.com" required autocomplete="email">
                        @error('email') <span class="err-msg">{{ $message }}</span> @enderror
                    </label>

                    {{-- Vai tro: chi Creator hoac Mentor (khong cho tu dang ky Admin) — 2 o bang nhau, full-width --}}
                    <div class="field">
                        <span class="label-up">Bạn tham gia với vai trò</span>
                        <div class="seg">
                            <label class="seg-opt">
                                <input type="radio" name="role" value="creator" @checked(old('role', 'creator') === 'creator')>
                                <span><strong>Creator</strong> — trình diễn tài năng</span>
                            </label>
                            <label class="seg-opt">
                                <input type="radio" name="role" value="mentor" @checked(old('role') === 'mentor')>
                                <span><strong>Mentor</strong> — cố vấn, nhận xét</span>
                            </label>
                        </div>
                        <span class="field-hint">Creator đăng video &amp; dự thi; Mentor nhắn tin hướng dẫn tài năng trẻ.</span>
                        @error('role') <span class="err-msg">{{ $message }}</span> @enderror
                    </div>

                    {{-- Mat khau + nhap lai: kiem tra ngay tren trinh duyet (tich xanh / x do), chan submit khi chua khop --}}
                    <div class="grid-2" style="gap: var(--space-4)" data-password-pair>
                        <label class="field">
                            <span class="label-up">Mật khẩu</span>
                            <span class="input-wrap">
                                <input class="input @error('password') is-invalid @enderror" type="password" name="password"
                                       placeholder="Ít nhất 8 ký tự" required minlength="8" autocomplete="new-password" data-pw>
                                <span class="input-check" aria-hidden="true"><x-icon name="check" size="13" class="ico-ok" /><x-icon name="x" size="13" class="ico-bad" /></span>
                            </span>
                            <span class="field-hint" data-pw-hint>Ít nhất 8 ký tự.</span>
                        </label>
                        <label class="field">
                            <span class="label-up">Nhập lại mật khẩu</span>
                            <span class="input-wrap">
                                <input class="input" type="password" name="password_confirmation" placeholder="Nhập lại" required autocomplete="new-password" data-pw-confirm>
                                <span class="input-check" aria-hidden="true"><x-icon name="check" size="13" class="ico-ok" /><x-icon name="x" size="13" class="ico-bad" /></span>
                            </span>
                            <span class="field-hint" data-pw-confirm-hint role="status" aria-live="polite"></span>
                        </label>
                    </div>
                    @error('password') <span class="err-msg" style="margin-top: calc(var(--space-2) * -1)">{{ $message }}</span> @enderror

                    <button type="submit" class="btn btn-primary"><x-icon name="user-plus" size="15" /> Tạo tài khoản</button>
                </form>

                <p class="auth-foot" style="margin: 0">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
