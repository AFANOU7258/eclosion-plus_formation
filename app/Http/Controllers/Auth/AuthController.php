<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /** Afficher le formulaire de connexion */
    public function showLogin()
    {
        return view("auth.login");
    }

    /** Traiter la connexion */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            "email" => ["required", "email"],
            "password" => ["required"],
        ]);

        if (Auth::attempt($credentials, $request->filled("remember"))) {
            $request->session()->regenerate();

            // Rediriger l'admin vers le dashboard
            if (Auth::user()->isAdmin()) {
                return redirect()->route("admin.dashboard");
            }

            return redirect()->intended("/");
        }

        return back()
            ->withErrors([
                "email" => "Identifiants incorrects.",
            ])
            ->onlyInput("email");
    }

    /** Traiter l'inscription */
    public function register(Request $request)
    {
        $validated = $request->validate([
            "name" => ["required", "string", "max:255"],
            "email" => ["required", "email", "unique:users"],
            "password" => ["required", "string", "min:8", "confirmed"],
        ]);

        $user = User::create([
            "name" => $validated["name"],
            "email" => $validated["email"],
            "password" => Hash::make($validated["password"]),
            "role" => "student",
        ]);

        Auth::login($user);

        return redirect("/")->with("success", "Bienvenue sur Eclosion+ !");
    }

    /** Déconnexion */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect("/");
    }
}
