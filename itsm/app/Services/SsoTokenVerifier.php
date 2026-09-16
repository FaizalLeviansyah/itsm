<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

class SsoTokenVerifier
{
    /**
     * Verify and decode an SSO token from the portal.
     *
     * Uses shared SSO secret for decryption (independent of APP_KEY).
     * Portal and internal systems only need the same SSO_SECRET.
     *
     * @param string $token The encrypted token from query string
     * @return array|null Returns payload if valid, null if invalid/expired
     */
    public function verify(string $token): ?array
    {
        try {
            // Decrypt using shared secret
            $json = $this->decrypt($token);

            if (!$json) {
                return null;
            }

            $payload = json_decode($json, true);

            if (!$payload || !isset($payload['signature'], $payload['employee_id'], $payload['email'], $payload['timestamp'], $payload['nonce'])) {
                return null;
            }

            // Check expiry
            $expiry = config('sso.token_expiry', 60);
            if ((time() - $payload['timestamp']) > $expiry) {
                return null;
            }

            // Prevent token reuse (replay attack)
            $cacheKey = 'sso_nonce_' . $payload['nonce'];
            if (Cache::has($cacheKey)) {
                return null;
            }

            // Verify HMAC signature
            $signature = $payload['signature'];
            unset($payload['signature']);

            $expectedSignature = $this->sign($payload);

            if (!hash_equals($expectedSignature, $signature)) {
                return null;
            }

            // Mark nonce as used (store for token_expiry + buffer)
            Cache::put($cacheKey, true, $expiry + 60);

            return [
                'employee_id' => $payload['employee_id'],
                'email' => $payload['email'],
            ];
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Decrypt data using shared SSO secret (AES-256-CBC).
     */
    private function decrypt(string $data): ?string
    {
        try {
            $key = $this->getEncryptionKey();
            $decoded = base64_decode($data);

            if (strlen($decoded) < 16) {
                return null;
            }

            $iv = substr($decoded, 0, 16);
            $encrypted = substr($decoded, 16);

            $decrypted = openssl_decrypt($encrypted, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);

            return $decrypted !== false ? $decrypted : null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Derive encryption key from SSO secret.
     */
    private function getEncryptionKey(): string
    {
        return hash('sha256', config('sso.secret'), true);
    }

    /**
     * Create HMAC signature for verification.
     */
    private function sign(array $payload): string
    {
        $data = json_encode([
            'employee_id' => $payload['employee_id'],
            'email' => $payload['email'],
            'timestamp' => $payload['timestamp'],
            'nonce' => $payload['nonce'],
        ]);

        return hash_hmac('sha256', $data, config('sso.secret'));
    }
}
