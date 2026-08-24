<?php

namespace Gzhegow\Ret\Core\Error\PHP7;

use Gzhegow\Ret\Exception\ExceptionInterface;
use Gzhegow\Ret\Core\Error\TriggeredErrorInterface;
use Gzhegow\Ret\Exception\TriggeredExceptionInterface;


class TriggeredError extends AbstractError implements TriggeredErrorInterface
{
    /**
     * @var int
     */
    public $severity;


    /**
     * @param int         $severity
     * @param string      $message
     * @param string|null $file
     * @param int|null    $line
     * @param int|null    $code
     *
     * @return static
     */
    public static function make(
        $severity, $message,
        $file = null, $line = null,
        $code = null
    )
    {
        $instance = new static();

        $instance->severity = $severity;
        //
        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = $code ?? -1;
        $instance->message = $message;

        return $instance;
    }

    protected function __construct()
    {
    }


    /**
     * @return static
     */
    public static function wrap(\ErrorException $e)
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
