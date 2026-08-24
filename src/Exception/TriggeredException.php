<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Core\Err;
use Gzhegow\Ret\Core\Error\TriggeredErrorInterface;


class TriggeredException extends \ErrorException implements TriggeredExceptionInterface
{
    use ExceptionTrait;


    /**
     * @return static
     */
    public static function fromErr(TriggeredErrorInterface $err)
    {
        if ( $ex = $err->throwable ) {
            if ( $ex instanceof static ) {
                return $ex;
            }
        }

        $ex = Err::unwrap($err);
        if ( $ex instanceof static ) {
            return $ex;
        }

        $instance = new static(
            $err->severity, $err->message,
            $err->file, $err->line,
            $err->code,
            $ex
        );
        $instance->traceShift(1);

        return $instance;
    }


    public function __construct(
        int $severity, string $message,
        ?string $file = null, ?int $line = null,
        ?int $code = null,
        ?\Throwable $previous = null
    )
    {
        if ( null === $previous ) {
            $this->severity = $severity;
            //
            $this->file = $file ?? 'unknown';
            $this->line = $line ?? 0;
            //
            $this->message = $message;
            $this->code = $code ?? -1;

        } else {
            parent::__construct(
                $message, $code,
                $severity,
                $file, $line,
                $previous
            );
        }
    }


    public function __toString() : string
    {
        // > @gzhegow, it should be...
        // trigger_error('Casting exceptions using `__toString` is deprecated, they MUST lead to fatal consequences when they hit an unexpected place', E_USER_DEPRECATED);

        return $this->toString();
    }
}
