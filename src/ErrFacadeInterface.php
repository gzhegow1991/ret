<?php

namespace Gzhegow\Ret;

use Gzhegow\Ret\Ret\RetInterface;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Error\MainErrorInterface;
use Gzhegow\Ret\ErrorBag\ErrorBagInterface;
use Gzhegow\Ret\Error\AggregateErrorInterface;
use Gzhegow\Ret\Error\TriggeredErrorInterface;
use Gzhegow\Ret\ErrorMessage\ErrorMessageInterface;


interface ErrFacadeInterface
{
    public function isDebug() : bool;

    /**
     * @return static
     */
    public function debug(bool $enable);


    public function bag() : ErrorBagInterface;


    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     */
    public function new($from, $file = null, $line = null);

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     */
    public function code($from, $file = null, $line = null);

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     */
    public function message($from, $file = null, $line = null);


    /**
     * @param ErrorInterface[] $children
     * @param string|null      $file
     * @param int|null         $line
     * @param mixed            $message
     *
     * @return AggregateErrorInterface
     */
    public function aggregate($children, $file = null, $line = null, $message = null);


    /**
     * @param int         $severity
     * @param string      $message
     * @param string|null $file
     * @param int|null    $line
     * @param array|null  $payload
     * @param int|null    $code
     *
     * @return TriggeredErrorInterface
     */
    public function triggered($severity, $message, $file = null, $line = null, $payload = null, $code = null);


    /**
     * @return ErrorInterface
     */
    public function wrap(\Throwable $e);

    /**
     * @return \Throwable
     */
    public function unwrap(ErrorInterface $err);


    /**
     * @param ErrorInterface|RetInterface|\Throwable $e
     *
     * @return int|string|\BackedEnum
     */
    public function getCode($e);

    /**
     * @param ErrorInterface|RetInterface|\Throwable $e
     * @param mixed                                  $code
     */
    public function isCode($e, $code) : bool;


    /**
     * @param ErrorInterface|\Throwable $e
     */
    public function getMessage($e) : ErrorMessageInterface;


    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return ErrorInterface[]
     */
    public function getErrors($e) : array;

    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return \Generator<array, ErrorInterface[]>
     */
    public function iterErrors($e) : iterable;


    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return \Gzhegow\Ret\Error\SingleErrorInterface[]
     */
    public function getChildren($e) : array;

    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return \Generator<array, \Gzhegow\Ret\Error\SingleErrorInterface[]>
     */
    public function iterChildren($e) : iterable;


    /**
     * @param ErrorInterface|\Throwable $e
     */
    public function toString($e) : string;
}
