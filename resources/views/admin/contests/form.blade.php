@extends('layouts.app')

@section('title', ($contest ? 'Sửa' : 'Tạo').' cuộc thi — TalentStage Admin')

@section('screen-kicker')<a href="{{ route('admin.dashboard') }}">Quản trị</a><span class="sep">/</span><a href="{{ route('admin.contests.index') }}">Cuộc thi</a><span class="sep">/</span><span>{{ $contest ? 'Sửa' : 'Tạo mới' }}</span>@endsection
@section('screen-title', $contest ? 'Sửa cuộc thi' : 'Tạo cuộc thi')
@section('screen-sub', 'Ba mốc thời gian phải theo thứ tự: mở nhận bài → hạn nộp bài → kết thúc (công bố).')

@section('content')
@php $ended = $contest && $contest->status === 'ended'; @endphp
@if ($contest && ! $ended && ($contest->entries_count ?? 0) > 0)
    <div class="flash flash-error" role="alert" style="max-width: 640px">
        <x-icon name="info" size="16" />
        <span>Cuộc thi đã có <strong>{{ $contest->entries_count }} bài dự thi</strong>{{ ($votesCount ?? 0) > 0 ? ' và <strong>'.$votesCount.' phiếu bầu</strong>' : '' }} — đổi mốc thời gian sẽ đổi trạng thái cuộc thi <strong>ngay lập tức</strong> (ví dụ kéo "kết thúc" về quá khứ sẽ chốt kết quả tức thì). Không thể dời "mở nộp bài" ra sau bài dự thi sớm nhất.</span>
    </div>
@endif
@if ($ended)
    <div class="flash" role="status" style="max-width: 640px">
        <x-icon name="lock" size="16" />
        <span>Cuộc thi đã kết thúc — kết quả đã công bố nên <strong>không thể sửa</strong>. Muốn tổ chức tiếp, hãy tạo cuộc thi mới.</span>
    </div>
@endif
<form method="POST"
      action="{{ $contest ? route('admin.contests.update', $contest) : route('admin.contests.store') }}"
      style="display: flex; flex-direction: column; gap: var(--space-4); max-width: 640px">
    @csrf
    @if ($contest) @method('PUT') @endif

    <label class="field">
        <span class="label-up">Tên cuộc thi</span>
        <input class="input @error('title') is-invalid @enderror" name="title"
               value="{{ old('title', $contest?->title) }}" placeholder="Tài năng Tháng Tám 2026" required autofocus>
        @error('title') <span class="err-msg">{{ $message }}</span> @enderror
    </label>

    <label class="field">
        <span class="label-up">Mô tả</span>
        <textarea class="input" name="description" rows="3" placeholder="Thể lệ, giải thưởng…">{{ old('description', $contest?->description) }}</textarea>
    </label>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: var(--space-3)" class="grid-dates">
        <label class="field">
            <span class="label-up">Mở nộp bài</span>
            <input class="input @error('start_at') is-invalid @enderror" type="datetime-local" name="start_at"
                   value="{{ old('start_at', $contest?->start_at?->format('Y-m-d\TH:i')) }}" required>
            @error('start_at') <span class="err-msg">{{ $message }}</span> @enderror
        </label>
        <label class="field">
            <span class="label-up">Hạn nộp bài</span>
            <input class="input @error('submission_deadline') is-invalid @enderror" type="datetime-local" name="submission_deadline"
                   value="{{ old('submission_deadline', $contest?->submission_deadline?->format('Y-m-d\TH:i')) }}" required>
            @error('submission_deadline') <span class="err-msg">{{ $message }}</span> @enderror
        </label>
        <label class="field">
            <span class="label-up">Kết thúc (công bố)</span>
            <input class="input @error('end_at') is-invalid @enderror" type="datetime-local" name="end_at"
                   value="{{ old('end_at', $contest?->end_at?->format('Y-m-d\TH:i')) }}" required>
            @error('end_at') <span class="err-msg">{{ $message }}</span> @enderror
        </label>
    </div>

    <div style="display: flex; gap: var(--space-3)">
        <button type="submit" class="btn btn-primary btn-sm" @disabled($ended)>{{ $contest ? 'Lưu thay đổi' : 'Tạo cuộc thi' }}</button>
        <a class="btn btn-ghost btn-sm" href="{{ route('admin.contests.index') }}">Hủy</a>
    </div>
</form>
@endsection

@push('scripts')
<style>
    @media (max-width: 720px) { .grid-dates { grid-template-columns: 1fr !important; } }
</style>
@endpush
