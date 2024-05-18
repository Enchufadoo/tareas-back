<?php

namespace App\Managers;

use Illuminate\Support\Str;

class PasswordReset
{
    const RECOVERY_CODE_LENGTH = 4;
    const MAX_NUMBER_OF_ATTEMPTS = 6;

    const MINUTES_BEFORE_CODE_EXPIRY = 30;

    const RENEWAL_TOKEN_LENGTH = 30;

    /**
     * Generates a unique token string.
     *
     * This method generates a token string consisting of random uppercase letters.
     * The length of the token is defined by the constant `NUMBER_OF_CHARACTERS`.
     *
     * @return string
     */
    public function createCode(): string
    {
        $str = '';

        for ($i = 0; $i < self::RECOVERY_CODE_LENGTH; $i++) {
            $str .= rand(0, 9);
        }

        return $str;
    }

    /**
     * Generates a renewal token for setting the new password
     *
     * @return string
     */
    public function createRenewalToken(): string
    {
        return substr(hash('sha256', Str::random(20)), 0, self::RENEWAL_TOKEN_LENGTH);
    }
}
