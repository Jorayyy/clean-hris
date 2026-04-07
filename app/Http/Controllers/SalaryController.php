<?php

namespace App\Http\Controllers;

use App\Models\PayrollItem;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollItem::with(['employee', 'payroll']);

        if ($request->employee_id) {
            $query->whereHas('employee', function($q) use ($request) {
                $q->where('employee_id', $request->employee_id);
            });
        }

        $salaries = $query->latest()->paginate(15);
        
        return view('salaries.index', compact('salaries'));
    }
}
