<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AbsenceRequestController;
use App\Http\Controllers\Api\ExpenseController;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware(['auth'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/vacations', function () {
        return view('vacations', [
            'pernr' => auth()->user()->sap_employee_id,
        ]);
    })->name('vacations');

    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses');

    Route::get('/pending-approvals', function () {
        return view('pending-approvals');
    })->name('pending-approvals');

  
    Route::post('/hr/absence-requests', [AbsenceRequestController::class, 'store']);
    Route::post('/hr/absence-requests/{absenceRequest}/sign-employee', [AbsenceRequestController::class, 'signByEmployee']);
    Route::post('/hr/absence-requests/{absenceRequest}/sign-signer', [AbsenceRequestController::class, 'signBySigner']);
    Route::post('/hr/absence-requests/{absenceRequest}/reject', [AbsenceRequestController::class, 'rejectBySigner']);

    Route::get('/hr/absence-requests/mine', [AbsenceRequestController::class, 'myRequests']);
    Route::get('/hr/absence-requests/pending-signer', [AbsenceRequestController::class, 'pendingForSigner']);

    Route::get('/expenses', function () {return view('expenses');})->name('expenses');

    // Gastos - vistas
    Route::get('/expenses', function () { return view('expenses'); })->name('expenses');

    // Gastos - rutas estáticas primero
    Route::get('/expenses/pending-approver', [ExpenseController::class, 'pendingForApprover']);
    Route::get('/expenses/latest/draft', [ExpenseController::class, 'latestDraft']);

    // Gastos - rutas dinámicas después
    Route::post('/expenses', [ExpenseController::class, 'store']);
    Route::post('/expenses/{id}/items', [ExpenseController::class, 'addItem']);
    Route::post('/expenses/{id}/sign', [ExpenseController::class, 'signByEmployee']);
    Route::post('/expenses/{id}/approve', [ExpenseController::class, 'approve']);
    Route::post('/expenses/{id}/reject', [ExpenseController::class, 'reject']);
    Route::get('/expenses/{id}', [ExpenseController::class, 'show']);

    Route::post('/expenses/{id}/approve-admin', [ExpenseController::class, 'approveByAdmin']);
    Route::post('/expenses/{id}/reject-admin', [ExpenseController::class, 'rejectByAdmin']);
    Route::get('/expenses/mine', [ExpenseController::class, 'myRequests']);
    
});

require __DIR__.'/auth.php';