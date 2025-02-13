<?php

namespace Fouladgar\OTP\Contracts;

use Fouladgar\OTP\Token\TokenPayload;

interface TokenRepositoryInterface
{
    /**
     * Create a new token record.
     */
    public function create(OTPNotifiable $notifiable): TokenPayload;

    /**
     * Determine if a token record exists and is valid.
     */
    public function exists(OTPNotifiable $notifiable, string $token): bool;

    /**
     * Delete all existing tokens from the storage.
     */
    public function deleteExisting(OTPNotifiable $notifiable): bool;

    /**
     * Determine if the given notifiable recently created an otp.
     */
    public function recentlyCreatedToken(OTPNotifiable $notifiable): bool;

}
