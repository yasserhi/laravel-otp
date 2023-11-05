<?php

namespace Fouladgar\OTP\Token;

use Carbon\Carbon;

class TokenPayload
{
    public function __construct(
        protected int $authenticable_id,
        protected string $authenticable_type,
        public string $token,
        protected Carbon $sent_at,
        public Carbon $expires_at,
        public Carbon $throttled_till,
    ) {
    }

    public function toArray($exclude = [])
    {
        $array = [
            'authenticable_id' => $this->authenticable_id,
            'authenticable_type' => $this->authenticable_type,
            'token' => $this->token,
            'sent_at' => $this->sent_at->toDateTimeString(),
            'expires_at' => $this->expires_at->toDateTimeString(),
            'throttled_till' => $this->throttled_till->toDateTimeString(),
        ];
        foreach ($exclude as $key) {
            unset($array[$key]);
        }

        return $array;
    }
}