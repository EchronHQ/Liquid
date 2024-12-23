<?php
declare(strict_types=1);

namespace Liquid\Framework\Encryption\Adapter;

/**
 * Sodium adapter for encrypting and decrypting strings
 */
class SodiumChachaIetf
{


    /**
     * Sodium constructor.
     */
    public function __construct(
        private readonly string $key
    )
    {

    }

    /**
     * Encrypt a string
     *
     * @param string $data
     * @return string string
     * @throws \SodiumException
     */
    public function encrypt(string $data): string
    {
        $nonce = random_bytes(SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES);
        $cipherText = sodium_crypto_aead_chacha20poly1305_ietf_encrypt(
            (string)$data,
            $nonce,
            $nonce,
            $this->key
        );

        return $nonce . $cipherText;
    }

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */
    public function decrypt(string $data): string
    {
        $nonce = mb_substr($data, 0, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES, '8bit');
        $payload = mb_substr($data, SODIUM_CRYPTO_AEAD_CHACHA20POLY1305_IETF_NPUBBYTES, null, '8bit');

        try {
            $plainText = sodium_crypto_aead_chacha20poly1305_ietf_decrypt(
                $payload,
                $nonce,
                $nonce,
                $this->key
            );
        } catch (\SodiumException $e) {
            $plainText = '';
        }

        return $plainText !== false ? $plainText : '';
    }
}
