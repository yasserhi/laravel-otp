<?php

use Fouladgar\OTP\Contracts\OTPNotifiable;
use Fouladgar\OTP\Exceptions\InvalidOTPTokenException;
use Fouladgar\OTP\OTPBroker;
use Fouladgar\OTP\Token\TokenPayload;

if (! function_exists('OTP')) {
    /**
     * @throws InvalidOTPTokenException|Throwable
     */
    function OTP(?OTPNotifiable $notifiable = null, $token = null, $revoke=True):OTPBroker|TokenPayload|bool
    {
        /** @var OTPBroker $OTP */
        $OTP = app(OTPBroker::class);

        if (is_null($notifiable)) {
            return $OTP;
        }

        if (is_null($token)) {
            return $OTP->send($notifiable);
        }

        if (is_array($token)) {
            return $OTP->channel($token)->send($notifiable);
        }

        return $OTP->validate($notifiable, $token, $revoke);
    }
}
