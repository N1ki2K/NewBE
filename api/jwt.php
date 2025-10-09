<?php
// ====================================================
// JWT Token Handler (Simple implementation without external libraries)
// ====================================================

class JWT {

    public static function encode($payload, $secret = JWT_SECRET) {
        $header = [
            'typ' => 'JWT',
            'alg' => JWT_ALGORITHM
        ];

        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRATION;

        $base64UrlHeader = self::base64UrlEncode(json_encode($header));
        $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            $secret,
            true
        );

        $base64UrlSignature = self::base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode($jwt, $secret = JWT_SECRET) {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            throw new Exception('Invalid token format');
        }

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        // Verify signature
        $signature = hash_hmac(
            'sha256',
            $base64UrlHeader . "." . $base64UrlPayload,
            $secret,
            true
        );

        $expectedSignature = self::base64UrlEncode($signature);

        if ($base64UrlSignature !== $expectedSignature) {
            throw new Exception('Invalid token signature');
        }

        // Decode payload
        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true);

        if (!$payload) {
            throw new Exception('Invalid token payload');
        }

        // Check expiration
        if (isset($payload['exp']) && $payload['exp'] < time()) {
            throw new Exception('Token has expired');
        }

        return $payload;
    }

    private static function base64UrlEncode($data) {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode($data) {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
