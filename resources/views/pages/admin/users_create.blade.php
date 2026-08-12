@extends('layouts.app')
@section('title', 'Créer un Utilisateur')
@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-gray-50">
<div class="max-w-lg mx-auto px-4 py-8">
    <div style="background:linear-gradient(135deg,#4F46E5,#7C3AED);border-radius:24px;padding:32px;margin-bottom:24px">
        <h1 style="font-size:24px;font-weight:800;color:white;margin:0">➕ Nouvel Utilisateur</h1>
        <p style="color:rgba(199,210,254,1);margin-top:6px;font-size:14px">Créer un compte approuvé directement</p>
    </div>
    @if($errors->any())
    <div style="background:#FEE2E2;border:1.5px solid #FCA5A5;border-radius:14px;padding:16px;margin-bottom:16px;color:#B91C1C;font-weight:600">
        @foreach($errors->all() as $error) ⚠️ {{ $error }}<br> @endforeach
    </div>
    @endif
    <div style="background:white;border-radius:20px;padding:32px;box-shadow:0 4px 20px rgba(0,0,0,0.08)">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:700;color:#1E293B;margin-bottom:8px">Nom complet</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       style="width:100%;padding:12px 16px;border:2px solid #E2E8F0;border-radius:12px;font-size:14px;font-family:inherit;box-sizing:border-box">
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:700;color:#1E293B;margin-bottom:8px">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       style="width:100%;padding:12px 16px;border:2px solid #E2E8F0;border-radius:12px;font-size:14px;font-family:inherit;box-sizing:border-box">
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:700;color:#1E293B;margin-bottom:8px">Mot de passe</label>
                <input type="password" name="password" required
                       style="width:100%;padding:12px 16px;border:2px solid #E2E8F0;border-radius:12px;font-size:14px;font-family:inherit;box-sizing:border-box">
            </div>
            <div style="margin-bottom:20px">
                <label style="display:block;font-weight:700;color:#1E293B;margin-bottom:8px">Confirmer le mot de passe</label>
                <input type="password" name="password_confirmation" required
                       style="width:100%;padding:12px 16px;border:2px solid #E2E8F0;border-radius:12px;font-size:14px;font-family:inherit;box-sizing:border-box">
            </div>
            <div style="margin-bottom:28px">
                <label style="display:block;font-weight:700;color:#1E293B;margin-bottom:8px">Rôle</label>
                <select name="role" style="width:100%;padding:12px 16px;border:2px solid #E2E8F0;border-radius:12px;font-size:14px;font-family:inherit;box-sizing:border-box">
                    <option value="superviseur">👁 Superviseur</option>
                    <option value="admin">👑 Admin</option>
                </select>
            </div>
            <div style="display:flex;gap:12px">
                <a href="{{ route('admin.users') }}"
                   style="flex:1;padding:14px;background:#F1F5F9;color:#475569;border-radius:12px;font-weight:700;text-align:center;text-decoration:none">
                    ← Annuler
                </a>
                <button type="submit"
                        style="flex:2;padding:14px;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:white;border:none;border-radius:12px;font-weight:700;font-size:15px;cursor:pointer;font-family:inherit">
                    ✅ Créer l'utilisateur
                </button>
            </div>
        </form>
    </div>
</div>
</div>
@endsection
