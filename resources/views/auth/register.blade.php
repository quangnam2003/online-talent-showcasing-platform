@extends('layouts.app')

@section('title', 'Đăng ký — TalentStage')

@section('screen-kicker', 'FR1 · Access')
@section('screen-title', 'Đăng ký & đăng nhập')
@section('screen-sub', 'Register, log in — bước đầu tiên lên sân khấu')

@section('content')
<div class="grid-2 auth-grid" style="max-width: 1080px; align-items: stretch; gap: var(--space-8)">

    <div class="plate hatch" style="min-height: 460px; display: flex; align-items: flex-end; padding: var(--space-6)">
        <span class="slot-note">[ ảnh bìa — creator trên sân khấu ]</span>
    </div>

    <div style="display: flex; flex-direction: column; gap: var(--space-4); justify-content: center">
        <div class="auth-tabs">
            <a class="auth-tab" href="{{ route('login') }}">Đăng nhập · Log in</a>
            <span class="auth-tab active">Đăng ký · Register</span>
        </div>

        <form method="POST" action="{{ route('register') }}" style="display: flex; flex-direction: column; gap: var(--space-4)">
            @csrf

            <label class="field">
                <span class="label-up">Họ tên · Full name</span>
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
                <span class="label-up">Bạn là ai? · Choose your role</span>
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

            <div class="grid-2" style="gap: var(--space-4)">
                <label class="field">
                    <span class="label-up">Mật khẩu · Password</span>
                    <input class="input @error('password') is-invalid @enderror" type="password" name="password"
                           placeholder="Ít nhất 8 ký tự" required>
                </label>
                <label class="field">
                    <span class="label-up">Xác nhận · Confirm</span>
                    <input class="input" type="password" name="password_confirmation" placeholder="Nhập lại mật khẩu" required>
                </label>
            </div>
            @error('password') <span class="err-msg" style="margin-top: calc(var(--space-2) * -1)">{{ $message }}</span> @enderror

            <div style="display: flex; align-items: center; gap: var(--space-3)">
                <button type="submit" class="btn btn-primary" style="font-size: 13px">Tạo tài khoản · Create account</button>
                <span class="muted-i" style="font-size: 12.5px">Đã có tài khoản? <a href="{{ route('login') }}">Đăng nhập</a></span>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .auth-grid { grid-template-columns: 1fr; } .auth-grid .plate { min-height: 180px; } }
</style>
@endpush
