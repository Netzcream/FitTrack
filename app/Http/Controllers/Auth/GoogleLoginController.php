<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleLoginController extends Controller
{
    private const STATE_TTL_SECONDS = 600;

    public function redirect(Request $request)
    {
        $tenant = tenant();
        if (! $tenant) {
            abort(404);
        }

        $payload = [
            'tenant_id' => $tenant->getTenantKey(),
            'domain' => $request->getHost(),
            'redirect' => $this->sanitizeRedirect($request->query('redirect')),
            'ts' => now()->timestamp,
            'nonce' => Str::random(16),
        ];

        $state = Crypt::encryptString(json_encode($payload));

        return Socialite::driver('google')
            ->stateless()
            ->with([
                'state' => $state,
                'prompt' => 'select_account',
            ])
            ->redirect();
    }

    public function callback(Request $request)
    {
        $payload = $this->decodeState($request->query('state'));
        if (! $payload) {
            abort(400);
        }

        $tenant = Tenant::find($payload['tenant_id'] ?? null);
        if (! $tenant) {
            abort(404);
        }

        $domain = $payload['domain'] ?? $tenant->mainDomain();
        if (! $tenant->domains()->where('domain', $domain)->exists()) {
            $domain = $tenant->mainDomain();
        }

        if ($this->isStateExpired($payload)) {
            return $this->redirectToTenantLogin($domain, $payload['redirect'] ?? null, 'state_expired');
        }

        try {
            $googleUser = Socialite::driver('google')->stateless()->user();


        } catch (Throwable $e) {
            return $this->redirectToTenantLogin($domain, $payload['redirect'] ?? null, 'oauth_failed');
        }

        $email = $googleUser->getEmail();
        if (! $email) {
            return $this->redirectToTenantLogin($domain, $payload['redirect'] ?? null, 'email_missing');
        }

        $email = Str::lower(trim($email));

        $user = null;
        $isStudent = false;

        $tenant->run(function () use ($email, $googleUser, &$user, &$isStudent) {
            // Try direct match first (email is already lowercase and trimmed)
            $user = User::where('email', $email)->first();

            // If not found, try case-insensitive search
            if (! $user) {
                $user = User::whereRaw('LOWER(email) = ?', [$email])->first();
            }

            // If still not found, try with TRIM
            if (! $user) {
                $user = User::whereRaw('LOWER(TRIM(email)) = ?', [$email])->first();
            }

            if (! $user) {
                // Log all users to debug
                $allUsers = User::select('id', 'email')->get();
                Log::warning('Google SSO user not found in tenant', [
                    'tenant_id' => tenant()?->getTenantKey(),
                    'search_email' => $email,
                    'db' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
                    'existing_users' => $allUsers->pluck('email')->toArray(),
                ]);
                return;
            }

            $name = $googleUser->getName() ?: $user->name;
            $user->name = $name;
            $user->email = $email;
            if (! $user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->save();

            // Check if user is a student
            $isStudent = $user->student()->exists();
        });

        if (! $user) {
            return $this->redirectToTenantLogin($domain, $payload['redirect'] ?? null, 'user_not_found');
        }

        $signature = hash_hmac('sha256', (string) $user->id, config('app.key'));
        $scheme = app()->environment('local') ? 'http' : 'https';
        $target = "{$scheme}://{$domain}/_impersonate-login/{$user->id}/{$signature}";

        // If user is a student and no custom redirect is provided, redirect to student dashboard
        $redirect = $this->sanitizeRedirect($payload['redirect'] ?? null);
        if (! $redirect && $isStudent) {
            $redirect = '/student';
        }

        if ($redirect) {
            $target .= '?redirect=' . urlencode($redirect);
        }

        return redirect()->away($target);
    }

    private function decodeState(?string $state): ?array
    {
        if (! $state) {
            return null;
        }

        try {
            $decoded = Crypt::decryptString($state);
        } catch (Throwable $e) {
            return null;
        }

        $payload = json_decode($decoded, true);

        return is_array($payload) ? $payload : null;
    }

    private function isStateExpired(array $payload): bool
    {
        $issuedAt = $payload['ts'] ?? null;
        if (! is_numeric($issuedAt)) {
            return true;
        }

        return (now()->timestamp - (int) $issuedAt) > self::STATE_TTL_SECONDS;
    }

    private function redirectToTenantLogin(string $domain, ?string $redirect, string $error)
    {
        $scheme = app()->environment('local') ? 'http' : 'https';
        $target = "{$scheme}://{$domain}/login";

        $query = ['sso_error' => $error];
        $redirect = $this->sanitizeRedirect($redirect);
        if ($redirect) {
            $query['redirect'] = $redirect;
        }

        return redirect()->away($target . '?' . http_build_query($query));
    }

    private function sanitizeRedirect(?string $redirect): ?string
    {
        if (! $redirect) {
            return null;
        }

        if (! str_starts_with($redirect, '/')) {
            return null;
        }

        if (str_starts_with($redirect, '//')) {
            return null;
        }

        if (str_contains($redirect, '://')) {
            return null;
        }

        return $redirect;
    }
}
