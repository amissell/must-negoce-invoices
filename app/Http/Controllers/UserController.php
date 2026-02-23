<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index()
    {
        $users = User::orderBy('name')->get();
        return view('admin.users', compact('users'));
    }

    public function updateRole(Request $request, User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas modifier votre propre rôle.');
        }

        $request->validate(['role' => 'required|in:admin,superviseur']);
        $user->update(['role' => $request->role]);

        return back()->with('success', "✅ Rôle de {$user->name} mis à jour !");
    }

    public function destroy(User $user)
    {
        // Prevent admin from deleting themselves
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "🗑️ Utilisateur {$name} supprimé avec succès.");
    }

    public function create()
{
    return view('admin.users_create');
}

public function store(Request $request)
{
    $request->validate([
        'name'     => 'required|string|max:255',
        'email'    => 'required|email|unique:users,email',
        'password' => 'required|min:8|confirmed',
        'role'     => 'required|in:admin,superviseur',
    ]);

    User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'password' => bcrypt($request->password),
        'role'     => $request->role,
        'status'   => 'approved',
    ]);

    return redirect()->route('admin.users')->with('success', '✅ Utilisateur créé avec succès !');
}
public function approve(User $user)
{
    $user->update(['status' => 'approved', 'role' => 'superviseur']);
    return back()->with('success', "✅ {$user->name} a été approuvé !");
    }
    
    public function reject(User $user)
    {
        $user->update(['status' => 'rejected']);
        return back()->with('success', "❌ {$user->name} a été rejeté.");
    }
}