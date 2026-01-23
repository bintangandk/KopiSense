<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with('profile')->orderBy('created_at');

        // Jika ada parameter search
        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->whereHas('profile', function ($q) use ($search) {
                $q->where('full_name', 'like', '%' . $search . '%')
                    ->orWhere('phone', 'like', '%' . $search . '%');
            })->orWhere('email', 'like', '%' . $search . '%')
                ->orWhere('username', 'like', '%' . $search . '%');
        }

        $users = $query->paginate(10);
        return view('pages.dataUser.index', compact('users'));
    }

    public function show($id)
    {
        $user = User::with('profile')->findOrFail($id);
        return view('pages.dataUser.detail.index', compact('user'));
    }
}
