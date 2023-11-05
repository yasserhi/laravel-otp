<?php

namespace Fouladgar\OTP\Tests;

use Carbon\Carbon;
use Fouladgar\OTP\Contracts\TokenRepositoryInterface;
use Fouladgar\OTP\Tests\Models\OTPNotifiableUser;
use Illuminate\Support\Str;

class DatabaseTokenRepositoryTest extends TestCase
{
    protected TokenRepositoryInterface $repository;

    protected OTPNotifiableUser $user;

    public function setUp(): void
    {
        parent::setUp();

        $config = app('config');
        $config->set('otp.token_storage', 'database');
        $this->repository = $this->app->make(TokenRepositoryInterface::class);

        $this->user = new OTPNotifiableUser([
            'id' => 1,
            'mobile' => '5555555555',
            'email' => 'testuser@example.com'
        ]);
    }

    /**
     * @test
     */
    public function it_can_create_a_token_successfully(): void
    {
        $token_payload = $this->repository->create($this->user);
        $token = $token_payload->token;

        $this->assertEquals(config('otp.token_length'), Str::length($token));

        $this->assertDatabaseHas('otp_tokens', [
            'authenticable_id' => $this->user->id,
            'authenticable_type' => $this->user::class,
            'token' => $token,
            'expires_at' => (string) now()->addMinutes(config('otp.token_lifetime')),
        ]);
    }

    /**
     * @test
     */
    public function it_can_delete_existing_token_successfully(): void
    {
        $token_payload = $this->repository->create($this->user);
        $token = $token_payload->token;

        $tokenRow = [
            'authenticable_id' => $this->user->id,
            'authenticable_type' => $this->user::class,
            'token' => $token,
        ];

        $this->assertTrue($this->repository->deleteExisting($this->user));
        $this->assertDatabaseMissing('otp_tokens', $tokenRow);
    }

    /**
     * @test
     */
    public function it_can_find_existing_and_not_expired_token_successfully(): void
    {
        $token_payload = $this->repository->create($this->user);
        $token = $token_payload->token;

        $this->assertTrue($this->repository->exists($this->user, $token));
    }

    /**
     * @test
     */
    public function it_fails_when_token_is_exist_but_expired(): void
    {
        $testDate = Carbon::create(2022, 1, 20, 12);
        Carbon::setTestNow($testDate);

        $this->repository = $this->app->make(TokenRepositoryInterface::class);
        $token_payload = $this->repository->create($this->user);
        $token = $token_payload->token;

        Carbon::setTestNow();
        $this->assertFalse($this->repository->exists($this->user, $token));
    }

    /**
     * @test
     */
    public function it_fails_when_token_does_not_exists(): void
    {
        $this->repository->create($this->user);

        $this->assertFalse($this->repository->exists($this->user, 'invalid_token'));
    }
}
