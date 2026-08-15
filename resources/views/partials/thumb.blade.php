{{--
    Noi dung ben trong o thumbnail cua mot tiet muc: anh bia (neu co) hoac placeholder,
    kem badge "Am thanh" (tiet muc mp3/m4a…) va thoi luong "3:24" (neu biet).
    Dung: @include('partials.thumb', ['video' => $video])            → the video (co .thumb-ph)
          @include('partials.thumb', ['video' => $video, 'compact' => true]) → o nho (khong .thumb-ph)
--}}
@php $compact = $compact ?? false; @endphp
@if ($video->thumbnail && file_exists(public_path('storage/'.$video->thumbnail)))
    <img src="{{ asset('storage/'.$video->thumbnail) }}" alt="{{ $video->title }}" loading="lazy">
@elseif (! $compact)
    <span class="thumb-ph {{ $video->isAudio() ? 'thumb-ph-audio' : '' }}" aria-hidden="true">
        @if ($video->isAudio())<x-icon name="mic" size="18" />@endif
    </span>
@endif
@if ($video->isAudio())
    <span class="thumb-badge thumb-badge-audio"><x-icon name="mic" size="11" /> Âm thanh</span>
@endif
@if ($video->durationLabel())
    <span class="thumb-badge thumb-badge-time num">{{ $video->durationLabel() }}</span>
@endif
