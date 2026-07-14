@extends('layouts.app')
@section('title', 'Gestion des Utilisateurs')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-gray-50">
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

    {{-- Header --}}
    <div style="background:linear-gradient(135deg,#4F46E5 0%,#7C3AED 100%);border-radius:24px;padding:32px;position:relative;overflow:hidden;">
        <div style="position:absolute;top:-40%;right:-10%;width:400px;height:400px;background:radial-gradient(circle,rgba(255,255,255,0.12) 0%,transparent 70%);pointer-events:none;"></div>
        <div style="position:relative;z-index:1;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px">
            <div>
                <h1 style="font-size:28px;font-weight:800;color:white;margin:0">👥 Gestion des Utilisateurs</h1>
                <p style="color:rgba(199,210,254,1);margin-top:6px;font-size:14px;font-weight:500">
                    Gérez les rôles de votre équipe — {{ $users->count() }} membres
                </p>
            </div>
            <div style="display:flex;gap:12px;flex-wrap:wrap">
                <a href="{{ route('admin.users.create') }}"
                   style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:white;color:#4F46E5;border-radius:14px;font-weight:700;font-size:14px;text-decoration:none;">
                    ➕ Nouvel Utilisateur
                </a>
                <a href="{{ route('suivi-vendeurs.index') }}"
                   style="display:inline-flex;align-items:center;gap:8px;padding:10px 20px;background:rgba(255,255,255,0.2);backdrop-filter:blur(8px);color:white;border-radius:14px;font-weight:700;font-size:14px;text-decoration:none;border:1px solid rgba(255,255,255,0.3)">
                    ← Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Alerts --}}
    @if(session('success'))
    <div style="background:#DCFCE7;border:1.5px solid #86EFAC;border-radius:14px;padding:16px 20px;color:#15803D;font-weight:600;display:flex;align-items:center;gap:10px">
        ✅ {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div style="background:#FEE2E2;border:1.5px solid #FCA5A5;border-radius:14px;padding:16px 20px;color:#B91C1C;font-weight:600;display:flex;align-items:center;gap:10px">
        ⚠️ {{ session('error') }}
    </div>
    @endif

    {{-- Legend --}}
    <div style="background:white;border-radius:16px;padding:16px 20px;border:1px solid #E2E8F0;display:flex;gap:20px;flex-wrap:wrap">
        <span style="font-size:12px;font-weight:700;color:#94A3B8;text-transform:uppercase;letter-spacing:0.5px;align-self:center">Rôles :</span>
        <span style="background:#EEF2FF;color:#4338CA;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:800;border:1px solid #C7D2FE">👑 Admin — Accès total</span>
        <span style="background:#F0FDF4;color:#15803D;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:800;border:1px solid #86EFAC">👁 Superviseur — Suivi vendeurs uniquement</span>
        <span style="background:#FEE2E2;color:#B91C1C;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:800;border:1px solid #FCA5A5">📊 ADV — Facturation uniquement</span>
        <span style="background:#FEF9C3;color:#854D0E;padding:5px 14px;border-radius:999px;font-size:12px;font-weight:800;border:1px solid #FDE047">⏳ En attente d'approbation</span>
    </div>

    {{-- Users Table --}}
    <div style="background:white;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.08);overflow:hidden;border:1px solid rgba(0,0,0,0.05)">
        <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;min-width:900px">
            <thead>
                <tr style="background:#F8FAFC">
                    <th style="padding:16px 20px;text-align:left;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #E2E8F0">#</th>
                    <th style="padding:16px 20px;text-align:left;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #E2E8F0">Utilisateur</th>
                    <th style="padding:16px 20px;text-align:left;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #E2E8F0">Email</th>
                    <th style="padding:16px 20px;text-align:center;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #E2E8F0">Rôle</th>
                    <th style="padding:16px 20px;text-align:center;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #E2E8F0">Statut</th>
                    <th style="padding:16px 20px;text-align:center;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #E2E8F0">Changer Rôle</th>
                    <th style="padding:16px 20px;text-align:center;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #E2E8F0">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $user)
                <tr style="border-top:1px solid #F1F5F9" onmouseover="this.style.background='#F8FAFC'" onmouseout="this.style.background='white'">

                    {{-- # --}}
                    <td style="padding:20px;color:#94A3B8;font-weight:700;font-size:13px">{{ $loop->iteration }}</td>

                    {{-- Utilisateur --}}
                    <td style="padding:20px">
                        <div style="display:flex;align-items:center;gap:12px">
                            <div style="width:42px;height:42px;border-radius:12px;background:linear-gradient(135deg,#4F46E5,#7C3AED);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:16px;flex-shrink:0">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div>
                                <div style="font-weight:700;color:#1E293B;font-size:14px">
                                    {{ $user->name }}
                                    @if($user->id === auth()->id())
                                        <span style="font-size:11px;color:#94A3B8;font-weight:600;margin-left:6px">(vous)</span>
                                    @endif
                                </div>
                                <div style="font-size:12px;color:#94A3B8;margin-top:2px">
                                    Inscrit {{ $user->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                    </td>

                    {{-- Email --}}
                    <td style="padding:20px;color:#64748B;font-size:14px">{{ $user->email }}</td>

                    {{-- Rôle badge --}}
                    <td style="padding:20px;text-align:center">
                        @if($user->role === 'admin')
                            <span style="background:#EEF2FF;color:#4338CA;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:800;border:1px solid #C7D2FE">👑 Admin</span>
                            @elseif($user->role === 'adv')
                            <span style="background:#FEE2E2;color:#B91C1C;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:800;border:1px solid #FCA5A5">📊 ADV</span>
                            @else
                            <span style="background:#F0FDF4;color:#15803D;padding:6px 14px;border-radius:999px;font-size:12px;font-weight:800;border:1px solid #86EFAC">👁 Superviseur</span>
                            @endif
                    </td>

                    {{-- Statut --}}
                    <td style="padding:20px;text-align:center">
                        @if($user->id === auth()->id())
                            <span style="color:#CBD5E1;font-size:13px">—</span>
                        @elseif($user->isPending())
                            <div style="display:flex;flex-direction:column;gap:6px;align-items:center">
                                <span style="background:#FEF9C3;color:#854D0E;padding:4px 12px;border-radius:999px;font-size:12px;font-weight:800;border:1px solid #FDE047">⏳ En attente</span>
                                <div style="display:flex;gap:6px">
                                    <form method="POST" action="{{ route('admin.users.approve', $user) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" style="padding:6px 12px;background:#F0FDF4;color:#15803D;border:1.5px solid #86EFAC;border-radius:8px;font-weight:700;font-size:12px;cursor:pointer">
                                            ✅ Approuver
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.users.reject', $user) }}" style="display:inline">
                                        @csrf
                                        <button type="submit" style="padding:6px 12px;background:#FEF2F2;color:#B91C1C;border:1.5px solid #FCA5A5;border-radius:8px;font-weight:700;font-size:12px;cursor:pointer">
                                            ❌ Rejeter
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @elseif($user->isApproved())
                            <span style="color:#15803D;font-weight:700;font-size:13px">✅ Approuvé</span>
                        @else
                            <span style="color:#B91C1C;font-weight:700;font-size:13px">❌ Rejeté</span>
                        @endif
                    </td>

                    {{-- Changer rôle --}}
                    <td style="padding:20px;text-align:center">
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.role', $user) }}"
                              style="display:inline-flex;align-items:center;gap:8px"
                              onsubmit="return confirm('Changer le rôle de {{ $user->name }} ?')">
                            @csrf
                            @method('PUT')
                            <select name="role" style="padding:8px 10px;border:2px solid #E2E8F0;border-radius:10px;font-size:13px;font-weight:600;color:#1E293B;background:#F8FAFC;font-family:inherit">
                                <option value="superviseur" @selected($user->role === 'superviseur')>👁 Superviseur</option>
                                <option value="adv" @selected($user->role === 'adv')>📊 ADV</option>
                                <option value="admin"       @selected($user->role === 'admin')>👑 Admin</option>
                            </select>
                            <button type="submit" style="padding:8px 14px;background:linear-gradient(135deg,#4F46E5,#7C3AED);color:white;border:none;border-radius:10px;font-weight:700;font-size:12px;cursor:pointer;font-family:inherit">
                                💾
                            </button>
                        </form>
                        @else
                            <span style="color:#CBD5E1;font-size:13px;font-style:italic">Non modifiable</span>
                        @endif
                    </td>

                    {{-- Supprimer --}}
                    <td style="padding:20px;text-align:center">
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                              onsubmit="return confirm('⚠️ Supprimer {{ $user->name }} ? Cette action est irréversible.')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="padding:8px 14px;background:#FEF2F2;color:#B91C1C;border:1.5px solid #FCA5A5;border-radius:10px;font-weight:700;font-size:13px;cursor:pointer;font-family:inherit">
                                🗑️
                            </button>
                        </form>
                        @else
                            <span style="color:#CBD5E1;font-size:13px">—</span>
                        @endif
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
    </div>

</div>
</div>
@endsection
