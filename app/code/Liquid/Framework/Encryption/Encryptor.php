<?php
declare(strict_types=1);

namespace Liquid\Framework\Encryption;

use Liquid\Framework\App\DeploymentConfig;
use Liquid\Framework\Config\ConfigOptionsListConstants;
use Liquid\Framework\Encryption\Adapter\SodiumChachaIetf;
use ParagonIE\Halite\Password;
use ParagonIE\HiddenString\HiddenString;

class Encryptor
{
    /**
     * Cipher versions
     */
    public const int CIPHER_BLOWFISH = 0;

    public const int CIPHER_RIJNDAEL_128 = 1;

    public const int CIPHER_RIJNDAEL_256 = 2;

    public const int CIPHER_AEAD_CHACHA20POLY1305 = 3;

    public const int CIPHER_LATEST = 3;
    /**
     * Array key of encryption key in deployment config
     */
    public const string PARAM_CRYPT_KEY = 'crypt/key';
    /**
     * Version of encryption key
     *
     * @var int
     */
    private int $keyVersion;
    /**
     * Array of encryption keys
     *
     * @var string[]
     */
    private array $keys = [];
    private int $cipher = self::CIPHER_LATEST;

    public function __construct(
        private readonly DeploymentConfig $deploymentConfig,
    )
    {
        // load all possible keys
        $this->keys = \preg_split('/\s+/s', \trim($deploymentConfig->getValueString(self::PARAM_CRYPT_KEY)));
        $this->keyVersion = count($this->keys) - 1;
    }

    /**
     * Decrypt a string
     *
     * @param string $data
     * @return string
     */

    /**
     * Prepend key and cipher versions to encrypted data after encrypting
     *
     * @param string $data
     * @return string
     */
    public function encrypt(string $data): string
    {
        $crypt = new SodiumChachaIetf($this->decodeKey($this->keys[$this->keyVersion]));

        return $this->keyVersion .
            ':' . self::CIPHER_AEAD_CHACHA20POLY1305 .
            ':' . \base64_encode($crypt->encrypt($data));
    }

    /**
     * Look for key and crypt versions in encrypted data before decrypting
     *
     * Unsupported/unspecified key version silently fallback to the oldest we have
     * Unsupported cipher versions eventually throw exception
     * Unspecified cipher version fallback to the oldest we support
     *
     * @param string $data
     * @return string
     * @throws \Exception
     */
    public function decrypt(string $data): string
    {
        if ($data) {
            $parts = \explode(':', $data, 4);
            $partsCount = count($parts);

            $initVector = null;
            // specified key, specified crypt, specified iv
            if (4 === $partsCount) {
                [$keyVersion, $cryptVersion, $iv, $data] = $parts;
                $initVector = $iv ? $iv : null;
                $keyVersion = (int)$keyVersion;
                $cryptVersion = self::CIPHER_RIJNDAEL_256;
                // specified key, specified crypt
            } elseif (3 === $partsCount) {
                [$keyVersion, $cryptVersion, $data] = $parts;
                $keyVersion = (int)$keyVersion;
                $cryptVersion = (int)$cryptVersion;
                // no key version = oldest key, specified crypt
            } elseif (2 === $partsCount) {
                [$cryptVersion, $data] = $parts;
                $keyVersion = 0;
                $cryptVersion = (int)$cryptVersion;
                // no key version = oldest key, no crypt version = oldest crypt
            } elseif (1 === $partsCount) {
                $keyVersion = 0;
                $cryptVersion = self::CIPHER_BLOWFISH;
                // not supported format
            } else {
                return '';
            }
            // no key for decryption
            if (!isset($this->keys[$keyVersion])) {
                return '';
            }
            $crypt = $this->getCrypt($this->decodeKey($this->keys[$keyVersion]), $cryptVersion, $initVector);
            if (null === $crypt) {
                return '';
            }
            return \trim($crypt->decrypt(\base64_decode((string)$data)));
        }
        return '';
    }

    /**
     * Check whether specified cipher version is supported
     *
     * Returns matched supported version or throws exception
     *
     * @param int $version
     * @return int
     * @throws \Exception
     */
    public function validateCipher(int $version): int
    {
        $types = [
            self::CIPHER_BLOWFISH,
            self::CIPHER_RIJNDAEL_128,
            self::CIPHER_RIJNDAEL_256,
            self::CIPHER_AEAD_CHACHA20POLY1305,
        ];
        if (!\in_array($version, $types, true)) {
            throw new \Exception('Not supported cipher version');
        }
        return $version;
    }

    public function getHash(string $password, bool $salt = false): string
    {
        if ($salt === false) {
            return $this->hash($password);
        }
        return Password::hash(new HiddenString($password), $salt);
    }


    public function hash(string $data): string
    {
        return \hash_hmac(
            'sha256',
            $data,
            $this->decodeKey($this->keys[$this->keyVersion]),
            false
        );
    }

    /**
     * Initialize crypt module if needed
     *
     * By default initializes with latest key and crypt versions
     *
     * @param string|null $key
     * @param int|null $cipherVersion
     * @param string|null $initVector
     * @return SodiumChachaIetf|null
     * @throws \Exception
     */
    private function getCrypt(
        string|null $key = null,
        int|null    $cipherVersion = null,
        string|null $initVector = null
    ): SodiumChachaIetf|null
    {
        //phpcs:disable PHPCompatibility.Constants.RemovedConstants
        if (null === $key && null === $cipherVersion) {
            $cipherVersion = $this->getCipherVersion();
        }

        if (null === $key) {
            $key = $this->decodeKey($this->keys[$this->keyVersion]);
        }

        if (!$key) {
            return null;
        }

        if (null === $cipherVersion) {
            $cipherVersion = $this->cipher;
        }
        $cipherVersion = $this->validateCipher($cipherVersion);

        if ($cipherVersion >= self::CIPHER_AEAD_CHACHA20POLY1305) {
            return new SodiumChachaIetf($key);
        }

        throw new \Exception('Encryption not supported');
    }

    /**
     * Get cipher version
     *
     * @return int
     */
    private function getCipherVersion(): int
    {
        return $this->cipher;
    }

    /**
     * Find out actual decode key
     *
     * @param string $key
     * @return null|string
     */
    private function decodeKey(string $key): string|null
    {
        $decoded = (str_starts_with($key, ConfigOptionsListConstants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX)) ?
            \base64_decode(\substr($key, \strlen(ConfigOptionsListConstants::STORE_KEY_ENCODED_RANDOM_STRING_PREFIX))) :
            $key;
        return ($decoded === false ? null : $decoded);
    }
}
