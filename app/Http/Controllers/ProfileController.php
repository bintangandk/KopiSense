<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('profile.province', 'profile.city');

        return view('pages.profile.index', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user()->load('profile');

        if (! $user->profile) {
            return redirect()->route('profile')->with('error', 'Profil pengguna belum tersedia.');
        }

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:500'],
            'province_id' => ['nullable', 'integer', 'exists:provinces,id', 'required_with:city_id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id', 'required_with:province_id'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif', 'max:2048'],
            'remove_profile_picture' => ['nullable', 'boolean'],
        ]);

        if (! empty($validated['province_id']) && ! empty($validated['city_id'])) {
            $isCityInProvince = City::where('id', $validated['city_id'])
                ->where('province_id', $validated['province_id'])
                ->exists();

            if (! $isCityInProvince) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['city_id' => 'Kota/Kabupaten tidak sesuai dengan provinsi yang dipilih.']);
            }
        }

        $fullName = trim($validated['first_name'] . ' ' . ($validated['last_name'] ?? ''));
        $oldProfilePicturePath = $user->profile->profile_picture;
        $uploadedProfilePicturePath = null;
        $newProfilePicturePath = $oldProfilePicturePath;
        $shouldRemoveProfilePicture = (bool) ($validated['remove_profile_picture'] ?? false);

        if ($request->hasFile('profile_picture')) {
            $uploadedProfilePicturePath = $request->file('profile_picture')->store('profile-pictures', 'public');
            $newProfilePicturePath = $uploadedProfilePicturePath;
            $shouldRemoveProfilePicture = false;
        } elseif ($shouldRemoveProfilePicture) {
            $newProfilePicturePath = null;
        }

        DB::beginTransaction();
        try {
            $user->update([
                'email' => $validated['email'],
            ]);

            $user->profile->update([
                'full_name' => $fullName,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'province_id' => $validated['province_id'] ?? null,
                'city_id' => $validated['city_id'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
                'profile_picture' => $newProfilePicturePath,
            ]);

            DB::commit();

            if ($uploadedProfilePicturePath && $oldProfilePicturePath && $oldProfilePicturePath !== $uploadedProfilePicturePath) {
                Storage::disk('public')->delete($oldProfilePicturePath);
            }

            if ($shouldRemoveProfilePicture && $oldProfilePicturePath) {
                Storage::disk('public')->delete($oldProfilePicturePath);
            }

            return redirect()->route('profile')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Throwable $th) {
            DB::rollBack();

            if ($uploadedProfilePicturePath) {
                Storage::disk('public')->delete($uploadedProfilePicturePath);
            }

            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui profil. Silakan coba lagi.');
        }
    }
}
