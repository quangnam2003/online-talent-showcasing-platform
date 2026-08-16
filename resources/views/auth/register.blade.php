@extends('layouts.app')

@section('title', 'Đăng ký — TalentStage')

@section('screen-title', 'Tạo tài khoản')
@section('screen-sub', 'Bước đầu tiên để bước lên sân khấu — miễn phí, chỉ mất một phút.')

@section('content')
<div class="grid-2 auth-grid" style="max-width: 1080px; align-items: stretch; gap: var(--space-8)">

    @include('auth._cover')

    <div style="display: flex; flex-direction: column; gap: var(--space-4); justify-content: center">
        <div class="auth-tabs">
            <a class="auth-tab" href="{{ route('login') }}">Đăng nhập</a>
            <span class="auth-tab active">Đăng ký</span>
        </div>

        <form method="POST" action="{{ route('register') }}" style="display: flex; flex-direction: column; gap: var(--space-4)">
            @csrf

            <label class="field">
                <span class="label-up">Họ tên</span>
                <input class="input @error('name') is-invalid @enderror" type="text" name="name"
                       value="{{ old('name') }}" placeholder="Nguyễn Hà Vi" required autofocus>
                @error('name') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span class="label-up">Email</span>
                <input class="input @error('email') is-invalid @enderror" type="email" name="email"
                       value="{{ old('email') }}" placeholder="ban@example.com" required>
                @error('email') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            {{-- Vai tro: chi Creator hoac Mentor (khong cho tu dang ky Admin) --}}
            <div class="field">
                <span class="label-up">Bạn tham gia với vai trò</span>
                <div class="seg" style="align-self: flex-start">
                    <label class="seg-opt">
                        <input type="radio" name="role" value="creator" @checked(old('role', 'creator') === 'creator')>
                        <span>Creator — trình diễn tài năng</span>
                    </label>
                    <label class="seg-opt">
                        <input type="radio" name="role" value="mentor" @checked(old('role') === 'mentor')>
                        <span>Mentor — cố vấn, nhận xét</span>
                    </label>
                </div>
                <span class="muted-i">Creator đăng video &amp; dự thi; Mentor nhắn tin hướng dẫn tài năng trẻ.</span>
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
                        <input class="input" type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" required autocomplete="new-password" data-pw-confirm>
                        <span class="input-check" aria-hidden="true"><x-icon name="check" size="13" class="ico-ok" /><x-icon name="x" size="13" class="ico-bad" /></span>
                    </span>
                    <span class="field-hint" data-pw-confirm-hint role="status" aria-live="polite"></span>
                </label>
            </div>
            @error('password') <span class="err-msg" style="margin-top: calc(var(--space-2) * -1)">{{ $message }}</span> @enderror

            <div style="display: flex; align-items: center; gap: var(--space-3)">
                <button type="submit" class="btn btn-primary" style="font-size: 13px"><x-icon name="user-plus" size="15" /> Tạo tài khoản</button>
                <span class="muted-i" style="font-size: 12.5px">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></span>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .auth-grid { grid-template-columns: 1fr; } .auth-cover { min-height: 0; } }
</style>
@endpush
