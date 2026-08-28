<?php

namespace Gzhegow\Ret;

use Gzhegow\Ret\Ret\RetBagInterface;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Exception\AggregateRuntimeException;


/**
 * @template T of mixed
 * @template-covariant TT of ErrorInterface
 */
interface RetInterface
{
    /**
     * @return static
     */
    public function toBag(RetBagInterface $registry, $key = null);


    public function isEmpty() : bool;


    /**
     * @param array{ 0: mixed } $refs
     */
    public function isOk(array $refs = []) : bool;

    /**
     * @param array{ 0: ErrorInterface[] } $refs
     */
    public function isFail(array $refs = []) : bool;


    /**
     * @return T
     */
    public function getValue();


    /**
     * @return ErrorInterface
     */
    public function getError();

    /**
     * @return int|string|\BackedEnum
     */
    public function getCode();


    /**
     * @return \Generator<array, ErrorInterface>
     */
    public function iterError() : iterable;

    /**
     * @return array<string, ErrorInterface>
     */
    public function dotError() : array;


    /**
     * @return \Generator<array, \Gzhegow\Ret\Error\SingleErrorInterface>
     */
    public function iterErrorChildren() : iterable;

    /**
     * @return array<string, \Gzhegow\Ret\Error\SingleErrorInterface>
     */
    public function dotErrorChildren() : array;


    /**
     * @return T
     * @throws AggregateRuntimeException
     */
    public function orThrow($message = null);

    /**
     * @return T|ErrorInterface
     */
    public function orError($message = null, $file = null, $line = null);


    /**
     * @template TTT
     *
     * @param TTT $value
     *
     * @return T|TTT
     */
    public function orValue($value);

    /**
     * @return T|null
     */
    public function orNull();
}
