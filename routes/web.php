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

/*
|--------------------------------------------------------------------------
| HOME
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('dashboard');
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
| CUSTOMER (PROTECTED)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    Route::get('/customer/dashboard', [CustomerController::class, 'dashboard']);
    Route::get('/customer/report', [CustomerController::class, 'report']);
    Route::get('/customer/my-faults', [CustomerController::class, 'myFaults']);
    Route::get('/customer/resolved', [CustomerController::class, 'resolved']);
    Route::get('/customer/account', [CustomerController::class, 'account']);

    Route::post('/customer/fault/store', [CustomerController::class, 'storeFault']);
});

/*
|--------------------------------------------------------------------------
| MANAGER
|--------------------------------------------------------------------------
*/


Route::middleware(['auth'])->group(function () {

    Route::get('/manager/dashboard', [ManagerController::class, 'dashboard']);
    Route::get('/manager/faults', [ManagerController::class, 'allFaults']);
    Route::get('/manager/assigned', [ManagerController::class, 'assigned']);
    Route::get('/manager/account', [ManagerController::class, 'account']);

    Route::post('/manager/assign/{id}', [ManagerController::class, 'assign']);

// TECHNICIANS
Route::get('/manager/technicians', [ManagerController::class, 'technicians']);
Route::post('/manager/technician/store', [ManagerController::class, 'storeTechnician']);
Route::post('/manager/technician/update/{id}', [ManagerController::class, 'updateTechnician']);
Route::get('/manager/technician/delete/{id}', [ManagerController::class, 'deleteTechnician']);
Route::get('/manager/user/approve/{id}', [ManagerController::class, 'approveUser']);
Route::get('/manager/user/deactivate/{id}', [ManagerController::class, 'deactivateUser']);

// CUSTOMERS
Route::get('/manager/customers', [ManagerController::class, 'customers']);
Route::post('/manager/customer/store', [ManagerController::class, 'storeCustomer']);
Route::post('/manager/customer/update/{id}', [ManagerController::class, 'updateCustomer']);
Route::get('/manager/customer/delete/{id}', [ManagerController::class, 'deleteCustomer']);
});



Route::middleware(['auth'])->group(function () {

    Route::get('/technician/dashboard', [TechnicianController::class, 'dashboard']);
    Route::get('/technician/assigned', [TechnicianController::class, 'assigned']);
    Route::get('/technician/completed', [TechnicianController::class, 'completed']);
    Route::get('/technician/account', [TechnicianController::class, 'account']);

    Route::post('/technician/update/{id}', [TechnicianController::class, 'updateStatus']);
});
