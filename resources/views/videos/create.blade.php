@extends('layouts.app')

@section('title', 'Đăng tiết mục — TalentStage')

@section('screen-title', 'Đăng tiết mục')
@section('screen-sub', 'Tải video hoặc bản thu âm lên và gửi duyệt — tiết mục hiển thị công khai sau khi quản trị viên phê duyệt.')

@section('content')
@php
    // dinh dang chap nhan (khop Video::MEDIA_MIMES) — them duoi tep de trinh duyet loc dung tren Windows
    $accept = implode(',', array_merge(\App\Models\Video::MEDIA_MIMES, ['.mp4', '.mov', '.webm', '.mp3', '.m4a', '.aac', '.wav', '.ogg', '.flac']));
@endphp
<div class="upload-grid" style="display: grid; grid-template-columns: 1.3fr 1fr; gap: var(--space-8); align-items: start">

    {{-- ── Form upload — gui bang XHR (co tien trinh), fallback POST thuong khi khong co JS ── --}}
    <form method="POST" action="{{ route('videos.store') }}" enctype="multipart/form-data" data-upload
          style="display: flex; flex-direction: column; gap: var(--space-4)">
        @csrf
        <input type="hidden" name="duration" value="">

        {{-- thong bao ket qua gui (JS dien vao); server-side: flash o dau trang --}}
        <div class="flash flash-error" role="alert" data-form-alert hidden>
            <x-icon name="info" size="16" /><span data-form-alert-text></span>
        </div>

        {{-- Vung tep: dropzone → khi da chon thanh the dinh kem (xem truoc, ten, loai, dung luong, thoi luong, nut go) --}}
        <div class="attach" data-attach>
            <label class="dropzone" data-dropzone>
                <span class="dropzone-ico"><x-icon name="upload" size="20" /></span>
                <span class="dropzone-title">Kéo thả video hoặc bản thu âm vào đây, hoặc <u>chọn tệp</u></span>
                <span class="dropzone-hint">Video MP4 · MOV · WEBM &nbsp;·&nbsp; Âm thanh MP3 · M4A · WAV · OGG · FLAC &nbsp;·&nbsp; tối đa 100 MB</span>
                <input class="visually-hidden" type="file" name="video" accept="{{ $accept }}" required data-attach-input>
            </label>

            <div class="attach-card" data-attach-card hidden>
                <div class="attach-preview" data-attach-preview></div>
                <div class="attach-info">
                    <span class="attach-name" data-attach-name></span>
                    <span class="attach-meta" data-attach-meta></span>
                    <div class="progress" data-progress hidden><span class="progress-bar" data-progress-bar></span></div>
                    <span class="attach-status" data-attach-status></span>
                </div>
                <button type="button" class="icon-btn attach-remove" data-attach-remove aria-label="Gỡ tệp" title="Gỡ tệp"><x-icon name="x" size="16" /></button>
            </div>
            <span class="err-msg" data-error-for="video" @unless ($errors->has('video')) hidden @endunless>{{ $errors->first('video') }}</span>
        </div>

        <div class="grid-2" style="gap: var(--space-4)">
            <label class="field" style="grid-column: 1 / -1">
                <span class="label-up">Tiêu đề</span>
                <input class="input @error('title') is-invalid @enderror" name="title" value="{{ old('title') }}"
                       placeholder="Ví dụ: Đêm nhạc mộc — bản thu phòng khách" required maxlength="255">
                <span class="err-msg" data-error-for="title" @unless ($errors->has('title')) hidden @endunless>{{ $errors->first('title') }}</span>
            </label>

            <label class="field">
                <span class="label-up">Thể loại</span>
                <select class="input @error('category_id') is-invalid @enderror" name="category_id" required>
                    <option value="">— Chọn thể loại —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
                <span class="err-msg" data-error-for="category_id" @unless ($errors->has('category_id')) hidden @endunless>{{ $errors->first('category_id') }}</span>
            </label>

            <label class="field">
                <span class="label-up">Quyền xem</span>
                <select class="input" name="privacy">
                    <option value="public" @selected(old('privacy', 'public') === 'public')>Công khai — ai cũng xem được sau khi duyệt</option>
                    <option value="private" @selected(old('privacy') === 'private')>Riêng tư — chỉ mình tôi</option>
                </select>
            </label>

            <label class="field" style="grid-column: 1 / -1">
                <span class="label-up">Mô tả</span>
                <textarea class="input" name="description" rows="4" placeholder="Kể ngắn gọn về tiết mục: hoàn cảnh thu, điều bạn muốn người xem chú ý…">{{ old('description') }}</textarea>
                <span class="err-msg" data-error-for="description" @unless ($errors->has('description')) hidden @endunless>{{ $errors->first('description') }}</span>
            </label>

            <label class="field">
                <span class="label-up">Ảnh bìa (tùy chọn)</span>
                <div style="display: flex; align-items: center; gap: var(--space-2)">
                    <img class="thumb-preview" data-thumb-preview alt="" hidden>
                    <input class="input" type="file" name="thumbnail" accept="image/*" style="padding: 6px" data-thumb-input>
                </div>
                <span class="err-msg" data-error-for="thumbnail" @unless ($errors->has('thumbnail')) hidden @endunless>{{ $errors->first('thumbnail') }}</span>
            </label>

            <label class="radio" style="align-self: end; padding-bottom: 8px">
                <input type="checkbox" name="allow_comments" value="1" @checked(old('allow_comments', true))>
                <span class="dot" style="border-radius: 3px"></span>
                Cho phép bình luận
            </label>
        </div>

        <div style="display: flex; gap: var(--space-3); align-items: center; flex-wrap: wrap">
            <button type="submit" class="btn btn-primary" data-submit><x-icon name="send" size="15" /> <span data-submit-text>Gửi duyệt</span></button>
            <span class="muted-i">Sau khi gửi, quản trị viên sẽ nhận được thông báo để duyệt; bạn sẽ được báo ngay khi có kết quả.</span>
        </div>
    </form>

    {{-- ── Cot phai: quy trinh + trang thai duyet ── --}}
    <div style="display: flex; flex-direction: column; gap: var(--space-4)">
        <div class="card" style="padding: var(--space-4); gap: var(--space-3)">
            <div class="card-kicker">Tiết mục của bạn đi đâu sau khi gửi?</div>
            <ol class="steps">
                <li><strong>Gửi duyệt</strong> — tệp được lưu ở trạng thái <span class="tag tag-status" data-status="pending">Chờ duyệt</span>, chỉ bạn và quản trị viên xem được.</li>
                <li><strong>Quản trị viên nhận thông báo</strong> và xem tiết mục trong hàng đợi kiểm duyệt.</li>
                <li><strong>Bạn nhận kết quả</strong> qua chuông thông báo: <span class="tag tag-status" data-status="approved">Đã duyệt</span> hoặc <span class="tag tag-status" data-status="rejected">Từ chối</span> kèm lý do để sửa và gửi lại.</li>
                <li><strong>Lên sóng</strong> — tiết mục đã duyệt &amp; công khai xuất hiện ở Khám phá, có thể dự thi và được mentor nhận xét.</li>
            </ol>
        </div>

        <div style="display: flex; flex-direction: column; gap: var(--space-3)">
            <h3 style="font-size: 19px; margin: 0">Trạng thái duyệt gần đây</h3>
            <div class="table-wrap">
            <table class="table" style="font-size: 12.5px">
                <thead><tr><th>Tiết mục</th><th>Gửi lúc</th><th>Trạng thái</th></tr></thead>
                <tbody>
                    @forelse ($myUploads as $up)
                        @php
                            $t = ['pending' => 'Chờ duyệt', 'approved' => 'Đã duyệt', 'rejected' => 'Từ chối'][$up->status];
                        @endphp
                        <tr>
                            <td>
                                <a href="{{ route('videos.show', $up) }}" style="display: inline-flex; align-items: center; gap: 6px">
                                    @if ($up->isAudio())<x-icon name="mic" size="13" style="color: var(--color-neutral-700)" />@endif
                                    {{ \Illuminate\Support\Str::limit($up->title, 32) }}
                                </a>
                            </td>
                            <td class="num" style="color: var(--color-neutral-700)">{{ $up->created_at->format('d/m H:i') }}</td>
                            <td><span class="tag tag-status" data-status="{{ $up->status }}">{{ $t }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="muted-i">Bạn chưa đăng tiết mục nào.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
            <a class="btn btn-ghost btn-sm" style="align-self: flex-start; padding-left: 0" href="{{ route('videos.mine') }}">
                Xem tất cả tiết mục của tôi <x-icon name="arrow-right" size="14" />
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<style>
    @media (max-width: 1080px) { .upload-grid { grid-template-columns: 1fr !important; } }
</style>
@endpush
