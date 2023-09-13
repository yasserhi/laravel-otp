<?php

namespace Fouladgar\OTP\Exceptions;

use Exception;

class OTPThrottledException extends Exception
{
    public function __construct()
    {
        parent::__construct('User recently requested an OTP', 406);
    }
}
