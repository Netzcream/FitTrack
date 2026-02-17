<?php

namespace App\Livewire\Tenant\Student;

use App\Models\Tenant\Student;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.student')]
class Profile extends Component
{
    public ?Student $student = null;

    public string $first_name = '';

    public string $last_name = '';

    public string $phone = '';

    public string $email = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $student = $user->student ?? Student::where('email', $user->email)->first();
        if (! $student) {
            abort(403);
        }

        if ($student->user_id && $student->user_id !== $user->id) {
            abort(403);
        }

        if ($student->email && $student->email !== $user->email) {
            abort(403);
        }

        $this->student = $student;
        $this->first_name = (string) $student->first_name;
        $this->last_name = (string) $student->last_name;
        $this->phone = (string) ($student->phone ?? '');
        $this->email = (string) ($student->email ?? $user->email ?? '');
    }

    public function updateProfile(): void
    {
        if (! $this->student) {
            abort(403);
        }

        $validated = $this->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);

        $this->student->fill($validated);
        $this->student->save();

        /** @var User|null $user */
        $user = Auth::user();
        if ($user) {
            $fullName = trim($this->first_name . ' ' . $this->last_name);
            if ($fullName !== '' && $user->name !== $fullName) {
                $user->name = $fullName;
                $user->save();
            }
        }

        $this->dispatch('profile-updated');
    }

    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => ['required', 'string', 'current_password'],
                'password' => ['required', 'string', PasswordRule::defaults(), 'confirmed'],
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        /** @var User $user */
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }

    public function render()
    {
        return view('livewire.tenant.student.profile', [
            'student' => $this->student,
        ]);
    }
}
