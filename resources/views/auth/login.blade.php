@extends('layouts.app')

@section('title', 'Đăng nhập — TalentStage')

@section('screen-title', 'Đăng nhập')
@section('screen-sub', 'Chào mừng trở lại sân khấu.')

@section('content')
<div class="grid-2 auth-grid" style="max-width: 1080px; align-items: stretch; gap: var(--space-8)">

    @include('auth._cover')

    <div style="display: flex; flex-direction: column; gap: var(--space-4); justify-content: center">
        <div class="auth-tabs">
            <span class="auth-tab active">Đăng nhập</span>
            <a class="auth-tab" href="{{ route('register') }}">Đăng ký</a>
        </div>

        <form method="POST" action="{{ route('login') }}" style="display: flex; flex-direction: column; gap: var(--space-4)">
            @csrf

            <label class="field">
                <span class="label-up">Email</span>
                <input class="input @error('email') is-invalid @enderror" type="email" name="email"
                       value="{{ old('email') }}" placeholder="ban@example.com" required autofocus autocomplete="email">
                @error('email') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="field">
                <span class="label-up">Mật khẩu</span>
                <input class="input @error('password') is-invalid @enderror" type="password" name="password"
                       placeholder="••••••••" required autocomplete="current-password">
                @error('password') <span class="err-msg">{{ $message }}</span> @enderror
            </label>

            <label class="radio" style="font-size: 13px">
                <input type="checkbox" name="remember">
                <span class="dot" style="border-radius: 3px"></span>
                Ghi nhớ đăng nhập
            </label>

            <div style="display: flex; align-items: center; gap: var(--space-3); flex-wrap: wrap">
                <button type="submit" class="btn btn-primary" style="font-size: 13px"><x-icon name="log-in" size="15" /> Đăng nhập</button>
                <span class="muted-i" style="font-size: 12.5px">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký miễn phí</a></span>
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
