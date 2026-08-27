<?php

namespace Gzhegow\Ret;

use Gzhegow\Ret\Ret\RetInterface;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Error\MainErrorInterface;
use Gzhegow\Ret\ErrorBag\ErrorBagInterface;
use Gzhegow\Ret\Error\AggregateErrorInterface;
use Gzhegow\Ret\Error\TriggeredErrorInterface;
use Gzhegow\Ret\ErrorMessage\ErrorMessageInterface;


abstract class Err
{
    public static function isDebug() : bool
    {
        return static::$facade->isDebug();
    }

    /**
     * @return \Gzhegow\Ret\ErrFacadeInterface
     */
    public static function debug(bool $enable)
    {
        return static::$facade->debug($enable);
    }


    /**
     * @return \Gzhegow\Ret\ErrorBag\ErrorBagInterface
     */
    public static function bag()
    {
        return static::$facade->bag();
    }


    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     */
    public static function new($from, $file = null, $line = null)
    {
        return static::$facade->new(
            $from,
            $file, $line
        );
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     */
    public static function code($from, $file = null, $line = null)
    {
        return static::$facade->code(
            $from,
            $file, $line
        );
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     */
    public static function message($from, $file = null, $line = null)
    {
        return static::$facade->message(
            $from,
            $file, $line
        );
    }


    /**
     * @param ErrorInterface[] $children
     * @param string|null      $file
     * @param int|null         $line
     * @param mixed            $message
     *
     * @return AggregateErrorInterface
     */
    public static function aggregate(
        $children,
        $file = null, $line = null, $message = null
    )
    {
        return static::$facade->aggregate(
            $children,
            $file, $line, $message
        );
    }


    /**
     * @param string      $message
     * @param int         $code
     * @param int         $severity
     * @param string|null $file
     * @param int|null    $line
     *
     * @return TriggeredErrorInterface
     */
    public static function triggered(
        $severity, $message,
        $file = null, $line = null,
        $payload = null, $code = null
    )
    {
        return static::$facade->triggered(
            $severity, $message,
            $file, $line,
            $payload, $code
        );
    }


    /**
     * @param ErrorInterface           $error
     * @param array<string, bool>|null $tags
     *
     * @return \Gzhegow\Ret\ErrorBag\TaggedErrorInterface
     */
    public static function tagged($error, $tags = null)
    {
        return static::$facade->tagged($error, $tags);
    }


    /**
     * @return ErrorInterface
     */
    public static function wrap(\Throwable $e)
    {
        return static::$facade->wrap($e);
    }

    /**
     * @return \Throwable
     */
    public static function unwrap(ErrorInterface $err)
    {
        return static::$facade->unwrap($err);
    }


    /**
     * @param ErrorInterface|RetInterface|\Throwable $e
     *
     * @return int|string|\BackedEnum
     */
    public static function getCode($e)
    {
        return static::$facade->getCode($e);
    }

    /**
     * @param ErrorInterface|RetInterface|\Throwable $e
     * @param mixed                                  $code
     */
    public static function isCode($e, $code) : bool
    {
        return static::$facade->isCode($e, $code);
    }


    /**
     * @param ErrorInterface|\Throwable $e
     */
    public static function getMessage($e) : ErrorMessageInterface
    {
        return static::$facade->getMessage($e);
    }


    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return ErrorInterface[]
     */
    public static function getErrors($e) : array
    {
        return static::$facade->getErrors($e);
    }

    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return \Generator<array, ErrorInterface[]>
     */
    public static function iterErrors($e) : iterable
    {
        return static::$facade->iterErrors($e);
    }


    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return ErrorInterface[]
     */
    public static function getChildren($e) : array
    {
        return static::$facade->getChildren($e);
    }

    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return \Generator<array, ErrorInterface[]>
     */
    public static function iterChildren($e) : iterable
    {
        return static::$facade->iterChildren($e);
    }


    /**
     * @param ErrorInterface|\Throwable $e
     */
    public static function toString($e) : string
    {
        return static::$facade->toString($e);
    }


    public static function hasFacade() : bool
    {
        return null !== static::$facade;
    }

    public static function setFacade(?ErrFacadeInterface $facade) : ?ErrFacadeInterface
    {
        $last = static::$facade;

        static::$facade = $facade;

        return $last;
    }

    /**
     * @var ErrFacadeInterface
     */
    protected static $facade;
}
