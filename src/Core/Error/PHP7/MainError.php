<?php

namespace Gzhegow\Ret\Core\Error\PHP7;

use Gzhegow\Ret\Core\ErrorMessage\ErrorMessage;
use Gzhegow\Ret\Exception\ExceptionInterface;
use Gzhegow\Ret\Core\Error\MainErrorInterface;


class MainError extends AbstractError implements MainErrorInterface
{
    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static
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

        return $instance;
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static
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

        return $instance;
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static
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
