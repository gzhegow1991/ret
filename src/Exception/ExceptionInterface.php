<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Exception\Interfaces\HasPayloadInterface;
use Gzhegow\Ret\Exception\Interfaces\CanTraceShiftInterface;


interface ExceptionInterface extends
    \Throwable,
    //
    HasPayloadInterface,
    //
    CanTraceShiftInterface
{
    public function toString() : string;
}
