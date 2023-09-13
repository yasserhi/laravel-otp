<?php

declare(strict_types=1);

namespace Fouladgar\OTP\Token;

use Fouladgar\OTP\Contracts\AbstractTokenRepository;
use Fouladgar\OTP\Contracts\OTPNotifiable;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Query\Builder;

class DatabaseTokenRepository extends AbstractTokenRepository
{
    public function __construct(
        protected ConnectionInterface $connection,
        protected int $expires,
        protected int $tokenLength,
        protected int $throttle,
        protected string $table
    ) {
        parent::__construct($expires, $tokenLength, $throttle);
    }

    public function deleteExisting(OTPNotifiable $notifiable): bool
    {
        return (bool) optional(
                $this->getTable()
                ->where('authenticable_id', $notifiable->id)
                ->where('authenticable_type', $notifiable::class)  
            )->delete();
    }

    public function exists(OTPNotifiable $notifiable, string $token): bool
    {
        $record = (array) $this->getTable()
                               ->where('authenticable_id', $notifiable->id)
                               ->where('authenticable_type', $notifiable::class)
                               ->where('token', $token)
                               ->first();

        return $record && ! $this->tokenExpired($record['expires_at']);
    }

    public function recentlyCreatedToken(OTPNotifiable $notifiable): bool
    {
        $record = (array) $this->getTable()
                                ->where('authenticable_id', $notifiable->id)
                                ->where('authenticable_type', $notifiable::class)
                                ->first();

        return $record && $this->tokenRecentlyCreated($record['sent_at']);
    }

    protected function getTable(): Builder
    {
        return $this->connection->table($this->table);
    }

    protected function save(OTPNotifiable $notifiable, string $token): bool
    {
        return $this->getTable()->insert($this->getPayload($notifiable, $token));
    }

    protected function getPayload(OTPNotifiable $notifiable, string $token): array
    {
        return parent::getPayload($notifiable, $token) + ['expires_at' => now()->addMinutes($this->expires)];
    }

    
}
