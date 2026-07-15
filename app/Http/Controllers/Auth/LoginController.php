<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('invoices.index');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|min:6',
        ], [
            'email.required'    => 'L\'email est requis',
            'email.email'       => 'L\'email doit être valide',
            'password.required' => 'Le mot de passe est requis',
            'password.min'      => 'Le mot de passe doit contenir au moins 6 caractères',
        ]);

        $remember = $request->has('remember');
        if (Auth::attempt($credentials, $remember)) {
    $request->session()->regenerate();

    /** @var \App\Models\User $user */
    $user = Auth::user();

    // Check if user is approved
    if (!$user->isAdmin() && !$user->isApproved()) {
        Auth::logout();
        return back()->with('error', '⏳ Votre compte est en attente d\'approbation par l\'administrateur.');
    }

    // Redirect adv to invoices
    if ($user->isAdv()) {
        return redirect()->route('invoices.index')
        ->with('success', 'Bienvenue ' . $user->name . '!');
        }

        if (!$user->isAdmin()) {
            return redirect()->route('dashboard.vendeurs')
            ->with('success', 'Bienvenue ' . $user->name . '!');
            }

            return redirect()->intended(route('invoices.index'))
            ->with('success', 'Bienvenue ' . $user->name . '!');
        }

        return back()->withErrors([
            'email' => 'Les identifiants fournis sont incorrects.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('invoices.index');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ], [
            'name.required'      => 'Le nom est requis',
            'email.required'     => 'L\'email est requis',
            'email.email'        => 'L\'email doit être valide',
            'email.unique'       => 'Cet email est déjà utilisé',
            'password.required'  => 'Le mot de passe est requis',
            'password.min'       => 'Le mot de passe doit contenir au moins 6 caractères',
            'password.confirmed' => 'Les mots de passe ne correspondent pas',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role'     => 'superviseur',
            'status'   => 'pending',
        ]);

        // Don't log them in — wait for admin approval
        return redirect()->route('login')
            ->with('success', '✅ Compte créé avec succès ! Attendez l\'approbation de l\'administrateur.');
    }
}
