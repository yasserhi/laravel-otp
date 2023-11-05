<?php

declare(strict_types=1);

namespace Fouladgar\OTP\Contracts;

use Fouladgar\OTP\Token\TokenPayload;
use Illuminate\Support\Carbon;

abstract class AbstractTokenRepository implements TokenRepositoryInterface
{
    public function __construct(
        protected int $expires,
        protected int $tokenLength,
        protected int $throttle,
    )
    {
    }

    /**
     * Create & store token
     * 
     * @param \Fouladgar\OTP\Contracts\OTPNotifiable $notifiable
     * 
     * @return TokenPayload
     */
    public function create(OTPNotifiable $notifiable): TokenPayload
    {
        $this->deleteExisting($notifiable);

        $token = $this->createNewToken();

        $token_payload = $this->save($notifiable, $token);

        return $token_payload;
    }

    protected function createNewToken(): string
    {
        return (string) random_int(10 ** ($this->tokenLength - 1), (10 ** $this->tokenLength) - 1);
    }

    protected function tokenExpired(string $expiresAt): bool
    {
        return Carbon::parse($expiresAt)->isPast();
    }

    protected function getPayload(OTPNotifiable $notifiable, string $token): TokenPayload
    {
        return new TokenPayload(
            $notifiable->id,
            $notifiable::class,
            $token,
            now(),
            now()->addMinutes($this->expires),
            now()->addSeconds($this->throttle),
        );
    }

    /**
     * Determine if the token was recently created.
     *
     * @param  string  $createdAt
     * @return bool
     */
    protected function tokenRecentlyCreated($createdAt): bool
    {
        if ($this->throttle <= 0) {
            return false;
        }

        return Carbon::parse($createdAt)->addSeconds(
            $this->throttle
        )->isFuture();
    }

    /**
     * Insert into token storage.
     * 
     * @param \Fouladgar\OTP\Contracts\OTPNotifiable $notifiable
     * @param string $token
     * 
     * @return TokenPayload
     */
    abstract protected function save(OTPNotifiable $notifiable, string $token): TokenPayload;

    /**
     * Determine if the given notifiable recently created an otp.
     * 
     * @param OTPNotifiable
     * 
     * @return bool
     */
    abstract public function recentlyCreatedToken(OTPNotifiable $notifiable): bool;
}
