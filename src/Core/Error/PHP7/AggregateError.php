<?php

namespace Gzhegow\Ret\Core\Error\PHP7;

use Gzhegow\Ret\Core\Error\Err;
use Gzhegow\Ret\Core\Error\ErrorInterface;
use Gzhegow\Ret\Core\Error\AggregateErrorInterface;
use Gzhegow\Ret\Exception\AggregateExceptionInterface;


class AggregateError extends AbstractError implements AggregateErrorInterface
{
    /**
     * @var ErrorInterface[]
     */
    public $children;


    /**
     * @param ErrorInterface[] $children
     * @param string|null      $file
     * @param int|null         $line
     * @param mixed            $message
     *
     * @return static
     */
    public static function make(
        $children,
        $file = null, $line = null, $message = null
    )
    {
        if ( [] === $children ) {
            throw new \LogicException('The `children` should be array, non-empty');

        } else {
            foreach ( $children as $c ) {
                if ( ! ($c instanceof ErrorInterface) ) {
                    throw new \LogicException('Each of `children` should be instance of: ' . ErrorInterface::class);
                }
            }
        }

        $instance = new static();

        $instance->children = $children;
        //
        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = -1;

        if ( null === $message ) {
            $instance->message = "[ AGGREGATE ERR # TOTAL " . count($children) . " ]";
            $instance->payload = null;

        } else {
            $err = Err::message($message);

            $instance->message = $err->message;
            $instance->payload = $err->payload;
        }

        return $instance;
    }


    /**
     * @return bool
     */
    public function isCode($value)
    {
        // > aggregates cannot be compared
        return false;
    }


    /**
     * @return static
     */
    public static function wrap(AggregateExceptionInterface $e)
    {
        $instance = new static();

        $instance->throwable = $e;
        //
        $instance->children = $e->getErrors();
        //
        $instance->file = $e->getFile();
        $instance->line = $e->getLine();
        //
        $instance->code = $e->getCode();
        $instance->message = $e->getMessage();
        $instance->payload = $e->getPayload();

        return $instance;
    }
}
