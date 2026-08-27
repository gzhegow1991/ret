<?php

namespace Gzhegow\Ret\Ret;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Exception\RuntimeException;
use Gzhegow\Ret\Error\TriggeredErrorInterface;
use Gzhegow\Ret\Exception\AggregateRuntimeException;


/**
 * @template T of mixed
 * @template-covariant TT of ErrorInterface
 */
class Ret
{
    /**
     * @var array{ 0?: T }
     */
    protected $value = [];
    /**
     * @var array{ 0?: TT }
     */
    protected $error = [];


    public static function registry() : RetRegistry
    {
        return RetRegistry::new();
    }

    public static function wrapper() : RetWrapper
    {
        return RetWrapper::new();
    }


    public static function wrap($value, ?RetWrapper $wrapper = null) : Ret
    {
        if ( null === $wrapper ) {
            $instance = Ret::ok($value);

        } else {
            $instance = $wrapper->run($value);
        }

        return $instance;
    }


    /**
     * @param T $value
     *
     * @return static<T, null>
     */
    public static function ok($value)
    {
        $instance = new static();
        $instance->value[0] = static::okValue($value);

        return $instance;
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
        $instance = new static();
        $instance->error[0] = static::failError($error, $file, $line);

        return $instance;
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
        return static::passRet($source, $message, $file, $line);
    }

    protected function __construct()
    {
    }


    /**
     * @return $this
     */
    public function track(RetRegistry $registry, $key = null)
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
     * @return TT
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
        $ex->applyTraceShift();

        throw $ex;
    }


    /**
     * @return T|TT
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

        return static::okValue($value);
    }


    /**
     * @return T|null
     */
    public function orNull()
    {
        return $this->orValue(null);
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
            return Ret::pass($ee, [ 'The `fnCall` intercepted warnings' ], $fileLine[0], $fileLine[1]);
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


    protected static function okValue($value)
    {
        if ( $value instanceof static ) {
            throw new LogicException(
                [ 'Unable to `okValue`: attempt to return `Ret` instance as value', $value ]
            );
        }

        return $value;
    }

    protected static function failError($error, $file = null, $line = null)
    {
        if ( $error instanceof static ) {
            throw new LogicException(
                [ 'Unable to `failError`: attempt to return `Ret` instance as error', $error ]
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

        return $e;
    }

    /**
     * @noinspection PhpUnusedLocalVariableInspection
     */
    protected static function passRet($source, $message = null, $file = null, $line = null)
    {
        $isArray = false;
        $isStatic = false;
        $isError = false;
        $isThrowable = false;

        $devnull = false
            || ($isArray = is_array($source))
            || ($isStatic = $source instanceof static)
            || ($isError = $source instanceof ErrorInterface)
            || ($isThrowable = $source instanceof \Throwable);

        $errors = [];

        if ( $isStatic ) {
            if ( $source->isEmpty() ) {
                throw new LogicException(
                    [ 'Unable to `passRet`: attempt to pass empty `Ret`', $source ]
                );
            }

            if ( [] !== $source->value ) {
                return clone $source;
            }

            $errors[] = $source->error[0];

        } elseif ( $isError ) {
            $errors[] = $source;

        } elseif ( $isThrowable ) {
            $errors[] = Err::wrap($source);

        } elseif ( $isArray ) {
            if ( [] === $source ) {
                throw new LogicException(
                    [ 'Unable to `passRet`: The `source` is empty array', $source ]
                );

            } else {
                foreach ( $source as $i => $s ) {
                    if ( $s instanceof ErrorInterface ) {
                        $errors[] = $s;

                    } elseif ( $s instanceof \Throwable ) {
                        $errors[] = Err::wrap($s);

                    } else {
                        throw new LogicException(
                            [ 'Unable to `passRet`: The `source[i]` is unknown', $i, $source ]
                        );
                    }
                }
            }

        } else {
            throw new LogicException(
                [ 'Unable to `passRet`: The `source` is unknown', $source ]
            );
        }

        if ( null !== $message ) {
            $error = Err::aggregate($errors, $file, $line, $message);

        } elseif ( count($errors) > 1 ) {
            $error = Err::aggregate($errors, $file, $line);

        } else {
            $error = $errors[0];
        }

        $instance = new static();
        $instance->error[0] = $error;

        return $instance;
    }
}
