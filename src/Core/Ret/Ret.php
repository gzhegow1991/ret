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
        $instance = new static();
        $instance->value[0] = static::okValue($value);

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
        $instance = new static();
        $instance->errors = [ static::failError($error, $file, $line) ];

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

    protected function __construct()
    {
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
    public function getValue()
    {
        if ( [] === $this->value ) {
            throw new RuntimeException('The `ret` contains no value');
        }

        return $this->value[0];
    }

    /**
     * @return int|string|\BackedEnum
     */
    public function getCode()
    {
        if ( [] !== $this->value ) {
            return 0;
        }

        if ( [] !== $this->errors ) {
            if ( ! isset($this->errors[1]) ) {
                return $this->errors[0]->code;
            }
        }

        return -1;
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
    public function orFallback($fallback, $default = null)
    {
        if ( [] !== $this->value ) {
            return $this->value[0];
        }

        if ( [] !== $this->errors ) {
            return $fallback;
        }

        return $default;
    }

    /**
     * @return T|null
     */
    public function orNull()
    {
        return $this->orFallback(null, null);
    }


    /**
     * @return static
     */
    public function tryInto(self $ret)
    {
        if ( [] !== $ret->errors ) {
            return $this;
        }

        if ( [] !== $this->errors ) {
            $ret->value = [];
            $ret->errors = array_merge($ret->errors, $this->errors);

            return $this;
        }

        if ( [] !== $this->value ) {
            return $this;
        }

        if ( [] !== $this->value ) {
            $ret->value = $this->value;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function putInto(self $ret)
    {
        if ( [] !== $ret->errors ) {
            return $this;
        }

        if ( [] !== $this->errors ) {
            $ret->value = [];
            $ret->errors = array_merge($ret->errors, $this->errors);

            return $this;
        }

        if ( [] !== $this->value ) {
            $ret->value = $this->value;
        }

        return $this;
    }

    /**
     * @return static
     */
    public function fillInto(self $ret)
    {
        if ( [] !== $ret->value ) {
            return $this;
        }

        if ( [] !== $this->value ) {
            $ret->value = $this->value;
            $ret->errors = [];
        }

        if ( [] !== $this->errors ) {
            $ret->errors = array_merge($ret->errors, $this->errors);
        }

        return $this;
    }

    /**
     * @return static
     */
    public function pushInto(self $ret)
    {
        if ( [] !== $this->value ) {
            $ret->value = $this->value;
            $ret->errors = [];
        }

        if ( [] !== $this->errors ) {
            $ret->errors = array_merge($ret->errors, $this->errors);
        }

        return $this;
    }


    /**
     * > BOOL NOT (IF FALSE OR NULL)
     *
     * @return static
     */
    public function resolve($value = null)
    {
        $clone = clone $this;

        $thisSuccess = ([] !== $clone->value);

        if ( $thisSuccess ) {
            return $clone;
        }

        $clone->value = [ static::okValue($value) ];
        $clone->errors = [];

        return $clone;
    }

    /**
     * > BOOL NOT (IF TRUE OR NULL)
     *
     * @return static
     */
    public function reject($error, $file = null, $line = null)
    {
        $clone = clone $this;

        $thisFail = ([] !== $clone->errors);

        if ( $thisFail ) {
            return $clone;
        }

        $clone->value = [];
        $clone->errors = [ static::failError($error, $file, $line) ];

        return $clone;
    }


    /**
     * > BOOL OR (IF A OR B THEN SUCCESS ELSE FAIL)
     *
     * @return static
     */
    public function or(self $ret)
    {
        $clone = clone $this;

        $thisSuccess = ([] !== $clone->value);

        if ( $thisSuccess ) {
            return $clone;
        }

        $retSuccess = ([] !== $ret->value);

        if ( $retSuccess ) {
            $clone->value = $ret->value;
            $clone->errors = [];

            return $clone;
        }

        $retFail = ([] !== $ret->errors);

        if ( $retFail ) {
            $clone->value = [];
            $clone->errors = array_merge($clone->errors, $ret->errors);
        }

        return $clone;
    }


    /**
     *  > BOOL AND (IF A AND B THEN SUCCESS A ELSE FAIL)
     *
     * @return static
     */
    public function andKeep(self $ret)
    {
        $clone = clone $this;

        $thisFail = ([] !== $clone->errors);

        if ( $thisFail ) {
            return $clone;
        }

        $retFail = ([] !== $ret->errors);

        if ( $retFail ) {
            $clone->value = [];
            $clone->errors = $ret->errors;

            return $clone;
        }

        $thisSuccess = ([] !== $clone->value);

        if ( $thisSuccess ) {
            return $clone;
        }

        $retSuccess = ([] !== $ret->value);

        if ( $retSuccess ) {
            $clone->value = $ret->value;
        }

        return $clone;
    }

    /**
     * > BOOL AND (IF A AND B THEN SUCCESS B ELSE FAIL)
     *
     * @return static
     */
    public function andLast(self $ret)
    {
        $clone = clone $this;

        $thisFail = ([] !== $clone->errors);

        if ( $thisFail ) {
            return $clone;
        }

        $retFail = ([] !== $ret->errors);

        if ( $retFail ) {
            $clone->value = [];
            $clone->errors = $ret->errors;

            return $clone;
        }

        $retSuccess = ([] !== $ret->value);

        if ( $retSuccess ) {
            $clone->value = $ret->value;
        }

        return $clone;
    }


    /**
     * > BOOL XOR (IF A !== B THEN SUCCESS ELSE FAIL)
     *
     * @return static
     */
    public function xor(self $ret, $error, $file = null, $line = null)
    {
        $clone = clone $this;

        $thisSuccess = ([] !== $clone->value);
        $retSuccess = ([] !== $ret->value);

        if ( false ) {
            //

        } elseif ( $thisSuccess && $retSuccess ) {
            $clone->value = [];
            $clone->errors = [ static::failError($error, $file, $line) ];

        } elseif ( $thisSuccess ) {
            // $clone->value = $clone->value;
            $clone->errors = [];

        } elseif ( $retSuccess ) {
            $clone->value = $ret->value;
            $clone->errors = [];

        } else {
            $clone->value = [];
            $clone->errors = array_merge($clone->errors, $ret->errors);
        }

        return $clone;
    }


    /**
     * > BOOL XNOR (IF A === B THEN SUCCESS ELSE FAIL)
     *
     * @return static
     */
    public function nxorKeep(self $ret)
    {
        $clone = clone $this;

        $thisSuccess = ([] !== $clone->value);
        $retSuccess = ([] !== $ret->value);

        if ( false ) {
            //

        } elseif ( $thisSuccess && $retSuccess ) {
            // $clone->value = $clone->value;
            $clone->errors = [];

        } elseif ( $thisSuccess ) {
            $clone->value = [];
            $clone->errors = $ret->errors;

        } elseif ( $retSuccess ) {
            $clone->value = [];
            // $clone->errors = $clone->errors;

        } else {
            $clone->value = [ null ];
            $clone->errors = [];
        }

        return $clone;
    }

    /**
     * > BOOL XNOR (IF A === B THEN SUCCESS ELSE FAIL)
     *
     * @return static
     */
    public function nxorLast(self $ret)
    {
        $clone = clone $this;

        $thisSuccess = ([] !== $clone->value);
        $retSuccess = ([] !== $ret->value);

        if ( false ) {
            //

        } elseif ( $thisSuccess && $retSuccess ) {
            $clone->value = $ret->value;
            $clone->errors = [];

        } elseif ( $thisSuccess ) {
            $clone->value = [];
            $clone->errors = $ret->errors;

        } elseif ( $retSuccess ) {
            $clone->value = [];
            // $clone->errors = $clone->errors;

        } else {
            $clone->value = [ null ];
            $clone->errors = [];
        }

        return $clone;
    }


    /**
     * > BOOL IMPLICATION (IF A AND !B THEN FAIL ELSE SUCCESS A)
     *
     * @return static
     */
    public function needsKeep(self $ret)
    {
        $clone = clone $this;

        $thisSuccess = ([] !== $clone->value);
        $retSuccess = ([] !== $ret->value);

        if ( false ) {
            //

        } elseif ( $thisSuccess && $retSuccess ) {
            // $clone->value = $clone->value;
            $clone->errors = [];

        } elseif ( $thisSuccess ) {
            $clone->value = [];
            $clone->errors = $ret->errors;

        } elseif ( $retSuccess ) {
            $clone->value = $ret->value;
            $clone->errors = [];

        } else {
            $clone->value = [ null ];
            $clone->errors = [];
        }

        return $clone;
    }

    /**
     * > BOOL IMPLICATION (IF A AND !B THEN FAIL ELSE SUCCESS B)
     *
     * @return static
     */
    public function needsLast(self $ret)
    {
        $clone = clone $this;

        $thisSuccess = ([] !== $clone->value);
        $retSuccess = ([] !== $ret->value);

        if ( false ) {
            //

        } elseif ( $thisSuccess && $retSuccess ) {
            $clone->value = $ret->value;
            $clone->errors = [];

        } elseif ( $thisSuccess ) {
            $clone->value = [];
            $clone->errors = $ret->errors;

        } elseif ( $retSuccess ) {
            $clone->value = $ret->value;
            $clone->errors = [];

        } else {
            $clone->value = [ null ];
            $clone->errors = [];
        }

        return $clone;
    }


    /**
     * @param static<T>[] $rets
     *
     * @return static<T>
     */
    public static function any(array $rets = [])
    {
        $value = null;
        $errors = [];

        foreach ( $rets as $ret ) {
            if ( $ret->isFail() ) {
                $errors = array_merge($errors, $ret->errors);

            } elseif ( $ret->isOk() ) {
                $value = $ret->value[0];
                $errors = [];

                break;
            }
        }

        $instance = new static();

        if ( [] !== $errors ) {
            $instance->errors = $errors;

            return $instance;
        }

        $instance->value[0] = $value;

        return $instance;
    }

    /**
     * @param static<T>[] $rets
     *
     * @return static<T[]>|static
     */
    public static function all(array $rets)
    {
        $errors = [];
        $values = [];

        foreach ( $rets as $i => $ret ) {
            if ( $ret->isFail() ) {
                $errors = array_merge($errors, $ret->errors);

            } elseif ( $ret->isOk() ) {
                $values[$i] = $ret->value[0];
            }
        }

        $instance = new static();

        if ( [] !== $errors ) {
            $instance->errors = $errors;

            return $instance;
        }

        $instance->value[0] = $values;

        return $instance;
    }

    /**
     * @param static<T>[] $rets
     *
     * @return array{ 0: static, 1: static<T[]> }
     */
    public static function both(array $rets) : array
    {
        $errors = [];
        $values = [];

        foreach ( $rets as $i => $ret ) {
            if ( $ret->isFail() ) {
                $errors = array_merge($errors, $ret->errors);

            } elseif ( $ret->isOk() ) {
                $values[$i] = $ret->value[0];
            }
        }

        $retErrors = new static();
        $retValues = new static();

        if ( [] !== $errors ) {
            $retErrors->errors = $errors;
        }

        if ( [] !== $values ) {
            $retValues->value[0] = $values;
        }

        return [ $retErrors, $retValues ];
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


    /**
     * @return mixed
     */
    protected static function okValue($value)
    {
        if ( $value instanceof static ) {
            throw new LogicException(
                [ 'Unable to `ok`: attempt to return `Ret` instance as value', $value ]
            );
        }

        return $value;
    }

    /**
     * @return ErrorInterface
     */
    protected static function failError($error, $file = null, $line = null)
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

        return $e;
    }
}
