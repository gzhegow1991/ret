<?php

namespace Gzhegow\Ret\Error\PHP7;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\ErrorMessage\ErrorMessage;
use Gzhegow\Ret\Error\AggregateErrorInterface;
use Gzhegow\Ret\Exception\AggregateRuntimeException;
use Gzhegow\Ret\Exception\AggregateExceptionInterface;


class AggregateError extends AbstractError implements AggregateErrorInterface
{
    /**
     * @var ErrorInterface[]
     */
    public $errors;


    /**
     * @param (ErrorInterface|\Throwable)[] $children
     * @param string|null                   $file
     * @param int|null                      $line
     * @param mixed                         $message
     *
     * @return static
     *
     * @internal use Err::aggregate() instead
     */
    public static function make(
        $children,
        $file = null, $line = null, $message = null
    )
    {
        if ( [] === $children ) {
            throw new \LogicException('The `children` should be array, non-empty');
        }

        $errorsArray = [];

        foreach ( $children as $c ) {
            if ( $c instanceof ErrorInterface ) {
                $errorsArray[] = $c;

            } elseif ( $c instanceof \Throwable ) {
                $errorsArray[] = Err::wrap($c);

            } else {
                throw new \LogicException(
                    ''
                    . 'Each of `children` should be instance one of: '
                    . '[ '
                    . implode(' ][ ', [
                        ErrorInterface::class,
                        \Throwable::class,
                    ])
                    . ' ]'
                );
            }
        }

        $instance = new static();

        $instance->errors = $errorsArray;
        //
        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = -1;

        if ( null === $message ) {
            $instance->message = "[ AGGREGATE ERROR # TOTAL " . count($children) . " ]";
            $instance->payload = null;

        } else {
            $msg = ErrorMessage::fromMessage($message);

            $instance->message = $msg->message;
            $instance->payload = $msg->payload;
        }

        if ( Err::$isDebug ) {
            $t = AggregateRuntimeException::fromErr($instance);
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
    public static function wrap(AggregateExceptionInterface $e)
    {
        $instance = new static();

        $instance->throwable = $e;
        //
        $instance->errors = $e->getErrors();
        //
        $instance->file = $e->getFile();
        $instance->line = $e->getLine();
        //
        $instance->code = $e->getCode();
        $instance->message = $e->getMessage();
        //
        $instance->payload = $e->getPayload();

        return $instance;
    }
}
