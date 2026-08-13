@extends('layouts.app')

@section('title', 'Đăng nhập — TalentStage')

@section('screen-kicker', 'FR1 · Access')
@section('screen-title', 'Đăng ký & đăng nhập')
@section('screen-sub', 'Register, log in — chào mừng trở lại sân khấu')

@section('content')
<div class="grid-2 auth-grid" style="max-width: 1080px; align-items: stretch; gap: var(--space-8)">

    <div class="plate hatch" style="min-height: 460px; display: flex; align-items: flex-end; padding: var(--space-6)">
        <span class="slot-note">[ ảnh bìa — creator trên sân khấu ]</span>
    </div>

    <div style="display: flex; flex-direction: column; gap: var(--space-4); justify-content: center">
        <div class="auth-tabs">
            <span class="auth-tab active">Đăng nhập · Log in</span>
            <a class="auth-tab" href="{{ route('register') }}">Đăng ký · Register</a>
        </div>

        <form method="POST" action="{{ route('login') }}" style="display: flex; flex-direction: column; gap: var(--space-4)">
            @csrf

            <label class="field">
                <span class="label-up">Email</span>
                <input class="input @error('email') is-invalid @enderror" type="email" name="email"
                       value="{{ old('email') }}" placeholder="ban@example.com" required autofocus>
                @error('email') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span class="label-up">Mật khẩu · Password</span>
                <input class="input @error('password') is-invalid @enderror" type="password" name="password"
                       placeholder="••••••••" required>
                @error('password') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="radio" style="font-size: 13px">
                <input type="checkbox" name="remember">
                <span class="dot" style="border-radius: 3px"></span>
                Ghi nhớ đăng nhập · Remember me
            </label>

            <div style="display: flex; align-items: center; gap: var(--space-3)">
                <button type="submit" class="btn btn-primary" style="font-size: 13px">Đăng nhập · Log in</button>
                <span class="muted-i" style="font-size: 12.5px">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký</a></span>
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
