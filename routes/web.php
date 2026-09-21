<?php

use Illuminate\Support\Facades\Route;

// Controllers (Auth Namespace)
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\UserController;
use App\Http\Controllers\Auth\StaffController;
use App\Http\Controllers\Auth\AdminController;
use App\Http\Controllers\Auth\ManagerController;
use App\Http\Controllers\Auth\ManagerServiceController;
use App\Http\Controllers\Auth\ManagerTransactionReportController;
use App\Http\Controllers\Auth\ManagerInventoryController;
use App\Http\Controllers\Auth\ManagerFinancialReportController;
use App\Http\Controllers\Auth\ManagerUtilityTrackingController;
use App\Http\Controllers\Auth\CustomerRegistrationController; 
use App\Http\Controllers\Auth\ManagerPaymentVerificationController;
use App\Http\Controllers\Auth\ServiceController;
use App\Http\Controllers\Auth\PaymentVerificationController;
use App\Http\Controllers\Auth\InventoryController;
use App\Http\Controllers\Auth\DailyExpenseController;
use App\Http\Controllers\Auth\ServiceHistoryController;
use App\Http\Controllers\Auth\BranchInventoryController;
use App\Http\Controllers\Auth\FinancialReportController;
use App\Http\Controllers\Auth\UtilityTrackingController;
use App\Http\Controllers\Auth\BranchController; 
use App\Http\Controllers\Auth\AdminUserController;
use App\Http\Controllers\Auth\LoyaltyController;
use App\Http\Controllers\Auth\ArchivedAccountController;
use App\Http\Controllers\Auth\LandingContentController;

// --- 1. PUBLIC ROUTES ---
Route::get('/', [LandingContentController::class, 'landing'])->name('landing');
Route::post('/contact-message', [LandingContentController::class, 'sendMessage'])->name('contact.message');

Route::get('/register', [RegisterController::class, 'showRegistrationForm'])->name('register');
Route::post('/register', [RegisterController::class, 'register']);

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);

// ==========================================
// FORGOT PASSWORD & OTP ROUTES
// ==========================================
Route::get('/forgot-password', [LoginController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('/forgot-password', [LoginController::class, 'sendOtp'])->name('password.email');
Route::post('/verify-otp-code', [LoginController::class, 'verifyOtpCode']); 
Route::post('/reset-password-store', [LoginController::class, 'resetPasswordWithOtp'])->name('password.update');
// ==========================================


// --- 2. AUTHENTICATED ROUTES ---
Route::middleware(['auth'])->group(function () {
    
    // General Logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // --- User Routes ---
    Route::get('/user', [UserController::class, 'index'])->name('user.dashboard');
    Route::post('/user/service-request', [UserController::class, 'requestService'])->name('user.service.request');
    Route::post('/user/service-request/{id}/cancel', [UserController::class, 'cancelServiceRequest'])->name('user.service.cancel');
    
    // Profile at Password Update Routes
    Route::post('/user/update-profile', [UserController::class, 'updateProfile'])->name('user.update.profile');
    Route::post('/user/update-password', [UserController::class, 'updatePassword'])->name('user.update-password');
    
    Route::post('/payment/submit', [PaymentVerificationController::class, 'submitPayment'])->name('payment.submit');

    // --- Staff Routes ---
    Route::get('/staff', [StaffController::class, 'index'])->name('staff.dashboard');
    Route::post('/staff/save-order', [StaffController::class, 'storeOrder'])->name('staff.save.order');
    Route::post('/staff/expenses/store', [StaffController::class, 'storeExpense'])->name('staff.expenses.store'); 
    Route::get('/staff/services', [ServiceController::class, 'index'])->name('staff.services');
    Route::put('/staff/services/update', [ServiceController::class, 'updateStatus'])->name('staff.services.update');
    
    // Print Receipt Route
    Route::get('/staff/print-receipt/{id}', [ServiceController::class, 'printReceipt'])->name('staff.print.receipt');

    Route::get('/staff/verify-payments', [PaymentVerificationController::class, 'index'])->name('staff.verify.payments');
    Route::put('/staff/verify-payments/approve', [PaymentVerificationController::class, 'approve'])->name('staff.verify.payments.approve');
    Route::get('/staff/inventory', [InventoryController::class, 'index'])->name('staff.inventory');
    Route::post('/staff/inventory/deduct', [InventoryController::class, 'deductManual'])->name('staff.inventory.deduct');
    Route::get('/staff/daily-expenses', [DailyExpenseController::class, 'index'])->name('staff.expenses');

    // --- Manager Routes ---
    Route::get('/manager', function () {
        return redirect()->route('manager.dashboard');
    });
    Route::get('/manager/dashboard', [ManagerController::class, 'dashboard'])->name('manager.dashboard');
    Route::post('/manager/expenses/store', [ManagerController::class, 'storeExpense'])->name('manager.expenses.store');
    
    // Manager Service List Routes
    Route::get('/manager/services', [ManagerServiceController::class, 'index'])->name('manager.services');
    Route::post('/manager/services/save', [ManagerServiceController::class, 'storeOrder'])->name('manager.save.order');
    Route::put('/manager/services/update', [ManagerServiceController::class, 'updateStatus'])->name('manager.services.update');
    Route::get('/manager/print-receipt/{id}', [ManagerServiceController::class, 'printReceipt'])->name('manager.print.receipt');

    // Manager Payment Verification Routes
    Route::get('/manager/verify-payments', [ManagerPaymentVerificationController::class, 'index'])->name('manager.verify.payments');
    Route::put('/manager/verify-payments/approve', [ManagerPaymentVerificationController::class, 'approve'])->name('manager.verify.payments.approve');
    Route::post('/manager/verify-payments/submit', [ManagerPaymentVerificationController::class, 'submitPayment'])->name('manager.verify.payments.submit');

    // Manager Inventory Routes
    Route::get('/manager/inventory', [ManagerInventoryController::class, 'index'])->name('manager.inventory');
    Route::post('/manager/inventory/update', [ManagerInventoryController::class, 'update'])->name('manager.inventory.update');
    Route::get('/manager/daily-expenses', [DailyExpenseController::class, 'index'])->name('manager.expenses');

    // Manager Financial Reports Route
    Route::get('/manager/financial-reports', [ManagerFinancialReportController::class, 'index'])->name('manager.financial.reports');

    // Manager Utility Tracking Route
    Route::get('/manager/utility-tracking', [ManagerUtilityTrackingController::class, 'index'])->name('manager.utility.tracking');
    Route::get('/manager/transaction-report', [ManagerTransactionReportController::class, 'index'])->name('manager.transaction.report');

    // Manager Register Customer Routes
    Route::get('/manager/register-customer', [CustomerRegistrationController::class, 'index'])->name('manager.register.customer');
    Route::post('/manager/register-customer', [CustomerRegistrationController::class, 'store'])->name('manager.register.customer.store');

    // --- Admin Routes ---
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/admin/service-history', [ServiceHistoryController::class, 'index'])->name('admin.service.history');
    Route::get('/admin/transaction-report', [ServiceHistoryController::class, 'index'])->name('admin.transaction.report');
    
    // Admin Inventory (BranchInventoryController)
    Route::get('/admin/inventory', [BranchInventoryController::class, 'index'])->name('admin.inventory');
    Route::get('/admin/inventory/history', [BranchInventoryController::class, 'history'])->name('admin.inventory.history');
    Route::post('/admin/inventory/update', [BranchInventoryController::class, 'updateStock'])->name('admin.inventory.update');

    // Admin Reports & Settings
    Route::get('/admin/financial-reports', [FinancialReportController::class, 'index'])->name('admin.financial.reports');
    Route::get('/admin/utility-tracking', [UtilityTrackingController::class, 'index'])->name('admin.utility.tracking');
    
    // Branch Management Routes
    Route::get('/admin/branch-management', [BranchController::class, 'index'])->name('admin.branch.management');
    Route::post('/admin/branch-management/store', [BranchController::class, 'store'])->name('admin.branch.store');
    Route::post('/admin/branch-management/update', [BranchController::class, 'update'])->name('admin.branch.update');
    Route::get('/admin/branch-management/archive/{id}', [BranchController::class, 'archive'])->name('admin.branch.archive');
    Route::get('/admin/archived-branches', [BranchController::class, 'archived'])->name('admin.branch.archived');
    Route::put('/admin/archived-branches/{id}/restore', [BranchController::class, 'restore'])->name('admin.branch.restore');
    Route::delete('/admin/archived-branches/{id}', [BranchController::class, 'destroy'])->name('admin.branch.destroy');

    // Manage Users Routes
    Route::get('/admin/manage-users', [AdminUserController::class, 'index'])->name('admin.manage.users');
    Route::get('/admin/manage-users/{id}/history', [AdminUserController::class, 'history'])->name('admin.manage.users.history');
    Route::post('/admin/manage-users/store', [AdminUserController::class, 'store'])->name('admin.manage.users.store');
    Route::post('/admin/manage-users/update', [AdminUserController::class, 'update'])->name('admin.manage.users.update');
    Route::get('/admin/manage-users/archive/{id}', [AdminUserController::class, 'archive'])->name('admin.manage.users.archive');

    // Landing Page Content Routes
    Route::get('/admin/modified-content', [LandingContentController::class, 'index'])->name('admin.modified.content');
    Route::post('/admin/modified-content', [LandingContentController::class, 'update'])->name('admin.modified.content.update');

    // --- Admin Archived Accounts Routes ---
    Route::get('/admin/archived-accounts', [ArchivedAccountController::class, 'index'])->name('admin.archived.index');
    Route::put('/admin/archived-accounts/{id}/restore', [ArchivedAccountController::class, 'restore'])->name('admin.archived.restore');
    Route::delete('/admin/archived-accounts/{id}', [ArchivedAccountController::class, 'destroy'])->name('admin.archived.destroy');

    // Loyalty Program Routes
    Route::get('/admin/loyalty-program', [LoyaltyController::class, 'index'])->name('admin.loyalty.program');
    Route::post('/admin/loyalty-program/process', [LoyaltyController::class, 'processPoints'])->name('admin.loyalty.process');

});