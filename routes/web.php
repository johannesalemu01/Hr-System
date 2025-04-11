<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use Spatie\Permission\Models\Role; // Import the Role class
use App\Models\User;


// Route::resource('permissions',App\Http\Controllers\PermissionController::class);

// $role=Role::create(['name' => 'admin']);
// $role=Role::create(['name' => 'user']);
// Group routes with middleware to check roles


// Add these to your routes/web.php file
Route::middleware(['auth'])->group(function () {
    Route::get('/employees', function () {
        return Inertia::render('Employees/index');
    });
    
    // Route::get('/departments', function () {
    //     return Inertia::render('Departments/index');
    // });
    
    Route::get('/kpis', function () {
        return Inertia::render('Kpis/index');
    });
    
    Route::get('/payroll', function () {
        return Inertia::render('Payroll/index');
    });
    
    Route::get('/attendance', function () {
        return Inertia::render('Attendance/index');
    });
    
    // Route::get('/leave', function () {
    //     return Inertia::render('Leave/index');
    // });
    
    Route::get('/settings', function () {
        return Inertia::render('Settings/index');
    })->name('settings');
});

Route::group(['middleware' => ['role:admin']], function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Route::get('/dashboard', function () {
    //     return Inertia::render('Dashboard/index');
    // })->name('dashboard');

    //IMPORTANT USE CONROLLER FOR THE DSHBOARD
    Route::middleware(['auth', 'verified'])->group(function () {
        // Dashboard
        Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
        

    });


    Route::get('/permissions', [PermissionController::class, 'index'])->name('roles.index');
    Route::get('/permissions/create', [PermissionController::class, 'create'])->name('roles.create');
    Route::post('/permissions', [PermissionController::class, 'store'])->name('roles.store');
    Route::get('/permissions/{role}/edit', [PermissionController::class, 'edit'])->name('roles.edit');
    Route::put('/permissions/{role}', [PermissionController::class, 'update'])->name('roles.update');
    Route::delete('/permissions/{role}', [PermissionController::class, 'destroy'])->name('roles.destroy');
});


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});


//NOTE NEW




// Add these routes to your existing web.php file
Route::middleware(['auth', 'verified'])->group(function () {
    // Department Management
    Route::get('/departments', [App\Http\Controllers\DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [App\Http\Controllers\DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [App\Http\Controllers\DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{id}', [App\Http\Controllers\DepartmentController::class, 'show'])->name('departments.show');
    Route::get('/departments/{id}/edit', [App\Http\Controllers\DepartmentController::class, 'edit'])->name('departments.edit');
    Route::patch('/departments/{id}', [App\Http\Controllers\DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{id}', [App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.destroy');
});



// Employee routes


Route::middleware(['auth', 'permission:view employees'])->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
});

// Employee management routes (for HR and admins)
Route::middleware(['auth', 'permission:create employees|edit employees|delete employees'])->group(function () {
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
});

// Add these routes to your existing web.php file
// Route::middleware(['auth', 'verified'])->group(function () {
//     // Leave Management
//     Route::post('/leave', [App\Http\Controllers\LeaveRequestController::class, 'store'])->name('leave.store');
// });


Route::middleware(['auth', 'verified'])->group(function () {
    // Leave Management
    Route::get('/leave', [App\Http\Controllers\LeaveRequestController::class, 'index'])->name('leave.index');
    Route::post('/leave', [App\Http\Controllers\LeaveRequestController::class, 'store'])->name('leave.store');
    Route::patch('/leave/{id}/status', [App\Http\Controllers\LeaveRequestController::class, 'updateStatus'])->name('leave.update-status');
});




// KPI Management Routes
Route::middleware(['auth', 'verified'])->group(function () {
    // KPI Dashboard
    Route::get('/kpis/dashboard', [App\Http\Controllers\KpiController::class, 'dashboard'])->name('kpis.dashboard');
    
    // KPI CRUD
    Route::get('/kpis', [App\Http\Controllers\KpiController::class, 'index'])->name('kpis.index');
    Route::get('/kpis/create', [App\Http\Controllers\KpiController::class, 'create'])->name('kpis.create');
    Route::post('/kpis', [App\Http\Controllers\KpiController::class, 'store'])->name('kpis.store');
    Route::get('/kpis/{id}', [App\Http\Controllers\KpiController::class, 'show'])->name('kpis.show');
    Route::get('/kpis/{id}/edit', [App\Http\Controllers\KpiController::class, 'edit'])->name('kpis.edit');
    Route::put('/kpis/{id}', [App\Http\Controllers\KpiController::class, 'update'])->name('kpis.update');
    Route::delete('/kpis/{id}', [App\Http\Controllers\KpiController::class, 'destroy'])->name('kpis.destroy');
    
    // Employee KPIs
    Route::get('/employee-kpis', [App\Http\Controllers\KpiController::class, 'employeeKpis'])->name('kpis.employee-kpis');
    Route::get('/kpis/assign', [App\Http\Controllers\KpiController::class, 'assignKpi'])->name('kpis.assign');
    Route::post('/employee-kpis', [App\Http\Controllers\KpiController::class, 'storeEmployeeKpi'])->name('kpis.store-employee-kpi');
    
    // KPI Records
    Route::get('/employee-kpis/{id}/record', [App\Http\Controllers\KpiController::class, 'recordKpi'])->name('kpis.record');
    Route::post('/kpi-records', [App\Http\Controllers\KpiController::class, 'storeKpiRecord'])->name('kpis.store-record');
});


require __DIR__.'/auth.php';

require __DIR__.'/auth.php';
