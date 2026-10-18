<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use App\Models\User;
use App\Services\ImageUploadService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
            'flash' => [
                'success' => $request->session()->get('success'),
            ],
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        try {
            $request->user()->fill($request->validated());

            // Store avatar if exists
            $avatarPath = null;
            if ($request->hasFile('avatar') && $request->file('avatar')->isValid()) {
                if ($request->user()->avatar && Storage::disk('public')->exists($request->user()->avatar)) {
                    Storage::disk('public')->delete($request->user()->avatar);
                }
                $avatarPath = ImageUploadService::storeStudent($request->file('avatar'));
            }

            if ($request->user()->isDirty('email')) {
                $request->user()->email_verified_at = now();
            }

            $user = User::findOrFail($request->user()->nim, 'nim');
            $user->update([
                'nama' => $request->nama,
                'email' => $request->email,
                'avatar' => $avatarPath ?? $request->user()->avatar,
            ]);

            return redirect()->back()->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memperbarui profil: ' . $e->getMessage());
        }
    }

}
