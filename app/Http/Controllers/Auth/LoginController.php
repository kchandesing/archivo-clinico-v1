<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('home');
        }
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        
        // Regla implícita de tu script SQL: el usuario debe estar activo
        $credentials['activo'] = true;

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            // Regenerar sesión para mitigar vulnerabilidades de seguridad
            $request->session()->regenerate();

            Log::info("Syslog_Acceso: Inicio de sesión exitoso para el usuario: " . $request->email);

            return redirect()->intended(route('home'));
        }

        Log::warning("Syslog_Acceso: Intento fallido de inicio de sesión para el correo: " . $request->email, [
            'ip' => $request->ip()
        ]);

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros o la cuenta está inactiva.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        $userEmail = Auth::user()->email ?? 'Desconocido';
        
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        Log::info("Syslog_Acceso: Sesión cerrada formalmente por el usuario: " . $userEmail);

        return redirect()->route('login');
    }
}