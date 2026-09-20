<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Rule;
use Livewire\Component;

#[Layout('layouts.app')]
class FieldLogin extends Component
{
    #[Rule('required|string|min:3')]
    public string $identifier = '';

    #[Rule('required|string|min:4')]
    public string $pin = '';

    public bool $remember = true;

    public ?string $errorMessage = null;

    public function mount(): void
    {
        // If already logged in, redirect based on role
        if (Auth::check()) {
            $this->redirectUser(Auth::user());
        }
    }

    public function fillQuickCredentials(string $id, string $pinCode = '1234'): void
    {
        $this->identifier = $id;
        $this->pin = $pinCode;
        $this->errorMessage = null;
    }

    public function authenticate(): void
    {
        $this->validate();
        $this->errorMessage = null;

        $rawInput = trim($this->identifier);

        // Normalize potential phone number (digits only)
        $numericOnly = preg_replace('/[^0-9]/', '', $rawInput);
        if (str_starts_with($numericOnly, '8801')) {
            $numericOnly = substr($numericOnly, 2);
        }

        // Query user by Employee ID, exact Phone, Email, or normalized Phone match
        $user = User::where(function ($query) use ($rawInput, $numericOnly) {
            $query->where('employee_id', $rawInput)
                ->orWhere('phone', $rawInput)
                ->orWhere('email', strtolower($rawInput));

            if (! empty($numericOnly) && strlen($numericOnly) >= 10) {
                $query->orWhere('phone', 'like', "%{$numericOnly}%");
            }
        })->first();

        if (! $user) {
            $this->errorMessage = app()->getLocale() === 'bn'
                ? 'প্রদত্ত মোবাইল নম্বর অথবা এমপ্লয়ি আইডি খুঁজে পাওয়া যায়নি।'
                : 'Employee ID or Mobile number not found.';

            return;
        }

        if (! $user->is_active) {
            $this->errorMessage = app()->getLocale() === 'bn'
                ? 'আপনার অ্যাকাউন্টটি সাময়িকভাবে নিষ্ক্রিয় রয়েছে।'
                : 'Your account is currently inactive. Contact fleet supervisor.';

            return;
        }

        // Verify PIN or Password
        $pinValid = ! empty($user->pin) && Hash::check($this->pin, $user->pin);
        $passwordValid = Hash::check($this->pin, $user->password);

        if (! $pinValid && ! $passwordValid) {
            $this->errorMessage = app()->getLocale() === 'bn'
                ? 'ভুল পিন (PIN) অথবা পাসওয়ার্ড দেওয়া হয়েছে।'
                : 'Invalid PIN or password. Please try again.';

            return;
        }

        Auth::login($user, $this->remember);
        session()->regenerate();

        $this->redirectUser($user);
    }

    protected function redirectUser(User $user): void
    {
        if ($user->isDriver()) {
            $this->redirectIntended(route('portal.driver'), navigate: true);

            return;
        }

        if ($user->isSecurityGuard()) {
            $this->redirectIntended(route('portal.gate-pass'), navigate: true);

            return;
        }

        if ($user->isEmployee()) {
            $this->redirectIntended(route('portal.requests'), navigate: true);

            return;
        }

        if ($user->isAdmin() || $user->isTransportOfficer()) {
            $this->redirectIntended(url('/admin'));

            return;
        }

        $this->redirectIntended(route('portal.requests'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.auth.field-login');
    }
}
