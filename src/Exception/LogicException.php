<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\Error\MainErrorInterface;
use Gzhegow\Ret\ErrorMessage\ErrorMessage;


class LogicException extends \LogicException implements MainExceptionInterface
{
    use ExceptionTrait;


    /**
     * @return static
     */
    public static function fromErr(MainErrorInterface $err)
    {
        if ( $ex = $err->throwable ) {
            if ( $ex instanceof static ) {
                return $ex;
            }
        }

        $instance = new static(ErrorMessage::fromError($err), $ex);
        $instance->traceShift(1);

        return $instance;
    }


    public function __construct($error, ?\Throwable $previous = null)
    {
        $err = ErrorMessage::fromMixed($error);

        if ( null === $previous ) {
            $this->message = $err->message;
            $this->code = $err->code;

        } else {
            parent::__construct($err->message, $err->code, $previous);
        }

        $this->payload = $err->payload;
    }


    public function __toString() : string
    {
        // > @gzhegow, it should be...
        // trigger_error('Casting exceptions using `__toString` is deprecated, they MUST lead to fatal consequences when they hit an unexpected place', E_USER_DEPRECATED);

        return $this->toString();
    }
}
