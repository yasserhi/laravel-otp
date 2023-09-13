<?php

namespace Fouladgar\OTP\Tests;

use Fouladgar\OTP\Contracts\OTPNotifiable;
use Fouladgar\OTP\Exceptions\InvalidOTPTokenException;
use Fouladgar\OTP\Exceptions\OTPThrottledException;
use Fouladgar\OTP\Notifications\Channels\OTPSMSChannel;
use Fouladgar\OTP\Notifications\OTPNotification;
use Fouladgar\OTP\Tests\Models\OTPNotifiableUser;
use Illuminate\Support\Facades\Notification;

use function PHPUnit\Framework\assertIsString;

class OTPBrokerTest extends TestCase
{
    protected OTPNotifiableUser $user;
    
    public function setUp(): void
    {
        parent::setUp();
        config()->set('otp.user_providers.users.model', OTPNotifiableUser::class);

        $this->user = new OTPNotifiableUser([
            'id' => 1,
            'mobile' => '5555555555',
            'email' => 'testuser@example.com'
        ]);
    }

    /**
     * @test
     */
    public function it_can_send_token_to_an_exist_user(): void
    {
        Notification::fake();

        $this->assertIsString(OTP()->send($this->user));

        Notification::assertSentTo(
            $this->user,
            OTPNotification::class
        );
    }

    /**
     * @test
     */
    public function it_can_send_token_with_using_default_channel(): void
    {
        Notification::fake();

        OTP()->send($this->user);
        $this->assertInstanceOf(OTPNotifiable::class, $this->user);

        Notification::assertSentTo(
            $this->user,
            fn (OTPNotification $notification, $channels) => $channels[0] == config('otp.channel')
        );
    }

    /**
     * @test
     */
    public function it_can_send_token_with_using_specified_channels(): void
    {
        Notification::fake();

        $useChannels = [OTPSMSChannel::class, 'mail'];
        $token = OTP($this->user, $useChannels);
        $this->assertIsString($token);

        Notification::assertSentTo(
            $this->user,
            fn (OTPNotification $notification, $channels) => $channels == $useChannels
        );
    }

    /**
     * @test
     */
    public function it_can_send_token_with_using_extended_channel(): void
    {
        Notification::fake();

        OTP()->channel('otp_sms')->send($this->user);
        $this->assertInstanceOf(OTPNotifiable::class, $this->user);

        Notification::assertSentTo(
            $this->user,
            fn (OTPNotification $notification, $channels) => $channels == ['otp_sms']
        );
    }

    /**
     * @test
     */
    public function it_can_send_token_with_using_custom_channel(): void
    {
        Notification::fake();

        $token = OTP($this->user, [CustomOTPChannel::class]);
        assertIsString($token);

        Notification::assertSentTo(
            $this->user,
            fn (OTPNotification $notification, $channels) => $channels == [CustomOTPChannel::class]
        );
    }

    /**
     * @test
     */
    public function it_can_not_validate_a_token_when_token_is_expired_or_invalid(): void
    {
        $this->expectException(InvalidOTPTokenException::class);

        OTP()->validate($this->user, '12345');
    }

    /**
     * @test
     */
    public function it_can_validate_a_valid_token(): void
    {
        // cache storage
        $token = OTP()->send($this->user);
        $valid = OTP()->validate($this->user, $token);

        $this->assertTrue($valid);

        // Database Storage
        config()->set('otp.token_storage', 'database');
        $otp = OTP();
        $token = $otp->send($this->user);
        $valid = OTP($this->user, $token);
        $this->assertTrue($valid);
    }

    /**
     * @test
     */
    public function it_can_revoke_a_token_successfully(): void
    {
        OTP($this->user);

        $this->assertTrue(OTP()->revoke($this->user));
        $this->assertFalse(OTP()->revoke($this->user));
    }

    /**
     * @test
     */
    public function it_fails_when_user_has_recently_created_otp(): void
    {
        config()->set('otp.throttle', 1);

        $token = OTP($this->user);
        assertIsString($token);
        
        $this->expectException(OTPThrottledException::class);
        $token = OTP($this->user);
    }
}
