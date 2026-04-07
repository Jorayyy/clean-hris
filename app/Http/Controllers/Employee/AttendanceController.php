<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $employeeId = Auth::user()->employee_id;
        
        $query = Attendance::where('employee_id', $employeeId);

        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(15);

        return view('employee.attendance', compact('attendances'));
    }
}
