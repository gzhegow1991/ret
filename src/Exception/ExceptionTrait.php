<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Exception\Traits\CanIsSameTrait;
use Gzhegow\Ret\Exception\Traits\HasPayloadTrait;
use Gzhegow\Ret\Exception\Traits\CanToStringTrait;
use Gzhegow\Ret\Exception\Traits\CanTraceShiftTrait;


/**
 * @mixin \Throwable
 *
 * @see \Gzhegow\Ret\Exception\MainExceptionInterface
 */
trait ExceptionTrait
{
    use HasPayloadTrait;

    use CanIsSameTrait;
    use CanToStringTrait;
    use CanTraceShiftTrait;


    public function getMessageArray() : array
    {
        return [ '' => $this->getCode(), 0 => $this->getMessage() ] + $this->getPayloadArray();
    }
}
