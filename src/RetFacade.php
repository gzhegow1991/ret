<?php

namespace Gzhegow\Ret;

use Gzhegow\Ret\Ret\RetBag;
use Gzhegow\Ret\Ret\RetWrapper;
use Gzhegow\Ret\Ret\RetBagInterface;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Ret\RetWrapperInterface;
use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Error\TriggeredErrorInterface;


/**
 * @template T
 * @template-covariant TT of ErrorInterface
 */
class RetFacade implements RetFacadeInterface
{
    public function __construct()
    {
        if ( ! Ret::hasFacade() ) {
            Ret::setFacade($this);
        }
    }


    public function bag() : RetBagInterface
    {
        return RetBag::new();
    }


    public function wrapper() : RetWrapperInterface
    {
        return RetWrapper::new();
    }

    public function wrap($value, ?RetWrapperInterface $wrapper = null) : Ret
    {
        if ( null === $wrapper ) {
            $instance = $this->ok($value);

        } else {
            $instance = $wrapper->run($value);
        }

        return $instance;
    }


    /**
     * @param T $value
     *
     * @return Ret<T, null>
     */
    public function ok($value)
    {
        return Ret::new($this, [ 0 => $this->okValue($value) ]);
    }

    /**
     * @param mixed       $error
     * @param string|null $file
     * @param int|null    $line
     *
     * @return Ret<null, TT>
     */
    public function fail($error, $file = null, $line = null)
    {
        return Ret::new($this, [ 1 => $this->failError($error, $file, $line) ]);
    }

    /**
     * @param Ret<T,TT>|ErrorInterface|\Throwable $source
     * @param mixed                               $message
     * @param string|null                         $file
     * @param int|null                            $line
     *
     * @return Ret<T, TT>
     */
    public function pass($source, $message = null, $file = null, $line = null)
    {
        return $this->passRet($source, $message, $file, $line);
    }


    /**
     * @return mixed
     */
    public function okValue($value)
    {
        if ( $value instanceof static ) {
            throw new LogicException(
                [ 'Unable to `okValue`: attempt to return `Ret` instance as value', $value ]
            );
        }

        return $value;
    }

    /**
     * @return ErrorInterface
     */
    public function failError($error, $file = null, $line = null)
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
     * @return Ret
     * @noinspection PhpUnusedLocalVariableInspection
     */
    public function passRet($source, $message = null, $file = null, $line = null)
    {
        $isArray = false;
        $isStatic = false;
        $isError = false;
        $isThrowable = false;

        $devnull = false
            || ($isArray = is_array($source))
            || ($isStatic = ($source instanceof Ret))
            || ($isError = ($source instanceof ErrorInterface))
            || ($isThrowable = ($source instanceof \Throwable));

        $errors = [];

        if ( $isStatic ) {
            if ( $source->isEmpty() ) {
                throw new LogicException(
                    [ 'Unable to `passRet`: attempt to pass empty `Ret`', $source ]
                );
            }

            if ( [] !== $source->isOk() ) {
                return clone $source;
            }

            $errors[] = $source->getError();

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

        $instance = Ret::new($this, [ 1 => $error ]);

        return $instance;
    }


    /**
     * @param callable $fn
     *
     * @return \Closure(array $fnArgs, ?array $fileLine) : Ret<T>
     */
    public function fn($fn, ?RetWrapperInterface $wrapper = null) : \Closure
    {
        return function (array $fnArgs = [], ?array $fileLine = null) use ($fn, $wrapper) {
            if ( null === $fileLine ) {
                $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

                $fileLine = [ $trace[0]['file'], $trace[0]['line'] ];
            }

            return $this->fnCall($fn, $fnArgs, $fileLine, $wrapper);
        };
    }

    /**
     * @param callable $fn
     *
     * @return Ret<T>
     */
    public function fnCall($fn, array $fnArgs = [], ?array $fileLine = null, ?RetWrapperInterface $wrapper = null)
    {
        if ( null === $fileLine ) {
            $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);

            $fileLine = [ $trace[0]['file'], $trace[0]['line'] ];
        }

        $errorReportingBefore = error_reporting($errorReportingNow = E_ALL);

        $this->fnCall__set_error_handler__ctx = [ [], $fileLine ];
        set_error_handler(
            [ $this, 'fnCall__set_error_handler' ],
            $errorReportingNow
        );

        $e = null;
        try {
            $result = call_user_func_array($fn, $fnArgs);
        }
        catch ( \Throwable $e ) {
        }
        finally {
            $ee = $this->fnCall__set_error_handler__ctx[0];
            $this->fnCall__set_error_handler__ctx = [];

            restore_error_handler();

            error_reporting($errorReportingBefore);
        }

        if ( null !== $e ) {
            return $this->pass($e, [ 'The `fnCall` caught the exception' ], $fileLine[0], $fileLine[1]);
        }

        if ( [] !== $ee ) {
            return $this->pass($ee, [ 'The `fnCall` intercepted warnings' ], $fileLine[0], $fileLine[1]);
        }

        if ( null !== $wrapper ) {
            $ret = $wrapper->run($result, $fileLine[0], $fileLine[1]);

            return $this->pass($ret);
        }

        return $this->ok($result);
    }

    protected function fnCall__set_error_handler($errno, $errstr, $errfile, $errline)
    {
        $fileLine = $this->fnCall__set_error_handler__ctx[1] ?? [ $errfile, $errline ];

        $err = Err::triggered($errno, $errstr, $fileLine[0], $fileLine[1]);

        $this->fnCall__set_error_handler__ctx[0][] = $err;

        return true;
    }

    /**
     * @var array{ 0: TriggeredErrorInterface[], 1: array }
     */
    protected $fnCall__set_error_handler__ctx = [ [], null ];
}
