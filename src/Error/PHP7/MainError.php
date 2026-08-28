<?php

namespace Gzhegow\Ret\Error\PHP7;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\ErrorMessage\ErrorMessage;
use Gzhegow\Ret\Exception\RuntimeException;
use Gzhegow\Ret\Exception\ExceptionInterface;
use Gzhegow\Ret\Error\MainErrorInterface;


class MainError extends AbstractError implements MainErrorInterface
{
    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static
     *
     * @internal use Err::new() instead
     */
    public static function make($from, $file = null, $line = null)
    {
        $msg = ErrorMessage::fromMixed($from);

        $instance = new static();

        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = $msg->code;
        $instance->message = $msg->message;
        $instance->payload = $msg->payload;

        if ( Err::$isDebug ) {
            $t = RuntimeException::fromErr($instance);
            $t->traceShiftIncrement(3);
            $t->applyTraceShift();

            $instance->throwable = $t;
        }

        return $instance;
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static
     *
     * @internal use Err::code() instead
     */
    public static function code($from, $file = null, $line = null)
    {
        $msg = ErrorMessage::fromCode($from);

        $instance = new static();

        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = $msg->code;
        $instance->message = $msg->message;
        $instance->payload = $msg->payload;

        if ( Err::$isDebug ) {
            $t = RuntimeException::fromErr($instance);
            $t->traceShiftIncrement(3);
            $t->applyTraceShift();

            $instance->throwable = $t;
        }

        return $instance;
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static
     *
     * @internal use Err::new() instead
     */
    public static function message($from, $file = null, $line = null)
    {
        $msg = ErrorMessage::fromMessage($from);

        $instance = new static();

        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = $msg->code;
        $instance->message = $msg->message;
        $instance->payload = $msg->payload;

        if ( Err::$isDebug ) {
            $t = RuntimeException::fromErr($instance);
            $t->traceShiftIncrement(3);
            $t->applyTraceShift();

            $instance->throwable = $t;
        }

        return $instance;
    }

    protected function __construct()
    {
    }


    /**
     * @return static
     */
    public static function wrap(\Throwable $e)
    {
        $instance = new static();

        $instance->throwable = $e;
        //
        $instance->file = $e->getFile();
        $instance->line = $e->getLine();
        //
        $instance->code = $e->getCode();
        $instance->message = $e->getMessage();

        if ( $e instanceof ExceptionInterface ) {
            $instance->payload = $e->getPayload();
        }

        return $instance;
    }
}
