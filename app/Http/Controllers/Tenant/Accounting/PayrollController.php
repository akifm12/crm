<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use Illuminate\Http\Request;

class PayrollController extends Controller
{
    public function index()
    {
        $tenant = app('tenant');
        $runs   = PayrollRun::where('tenant_id', $tenant->id)
            ->withCount('items')
            ->orderByDesc('pay_date')
            ->paginate(20);

        $activeEmployees = Employee::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->count();

        return view('tenant.accounting.payroll.index', compact('tenant', 'runs', 'activeEmployees'));
    }

    public function employees()
    {
        $tenant    = app('tenant');
        $employees = Employee::where('tenant_id', $tenant->id)
            ->with('department')
            ->orderBy('full_name')
            ->paginate(40);

        return view('tenant.accounting.payroll.employees', compact('tenant', 'employees'));
    }

    public function createEmployee()
    {
        $tenant      = app('tenant');
        $departments = Department::where('tenant_id', $tenant->id)->orderBy('name')->get();

        return view('tenant.accounting.payroll.create_employee', compact('tenant', 'departments'));
    }

    public function storeEmployee(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'full_name'    => 'required|string|max:255',
            'basic_salary' => 'required|numeric|min:0',
            'position'     => 'nullable|string|max:255',
            'join_date'    => 'nullable|date',
        ]);

        $count  = Employee::where('tenant_id', $tenant->id)->count() + 1;
        $empNum = 'EMP-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        Employee::create([
            'tenant_id'       => $tenant->id,
            'employee_number' => $empNum,
            'full_name'       => $request->full_name,
            'position'        => $request->position,
            'department_id'   => $request->department_id,
            'basic_salary'    => $request->basic_salary,
            'join_date'       => $request->join_date,
        ]);

        return redirect()->route('tenant.accounting.payroll.employees', $tenant->slug)
            ->with('success', 'Employee added.');
    }

    public function showEmployee(string $slug, Employee $employee)
    {
        $tenant = app('tenant');
        abort_if($employee->tenant_id !== $tenant->id, 404);

        $employee->load('department', 'payrollItems.payrollRun');

        return view('tenant.accounting.payroll.show_employee', compact('tenant', 'employee'));
    }

    public function runForm()
    {
        $tenant    = app('tenant');
        $employees = Employee::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->orderBy('full_name')
            ->get();

        return view('tenant.accounting.payroll.run', compact('tenant', 'employees'));
    }

    public function processPayroll(Request $request)
    {
        $tenant = app('tenant');
        $request->validate([
            'period_start' => 'required|date',
            'period_end'   => 'required|date|after_or_equal:period_start',
            'pay_date'     => 'required|date',
            'items'        => 'required|array|min:1',
            'items.*.employee_id' => 'required|exists:employees,id',
            'items.*.basic_salary'=> 'required|numeric|min:0',
        ]);

        $count   = PayrollRun::where('tenant_id', $tenant->id)->count() + 1;
        $runNum  = 'PR-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $totalGross = collect($request->items)->sum(fn($i) => (float)($i['basic_salary'] ?? 0) + (float)($i['allowances'] ?? 0));
        $totalDed   = collect($request->items)->sum(fn($i) => (float)($i['deductions'] ?? 0));
        $totalNet   = $totalGross - $totalDed;

        $run = PayrollRun::create([
            'tenant_id'        => $tenant->id,
            'run_number'       => $runNum,
            'period_start'     => $request->period_start,
            'period_end'       => $request->period_end,
            'pay_date'         => $request->pay_date,
            'total_gross'      => $totalGross,
            'total_deductions' => $totalDed,
            'total_net'        => $totalNet,
            'status'           => 'posted',
        ]);

        foreach ($request->items as $item) {
            $gross  = (float)($item['basic_salary'] ?? 0) + (float)($item['allowances'] ?? 0);
            $ded    = (float)($item['deductions'] ?? 0);
            PayrollItem::create([
                'payroll_run_id' => $run->id,
                'employee_id'    => $item['employee_id'],
                'basic_salary'   => $item['basic_salary'],
                'allowances'     => $item['allowances'] ?? 0,
                'deductions'     => $ded,
                'net_pay'        => $gross - $ded,
                'notes'          => $item['notes'] ?? null,
            ]);
        }

        return redirect()->route('tenant.accounting.payroll.show', [$tenant->slug, $run->id])
            ->with('success', "Payroll run {$runNum} posted.");
    }

    public function showRun(string $slug, PayrollRun $payrollRun)
    {
        $tenant = app('tenant');
        abort_if($payrollRun->tenant_id !== $tenant->id, 404);

        $payrollRun->load('items.employee');

        return view('tenant.accounting.payroll.show_run', compact('tenant', 'payrollRun'));
    }
}
