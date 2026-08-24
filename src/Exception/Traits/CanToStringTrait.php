<?php

namespace Gzhegow\Ret\Exception\Traits;

use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Core\Error\ErrorInterface;
use Gzhegow\Ret\Core\Error\AggregateErrorInterface;
use Gzhegow\Ret\Exception\AggregateExceptionInterface;


/**
 * @mixin \Throwable
 *
 * @see \Gzhegow\Ret\Exception\Interfaces\CanToStringInterface
 */
trait CanToStringTrait
{
    public function toString() : string
    {
        return static::castToString($this);
    }


    /**
     * @param \Throwable|ErrorInterface $e
     */
    public static function castToString($e) : string
    {
        $id = 1;

        return static::doCastToString($e, 0, $id);
    }

    /**
     * @param \Throwable|ErrorInterface $e
     */
    protected static function doCastToString($e, $level, &$id) : string
    {
        $result = null
            ?? static::doCastToStringError($e, $level, $id)
            ?? static::doCastToStringThrowable($e, $level, $id);

        if ( null === $result ) {
            throw new LogicException(
                [ 'The `e` is unknown', $e ]
            );
        }

        return $result;
    }

    /**
     * @param ErrorInterface $e
     */
    protected static function doCastToStringError($e, $level, &$id) : ?string
    {
        if ( ! ($e instanceof ErrorInterface) ) {
            return null;
        }

        if ( $e->throwable ) {
            return static::doCastToStringThrowable($e->throwable, $level, $id);
        }

        $class = get_class($e);
        $message = $e->message;
        $file = $e->file;
        $line = $e->line;
        $traceAsString = '#0 {main}';

        $withMessage = ('' === $message) ? '' : " with message '" . $message . "'";

        if ( $level === 0 ) {
            $linePad = '';

        } else {
            $linePad = str_repeat('--', $level) . ' ';
        }

        $result = ""
            . "{$linePad}[#{$id}] `{$class}`{$withMessage} in {$file}:{$line}\n"
            . "{$linePad}Stack trace:\n"
            . "{$linePad}{$traceAsString}";

        if ( $e instanceof AggregateErrorInterface ) {
            $id++;

            $linePad = str_repeat('--', $level + 1) . ' ';

            foreach ( $e->children as $ee ) {
                $result .= "\n"
                    . "{$linePad}\n"
                    . static::doCastToStringError($ee, $level + 1, $id);
            }
        }

        return $result;
    }

    /**
     * @param \Throwable $e
     */
    protected static function doCastToStringThrowable($e, $level, &$id) : ?string
    {
        if ( ! ($e instanceof \Throwable) ) {
            return null;
        }

        $class = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $traceAsString = $e->getTraceAsString();

        $withMessage = ('' === $message) ? '' : " with message '" . $message . "'";

        if ( $level === 0 ) {
            $linePad = '';

        } else {
            $linePad = str_repeat('--', $level) . ' ';

            $traceAsString = preg_replace("/\r?\n/", "\n{$linePad}", $traceAsString);
        }

        $result = ""
            . "{$linePad}[#{$id}] `{$class}`{$withMessage} in {$file}:{$line}\n"
            . "{$linePad}Stack trace:\n"
            . "{$linePad}{$traceAsString}";

        if ( $e instanceof AggregateExceptionInterface ) {
            $linePad = str_repeat('--', $level + 1) . ' ';

            foreach ( $e->getErrors() as $ee ) {
                $id++;

                $result .= "\n"
                    . "{$linePad}\n"
                    . static::doCastToString($ee, $level + 1, $id);
            }

        } elseif ( $ePrevious = $e->getPrevious() ) {
            $id++;

            $linePad = str_repeat('--', $level + 1) . ' ';

            $result .= "\n"
                . "{$linePad}\n"
                . static::doCastToStringThrowable($ePrevious, $level + 1, $id);
        }

        return $result;
    }
}
