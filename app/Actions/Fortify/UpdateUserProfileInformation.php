<?php

namespace App\Actions\Fortify;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * Validate and update the given user's profile information.
     *
     * @param  array<string, mixed>  $input
     */
    public function update(User $user, array $input): void
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'photo' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'signature' => ['nullable', 'mimes:jpg,jpeg,png', 'max:1024'],
            'role' => ['nullable', Rule::enum(UserRole::class)],
        ])->validateWithBag('updateProfileInformation');

        if (isset($input['role']) && $input['role'] !== $user->role->value) {
            if (Auth::user()->role !== UserRole::Admin) {
                throw ValidationException::withMessages([
                    'role' => [__('Only administrators can change user roles.')],
                ]);
            }

            if ($user->role === UserRole::Admin && User::where('role', UserRole::Admin)->count() === 1) {
                throw ValidationException::withMessages([
                    'role' => [__('Cannot change the role of the last administrator.')],
                ]);
            }
            
            $user->forceFill(['role' => $input['role']])->save();
        }

        if (isset($input['photo'])) {
            $user->updateProfilePhoto($input['photo']);
        }

        if (isset($input['signature'])) {
            $user->update([
                'signature_path' => $input['signature']->store('signatures', 'public'),
            ]);
        }

        if ($input['email'] !== $user->email &&
            $user instanceof MustVerifyEmail) {
            $this->updateVerifiedUser($user, $input);
        } else {
            $user->forceFill([
                'name' => $input['name'],
                'email' => $input['email'],
                'job_title' => $input['job_title'] ?? null,
                'phone' => $input['phone'] ?? null,
            ])->save();
        }
    }

    /**
     * Update the given verified user's profile information.
     *
     * @param  array<string, string>  $input
     */
    protected function updateVerifiedUser(User $user, array $input): void
    {
        $user->forceFill([
            'name' => $input['name'],
            'email' => $input['email'],
            'email_verified_at' => null,
            'job_title' => $input['job_title'] ?? null,
            'phone' => $input['phone'] ?? null,
        ])->save();

        $user->sendEmailVerificationNotification();
    }
}
