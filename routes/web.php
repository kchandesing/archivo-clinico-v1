<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Pacientes\PacienteController;

// 1. RUTAS DEL ASISTENTE DE INSTALACIÓN (WIZARD)
Route::get('/install', [InstallController::class, 'index'])->name('install.index');
Route::post('/install', [InstallController::class, 'store'])->name('install.store');
Route::get('/install/success', function () { 
    return view('install.success'); 
})->name('install.success');

// 2. RUTAS PÚBLICAS DE AUTENTICACIÓN (Solo accesibles para usuarios NO logueados)
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.store');
});

// 3. RUTAS PROTEGIDAS DEL SISTEMA (Solo para personal que ya inició sesión)
Route::middleware('auth')->group(function () {
    // Ruta para cerrar sesión
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    
        // Panel de Control Principal (Dashboard)
    Route::get('/', function () {
        return view('dashboard.index');
    })->name('home');

    // --- NUEVAS RUTAS: MODULO DE PACIENTES ---
    Route::get('/pacientes', [PacienteController::class, 'index'])->name('pacientes.index');
    Route::get('/pacientes/crear', [PacienteController::class, 'create'])->name('pacientes.create');
    Route::post('/pacientes', [PacienteController::class, 'store'])->name('pacientes.store');
    Route::get('/pacientes/{id}', [PacienteController::class, 'show'])->name('pacientes.show');

});
