<?php

namespace App\Http\Controllers\Tenant\Accounting;

use App\Http\Controllers\Controller;
use App\Models\ChartOfAccount;
use App\Models\Department;
use App\Models\Employee;
use App\Models\JournalEntry;
use App\Models\JournalLine;
use App\Models\PayrollLine;
use App\Models\PayrollRun;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    // ── Employees ──────────────────────────────────────────────────────────

    public function employees(string $slug)
    {
        $tenant      = app('tenant');
        $employees   = Employee::where('tenant_id', $tenant->id)
            ->where('is_deleted', false)
            ->with('department')
            ->orderBy('full_name')
            ->get();
        $departments = Department::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();

        return view('tenant.accounting.payroll.employees', compact('tenant', 'employees', 'departments'));
    }

    public function createEmployee(string $slug)
    {
        $tenant      = app('tenant');
        $departments = Department::where('tenant_id', $tenant->id)->where('is_active', true)->orderBy('name')->get();
        $accounts    = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->whereIn('type', ['ASSET', 'LIABILITY', 'EXPENSE'])
            ->orderBy('code')
            ->get();

        return view('tenant.accounting.payroll.create_employee', compact('tenant', 'departments', 'accounts'));
    }

    public function storeEmployee(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'join_date'    => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
        ]);

        $count      = Employee::where('tenant_id', $tenant->id)->count() + 1;
        $employeeId = 'EMP-' . str_pad($count, 4, '0', STR_PAD_LEFT);

        $basic     = floatval($request->basic_salary);
        $housing   = floatval($request->housing_allowance ?? 0);
        $transport = floatval($request->transport_allowance ?? 0);
        $other     = floatval($request->other_allowances ?? 0);

        Employee::create([
            'tenant_id'           => $tenant->id,
            'employee_id'         => $employeeId,
            'department_id'       => $request->department_id ?: null,
            'first_name'          => $request->first_name,
            'last_name'           => $request->last_name,
            'full_name'           => trim($request->first_name . ' ' . $request->last_name),
            'nationality'         => $request->nationality,
            'date_of_birth'       => $request->date_of_birth ?: null,
            'gender'              => $request->gender,
            'emirates_id'         => $request->emirates_id,
            'passport_no'         => $request->passport_no,
            'visa_no'             => $request->visa_no,
            'visa_expiry'         => $request->visa_expiry ?: null,
            'labour_card_no'      => $request->labour_card_no,
            'designation'         => $request->designation,
            'join_date'           => $request->join_date,
            'status'              => 'active',
            'basic_salary'        => $basic,
            'housing_allowance'   => $housing,
            'transport_allowance' => $transport,
            'other_allowances'    => $other,
            'gross_salary'        => $basic + $housing + $transport + $other,
            'payment_frequency'   => $request->payment_frequency ?? 'monthly',
            'bank_name'           => $request->bank_name,
            'bank_account_no'     => $request->bank_account_no,
            'iban'                => $request->iban,
            'wps_routing_code'    => $request->wps_routing_code,
            'salary_account_id'   => $request->salary_account_id ?: null,
            'annual_leave_days'   => $request->annual_leave_days ?? 30,
            'sick_leave_days'     => $request->sick_leave_days ?? 15,
            'notes'               => $request->notes,
        ]);

        return redirect()->route('tenant.accounting.payroll.employees', $slug)
            ->with('success', 'Employee added.');
    }

    public function showEmployee(string $slug, Employee $employee)
    {
        $tenant = app('tenant');
        abort_if($employee->tenant_id !== $tenant->id, 403);

        $employee->load('department', 'salaryAccount');
        $recentRuns = PayrollLine::where('employee_id', $employee->id)
            ->with('payrollRun')
            ->orderByDesc('created_at')
            ->limit(12)
            ->get();

        return view('tenant.accounting.payroll.show_employee', compact('tenant', 'employee', 'recentRuns'));
    }

    public function updateEmployee(Request $request, string $slug, Employee $employee)
    {
        $tenant = app('tenant');
        abort_if($employee->tenant_id !== $tenant->id, 403);

        $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'join_date'    => 'required|date',
            'basic_salary' => 'required|numeric|min:0',
        ]);

        $basic     = floatval($request->basic_salary);
        $housing   = floatval($request->housing_allowance ?? 0);
        $transport = floatval($request->transport_allowance ?? 0);
        $other     = floatval($request->other_allowances ?? 0);

        $employee->update([
            'department_id'       => $request->department_id ?: null,
            'first_name'          => $request->first_name,
            'last_name'           => $request->last_name,
            'full_name'           => trim($request->first_name . ' ' . $request->last_name),
            'nationality'         => $request->nationality,
            'date_of_birth'       => $request->date_of_birth ?: null,
            'gender'              => $request->gender,
            'emirates_id'         => $request->emirates_id,
            'passport_no'         => $request->passport_no,
            'visa_no'             => $request->visa_no,
            'visa_expiry'         => $request->visa_expiry ?: null,
            'labour_card_no'      => $request->labour_card_no,
            'designation'         => $request->designation,
            'join_date'           => $request->join_date,
            'end_date'            => $request->end_date ?: null,
            'status'              => $request->status ?? $employee->status,
            'basic_salary'        => $basic,
            'housing_allowance'   => $housing,
            'transport_allowance' => $transport,
            'other_allowances'    => $other,
            'gross_salary'        => $basic + $housing + $transport + $other,
            'payment_frequency'   => $request->payment_frequency ?? $employee->payment_frequency,
            'bank_name'           => $request->bank_name,
            'bank_account_no'     => $request->bank_account_no,
            'iban'                => $request->iban,
            'wps_routing_code'    => $request->wps_routing_code,
            'salary_account_id'   => $request->salary_account_id ?: null,
            'annual_leave_days'   => $request->annual_leave_days ?? $employee->annual_leave_days,
            'sick_leave_days'     => $request->sick_leave_days ?? $employee->sick_leave_days,
            'notes'               => $request->notes,
        ]);

        return back()->with('success', 'Employee updated.');
    }

    public function destroyEmployee(string $slug, Employee $employee)
    {
        $tenant = app('tenant');
        abort_if($employee->tenant_id !== $tenant->id, 403);
        $employee->update(['is_deleted' => true, 'deleted_at' => now(), 'status' => 'inactive']);
        return redirect()->route('tenant.accounting.payroll.employees', $slug)->with('success', 'Employee removed.');
    }

    // ── Departments ────────────────────────────────────────────────────────

    public function storeDepartment(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate(['code' => 'required|string|max:20', 'name' => 'required|string|max:100']);

        Department::create([
            'tenant_id'  => $tenant->id,
            'code'       => strtoupper($request->code),
            'name'       => $request->name,
            'manager_id' => $request->manager_id ?: null,
            'is_active'  => true,
        ]);

        return back()->with('success', 'Department created.');
    }

    // ── Payroll Runs ───────────────────────────────────────────────────────

    public function index(string $slug)
    {
        $tenant = app('tenant');
        $runs   = PayrollRun::where('tenant_id', $tenant->id)
            ->where('is_deleted', false)
            ->orderByDesc('pay_date')
            ->get();

        $activeCount = Employee::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('is_deleted', false)
            ->count();

        return view('tenant.accounting.payroll.index', compact('tenant', 'runs', 'activeCount'));
    }

    public function createRun(string $slug)
    {
        $tenant    = app('tenant');
        $employees = Employee::where('tenant_id', $tenant->id)
            ->where('status', 'active')
            ->where('is_deleted', false)
            ->orderBy('full_name')
            ->get();

        $accounts = ChartOfAccount::where('tenant_id', $tenant->id)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('tenant.accounting.payroll.run', compact('tenant', 'employees', 'accounts'));
    }

    public function storeRun(Request $request, string $slug)
    {
        $tenant = app('tenant');
        $request->validate([
            'pay_period_start' => 'required|date',
            'pay_period_end'   => 'required|date|after_or_equal:pay_period_start',
            'pay_date'         => 'required|date',
            'lines'            => 'required|array|min:1',
        ]);

        DB::transaction(function () use ($request, $tenant, $slug, &$run) {
            $count     = PayrollRun::where('tenant_id', $tenant->id)->count() + 1;
            $runNumber = 'PAY-' . date('Ym', strtotime($request->pay_date)) . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);

            $lines         = collect($request->lines)->filter(fn($l) => !empty($l['employee_id']));
            $totalBasic    = $lines->sum(fn($l) => floatval($l['basic_salary'] ?? 0));
            $totalAllow    = $lines->sum(fn($l) => floatval($l['housing_allowance'] ?? 0) + floatval($l['transport_allowance'] ?? 0) + floatval($l['other_allowances'] ?? 0) + floatval($l['overtime_amount'] ?? 0));
            $totalDeduct   = $lines->sum(fn($l) => floatval($l['loan_deduction'] ?? 0) + floatval($l['absence_deduction'] ?? 0) + floatval($l['other_deductions'] ?? 0));
            $totalNet      = $lines->sum(fn($l) => floatval($l['net_pay'] ?? 0));

            $run = PayrollRun::create([
                'tenant_id'          => $tenant->id,
                'run_number'         => $runNumber,
                'pay_period_start'   => $request->pay_period_start,
                'pay_period_end'     => $request->pay_period_end,
                'pay_date'           => $request->pay_date,
                'status'             => 'DRAFT',
                'total_basic_salary' => $totalBasic,
                'total_allowances'   => $totalAllow,
                'total_deductions'   => $totalDeduct,
                'total_net_pay'      => $totalNet,
                'employee_count'     => $lines->count(),
                'notes'              => $request->notes,
            ]);

            foreach ($lines as $line) {
                $basic     = floatval($line['basic_salary'] ?? 0);
                $housing   = floatval($line['housing_allowance'] ?? 0);
                $transport = floatval($line['transport_allowance'] ?? 0);
                $other     = floatval($line['other_allowances'] ?? 0);
                $overtime  = floatval($line['overtime_amount'] ?? 0);
                $grossPay  = $basic + $housing + $transport + $other + $overtime;
                $loanDed   = floatval($line['loan_deduction'] ?? 0);
                $absDed    = floatval($line['absence_deduction'] ?? 0);
                $otherDed  = floatval($line['other_deductions'] ?? 0);
                $totalDed  = $loanDed + $absDed + $otherDed;

                PayrollLine::create([
                    'payroll_run_id'      => $run->id,
                    'employee_id'         => $line['employee_id'],
                    'tenant_id'           => $tenant->id,
                    'basic_salary'        => $basic,
                    'housing_allowance'   => $housing,
                    'transport_allowance' => $transport,
                    'other_allowances'    => $other,
                    'overtime_amount'     => $overtime,
                    'gross_pay'           => $grossPay,
                    'loan_deduction'      => $loanDed,
                    'absence_deduction'   => $absDed,
                    'other_deductions'    => $otherDed,
                    'total_deductions'    => $totalDed,
                    'net_pay'             => floatval($line['net_pay'] ?? ($grossPay - $totalDed)),
                    'days_worked'         => $line['days_worked'] ?? null,
                    'days_absent'         => floatval($line['days_absent'] ?? 0),
                    'overtime_hours'      => floatval($line['overtime_hours'] ?? 0),
                    'eosb_accrual'        => floatval($line['eosb_accrual'] ?? 0),
                    'remarks'             => $line['remarks'] ?? null,
                ]);
            }
        });

        return redirect()->route('tenant.accounting.payroll.show', [$slug, $run->id])
            ->with('success', 'Payroll run saved as draft.');
    }

    public function showRun(string $slug, PayrollRun $run)
    {
        $tenant = app('tenant');
        abort_if($run->tenant_id !== $tenant->id, 403);
        $run->load('lines.employee', 'journalEntry');
        return view('tenant.accounting.payroll.show_run', compact('tenant', 'run'));
    }

    public function postRun(Request $request, string $slug, PayrollRun $run)
    {
        $tenant = app('tenant');
        abort_if($run->tenant_id !== $tenant->id, 403);
        abort_if($run->status !== 'DRAFT', 422, 'Run already posted.');

        $request->validate([
            'salary_payable_account_id' => 'required|integer',
            'wage_expense_account_id'   => 'required|integer',
        ]);

        DB::transaction(function () use ($request, $tenant, $run) {
            $count = JournalEntry::where('tenant_id', $tenant->id)->count() + 1;
            $ref   = 'JE-' . str_pad($count, 5, '0', STR_PAD_LEFT);

            $je = JournalEntry::create([
                'tenant_id'    => $tenant->id,
                'entry_date'   => $run->pay_date,
                'source_type'  => 'BANK_PAYMENT',
                'reference'    => $run->run_number,
                'memo'         => 'Payroll — ' . $run->pay_period_start->format('M Y'),
                'status'       => 'posted',
                'currency_code'=> 'AED',
                'exchange_rate'=> 1.0,
                'created_by'   => auth()->id(),
            ]);

            // DR Wage Expense
            JournalLine::create([
                'journal_entry_id'   => $je->id,
                'chart_of_account_id'=> $request->wage_expense_account_id,
                'description'        => 'Gross payroll ' . $run->run_number,
                'debit'              => $run->total_basic_salary + $run->total_allowances,
                'credit'             => 0,
                'line_order'         => 1,
            ]);

            // CR Salary Payable / Bank
            JournalLine::create([
                'journal_entry_id'   => $je->id,
                'chart_of_account_id'=> $request->salary_payable_account_id,
                'description'        => 'Net pay ' . $run->run_number,
                'debit'              => 0,
                'credit'             => $run->total_net_pay,
                'line_order'         => 2,
            ]);

            $run->update(['status' => 'POSTED', 'journal_entry_id' => $je->id]);
        });

        return back()->with('success', 'Payroll posted to ledger.');
    }

    public function voidRun(string $slug, PayrollRun $run)
    {
        $tenant = app('tenant');
        abort_if($run->tenant_id !== $tenant->id, 403);
        abort_if($run->status === 'VOID', 422);

        $run->update(['status' => 'VOID']);
        if ($run->journalEntry) {
            $run->journalEntry->update(['status' => 'voided']);
        }

        return back()->with('success', 'Payroll run voided.');
    }
}
