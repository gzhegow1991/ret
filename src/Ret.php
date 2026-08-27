<?php

namespace Gzhegow\Ret;

use Gzhegow\Ret\Ret\RetInterface;
use Gzhegow\Ret\Ret\RetBagInterface;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Ret\RetWrapperInterface;
use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Exception\RuntimeException;
use Gzhegow\Ret\Exception\AggregateRuntimeException;


/**
 * @template T of mixed
 * @template-covariant TT of ErrorInterface
 */
class Ret implements RetInterface
{
    /**
     * @var array{ 0?: T }
     */
    protected $value = [];
    /**
     * @var array{ 0?: TT }
     */
    protected $error = [];


    public static function bag() : RetBagInterface
    {
        return static::$facade->bag();
    }


    public static function wrapper() : RetWrapperInterface
    {
        return static::$facade->wrapper();
    }

    public static function wrap($value, ?RetWrapperInterface $wrapper = null) : Ret
    {
        return static::$facade->wrap($value, $wrapper);
    }


    /**
     * @param \Gzhegow\Ret\RetFacadeInterface $ret
     * @param array{ 0?: T, 1?: TT }          $valueError
     *
     * @return static
     * @noinspection PhpUnusedParameterInspection
     */
    public static function new(RetFacadeInterface $ret, array $valueError = [])
    {
        $instance = new static();

        if ( isset($valueError[0]) ) $instance->value[0] = $valueError[0];
        if ( isset($valueError[1]) ) $instance->error[0] = $valueError[1];

        return $instance;
    }

    protected function __construct()
    {
    }


    /**
     * @param T $value
     *
     * @return static<T, null>
     */
    public static function ok($value)
    {
        return static::$facade->ok($value);
    }

    /**
     * @param mixed       $error
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static<null, TT>
     */
    public static function fail($error, $file = null, $line = null)
    {
        return static::$facade->fail($error, $file, $line);
    }

    /**
     * @param static<T,TT>|ErrorInterface|\Throwable $source
     * @param mixed                                  $message
     * @param string|null                            $file
     * @param int|null                               $line
     *
     * @return static<T, TT>
     */
    public static function pass($source, $message = null, $file = null, $line = null)
    {
        return static::$facade->pass($source, $message, $file, $line);
    }


    /**
     * @param callable $fn
     *
     * @return \Closure(array $fnArgs, ?array $fileLine) : Ret<T>
     */
    public static function fn($fn, ?RetWrapperInterface $wrapper = null) : \Closure
    {
        return static::$facade->fn($fn, $wrapper);
    }

    /**
     * @param callable $fn
     *
     * @return Ret<T>
     */
    public static function fnCall($fn, array $fnArgs = [], ?array $fileLine = null, ?RetWrapperInterface $wrapper = null)
    {
        return static::$facade->fnCall($fn, $fnArgs, $fileLine, $wrapper);
    }


    /**
     * @return static
     */
    public function toBag(RetBagInterface $registry, $key = null)
    {
        $registry->push($this, $key);

        return $this;
    }


    public function isEmpty() : bool
    {
        return ([] === $this->value) && ([] === $this->error);
    }


    /**
     * @param array{ 0: mixed } $refs
     */
    public function isOk(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refValue =& $refs[0];
        $refValue = null;

        if ( [] !== $this->error ) {
            return false;
        }

        if ( [] === $this->value ) {
            return false;
        }

        $refValue = $this->value[0];

        return true;
    }

    /**
     * @param array{ 0: ErrorInterface[] } $refs
     */
    public function isFail(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refError =& $refs[0];
        $refError = null;

        if ( [] !== $this->value ) {
            return false;
        }

        if ( [] === $this->error ) {
            return false;
        }

        $refError = $this->error[0];

        return true;
    }


    /**
     * @return T
     */
    public function getValue()
    {
        if ( [] === $this->value ) {
            throw new RuntimeException('The `ret` contains no value');
        }

        return $this->value[0];
    }


    /**
     * @return ErrorInterface
     */
    public function getError()
    {
        if ( [] === $this->error ) {
            throw new RuntimeException('The `ret` contains no error');
        }

        return $this->error[0];
    }


    /**
     * @return \Generator<array, ErrorInterface>
     */
    public function iterErrors() : iterable
    {
        if ( [] === $this->error ) {
            throw new RuntimeException('The `ret` contains no error');
        }

        yield from Err::iterErrors($this->error[0]);
    }

    /**
     * @return array<string, ErrorInterface>
     */
    public function dotErrors() : array
    {
        $errors = [];

        foreach ( $this->iterErrors() as $path => $error ) {
            $errors[implode('.', $path)] = $error;
        }

        return $errors;
    }


    /**
     * @return int|string|\BackedEnum
     */
    public function getCode()
    {
        if ( $this->isEmpty() ) {
            return -1;
        }

        if ( $this->isFail() ) {
            return $this->error[0]->code;
        }

        return 0;
    }


    /**
     * @return T
     * @throws AggregateRuntimeException
     */
    public function orThrow($message = null)
    {
        if ( $this->isEmpty() ) {
            throw new LogicException([ 'The `ret` contains neither an error nor a value', $this ]);
        }

        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        $message = $message ?? 'The `orThrow` caused exception';

        $ex = new AggregateRuntimeException([ $this->error[0] ], $message);
        $ex->traceShift(1);

        throw $ex;
    }


    /**
     * @return T|ErrorInterface
     */
    public function orError($message = null, $file = null, $line = null)
    {
        if ( $this->isEmpty() ) {
            throw new LogicException([ 'The `ret` contains neither an error nor a value', $this ]);
        }

        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        $error = $this->error[0];

        if ( null !== $message ) {
            $error = Err::aggregate([ $error ], $file, $line, $message);
        }

        return $error;
    }


    /**
     * @template TTT
     *
     * @param TTT $value
     *
     * @return T|TTT
     */
    public function orValue($value)
    {
        if ( $this->isEmpty() ) {
            throw new LogicException([ 'The `ret` contains neither an error nor a value', $this ]);
        }

        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        return static::$facade->okValue($value);
    }


    /**
     * @return T|null
     */
    public function orNull()
    {
        return $this->orValue(null);
    }


    public static function hasFacade() : bool
    {
        return null !== static::$facade;
    }

    public static function setFacade(?RetFacadeInterface $facade) : ?RetFacadeInterface
    {
        $last = static::$facade;

        static::$facade = $facade;

        return $last;
    }

    /**
     * @var RetFacadeInterface
     */
    protected static $facade;
}
