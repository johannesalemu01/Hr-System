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
    
    // Route::get('/payroll', function () {
    //     return Inertia::render('Payroll/index');
    // });
    
    Route::get('/attendance', function () {
        return Inertia::render('Attendance/index');
    });
    
    Route::get('/leave', function () {
        return Inertia::render('Leave/index');
    });
    
    Route::get('/settings', function () {
        return Inertia::render('Settings/index');
    })->name('settings');
});

Route::group(['middleware' => ['role:admin']], function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard/index');
    })->name('dashboard');

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


// Employee routes


// Route::middleware(['auth', 'permission:view employees'])->group(function () {
//     Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
//     Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');
// });

// Employee management routes (for HR and admins)
Route::middleware(['auth', 'permission:view employees|create employees|edit employees|delete employees'])->group(function () {
    
    Route::get('/employees',[EmployeeController::class,'index'])->name('employees.index');
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show');

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

// Add these routes to your existing web.php file
Route::middleware(['auth', 'verified'])->group(function () {
    // Leave Management
    Route::get('/leave', [App\Http\Controllers\LeaveRequestController::class, 'index'])->name('leave.index');
    Route::post('/leave', [App\Http\Controllers\LeaveRequestController::class, 'store'])->name('leave.store');
    Route::patch('/leave/{id}/status', [App\Http\Controllers\LeaveRequestController::class, 'updateStatus'])->name('leave.update-status');
});



//payroll

// Payroll Routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/payroll', [App\Http\Controllers\PayrollController::class, 'index'])->name('payroll.index');
    Route::get('/payroll/payslip/{id}', [App\Http\Controllers\PayrollController::class, 'generatePayslip'])->name('payroll.payslip');
    Route::post('/payroll/{id}/process', [App\Http\Controllers\PayrollController::class, 'processPayroll'])->name('payroll.process');
    Route::post('/payroll/{id}/release', [App\Http\Controllers\PayrollController::class, 'releasePayroll'])->name('payroll.release');
});



//attenance

// Add these routes to your existing web.php file
Route::middleware(['auth', 'verified'])->group(function () {
    // Attendance Management
    Route::get('/attendance', [App\Http\Controllers\AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [App\Http\Controllers\AttendanceController::class, 'store'])->name('attendance.store');
    Route::patch('/attendance/{attendance}', [App\Http\Controllers\AttendanceController::class, 'update'])->name('attendance.update');
});



//department


// Department Management
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/departments', [App\Http\Controllers\DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/create', [App\Http\Controllers\DepartmentController::class, 'create'])->name('departments.create');
    Route::post('/departments', [App\Http\Controllers\DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{id}', [App\Http\Controllers\DepartmentController::class, 'show'])->name('departments.show');
    Route::get('/departments/{id}/edit', [App\Http\Controllers\DepartmentController::class, 'edit'])->name('departments.edit');
    Route::patch('/departments/{id}', [App\Http\Controllers\DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{id}', [App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.destroy');
});
require __DIR__.'/auth.php';
