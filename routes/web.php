<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CrmController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ScreeningController;
use App\Http\Controllers\Admin\WhatsAppController;
use App\Http\Controllers\Admin\DocumentController;
use App\Http\Controllers\Tenant\DashboardController as TenantDashboard;
use App\Http\Controllers\Tenant\ClientController;
use App\Http\Controllers\Tenant\ScreeningController as TenantScreeningController;
use App\Http\Controllers\Tenant\RiskController;
use App\Http\Controllers\Tenant\TenantDocumentController;
use App\Models\CrmQuotation;

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
    Route::patch('/crm/{crm}/stage',               [CrmController::class, 'updateStage'])->name('crm.stage');
    Route::post('/crm/{crm}/notes',                [CrmController::class, 'addNote'])->name('crm.notes.store');
    Route::post('/crm/{crm}/tasks',                [CrmController::class, 'addTask'])->name('crm.tasks.store');
    Route::patch('/crm/tasks/{task}/complete',     [CrmController::class, 'completeTask'])->name('crm.tasks.complete');
    Route::post('/crm/{crm}/documents',            [CrmController::class, 'uploadDocument'])->name('crm.documents.upload');
    Route::get('/crm/documents/{document}/download',[CrmController::class, 'downloadDocument'])->name('crm.documents.download');
    Route::delete('/crm/documents/{document}',     [CrmController::class, 'deleteDocument'])->name('crm.documents.delete');
    Route::post('/crm/{crm}/slas',                 [CrmController::class, 'createSla'])->name('crm.slas.store');
    Route::patch('/crm/slas/{sla}/status',         [CrmController::class, 'updateSlaStatus'])->name('crm.slas.status');
    Route::post('/crm/slas/{sla}/upload',          [CrmController::class, 'uploadSignedSla'])->name('crm.slas.upload');
    Route::post('/crm/{crm}/quotations',           [CrmController::class, 'createQuotation'])->name('crm.quotations.store');

    // ── Document generation ───────────────────────────────────────────────
    Route::get('/crm/slas/{sla}/download',             [DocumentController::class, 'generateSla'])->name('sla.download');
    Route::get('/crm/quotations/{quotation}/download', [DocumentController::class, 'generateQuotation'])->name('crm.quotations.download');

    // ── Standalone quotations ─────────────────────────────────────────────
    Route::get('/quotations',              [DocumentController::class, 'quotationIndex'])->name('quotations.index');
    Route::get('/quotations/new',          [DocumentController::class, 'quotationCreate'])->name('quotations.create');
    Route::post('/quotations',             [DocumentController::class, 'quotationStore'])->name('quotations.store');
    Route::get('/quotations/{quotation}',  [DocumentController::class, 'quotationShow'])->name('quotations.show');
    Route::get('/quotations/{quotation}/download', [DocumentController::class, 'generateStandaloneQuotation'])->name('quotations.download');
    Route::patch('/quotations/{quotation}/status', function (Request $request, CrmQuotation $quotation) {
        $quotation->update(['status' => $request->status]);
        return back()->with('success', 'Status updated.');
    })->name('quotations.status');

    // ── Settings ──────────────────────────────────────────────────────────
    Route::get('/settings',                         [SettingsController::class, 'index'])->name('settings.index');
    Route::get('/settings/sla/new',                 [SettingsController::class, 'createSlaTemplate'])->name('settings.sla.create');
    Route::post('/settings/sla',                    [SettingsController::class, 'storeSlaTemplate'])->name('settings.sla.store');
    Route::get('/settings/sla/{template}/edit',     [SettingsController::class, 'editSlaTemplate'])->name('settings.sla.edit');
    Route::put('/settings/sla/{template}',          [SettingsController::class, 'updateSlaTemplate'])->name('settings.sla.update');
    Route::patch('/settings/sla/{template}/toggle', [SettingsController::class, 'toggleSlaTemplate'])->name('settings.sla.toggle');
    Route::get('/settings/qt/new',                  [SettingsController::class, 'createQtTemplate'])->name('settings.qt.create');
    Route::post('/settings/qt',                     [SettingsController::class, 'storeQtTemplate'])->name('settings.qt.store');
    Route::get('/settings/qt/{template}/edit',      [SettingsController::class, 'editQtTemplate'])->name('settings.qt.edit');
    Route::put('/settings/qt/{template}',           [SettingsController::class, 'updateQtTemplate'])->name('settings.qt.update');
    Route::patch('/settings/qt/{template}/toggle',  [SettingsController::class, 'toggleQtTemplate'])->name('settings.qt.toggle');
    Route::post('/settings/staff',                  [SettingsController::class, 'storeStaff'])->name('settings.staff.store');
    Route::patch('/settings/staff/{user}/toggle',   [SettingsController::class, 'toggleStaff'])->name('settings.staff.toggle');

    // ── Other admin stubs ─────────────────────────────────────────────────
    Route::get('/marketing', fn() => view('admin.marketing.index'))->name('marketing.index');
    // ── Screening ─────────────────────────────────────────────────────────
    Route::get('/screening',                              [ScreeningController::class, 'index'])->name('screening.index');
    Route::post('/screening/run',                         [ScreeningController::class, 'run'])->name('screening.run');
    Route::post('/crm/{crm}/screen',                      [ScreeningController::class, 'screenClient'])->name('screening.client');
    Route::post('/crm/shareholders/{shareholder}/screen', [ScreeningController::class, 'screenShareholder'])->name('screening.shareholder');
    // ── WhatsApp ──────────────────────────────────────────────────────────
    Route::get('/whatsapp',                          [WhatsAppController::class, 'index'])->name('whatsapp.index');
    Route::get('/whatsapp/api/status',               [WhatsAppController::class, 'status'])->name('wa.status');
    Route::get('/whatsapp/api/groups',               [WhatsAppController::class, 'groups'])->name('wa.groups');
    Route::post('/whatsapp/api/groups/refresh',      [WhatsAppController::class, 'refreshGroups'])->name('wa.groups.refresh');
    Route::get('/whatsapp/api/schedules',            [WhatsAppController::class, 'schedules'])->name('wa.schedules');
    Route::post('/whatsapp/api/schedules',           [WhatsAppController::class, 'addSchedule'])->name('wa.schedules.add');
    Route::put('/whatsapp/api/schedules/{id}',      [WhatsAppController::class, 'updateSchedule'])->name('wa.schedules.update');
    Route::delete('/whatsapp/api/schedules/{id}',   [WhatsAppController::class, 'deleteSchedule'])->name('wa.schedules.delete');
    Route::post('/whatsapp/api/send/immediate',      [WhatsAppController::class, 'sendImmediate'])->name('wa.send');
    Route::post('/whatsapp/api/reconnect',           [WhatsAppController::class, 'reconnect'])->name('wa.reconnect');
    Route::post('/whatsapp/api/disconnect',          [WhatsAppController::class, 'disconnect'])->name('wa.disconnect');
    Route::get('/whatsapp/api/logs',                 [WhatsAppController::class, 'logs'])->name('wa.logs');
    Route::get('/accounting', fn() => view('admin.stub', ['module' => 'Accounting']))->name('admin.accounting');
    Route::get('/kyc',                          fn() => view('admin.stub', ['module' => 'KYC Submissions']))->name('kyc.index');
    Route::get('/kyc/submissions',              fn() => view('admin.stub', ['module' => 'KYC Submissions']))->name('kyc.submissions');
    Route::get('/kyc/submissions/{id}',         fn() => view('admin.stub', ['module' => 'KYC Review']))->name('kyc.review');
    Route::get('/kyc/tenants',                  fn() => view('admin.stub', ['module' => 'Tenants']))->name('kyc.tenants');
    Route::post('/kyc/tenants',                 fn() => back())->name('kyc.tenants.create');
    Route::patch('/kyc/submissions/{id}/approve', fn() => back())->name('kyc.approve');
    Route::patch('/kyc/submissions/{id}/reject',  fn() => back())->name('kyc.reject');

}); // ← end auth middleware group

// ── Tenant portal routes  /{slug}/... ──────────────────────────────────────
Route::prefix('{slug}')
    ->middleware('resolve.tenant')
    ->name('tenant.')
    ->group(function () {
        Route::get('/',             [TenantDashboard::class, 'index'])->name('dashboard');
        Route::get('/clients',      [ClientController::class, 'index'])->name('clients.index');
        Route::get('/clients/new',  [ClientController::class, 'create'])->name('clients.create');
        Route::post('/clients',     [ClientController::class, 'store'])->name('clients.store');
        Route::get('/clients/{client}',        [ClientController::class, 'show'])->name('clients.show');
        Route::get('/clients/{client}/edit',   [ClientController::class, 'edit'])->name('clients.edit');
        Route::patch('/clients/{client}',      [ClientController::class, 'update'])->name('clients.update');
        Route::patch('/clients/{client}/risk',         [ClientController::class, 'updateRisk'])->name('clients.risk');
        Route::patch('/clients/{client}/status',       [ClientController::class, 'updateStatus'])->name('clients.status');
        Route::patch('/clients/{client}/declarations', [ClientController::class, 'updateDeclarations'])->name('clients.declarations');
        Route::post('/clients/{client}/documents',   [ClientController::class, 'uploadDocument'])->name('docs.upload');
        Route::get('/documents/{document}/download', [ClientController::class, 'downloadDocument'])->name('docs.download');
        Route::delete('/documents/{document}',       [ClientController::class, 'deleteDocument'])->name('docs.delete');
        Route::get('/screening',                          [TenantScreeningController::class, 'index'])->name('screening');
        Route::post('/screening/run',                      [TenantScreeningController::class, 'run'])->name('screening.run');
        Route::post('/clients/{client}/screen',           [TenantScreeningController::class, 'screenClient'])->name('clients.screen');
        Route::get('/risk',                          [RiskController::class, 'index'])->name('risk');
        Route::get('/risk/{client}/assess',          [RiskController::class, 'assess'])->name('risk.assess');
        Route::post('/risk/{client}/assess',         [RiskController::class, 'saveAssessment'])->name('risk.save');
        // ── Documents ──────────────────────────────────────────────────────────
        Route::get('/docs/company',                         [TenantDocumentController::class, 'companyIndex'])->name('docs.company');
        Route::post('/docs/company/upload',                 [TenantDocumentController::class, 'companyUpload'])->name('docs.company.upload');
        Route::get('/docs/company/{document}/download',     [TenantDocumentController::class, 'companyDownload'])->name('docs.company.download');
        Route::delete('/docs/company/{document}',           [TenantDocumentController::class, 'companyDelete'])->name('docs.company.delete');
        Route::get('/docs/clients',                         [TenantDocumentController::class, 'clientIndex'])->name('docs.clients');
        Route::get('/docs/clients/{document}/download',     [TenantDocumentController::class, 'clientDownload'])->name('docs.client.download');
        Route::delete('/docs/clients/{document}',           [TenantDocumentController::class, 'clientDelete'])->name('docs.client.delete');
        Route::get('/goaml',        fn() => view('tenant.stub', ['module' => 'goAML Reports',     'tenant' => app('tenant')]))->name('goaml');
        Route::get('/settings',     fn() => view('tenant.stub', ['module' => 'Settings',          'tenant' => app('tenant')]))->name('settings');
    });
