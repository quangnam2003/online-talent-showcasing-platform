@extends('layouts.app')

@section('title', 'Tìm kiếm — TalentStage')

@section('screen-title', 'Tìm kiếm')
@section('screen-sub', 'Gõ vào ô tìm kiếm phía trên — kết quả hiện ngay bên dưới. Tìm theo tiêu đề, mô tả, tên creator hoặc thể loại.')

@section('content')
{{-- Mot o tim kiem duy nhat (o header); khoi nay duoc thay the truc tiep khi go (live search) --}}
<div id="exploreResults" data-explore-root style="display: flex; flex-direction: column; gap: var(--space-6)">
    @include('explore._results')
</div>
@endsection
