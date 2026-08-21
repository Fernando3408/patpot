<?php

namespace App\Http\Controllers;

use App\Models\LoginLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LoginLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = LoginLog::with('user');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        $logs = $query->latest()->paginate(50)->appends($request->query());

        return view('admin.login-logs.index', compact('logs'));
    }
}
