@extends('layouts.app')

@section('title', ($contest ? 'Sửa' : 'Tạo').' cuộc thi — TalentStage Admin')

@section('screen-kicker', 'Admin · FR7 Contest')
@section('screen-title', $contest ? 'Sửa cuộc thi' : 'Tạo cuộc thi')
@section('screen-sub', 'Thứ tự bắt buộc: mở nộp bài < hạn nộp bài < kết thúc')

@section('content')
<form method="POST"
      action="{{ $contest ? route('admin.contests.update', $contest) : route('admin.contests.store') }}"
      style="display: flex; flex-direction: column; gap: var(--space-4); max-width: 640px">
    @csrf
    @if ($contest) @method('PUT') @endif

    <label class="field">
        <span class="label-up">Tên cuộc thi · Title</span>
        <input class="input @error('title') is-invalid @enderror" name="title"
               value="{{ old('title', $contest?->title) }}" placeholder="Tài năng Tháng Tám 2026" required autofocus>
        @error('title') <span class="err-msg">{{ $message }}</span> @enderror
    </label>

    <label class="field">
        <span class="label-up">Mô tả · Description</span>
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
        <button type="submit" class="btn btn-primary btn-sm">{{ $contest ? 'Lưu · Save' : 'Tạo · Create' }}</button>
        <a class="btn btn-ghost btn-sm" href="{{ route('admin.contests.index') }}">Hủy</a>
    </div>
</form>
@endsection

@push('scripts')
<style>
    @media (max-width: 720px) { .grid-dates { grid-template-columns: 1fr !important; } }
</style>
@endpush
