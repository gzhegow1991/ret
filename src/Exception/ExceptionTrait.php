<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\Exception\Traits\HasPayloadTrait;
use Gzhegow\Ret\Exception\Traits\CanTraceShiftTrait;


/**
 * @mixin \Throwable
 *
 * @see \Gzhegow\Ret\Exception\ExceptionInterface
 */
trait ExceptionTrait
{
    use HasPayloadTrait;

    use CanTraceShiftTrait;


    public function toString() : string
    {
        return Err::toString($this);
    }
}
