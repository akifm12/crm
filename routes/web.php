<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CrmController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboard;
use App\Http\Controllers\Tenant\ClientController;

require __DIR__.'/auth.php';

// ── Admin routes ───────────────────────────────────────────────────────────
Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/',          [DashboardController::class, 'index'])->name('admin.dashboard');

    // ── CRM ───────────────────────────────────────────────────────────────
    Route::get('/crm',          [CrmController::class, 'index'])->name('crm.index');
    Route::get('/crm/new',      [CrmController::class, 'create'])->name('crm.create');
    Route::post('/crm',         [CrmController::class, 'store'])->name('crm.store');
    Route::get('/crm/{crm}',    [CrmController::class, 'show'])->name('crm.show');
    Route::patch('/crm/{crm}/stage', [CrmController::class, 'updateStage'])->name('crm.stage');
    Route::post('/crm/{crm}/notes',            [CrmController::class, 'addNote'])->name('crm.notes.store');
    Route::post('/crm/{crm}/tasks',            [CrmController::class, 'addTask'])->name('crm.tasks.store');
    Route::patch('/crm/tasks/{task}/complete', [CrmController::class, 'completeTask'])->name('crm.tasks.complete');
    Route::post('/crm/{crm}/documents',            [CrmController::class, 'uploadDocument'])->name('crm.documents.upload');
    Route::get('/crm/documents/{document}/download',[CrmController::class, 'downloadDocument'])->name('crm.documents.download');
    Route::delete('/crm/documents/{document}',      [CrmController::class, 'deleteDocument'])->name('crm.documents.delete');
    Route::post('/crm/{crm}/slas',              [CrmController::class, 'createSla'])->name('crm.slas.store');
    Route::patch('/crm/slas/{sla}/status',      [CrmController::class, 'updateSlaStatus'])->name('crm.slas.status');
    Route::post('/crm/slas/{sla}/upload',       [CrmController::class, 'uploadSignedSla'])->name('crm.slas.upload');
    Route::post('/crm/{crm}/quotations',        [CrmController::class, 'createQuotation'])->name('crm.quotations.store');

    // ── Settings ──────────────────────────────────────────────────────────
    Route::get('/settings',              [SettingsController::class, 'index'])->name('settings.index');

    Route::get('/settings/sla/new',      [SettingsController::class, 'createSlaTemplate'])->name('settings.sla.create');
    Route::post('/settings/sla',         [SettingsController::class, 'storeSlaTemplate'])->name('settings.sla.store');
    Route::get('/settings/sla/{template}/edit', [SettingsController::class, 'editSlaTemplate'])->name('settings.sla.edit');
    Route::put('/settings/sla/{template}',      [SettingsController::class, 'updateSlaTemplate'])->name('settings.sla.update');
    Route::patch('/settings/sla/{template}/toggle', [SettingsController::class, 'toggleSlaTemplate'])->name('settings.sla.toggle');

    Route::get('/settings/qt/new',       [SettingsController::class, 'createQtTemplate'])->name('settings.qt.create');
    Route::post('/settings/qt',          [SettingsController::class, 'storeQtTemplate'])->name('settings.qt.store');
    Route::get('/settings/qt/{template}/edit', [SettingsController::class, 'editQtTemplate'])->name('settings.qt.edit');
    Route::put('/settings/qt/{template}',      [SettingsController::class, 'updateQtTemplate'])->name('settings.qt.update');
    Route::patch('/settings/qt/{template}/toggle', [SettingsController::class, 'toggleQtTemplate'])->name('settings.qt.toggle');

    Route::post('/settings/staff',           [SettingsController::class, 'storeStaff'])->name('settings.staff.store');
    Route::patch('/settings/staff/{user}/toggle', [SettingsController::class, 'toggleStaff'])->name('settings.staff.toggle');

    // ── Other admin stubs ─────────────────────────────────────────────────
    Route::get('/marketing',   fn() => view('admin.stub', ['module' => 'Marketing']))->name('marketing.index');
    Route::get('/screening',   fn() => view('admin.stub', ['module' => 'Screening']))->name('screening.index');
    Route::get('/whatsapp',    fn() => view('admin.stub', ['module' => 'WhatsApp']))->name('whatsapp.index');
    Route::get('/accounting',  fn() => view('admin.stub', ['module' => 'Accounting']))->name('admin.accounting');
    Route::get('/kyc',                    fn() => view('admin.stub', ['module' => 'KYC Submissions']))->name('kyc.index');
    Route::get('/kyc/submissions',        fn() => view('admin.stub', ['module' => 'KYC Submissions']))->name('kyc.submissions');
    Route::get('/kyc/submissions/{id}',   fn() => view('admin.stub', ['module' => 'KYC Review']))->name('kyc.review');
    Route::get('/kyc/tenants',            fn() => view('admin.stub', ['module' => 'Tenants']))->name('kyc.tenants');
    Route::post('/kyc/tenants',           fn() => back())->name('kyc.tenants.create');
    Route::patch('/kyc/submissions/{id}/approve', fn() => back())->name('kyc.approve');
    Route::patch('/kyc/submissions/{id}/reject',  fn() => back())->name('kyc.reject');
});

// ── Tenant portal routes  /{slug}/... ──────────────────────────────────────
Route::prefix('{slug}')
    ->middleware('resolve.tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/',             [TenantDashboard::class, 'index'])->name('dashboard');
        Route::get('/clients',      [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/new',  [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients',     [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}', [ClientController::class, 'show'])->name('clients.show');
        Route::post('/clients/{client}/documents',           [ClientController::class, 'uploadDocument'])->name('docs.upload');
        Route::get('/documents/{document}/download',         [ClientController::class, 'downloadDocument'])->name('docs.download');
        Route::delete('/documents/{document}',               [ClientController::class, 'deleteDocument'])->name('docs.delete');
        Route::get('/screening',    fn() => view('tenant.stub', ['module' => 'Screening',         'tenant' => app('tenant')]))->name('screening');
        Route::get('/risk',         fn() => view('tenant.stub', ['module' => 'Risk Assessment',   'tenant' => app('tenant')]))->name('risk');
        Route::get('/docs/company', fn() => view('tenant.stub', ['module' => 'Company Documents', 'tenant' => app('tenant')]))->name('docs.company');
        Route::get('/docs/clients', fn() => view('tenant.stub', ['module' => 'Client Documents',  'tenant' => app('tenant')]))->name('docs.clients');
        Route::get('/goaml',        fn() => view('tenant.stub', ['module' => 'goAML Reports',     'tenant' => app('tenant')]))->name('goaml');
        Route::get('/settings',     fn() => view('tenant.stub', ['module' => 'Settings',          'tenant' => app('tenant')]))->name('settings');
    });
