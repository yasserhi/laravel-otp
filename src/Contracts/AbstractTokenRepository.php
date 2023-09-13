<?php

declare(strict_types=1);

namespace Fouladgar\OTP\Contracts;

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
     * @return string
     */
    public function create(OTPNotifiable $notifiable): string
    {
        $this->deleteExisting($notifiable);

        $token = $this->createNewToken();

        $this->save($notifiable, $token);

        return $token;
    }

    protected function createNewToken(): string
    {
        return (string) random_int(10 ** ($this->tokenLength - 1), (10 ** $this->tokenLength) - 1);
    }

    protected function tokenExpired(string $expiresAt): bool
    {
        return Carbon::parse($expiresAt)->isPast();
    }

    protected function getPayload(OTPNotifiable $notifiable, string $token): array
    {
        return [
            'authenticable_id' => $notifiable->id,
            'authenticable_type' => $notifiable::class,
            'token' => $token,
            'sent_at' => now()->toDateTimeString()
        ];
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
     * @return bool
     */
    abstract protected function save(OTPNotifiable $notifiable, string $token): bool;

    /**
     * Determine if the given notifiable recently created an otp.
     * 
     * @param OTPNotifiable
     * 
     * @return bool
     */
    abstract public function recentlyCreatedToken(OTPNotifiable $notifiable): bool;
}
