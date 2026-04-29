<?php
declare(strict_types=1);

function get_crypto_key(): string {
    $base = __DIR__ . '/../data';
    $keyPath = $base . '/secret.key';

    if (!file_exists($keyPath)) {
        file_put_contents($keyPath, base64_encode(random_bytes(32)));
    }

    $key = base64_decode(trim((string) file_get_contents($keyPath)), true);
    if ($key === false || strlen($key) !== 32) {
        throw new RuntimeException('Clé de chiffrement invalide.');
    }

    return $key;
}

function encrypt_text(string $plainText): string {
    $cipher = 'aes-256-cbc';
    $iv = random_bytes(openssl_cipher_iv_length($cipher));
    $key = get_crypto_key();
    $encrypted = openssl_encrypt($plainText, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    if ($encrypted === false) {
        throw new RuntimeException('Erreur de chiffrement.');
    }

    return base64_encode($iv . $encrypted);
}

function decrypt_text(string $payload): string {
    $cipher = 'aes-256-cbc';
    $raw = base64_decode($payload, true);

    if ($raw === false) {
        return '';
    }

    $ivLength = openssl_cipher_iv_length($cipher);
    $iv = substr($raw, 0, $ivLength);
    $cipherText = substr($raw, $ivLength);
    $key = get_crypto_key();

    $plain = openssl_decrypt($cipherText, $cipher, $key, OPENSSL_RAW_DATA, $iv);

    return $plain === false ? '' : $plain;
}
?>