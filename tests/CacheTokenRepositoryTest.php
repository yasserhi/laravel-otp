<?php

namespace Fouladgar\OTP\Tests;

use Carbon\Carbon;
use Fouladgar\OTP\Contracts\TokenRepositoryInterface;
use Fouladgar\OTP\Tests\Models\OTPNotifiableUser;
use Illuminate\Support\Facades\Cache;

class CacheTokenRepositoryTest extends TestCase
{
    protected TokenRepositoryInterface $repository;

    protected OTPNotifiableUser $user;

    public function setUp(): void
    {
        parent::setUp();

        config()->set('otp.token_storage', 'cache');
        $this->repository = $this->app->make(TokenRepositoryInterface::class);

        $this->user = new OTPNotifiableUser([
            'id' => 1,
            'mobile' => '5555555555',
            'email' => 'testuser@example.com'
        ]);
    }

    private function getSignatureKey(): string
    {
        return $this->user::class.$this->user->id;
    }
    /**
     * @test
     */
    public function it_can_create_a_token_successfully(): void
    {
        $payload = [
            'authenticable_id' => $this->user->id,
            'authenticable_type' => $this->user::class,
            'sent_at' => now()->toDateTimeString()
        ];

        $token_payload = $this->repository->create($this->user);
        $payload['token'] = $token_payload->token;;

        $this->assertEquals(Cache::get($this->getSignatureKey()), $payload);
    }

    /**
     * @test
     */
    public function it_can_delete_existing_token_successfully(): void
    {
        $this->repository->create($this->user);

        $this->assertTrue($this->repository->deleteExisting($this->user));

        $this->assertNull(Cache::get($this->getSignatureKey()));
    }

    /**
     * @test
     */
    public function it_can_find_existing_and_not_expired_token_successfully(): void
    {
        $token_payload = $this->repository->create($this->user);

        $this->assertTrue($this->repository->exists($this->user, $token_payload->token));
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

        Carbon::setTestNow();
        $this->assertFalse($this->repository->exists($this->user, $token_payload->token));
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
