<?php

declare(strict_types=1);

namespace Fouladgar\OTP\Token;

use Fouladgar\OTP\Contracts\AbstractTokenRepository;
use Fouladgar\OTP\Contracts\OTPNotifiable;
use Illuminate\Contracts\Cache\Repository as Cache;

class CacheTokenRepository extends AbstractTokenRepository
{
    public function __construct(
        protected Cache $cache,
        protected int $expires,
        protected int $tokenLength,
        protected int $throttle,
        protected string $prefix
    ) {
        parent::__construct($expires, $tokenLength, $throttle);
    }

    public function deleteExisting(OTPNotifiable $notifiable): bool
    {
        return $this->cache->forget($this->getSignatureKey($notifiable));
    }

    public function exists(OTPNotifiable $notifiable, string $token): bool
    {
        $signature = $this->getSignatureKey($notifiable);

        return $this->cache->has($signature) &&
            $this->cache->get($signature)['token'] === $token;
    }

    public function recentlyCreatedToken(OTPNotifiable $notifiable): bool
    {
        $signature = $this->getSignatureKey($notifiable);

        return $this->cache->has($signature) &&
            $this->tokenRecentlyCreated($this->cache->get($signature)['sent_at']);
    }

    protected function save(OTPNotifiable $notifiable, string $token): TokenPayload
    {
        
        $token_payload = $this->getPayload($notifiable, $token);
        $this->cache->add(
            $this->getSignatureKey($notifiable),
            $token_payload->toArray(['throttled_till', 'expires_at']),
            $token_payload->expires_at,
        );
        return $token_payload;
    }

    protected function getSignatureKey(OTPNotifiable $notifiable): string
    {
        return $this->prefix.$notifiable::class.$notifiable->id;
    }
}
