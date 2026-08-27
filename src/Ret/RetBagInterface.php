<?php

namespace Gzhegow\Ret\Ret;

interface RetBagInterface
{
    /**
     * @return int|string|null
     */
    public function push(Ret $ret, $key = null);

    /**
     * @return int|string|null
     */
    public function add($key, Ret $ret);

    /**
     * @return int|string
     */
    public function set($key, Ret $ret);


    public function get($key, bool $orNull = false) : ?Ret;

    public function pos(int $position, bool $orNull = false) : ?Ret;


    /**
     * @param Ret[] $rets
     *
     * @return static
     */
    public function merge(array $rets);

    /**
     * @param Ret[] $rets
     *
     * @return static
     */
    public function replace(array $rets);

    /**
     * @param Ret[] $rets
     *
     * @return static
     */
    public function append(array $rets);


    public function isEmpty() : bool;


    /**
     * @template T
     *
     * @param array{ 0: T[], 1: Ret<T[]> } $refs
     */
    public function isOk(array $refs = []) : bool;

    /**
     * @template T
     *
     * @param array{ 0: T, 1: Ret<T> } $refs
     */
    public function hasOk(array $refs = []) : bool;


    /**
     * @param array{ 0?: \Gzhegow\Ret\Error\ErrorInterface[], 1?: Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface> } $refs
     */
    public function isFail(array $refs = []) : bool;

    /**
     * @param array{ 0?: \Gzhegow\Ret\Error\ErrorInterface, 1?: Ret<null, \Gzhegow\Ret\Error\ErrorInterface>  } $refs
     */
    public function hasFail(array $refs = []) : bool;


    public function isMixed() : int;


    /**
     * @return Ret<mixed, null>|Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function first(bool $orNull = false) : ?Ret;

    /**
     * @return Ret<mixed, null>|null
     */
    public function firstOk(bool $orNull = false) : ?Ret;

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function firstFail(bool $orNull = false) : ?Ret;

    /**
     * @return array{ 0: Ret<mixed, null>|null, 1: Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null }
     */
    public function firstPair() : array;


    /**
     * @return Ret<mixed, null>|Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function last(bool $orNull = false) : ?Ret;

    /**
     * @return Ret<mixed, null>|null
     */
    public function lastOk(bool $orNull = false) : ?Ret;

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function lastFail(bool $orNull = true) : ?Ret;

    /**
     * @return array{ 0: Ret<mixed, null>|null, 1: Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null }
     */
    public function lastPair() : array;


    /**
     * @return (Ret<mixed, null>|Ret<null, \Gzhegow\Ret\Error\ErrorInterface>)[]
     */
    public function all() : array;

    /**
     * @return Ret<mixed, null>[]
     */
    public function allOk() : array;

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\ErrorInterface>[]
     */
    public function allFail() : array;

    /**
     * @return array{ 0: Ret<mixed, null>[], 1: Ret<null, \Gzhegow\Ret\Error\ErrorInterface>[] }
     */
    public function allPair() : array;


    /**
     * @return Ret<array, null>|null
     */
    public function groupOk(bool $orNull = false) : ?Ret;

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null
     */
    public function groupFail(bool $orNull = false) : ?Ret;

    /**
     * @return array{ 0: Ret<array, null>|null, 1: Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null }
     */
    public function groupPair() : array;


    /**
     * @return Ret<array, null>|Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null
     */
    public function resolved(bool $orNull = false) : ?Ret;

    /**
     * @return Ret<array, null>|null
     */
    public function resolvedOk(bool $orNull = false) : ?Ret;

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null
     */
    public function resolvedFail(bool $orNull = false) : ?Ret;


    /**
     * @return Ret<array, null>|Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function firstFailOrResolvedOk(bool $orNull = false) : ?Ret;

    /**
     * @return Ret<mixed, null>|Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null
     */
    public function firstOkOrResolvedFail(bool $orNull = false) : ?Ret;


    public function getValues() : array;


    /**
     * @return \Gzhegow\Ret\Error\ErrorInterface[]
     */
    public function getErrors() : array;

    /**
     * @return \Generator<array, \Gzhegow\Ret\Error\ErrorInterface>
     */
    public function iterErrors() : iterable;

    /**
     * @return array<string, \Gzhegow\Ret\Error\ErrorInterface>
     */
    public function dotErrors() : array;
}
