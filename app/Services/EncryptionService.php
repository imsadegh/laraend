<?php

namespace App\Services;

use Illuminate\Support\Facades\Crypt;

class EncryptionService
{
    /**
     * Encrypt a video URL
     *
     * @param string $url
     * @return string
     */
    public function encryptUrl(string $url): string
    {
        return Crypt::encrypt($url);
    }

    /**
     * Decrypt a video URL
     *
     * @param string $encryptedUrl
     * @return string|null
     */
    public function decryptUrl(string $encryptedUrl): ?string
    {
        try {
            return Crypt::decrypt($encryptedUrl);
        } catch (\Exception $e) {
            // Decryption failed (likely due to APP_KEY change)
            return null;
        }
    }
}
