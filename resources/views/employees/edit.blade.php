@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">Edit Employee</h4>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ route('employees.index') }}">Employees</a></li>
                <li class="breadcrumb-item active" aria-current="page">Edit</li>
            </ol>
        </nav>
    </div>

    <form action="{{ route('employees.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        @include('employees._form')
    </form>
</div>

<script>
    function copyAddress() {
        if (document.getElementById('same_address_check').checked) {
            document.getElementById('present_address_brgy').value = document.getElementsByName('permanent_address_brgy')[0].value;
            document.getElementById('present_address_province').value = document.getElementsByName('permanent_address_province')[0].value;
        }
    }
</script>

<style>
    .border-bottom-primary {
        border-bottom: 2px solid #0d6efd !important;
    }
    .form-label {
        font-size: 0.9rem;
    }
    .card-header h5 {
        font-weight: 600;
    }
    .text-primary {
        color: #31708f !important;
    }
    .breadcrumb-item a {
        text-decoration: none;
    }
</style>
@endsection
