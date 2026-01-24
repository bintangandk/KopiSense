<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function create()
    {
        return view('pages.dataUser.create.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'full_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users,username',
            'nik' => 'required|string|max:20|unique:user_profiles,nik',
            'email' => 'required|string|email|max:255|unique:users,email',
            'gender' => 'nullable|in:Laki-laki,Perempuan',
            'role' => 'required|in:admin,pegawai',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
            'province_id' => 'required|integer|exists:provinces,id',
            'city_id' => 'required|integer|exists:cities,id',
            'postal_code' => 'required|string|max:20',
        ]);

        DB::beginTransaction();
        try {
            // Create User
            $user = User::create([
                'username' => $request->username,
                'email' => $request->email,
                'role' => $request->role,
                'password' => bcrypt('defaultpassword'), // Set a default password or generate one
            ]);

            // Create User Profile
            $user->profile()->create([
                'full_name' => $request->full_name,
                'nik' => $request->nik,
                'gender' => $request->gender,
                'phone' => $request->phone,
                'address' => $request->address,
                'province_id' => $request->province_id,
                'city_id' => $request->city_id,
                'postal_code' => $request->postal_code,
            ]);

            DB::commit();
            return redirect()->route('data-user')->with('success', 'Data anggota berhasil ditambahkan.');
        } catch (\Exception $e) {

            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan data anggota: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $user = User::with('profile')->findOrFail($id);
        return view('pages.dataUser.detail.index', compact('user'));
    }

    public function destroy($id)
    {
        try {
            $user = User::with('profile')->findOrFail($id);

            // Delete user profile first if exists
            if ($user->profile) {
                $user->profile->delete();
            }

            // Then delete user
            $user->delete();

            return redirect()->route('data-user')->with('success', 'Data anggota berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('data-user')->with('error', 'Gagal menghapus data anggota.');
        }
    }
}
