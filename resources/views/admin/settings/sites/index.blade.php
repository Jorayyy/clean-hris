@extends('layouts.app')

@section('content')
<script>window.location.href = "{{ route('schedules.index') }}";</script>
<div class="container-fluid py-4 px-4">
    <div class="alert alert-info border-0 shadow-sm rounded-4">
        This page has moved. You are being redirected to <a href="{{ route('schedules.index') }}">Schedules & Plotting</a>.
    </div>
</div>
@endsection