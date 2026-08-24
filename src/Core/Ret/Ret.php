<?php

namespace Gzhegow\Ret\Core\Ret;

use Gzhegow\Ret\Core\Err;
use Gzhegow\Ret\Core\ErrorBag\ErrorBag;
use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Core\Error\ErrorInterface;
use Gzhegow\Ret\Exception\RuntimeException;
use Gzhegow\Ret\Core\ErrorBag\ErrorBagInterface;
use Gzhegow\Ret\Core\Error\TriggeredErrorInterface;
use Gzhegow\Ret\Exception\AggregateRuntimeException;


/**
 * @template T of mixed
 */
class Ret
{
    /**
     * @var array{ 0?: T }
     */
    protected $value = [];
    /**
     * @var ErrorInterface[]
     */
    protected $errors = [];


    /**
     * @return static
     */
    public static function new()
    {
        return new static();
    }


    public static function wrapper() : RetWrapper
    {
        return new RetWrapper();
    }

    public static function wrap($value, ?RetWrapper $wrapper = null) : Ret
    {
        if ( null === $wrapper ) {
            $instance = new static();
            $instance->value[0] = $value;

        } else {
            $instance = $wrapper->run($value);
        }

        return $instance;
    }


    /**
     * @param T $value
     *
     * @return static<T>
     */
    public static function ok($value)
    {
        if ( $value instanceof static ) {
            throw new LogicException(
                [ 'Unable to `ok`: attempt to return `Ret` instance as value', $value ]
            );
        }

        $instance = new static();
        $instance->value[0] = $value;

        return $instance;
    }

    /**
     * @param mixed       $error
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static<T>
     */
    public static function fail($error, $file = null, $line = null)
    {
        if ( $error instanceof static ) {
            throw new LogicException(
                [ 'Unable to `fail`: attempt to return `Ret` instance as error', $error ]
            );
        }

        if ( $error instanceof ErrorInterface ) {
            $e = $error;

            if ( null !== $file ) {
                $e = Err::aggregate([ $e ], $file, $line, '[FAIL]');
            }

        } elseif ( $error instanceof \Throwable ) {
            $e = Err::wrap($error);

            if ( null !== $file ) {
                $e = Err::aggregate([ $e ], $file, $line, '[FAIL]');
            }

        } else {
            $e = Err::new($error, $file, $line);
        }

        $instance = new static();
        $instance->errors = [ $e ];

        return $instance;
    }

    /**
     * @param static<T>|ErrorBagInterface|ErrorInterface|\Throwable $source
     * @param mixed                                                 $message
     * @param string|null                                           $file
     * @param int|null                                              $line
     *
     * @return static<T>
     */
    public static function pass($source, $message = null, $file = null, $line = null)
    {
        if ( $isStatic = ($source instanceof static) ) {
            if ( [] !== $source->value ) {
                $instance = new static();
                $instance->value = $source->value;

                return $instance;
            }
        }

        if ( $isStatic ) {
            $errors = $source->errors;

        } elseif ( $source instanceof ErrorBagInterface ) {
            $errors = $source->getErrors();

        } elseif ( $source instanceof ErrorInterface ) {
            $errors = [ $source ];

        } elseif ( $source instanceof \Throwable ) {
            $errors = [ Err::wrap($source) ];

        } else {
            throw new LogicException(
                [ 'The `source` is unknown', $source ]
            );
        }

        if ( null !== $message ) {
            $errors = [ Err::aggregate($errors, $file, $line, $message) ];
        }

        $instance = new static();
        $instance->errors = $errors;

        return $instance;
    }


    /**
     * @param array{ 0: mixed } $refs
     */
    public function isOk(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refValue =& $refs[0];
        $refValue = null;

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
        if ( array_key_exists(0, $refs) ) $refErrors =& $refs[0];
        $refErrors = null;

        if ( [] === $this->errors ) {
            return false;
        }

        $refErrors = $this->errors;

        return true;
    }


    /**
     * @return T
     */
    public function getResult()
    {
        if ( [] === $this->value ) {
            throw new RuntimeException('The `ret` contains no value');
        }

        return $this->value[0];
    }

    /**
     * @return ErrorInterface[]
     */
    public function getErrors() : array
    {
        if ( [] === $this->errors ) {
            throw new RuntimeException('The `ret` contains no errors');
        }

        return $this->errors;
    }

    /**
     * @return \Generator<array, \Gzhegow\Ret\Core\Error\ErrorInterface[]>
     */
    public function getErrorsRecursive() : iterable
    {
        if ( [] === $this->errors ) {
            throw new RuntimeException('The `ret` contains no errors');
        }

        $agg = Err::aggregate($this->errors);

        return Err::getChildrenRecursive($agg);
    }


    /**
     * @return T|null
     * @throws AggregateRuntimeException
     */
    public function orThrow($message = null)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( [] !== $this->errors ) {
            $message = $message ?? 'The `orThrow` caused exception';

            $ex = new AggregateRuntimeException($this->errors, $message);
            $ex->traceShift(1);
            $ex->applyTraceShift();

            throw $ex;
        }

        return null;
    }

    /**
     * @return T|\Gzhegow\Ret\Core\Error\AggregateErrorInterface
     * @throws RuntimeException
     */
    public function orError($message = null, $file = null, $line = null)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( [] !== $this->errors ) {
            $message = $message ?? 'The `orError` caused error';

            $err = Err::aggregate($this->errors, $file, $line, $message);

            return $err;
        }

        throw new RuntimeException([ 'The `ret` contains neither an error nor a value', $this ]);
    }


    /**
     * @template TT
     * @template TTT
     *
     * @param TT  $fallback
     * @param TTT $default
     *
     * @return T|TT|TTT
     */
    public function orFallback($fallback, $default = null, ?self $mergeTo = null)
    {
        if ( [] !== $this->value ) {
            if ( null !== $mergeTo ) {
                $mergeTo->value = [ $this->value[0] ];
                $mergeTo->errors = [];
            }

            return $this->value[0];
        }

        if ( [] !== $this->errors ) {
            if ( null !== $mergeTo ) {
                $mergeTo->value = [];
                $mergeTo->errors = array_merge($mergeTo->errors, $this->errors);
            }

            return $fallback;
        }

        return $default;
    }

    /**
     * @return T|null
     */
    public function orNull(?self $mergeTo = null)
    {
        return $this->orFallback(null, null, $mergeTo);
    }


    /**
     * @param static<T> $ret
     *
     * @return static<T>
     */
    public function tryAny(self $ret)
    {
        if ( [] !== $this->value ) {
            return $this;
        }

        if ( [] !== $ret->value ) {
            $this->value = $ret->value;
            $this->errors = [];

            return $this;
        }

        if ( [] !== $ret->errors ) {
            $this->value = [];
            $this->errors = array_merge($this->errors, $ret->errors);
        }

        return $this;
    }

    /**
     * @param static<T> $ret
     *
     * @return static<T>
     */
    public function tryAllFirst(self $ret)
    {
        if ( [] !== $ret->errors ) {
            $this->value = [];
            $this->errors = array_merge($this->errors, $ret->errors);

            return $this;
        }

        return $this;
    }

    /**
     * @param static<T> $ret
     *
     * @return static<T>
     */
    public function tryAllLast(self $ret)
    {
        if ( [] !== $ret->errors ) {
            $this->value = [];
            $this->errors = array_merge($this->errors, $ret->errors);

            return $this;
        }

        if ( [] !== $this->errors ) {
            return $this;
        }

        if ( [] !== $ret->value ) {
            $this->value = $ret->value;
        }

        return $this;
    }


    /**
     * @param static<T>[] $rets
     *
     * @return static<T>
     */
    public static function any(array $rets = [])
    {
        $instance = new static();

        $value = null;
        $errors = [];

        foreach ( $rets as $ret ) {
            if ( [] !== $ret->errors ) {
                $errors = array_merge($errors, $ret->errors);
            }

            if ( [] !== $ret->value ) {
                $value = $ret->value;

                break;
            }
        }

        if ( null !== $value ) {
            $instance->value = $value;

        } else {
            $instance->errors = $errors;
        }

        return $instance;
    }

    /**
     * @param static<T>[] $rets
     *
     * @return static<array<int, T>>
     */
    public static function all(array $rets)
    {
        $values = [];
        $errors = [];

        foreach ( $rets as $i => $ret ) {
            if ( [] !== $ret->errors ) {
                $errors = array_merge($errors, $ret->errors);
            }

            if ( [] !== $ret->value ) {
                $values[$i] = $ret->value[0];
            }
        }

        $instance = new static();

        if ( [] === $errors ) {
            $instance->value[0] = $values;

        } else {
            $instance->errors = $errors;
        }

        return $instance;
    }

    /**
     * @param static<T>[] $rets
     *
     * @return static<array{ values: T[], errors: ErrorInterface[] }>
     */
    public static function some(array $rets)
    {
        $values = [];
        $errors = [];

        foreach ( $rets as $i => $ret ) {
            if ( [] !== $ret->errors ) {
                $errors = array_merge($errors, $ret->errors);
            }

            if ( [] !== $ret->value ) {
                $values[$i] = $ret->value[0];
            }
        }

        $instance = new static();
        $instance->value[0] = [
            'errors' => $errors,
            'values' => $values,
        ];

        return $instance;
    }


    /**
     * @param callable $fn
     *
     * @return \Closure(array $fnArgs, ?array $fileLine) : Ret<T>
     */
    public static function fn($fn, ?RetWrapper $wrapper = null) : \Closure
    {
        return static function (array $fnArgs = [], ?array $fileLine = null) use ($fn, $wrapper) {
            if ( null === $fileLine ) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

                $fileLine = [ $trace[0]['file'], $trace[0]['line'] ];
            }

            return static::fnCall($fn, $fnArgs, $wrapper, $fileLine);
        };
    }

    /**
     * @param callable $fn
     *
     * @return Ret<T>
     */
    public static function fnCall($fn, array $fnArgs = [], ?RetWrapper $wrapper = null, ?array $fileLine = null)
    {
        if ( null === $fileLine ) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

            $fileLine = [ $trace[0]['file'], $trace[0]['line'] ];
        }

        $errorReportingBefore = error_reporting($errorReportingNow = E_ALL);

        static::$call__set_error_handler__ctx = [ [], $fileLine ];
        set_error_handler(
            [ static::class, 'call__set_error_handler' ],
            $errorReportingNow
        );

        $e = null;
        try {
            $result = call_user_func_array($fn, $fnArgs);
        }
        catch ( \Throwable $e ) {
        }
        finally {
            $ee = static::$call__set_error_handler__ctx[0];
            static::$call__set_error_handler__ctx = [];

            restore_error_handler();

            error_reporting($errorReportingBefore);
        }

        if ( null !== $e ) {
            return Ret::pass($e, [ 'The `fnCall` caught the exception' ], $fileLine[0], $fileLine[1]);
        }

        if ( [] !== $ee ) {
            $bag = ErrorBag::collect($ee);

            return Ret::pass($bag, [ 'The `fnCall` intercepted warnings' ], $fileLine[0], $fileLine[1]);
        }

        if ( null !== $wrapper ) {
            $result = $wrapper->run($result);

            return Ret::pass($result);
        }

        return Ret::ok($result);
    }

    /**
     * @var array{ 0: TriggeredErrorInterface[], 1: array }
     */
    protected static $call__set_error_handler__ctx = [ [], null ];

    protected static function call__set_error_handler($errno, $errstr, $errfile, $errline)
    {
        $fileLine = static::$call__set_error_handler__ctx[1] ?? [ $errfile, $errline ];

        $err = Err::triggered($errno, $errstr, $fileLine[0], $fileLine[1]);

        static::$call__set_error_handler__ctx[0][] = $err;

        return true;
    }
}
