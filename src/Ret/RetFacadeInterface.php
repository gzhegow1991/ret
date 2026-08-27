<?php

namespace Gzhegow\Ret\Ret;

use Gzhegow\Ret\Error\ErrorInterface;


/**
 * @template T
 * @template-covariant TT of ErrorInterface
 */
interface RetFacadeInterface
{
    public function bag() : RetBagInterface;


    public function wrapper() : RetWrapperInterface;

    public function wrap($value, ?RetWrapperInterface $wrapper = null) : Ret;


    /**
     * @param T $value
     *
     * @return Ret<T, null>
     */
    public function ok($value);

    /**
     * @param mixed       $error
     * @param string|null $file
     * @param int|null    $line
     *
     * @return Ret<null, TT>
     */
    public function fail($error, $file = null, $line = null);

    /**
     * @param Ret<T,TT>|TT|\Throwable $source
     * @param mixed                   $message
     * @param string|null             $file
     * @param int|null                $line
     *
     * @return Ret<T, TT>
     */
    public function pass($source, $message = null, $file = null, $line = null);


    /**
     * @return mixed
     */
    public function okValue($value);

    /**
     * @return ErrorInterface
     */
    public function failError($error, $file = null, $line = null);

    /**
     * @return Ret
     */
    public function passRet($source, $message = null, $file = null, $line = null);


    /**
     * @param callable $fn
     *
     * @return \Closure(array $fnArgs, ?array $fileLine) : Ret<T>
     */
    public function fn($fn, ?RetWrapperInterface $wrapper = null) : \Closure;

    /**
     * @param callable $fn
     *
     * @return Ret<T>
     */
    public function fnCall($fn, array $fnArgs = [], ?array $fileLine = null, ?RetWrapperInterface $wrapper = null);
}
