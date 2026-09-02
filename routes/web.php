<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\Auth\LoginController;

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
        return "¡Bienvenido al Panel de Control del Archivo Clínico! Has iniciado sesión con éxito como administrador.";
    })->name('home');
});
