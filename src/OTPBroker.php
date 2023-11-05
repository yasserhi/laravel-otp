<?php

declare(strict_types=1);

namespace Fouladgar\OTP;

use Fouladgar\OTP\Contracts\OTPNotifiable;
use Fouladgar\OTP\Contracts\TokenRepositoryInterface;
use Fouladgar\OTP\Exceptions\InvalidOTPTokenException;
use Fouladgar\OTP\Exceptions\OTPThrottledException;
use Fouladgar\OTP\Token\TokenPayload;
use Illuminate\Support\Arr;
use Throwable;

class OTPBroker
{
    private array $channel;

    public function __construct(
        private TokenRepositoryInterface $tokenRepository,
    ) {
        $this->channel = $this->getDefaultChannel();
    }

    /**
     * @throws OTPThrottledException|Throwable
     */
    public function send(OTPNotifiable $notifiable): TokenPayload
    {
        throw_if($this->tokenRepository->recentlyCreatedToken($notifiable), OTPThrottledException::class);
        
        $token_payload = $this->tokenRepository->create($notifiable);

        $notifiable->sendOTPNotification(
            $token_payload->token,
            $this->channel
        );

        return $token_payload;
    }

    /**
     * @throws InvalidOTPTokenException|Throwable
     */
    public function validate(OTPNotifiable $notifiable, string $token, bool $revoke=true): bool
    {
        throw_unless($this->tokenExists($notifiable, $token), InvalidOTPTokenException::class);

        if ($revoke)
            $this->revoke($notifiable);

        return true;
    }

    public function channel($channel = ['']): static
    {
        $this->channel = is_array($channel) ? $channel : func_get_args();

        return $this;
    }

    public function revoke(OTPNotifiable $notifiable): bool
    {
        return $this->tokenRepository->deleteExisting($notifiable);
    }

    private function getDefaultChannel(): array
    {
        $channel = config('otp.channel');

        return is_array($channel) ? $channel : Arr::wrap($channel);
    }

    private function tokenExists(OTPNotifiable $notifiable, string $token): bool
    {
        return $this->tokenRepository->exists($notifiable, $token);
    }
}
