<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->guest(route('login'));
        }

        $user = $request->user();

        if (! $user->is_active) {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'identifier' => app()->getLocale() === 'bn'
                    ? 'আপনার অ্যাকাউন্ট নিষ্ক্রিয় করা হয়েছে। অ্যাডমিনের সাথে যোগাযোগ করুন।'
                    : 'Your account is deactivated. Please contact fleet administration.',
            ]);
        }

        // If no specific role required or user matches allowed role
        if (empty($roles) || in_array($user->role, $roles, true)) {
            return $next($request);
        }

        // Super admins and transport officers have supervisory access
        if (in_array($user->role, ['ADMIN', 'TRANSPORT_OFFICER'], true)) {
            return $next($request);
        }

        abort(403, app()->getLocale() === 'bn'
            ? 'অননুমোদিত অ্যাক্সেস। এই টার্মিনালটি শুধুমাত্র নির্ধারিত কর্মীদের জন্য উন্মুক্ত।'
            : 'Access denied. You do not have permission to access this terminal.');
    }
}
