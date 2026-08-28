<?php

namespace Gzhegow\Ret\Error\PHP8;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\Exception\TriggeredException;
use Gzhegow\Ret\Error\TriggeredErrorInterface;
use Gzhegow\Ret\Exception\TriggeredExceptionInterface;


class TriggeredError extends AbstractError implements TriggeredErrorInterface
{
    public int $severity;


    /**
     * @param int         $severity
     * @param string      $message
     * @param string|null $file
     * @param int|null    $line
     * @param array|null  $payload
     * @param int|null    $code
     *
     * @return static
     *
     * @internal use Err::triggered() instead
     */
    public static function make(
        int $severity, string $message,
        ?string $file = null, ?int $line = null,
        ?array $payload = null, ?int $code = null
    ) : static
    {
        $eCode = $code ?: -1;

        $instance = new static();

        $instance->severity = $severity;
        //
        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->message = $message;
        //
        $instance->code = $eCode;
        //
        $instance->payload = $payload;

        if ( Err::$isDebug ) {
            $t = TriggeredException::fromErr($instance);
            $t->traceShiftIncrement(3);
            $t->applyTraceShift();

            $instance->throwable = $t;
        }

        return $instance;
    }

    protected function __construct()
    {
    }


    public static function wrap(\ErrorException $e) : static
    {
        $instance = new static();

        $instance->throwable = $e;
        //
        $instance->severity = $e->getSeverity();
        //
        $instance->file = $e->getFile();
        $instance->line = $e->getLine();
        //
        $instance->code = $e->getCode();
        $instance->message = $e->getMessage();

        if ( $e instanceof TriggeredExceptionInterface ) {
            $instance->payload = $e->getPayload();
        }

        return $instance;
    }
}
