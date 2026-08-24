<?php

namespace Gzhegow\Ret\Core\Error\PHP8;

use Gzhegow\Ret\Core\Error\TriggeredErrorInterface;


class TriggeredError extends AbstractError implements TriggeredErrorInterface
{
    public int $severity;


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
        int $severity, string $message,
        ?string $file = null, ?int $line = null,
        ?int $code = null
    ) : static
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

        return $instance;
    }
}
