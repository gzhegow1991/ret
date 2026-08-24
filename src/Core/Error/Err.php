<?php

namespace Gzhegow\Ret\Core\Error;

use Gzhegow\Ret\Exception\Exception;
use Gzhegow\Ret\Exception\TriggeredException;
use Gzhegow\Ret\Exception\AggregateException;
use Gzhegow\Ret\Exception\AggregateExceptionInterface;


abstract class Err
{
    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function new($from, $file = null, $line = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\MainError::make($from, $file, $line)
            : \Gzhegow\Ret\Core\Error\PHP7\MainError::make($from, $file, $line);
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function code($from, $file = null, $line = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\MainError::code($from, $file, $line)
            : \Gzhegow\Ret\Core\Error\PHP7\MainError::code($from, $file, $line);
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function message($from, $file = null, $line = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\MainError::message($from, $file, $line)
            : \Gzhegow\Ret\Core\Error\PHP7\MainError::message($from, $file, $line);
    }


    /**
     * @param ErrorInterface[] $children
     * @param string|null      $file
     * @param int|null         $line
     * @param mixed            $message
     *
     * @return AggregateErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function aggregate(
        $children,
        $file = null, $line = null, $message = null
    )
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\AggregateError::make($children, $file, $line, $message)
            : \Gzhegow\Ret\Core\Error\PHP7\AggregateError::make($children, $file, $line, $message);
    }


    /**
     * @param string      $message
     * @param int         $code
     * @param int         $severity
     * @param string|null $file
     * @param int|null    $line
     *
     * @return TriggeredErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function triggered(
        $severity, $message,
        $file = null, $line = null,
        $code = null
    )
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\TriggeredError::make($severity, $message, $file, $line, $code)
            : \Gzhegow\Ret\Core\Error\PHP7\TriggeredError::make($severity, $message, $file, $line, $code);
    }


    /**
     * @return ErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function wrap(\Throwable $e)
    {
        if ( $e instanceof AggregateExceptionInterface ) {
            $instance = (PHP_VERSION_ID >= 80000)
                ? \Gzhegow\Ret\Core\Error\PHP8\AggregateError::wrap($e)
                : \Gzhegow\Ret\Core\Error\PHP7\AggregateError::wrap($e);

        } elseif ( $e instanceof \ErrorException ) {
            $instance = (PHP_VERSION_ID >= 80000)
                ? \Gzhegow\Ret\Core\Error\PHP8\TriggeredError::wrap($e)
                : \Gzhegow\Ret\Core\Error\PHP7\TriggeredError::wrap($e);

        } else {
            $instance = (PHP_VERSION_ID >= 80000)
                ? \Gzhegow\Ret\Core\Error\PHP8\MainError::wrap($e)
                : \Gzhegow\Ret\Core\Error\PHP7\MainError::wrap($e);
        }

        return $instance;
    }

    /**
     * @return \Throwable
     */
    public static function unwrap(ErrorInterface $err)
    {
        if ( $ex = $err->throwable ) {
            return $ex;
        }

        if ( $err instanceof AggregateErrorInterface ) {
            $instance = new AggregateException($err->children ?? [], $err->getMessageArray());

        } else {
            if ( $err instanceof TriggeredErrorInterface ) {
                $instance = new TriggeredException(
                    $err->severity, $err->message,
                    $err->file, $err->line,
                    $err->code
                );

            } else {
                $instance = new Exception($err->getMessageArray());
            }
        }

        $instance->traceShift(1);
        $instance->applyTraceShift();

        return $instance;
    }


    public static function isCode($left, $right) : bool
    {
        if ( $left instanceof ErrorInterface ) {
            return $left->isCode($right);
        }

        if ( $right instanceof ErrorInterface ) {
            return $right->isCode($left);
        }

        return false;
    }
}
