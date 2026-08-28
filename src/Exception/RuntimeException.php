<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Error\MainErrorInterface;
use Gzhegow\Ret\ErrorMessage\ErrorMessage;


class RuntimeException extends \RuntimeException implements MainExceptionInterface
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
        $msg = ErrorMessage::fromMixed($error);

        if ( null === $previous ) {
            $this->message = $msg->message;
            $this->code = $msg->code;

        } else {
            parent::__construct($msg->message, $msg->code, $previous);
        }

        $this->payload = $msg->payload;
    }


    public function __toString() : string
    {
        // > @gzhegow, it should be...
        // trigger_error('Casting exceptions using `__toString` is deprecated, they MUST lead to fatal consequences when they hit an unexpected place', E_USER_DEPRECATED);

        return $this->toString();
    }
}
