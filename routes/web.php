<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\KpiController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\AttendanceController;
use Spatie\Permission\Models\Role; // Import the Role class
use App\Models\User;
use App\Http\Controllers\UserController;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use App\Http\Controllers\DepartmentController; // Ensure DepartmentController is imported


// --- WORKING DEBUGGING ROUTE (No Auth/Permission Middleware) ---
Route::get('/employees/create', [EmployeeController::class, 'create'])
    ->middleware(['web']) // Only apply 'web' middleware
    ->name('employees.create');
// --- END WORKING DEBUGGING ROUTE ---

// --- TEMPORARILY MOVED DEPARTMENT CREATE ROUTE (Auth only) ---
Route::get('/departments/create', [DepartmentController::class, 'create'])
    ->middleware(['web', 'auth']) // Apply 'web' middleware
    ->name('departments.create');
// --- END TEMPORARILY MOVED ROUTE ---

Route::middleware(['auth'])->group(function () {
    Route::get('/kpis/employee-kpis', [KpiController::class, 'employeeKpis'])->name('kpis.employee-kpis');
});


Route::get('/kpis/leaderboard', [KpiController::class, 'leaderboard'])->name('kpis.leaderboard'); // Add this line
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
    
    Route::get('/kpis', [KpiController::class,'index'])->name('kpis.index');
    
    // Route::get('/kpis/create', function () {
    //     return Inertia::render('Kpis/Create');
    // });


    
    Route::get('/payroll', [PayrollController::class,'index'])->name('payroll.index');
    
    Route::get('/payroll/payslip/{id}', [PayrollController::class, 'payslip'])->name('payroll.payslip');
    
    Route::get('/attendance', 
        [AttendanceController::class, 'index'])->name('attendance.index');
    
    // Route::get('/leave', function () {
    //     return Inertia::render('Leave/index');
    // });
    
    Route::get('/settings', function () {
        return Inertia::render('Settings/index');
    })->name('settings');


    //IMPORTANT PROFILE

    Route::post('/user/upload-profile-picture', [UserController::class, 'uploadProfilePicture'])->name('user.upload-profile-picture');

    Route::get('/payroll/payslip/{id}/download', [PayrollController::class, 'downloadPayslip'])->name('payroll.payslip.download');
    Route::get('/payroll/download-all-payslips', [PayrollController::class, 'downloadAllPayslips'])->name('payroll.downloadAllPayslips');
});


Route::group(['middleware' => ['role:admin']], function () {
    Route::get('/admin', [AdminController::class, 'index'])->name('admin.index');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    // Route::get('/dashboard', function () {
    //     return Inertia::render('Dashboard/index');
    // })->name('dashboard');

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
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update'); // Ensure this route exists
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update'); // Allow POST for updates
    Route::post('/profile/upload-profile-picture', [ProfileController::class, 'uploadProfilePicture'])->name('profile.upload-profile-picture');
});


//NOTE NEW


// Employee routes


Route::middleware(['auth', 'permission:view employees'])->group(function () {
    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index'); // Ensure this route exists
    Route::get('/employees/{employee}', [EmployeeController::class, 'show'])->name('employees.show'); // Ensure this route exists
    Route::get('/employees/{employee}/profile', [EmployeeController::class, 'profile'])->name('employees.profile');
    Route::post('/employees/{employee}/upload-profile-picture', [EmployeeController::class, 'uploadProfilePicture'])->name('employees.upload-profile-picture');

    Route::post('/api/employees/{employee}/upload-profile', [EmployeeController::class, 'uploadProfilePicture'])->name('employees.upload-profile');
});

Route::post('/employees/{id}/upload-profile', [EmployeeController::class, 'uploadProfilePicture']);

// Employee management routes (for HR and admins)
// Keep this group without the create route for now
Route::middleware(['web', 'auth', 'permission:create employees|edit employees|delete employees'])->group(function () {
    // Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create'); // MOVED TO TOP FOR DEBUGGING
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('/employees/{employee}/edit', [EmployeeController::class, 'edit'])->name('employees.edit');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::post('/employees/{employee}/upload-profile-picture', [EmployeeController::class, 'uploadProfilePicture'])->name('employees.upload-profile-picture');
});

Route::middleware(['auth', 'permission:create employees'])->group(function () {
    Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create');
    Route::get('/employees/create-data', [EmployeeController::class, 'createData'])->name('employees.create-data');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
});


// Route::middleware(['auth', 'permission:create employees'])->group(function () {
//     Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create'); // Ensure this route exists
//     Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
// });

// Add these routes to your existing web.php file
// Route::middleware(['auth', 'verified'])->group(function () {
//     // Leave Management
//     Route::post('/leave', [App\Http\Controllers\LeaveRequestController::class, 'store'])->name('leave.store');
// });

// Add these routes to your existing web.php file
// Route::middleware(['auth', 'verified'])->group(function () {
//     // Leave Management
//     Route::get('/leave', [App\Http\Controllers\LeaveRequestController::class, 'index'])->name('leave.index');
//     Route::post('/leave', [App\Http\Controllers\LeaveRequestController::class, 'store'])->name('leave.store');
//     Route::patch('/leave/{id}/status', [App\Http\Controllers\LeaveRequestController::class, 'updateStatus'])->name('leave.update-status');
// });

// Department routes
Route::middleware(['auth', 'permission:view departments'])->group(function () {
    Route::get('/departments', [\App\Http\Controllers\DepartmentController::class, 'index'])->name('departments.index');
    Route::get('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'show'])->name('departments.show');
});

// Remove the GET /departments/create route from this group
Route::middleware(['auth', 'permission:create departments|edit departments|delete departments'])->group(function () {
    // Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create'); // MOVED OUTSIDE
    Route::post('/departments', [\App\Http\Controllers\DepartmentController::class, 'store'])->name('departments.store');
    Route::get('/departments/{department}/edit', [\App\Http\Controllers\DepartmentController::class, 'edit'])->name('departments.edit');
    Route::put('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'update'])->name('departments.update');
    Route::delete('/departments/{department}', [\App\Http\Controllers\DepartmentController::class, 'destroy'])->name('departments.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
});

// Attendance routes
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::patch('/attendance/{id}', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::delete('/attendance/{id}', [AttendanceController::class, 'destroy'])->name('attendance.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
});

Route::middleware(['auth', 'permission:edit kpis'])->group(function () {
    Route::get('/kpis/{kpi}/edit', [KpiController::class, 'edit'])->name('kpis.edit'); // Ensure this GET route exists
    Route::put('/kpis/{kpi}', [KpiController::class, 'update'])->name('kpis.update'); // Ensure this PUT route exists
});

Route::middleware(['auth', 'permission:create kpis'])->group(function () {
    Route::get('/kpis/create', [KpiController::class, 'create'])->name('kpis.create');
    Route::post('/kpis', [KpiController::class, 'store'])->name('kpis.store');
});

Route::middleware(['auth', 'permission:create employee kpis'])->group(function () {
    Route::get('/kpis/assign', [KpiController::class, 'assignKpi'])->name('kpis.assign');
    Route::post('/kpis/assign', [KpiController::class, 'storeEmployeeKpi'])->name('kpis.store-employee-kpi');
});

Route::middleware(['auth', 'permission:create kpi records'])->group(function () {
    Route::get('/kpis/{employeeKpi}/record', [KpiController::class, 'recordKpi'])->name('kpis.record');
    Route::post('/kpis/record', [KpiController::class, 'storeKpiRecord'])->name('kpis.store-record');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/kpis/dashboard', [KpiController::class, 'dashboard'])->name('kpis.dashboard');
    Route::get('/kpis/{kpi}', [KpiController::class, 'show'])->name('kpis.show');
    // Route::get('/kpis/leaderboard', [KpiController::class, 'leaderboard'])->name('kpis.leaderboard'); // Add this line
});

// Route::middleware(['auth', 'permission:view employee kpis'])->group(function () {
//     Route::get('/kpis/employee-kpis', [KpiController::class, 'employeeKpis'])->name('kpis.employee-kpis');
// });



Route::middleware(['auth'])->group(function () {
    Route::get('/leave', [\App\Http\Controllers\LeaveRequestController::class, 'index'])->name('leave.index');
    Route::post('/leave', [\App\Http\Controllers\LeaveRequestController::class, 'store'])->name('leave.store');
    Route::patch('/leave/{id}/status', [\App\Http\Controllers\LeaveRequestController::class, 'updateStatus'])->name('leave.update-status');
});

Route::middleware(['auth', 'permission:delete kpis'])->group(function () {
    Route::delete('/kpis/{kpi}', [KpiController::class, 'destroy'])->name('kpis.destroy');
});

Route::post('/employees/{id}/upload-profile', [EmployeeController::class, 'uploadProfilePicture']);


// Route::middleware(['auth'])->group(function () {
//     Route::get('/employees/create', [EmployeeController::class, 'create'])->name('employees.create'); // Ensure this route exists
//     Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
// });

Route::middleware(['auth', 'verified'])->group(function () {
    // Leave Management
    Route::get('/leave', [\App\Http\Controllers\LeaveRequestController::class, 'index'])->name('leave.index');
    Route::post('/leave', [\App\Http\Controllers\LeaveRequestController::class, 'store'])->name('leave.store');
    Route::patch('/leave/{id}/status', [\App\Http\Controllers\LeaveRequestController::class, 'updateStatus'])->name('leave.update-status');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/settings', [\App\Http\Controllers\SettingsController::class, 'index'])->name('settings');
    Route::post('/settings/profile', [\App\Http\Controllers\SettingsController::class, 'updateProfile'])->name('settings.update-profile');
    Route::post('/settings/company', [\App\Http\Controllers\SettingsController::class, 'updateCompany'])->name('settings.update-company');
    
});



require __DIR__.'/auth.php';
