<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\AdminController;
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
    
    Route::get('/departments', function () {
        return Inertia::render('Departments/index');
    });
    
    Route::get('/kpis', function () {
        return Inertia::render('Kpis/index');
    });
    
    Route::get('/payroll', function () {
        return Inertia::render('Payroll/index');
    });
    
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

require __DIR__.'/auth.php';
