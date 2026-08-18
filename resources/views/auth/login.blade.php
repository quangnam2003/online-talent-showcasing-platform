@extends('layouts.app')

@section('title', 'Đăng nhập — TalentStage')

{{-- Khong dung screen-title: the dang nhap tu mang tieu de (tab Dang nhap / Dang ky) va duoc can giua trang --}}
@section('content')
<div class="auth-wrap">
    <div class="auth-card">
        @include('auth._cover')

        <div class="auth-panel">
            <div class="auth-panel-inner">
                <div class="auth-tabs" role="tablist" aria-label="Đăng nhập hoặc đăng ký">
                    <span class="auth-tab active" role="tab" aria-selected="true">Đăng nhập</span>
                    <a class="auth-tab" role="tab" aria-selected="false" href="{{ route('register') }}">Đăng ký</a>
                </div>
                <p class="auth-lead">Chào mừng trở lại sân khấu — đăng nhập để tiếp tục.</p>

                <form method="POST" action="{{ route('login') }}">
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
                        <input type="checkbox" name="remember" @checked(old('remember'))>
                        <span class="dot" style="border-radius: 3px"></span>
                        Ghi nhớ đăng nhập trên thiết bị này
                    </label>

                    <button type="submit" class="btn btn-primary"><x-icon name="log-in" size="15" /> Đăng nhập</button>
                </form>

                <p class="auth-foot" style="margin: 0">Chưa có tài khoản? <a href="{{ route('register') }}">Đăng ký miễn phí</a></p>
            </div>
        </div>
    </div>
</div>
@endsection
