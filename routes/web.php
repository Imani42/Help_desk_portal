<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerAuthController;
use App\Http\Controllers\StaffAuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\TechnicianController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\FaultCommentController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordResetController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('dashboard', [
        'adminExists' => User::where('role', 'admin')->exists(),
        'showRegisterOptions' => false,
    ]);
});

Route::get('/register', function () {
    return view('dashboard', [
        'adminExists' => User::where('role', 'admin')->exists(),
        'showRegisterOptions' => true,
    ]);
});

/*
|--------------------------------------------------------------------------
| REGISTRATION
|--------------------------------------------------------------------------
*/
Route::get('/register/customer', [CustomerAuthController::class, 'showRegister']);
Route::post('/register/customer', [CustomerAuthController::class, 'register']);

Route::get('/register/manager', [StaffAuthController::class, 'managerForm']);
Route::post('/register/manager', [StaffAuthController::class, 'registerManager']);

Route::get('/register/technician', [StaffAuthController::class, 'technicianForm']);
Route::post('/register/technician', [StaffAuthController::class, 'registerTechnician']);

Route::get('/register/admin', [StaffAuthController::class, 'adminForm']);
Route::post('/register/admin', [StaffAuthController::class, 'registerAdmin']);

/*
|--------------------------------------------------------------------------
| LOGIN
|--------------------------------------------------------------------------
*/
Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::get('/logout', [LoginController::class, 'logout']);

/*
|--------------------------------------------------------------------------
| PASSWORD RESET
|--------------------------------------------------------------------------
*/
Route::get('/forgot-password', [PasswordResetController::class, 'showLinkRequestForm'])
    ->middleware('guest')
    ->name('password.request');
Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLinkEmail'])
    ->middleware('guest')
    ->name('password.email');
Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetForm'])
    ->middleware('guest')
    ->name('password.reset');
Route::post('/reset-password', [PasswordResetController::class, 'reset'])
    ->middleware('guest')
    ->name('password.update');

/*
|--------------------------------------------------------------------------
| CUSTOMER (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {
    Route::post('/account/update', [AccountController::class, 'updateInfo']);
    Route::post('/account/password', [AccountController::class, 'resetPassword']);
    Route::delete('/account/delete', [AccountController::class, 'delete']);
    Route::post('/faults/{fault}/comments', [FaultCommentController::class, 'store']);
    Route::post('/fault-comments/{comment}/reply', [FaultCommentController::class, 'reply']);
    Route::delete('/fault-comments/{comment}', [FaultCommentController::class, 'destroy']);
});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
    Route::get('/admin/managers/add', [AdminController::class, 'addManager']);
    Route::post('/admin/managers/store', [AdminController::class, 'storeManager']);
    Route::get('/admin/managers', [AdminController::class, 'managers']);
    Route::post('/admin/manager/approve/{id}', [AdminController::class, 'approveManager']);
    Route::post('/admin/manager/deactivate/{id}', [AdminController::class, 'deactivateManager']);
    Route::delete('/admin/manager/delete/{id}', [AdminController::class, 'deleteManager']);
    Route::get('/admin/technicians', [AdminController::class, 'technicians']);
    Route::get('/admin/customers', [AdminController::class, 'customers']);
    Route::get('/admin/reports', [AdminController::class, 'reports']);
    Route::post('/admin/reports/comments/{manager}', [AdminController::class, 'saveReportComment']);
    Route::get('/admin/account', [AdminController::class, 'account']);
});

/*
|--------------------------------------------------------------------------
| CUSTOMER (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:customer'])->group(function () {

    Route::get('/customer/dashboard', [CustomerController::class, 'dashboard']);
    Route::get('/customer/report', [CustomerController::class, 'report']);
    Route::get('/customer/my-faults', [CustomerController::class, 'myFaults']);
    Route::get('/customer/resolved', [CustomerController::class, 'resolved']);
    Route::get('/customer/account', [CustomerController::class, 'account']);

    Route::post('/customer/fault/store', [CustomerController::class, 'storeFault']);
    Route::delete('/customer/fault/{id}', [CustomerController::class, 'deleteFault']);
});

/*
|--------------------------------------------------------------------------
| MANAGER
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', 'role:manager'])->group(function () {

    Route::get('/manager/dashboard', [ManagerController::class, 'dashboard']);
    Route::get('/manager/faults', [ManagerController::class, 'allFaults']);
    Route::get('/manager/assigned', [ManagerController::class, 'assigned']);
    Route::get('/manager/account', [ManagerController::class, 'account']);

    Route::post('/manager/assign/{id}', [ManagerController::class, 'assign']);

// TECHNICIANS
Route::get('/manager/technicians', [ManagerController::class, 'technicians']);
Route::post('/manager/technician/store', [ManagerController::class, 'storeTechnician']);
Route::post('/manager/technician/update/{id}', [ManagerController::class, 'updateTechnician']);
Route::delete('/manager/technician/delete/{id}', [ManagerController::class, 'deleteTechnician']);
Route::post('/manager/user/approve/{id}', [ManagerController::class, 'approveUser']);
Route::post('/manager/user/deactivate/{id}', [ManagerController::class, 'deactivateUser']);

// USERS
Route::get('/manager/users', [ManagerController::class, 'users']);
Route::post('/manager/users/store', [ManagerController::class, 'storeUser']);

// CUSTOMERS
Route::get('/manager/customers', [ManagerController::class, 'customers']);
Route::post('/manager/customer/store', [ManagerController::class, 'storeCustomer']);
Route::post('/manager/customer/update/{id}', [ManagerController::class, 'updateCustomer']);
Route::delete('/manager/customer/delete/{id}', [ManagerController::class, 'deleteCustomer']);
});



Route::middleware(['auth', 'role:technician'])->group(function () {

    Route::get('/technician/dashboard', [TechnicianController::class, 'dashboard']);
    Route::get('/technician/assigned', [TechnicianController::class, 'assigned']);
    Route::get('/technician/completed', [TechnicianController::class, 'completed']);
    Route::get('/technician/account', [TechnicianController::class, 'account']);

    Route::post('/technician/update/{id}', [TechnicianController::class, 'updateStatus']);
});
