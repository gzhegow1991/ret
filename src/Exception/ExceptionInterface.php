<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Exception\Interfaces\CanIsSameInterface;
use Gzhegow\Ret\Exception\Interfaces\HasPayloadInterface;
use Gzhegow\Ret\Exception\Interfaces\CanToStringInterface;
use Gzhegow\Ret\Exception\Interfaces\CanTraceShiftInterface;


interface ExceptionInterface extends
    \Throwable,
    //
    HasPayloadInterface,
    //
    CanIsSameInterface,
    CanToStringInterface,
    CanTraceShiftInterface
{
    public function getMessageArray() : array;
}
