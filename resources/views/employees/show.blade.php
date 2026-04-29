@extends('layouts.app')

@section('header')
    <div class="flex justify-between items-center">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Employee Details') }}: {{ $employee->first_name }} {{ $employee->last_name }}
        </h2>
        <a href="{{ route('employees.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded text-sm">
            Back to List
        </a>
    </div>
@endsection

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 text-gray-900">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Personal Information</h3>
                        <div class="flex flex-col space-y-3">
                            <div>
                                <span class="font-bold text-gray-700">Employee ID:</span>
                                <span class="text-gray-600">{{ $employee->employee_id }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Full Name:</span>
                                <span class="text-gray-600">{{ $employee->first_name }} {{ $employee->middle_name }} {{ $employee->last_name }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Email:</span>
                                <span class="text-gray-600">{{ $employee->email }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Phone:</span>
                                <span class="text-gray-600">{{ $employee->phone }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-4 border-b pb-2">Employment Details</h3>
                        <div class="flex flex-col space-y-3">
                            <div>
                                <span class="font-bold text-gray-700">Payroll Group:</span>
                                <span class="text-gray-600">{{ $employee->payrollGroup->name ?? 'None' }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Site:</span>
                                <span class="text-gray-600">{{ $employee->site->name ?? 'None' }}</span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Status:</span>
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $employee->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ ucfirst($employee->status) }}
                                </span>
                            </div>
                            <div>
                                <span class="font-bold text-gray-700">Date Joined:</span>
                                <span class="text-gray-600">{{ $employee->hire_date ? $employee->hire_date->format('M d, Y') : 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex space-x-4">
                    <a href="{{ route('employees.edit', $employee) }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded text-sm">
                        Edit Employee
                    </a>
                    <a href="{{ route('attendance.show', $employee) }}" class="bg-indigo-500 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded text-sm">
                        View Attendance
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
