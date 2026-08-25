<?php

namespace Gzhegow\Ret\Core;

use Gzhegow\Ret\Core\Ret\Ret;
use Gzhegow\Ret\Exception\Exception;
use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Core\Error\ErrorInterface;
use Gzhegow\Ret\Exception\TriggeredException;
use Gzhegow\Ret\Exception\AggregateException;
use Gzhegow\Ret\Core\Error\MainErrorInterface;
use Gzhegow\Ret\Core\ErrorMessage\ErrorMessage;
use Gzhegow\Ret\Core\Error\AggregateErrorInterface;
use Gzhegow\Ret\Core\Error\TriggeredErrorInterface;
use Gzhegow\Ret\Exception\AggregateExceptionInterface;
use Gzhegow\Ret\Core\ErrorMessage\ErrorMessageInterface;


abstract class Err
{
    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function new($from, $file = null, $line = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\MainError::make($from, $file, $line)
            : \Gzhegow\Ret\Core\Error\PHP7\MainError::make($from, $file, $line);
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function code($from, $file = null, $line = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\MainError::code($from, $file, $line)
            : \Gzhegow\Ret\Core\Error\PHP7\MainError::code($from, $file, $line);
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function message($from, $file = null, $line = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\MainError::message($from, $file, $line)
            : \Gzhegow\Ret\Core\Error\PHP7\MainError::message($from, $file, $line);
    }


    /**
     * @param ErrorInterface[] $children
     * @param string|null      $file
     * @param int|null         $line
     * @param mixed            $message
     *
     * @return AggregateErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function aggregate(
        $children,
        $file = null, $line = null, $message = null
    )
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\AggregateError::make($children, $file, $line, $message)
            : \Gzhegow\Ret\Core\Error\PHP7\AggregateError::make($children, $file, $line, $message);
    }


    /**
     * @param string      $message
     * @param int         $code
     * @param int         $severity
     * @param string|null $file
     * @param int|null    $line
     *
     * @return TriggeredErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function triggered(
        $severity, $message,
        $file = null, $line = null,
        $code = null
    )
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Core\Error\PHP8\TriggeredError::make($severity, $message, $file, $line, $code)
            : \Gzhegow\Ret\Core\Error\PHP7\TriggeredError::make($severity, $message, $file, $line, $code);
    }


    /**
     * @return ErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public static function wrap(\Throwable $e)
    {
        if ( $e instanceof AggregateExceptionInterface ) {
            $instance = (PHP_VERSION_ID >= 80000)
                ? \Gzhegow\Ret\Core\Error\PHP8\AggregateError::wrap($e)
                : \Gzhegow\Ret\Core\Error\PHP7\AggregateError::wrap($e);

        } elseif ( $e instanceof \ErrorException ) {
            $instance = (PHP_VERSION_ID >= 80000)
                ? \Gzhegow\Ret\Core\Error\PHP8\TriggeredError::wrap($e)
                : \Gzhegow\Ret\Core\Error\PHP7\TriggeredError::wrap($e);

        } else {
            $instance = (PHP_VERSION_ID >= 80000)
                ? \Gzhegow\Ret\Core\Error\PHP8\MainError::wrap($e)
                : \Gzhegow\Ret\Core\Error\PHP7\MainError::wrap($e);
        }

        return $instance;
    }

    /**
     * @return \Throwable
     */
    public static function unwrap(ErrorInterface $err)
    {
        if ( $ex = $err->throwable ) {
            return $ex;
        }

        if ( $err instanceof AggregateErrorInterface ) {
            $instance = new AggregateException($err->errors, Err::getMessage($err));

        } else {
            if ( $err instanceof TriggeredErrorInterface ) {
                $instance = new TriggeredException(
                    $err->severity, $err->message,
                    $err->file, $err->line,
                    $err->code
                );

            } else {
                $instance = new Exception(Err::getMessage($err));
            }
        }

        $instance->traceShift(1);
        $instance->applyTraceShift();

        return $instance;
    }


    /**
     * @param ErrorInterface|Ret|\Throwable $e
     *
     * @return int|string|\BackedEnum
     */
    public static function getCode($e)
    {
        if ( $e instanceof ErrorInterface ) {
            return $e->code ?: -1;

        } elseif ( $e instanceof Ret ) {
            return $e->getCode() ?: -1;

        } elseif ( $e instanceof \Throwable ) {
            return $e->getCode() ?: -1;

        } else {
            throw new \LogicException('The `e` is unknown');
        }
    }

    /**
     * @param ErrorInterface|Ret|\Throwable $e
     * @param mixed                         $code
     */
    public static function isCode($e, $code) : bool
    {
        if ( $e instanceof ErrorInterface ) {
            $leftCode = $e->code ?: -1;

        } elseif ( $e instanceof Ret ) {
            $leftCode = $e->getCode() ?: -1;

        } elseif ( $e instanceof \Throwable ) {
            $leftCode = $e->getCode() ?: -1;

        } else {
            return false;
        }

        $rightCode = $code;

        $leftCode = $leftCode ?? -1;
        $rightCode = $rightCode ?? -1;

        if ( $leftCode === -1 ) return false;
        if ( $rightCode === -1 ) return false;

        return $leftCode === $rightCode;
    }


    /**
     * @param ErrorInterface|\Throwable $e
     */
    public static function getMessage($e) : ErrorMessageInterface
    {
        if ( $e instanceof ErrorInterface ) {
            return ErrorMessage::fromError($e);

        } elseif ( $e instanceof \Throwable ) {
            return ErrorMessage::fromThrowable($e);

        } else {
            throw new \LogicException('The `e` is unknown');
        }
    }


    /**
     * @param ErrorInterface|\Throwable $e
     *
     * @return ErrorInterface[]
     */
    public static function getChildren($e) : array
    {
        if ( $e instanceof AggregateErrorInterface ) {
            return $e->errors;

        } elseif ( $e instanceof AggregateExceptionInterface ) {
            return $e->getErrors();

        } elseif ( $e instanceof \Throwable ) {
            if ( $ex = $e->getPrevious() ) {
                return [ Err::wrap($ex) ];
            }

            return [];
        }

        throw new \LogicException('The `e` is unknown');
    }

    /**
     * @param ErrorInterface|\Throwable $e
     *
     * @return \Generator<array, ErrorInterface[]>
     */
    public static function getChildrenRecursive($e) : iterable
    {
        $children = static::getChildren($e);
        if ( [] === $children ) {
            return;
        }

        $childrenReversed = array_reverse($children, true);

        /**
         * @var ErrorInterface[] $stack
         * @var int[][]          $stackPath
         */
        $stack = $childrenReversed;
        $stackPath = [];

        foreach ( $childrenReversed as $i => $c ) {
            $stackPath[] = [ $i ];
        }

        while ( [] !== $stack ) {
            $current = array_pop($stack);
            $currentPath = array_pop($stackPath);

            yield $currentPath => $current;

            if ( $current instanceof AggregateErrorInterface ) {
                $currentChildrenReversed = array_reverse($current->errors, true);

                foreach ( $currentChildrenReversed as $i => $e ) {
                    $currentFullpath = $currentPath;
                    $currentFullpath[] = $i;

                    $stack[] = $e;
                    $stackPath[] = $currentFullpath;
                }

            } else {
                if ( null !== $current->throwable ) {
                    if ( $ex = $current->throwable->getPrevious() ) {
                        $e = Err::wrap($ex);

                        $currentFullpath = $currentPath;
                        $currentFullpath[] = 0;

                        $stack[] = $e;
                        $stackPath[] = $currentFullpath;
                    }
                }
            }
        }
    }


    /**
     * @param ErrorInterface|\Throwable $e
     */
    public static function toString($e) : string
    {
        $id = 1;

        return static::doToString($e, 0, $id);
    }

    /**
     * @param ErrorInterface|\Throwable $e
     */
    protected static function doToString($e, $level, &$id) : string
    {
        $result = null
            ?? static::doToStringError($e, $level, $id)
            ?? static::doToStringThrowable($e, $level, $id);

        if ( null === $result ) {
            throw new LogicException(
                [ 'The `e` is unknown', $e ]
            );
        }

        return $result;
    }

    /**
     * @param ErrorInterface $e
     */
    protected static function doToStringError($e, $level, &$id) : ?string
    {
        if ( ! ($e instanceof ErrorInterface) ) {
            return null;
        }

        if ( $e->throwable ) {
            return static::doToStringThrowable($e->throwable, $level, $id);
        }

        $class = get_class($e);
        $message = $e->message;
        $file = $e->file;
        $line = $e->line;
        $traceAsString = '#0 {main}';

        $withMessage = ('' === $message) ? '' : " with message '" . $message . "'";

        if ( $level === 0 ) {
            $linePad = '';

        } else {
            $linePad = str_repeat('--', $level) . ' ';
        }

        $result = ""
            . "{$linePad}[E{$id}] `{$class}`{$withMessage} in {$file}:{$line}\n"
            . "{$linePad}Stack trace:\n"
            . "{$linePad}{$traceAsString}";

        if ( $e instanceof AggregateErrorInterface ) {
            $id++;

            $linePad = str_repeat('--', $level + 1) . ' ';

            foreach ( $e->errors as $ee ) {
                $result .= "\n"
                    . "{$linePad}\n"
                    . static::doToStringError($ee, $level + 1, $id);
            }
        }

        return $result;
    }

    /**
     * @param \Throwable $e
     */
    protected static function doToStringThrowable($e, $level, &$id) : ?string
    {
        if ( ! ($e instanceof \Throwable) ) {
            return null;
        }

        $class = get_class($e);
        $message = $e->getMessage();
        $file = $e->getFile();
        $line = $e->getLine();
        $traceAsString = $e->getTraceAsString();

        $withMessage = ('' === $message) ? '' : " with message '" . $message . "'";

        if ( $level === 0 ) {
            $linePad = '';

        } else {
            $linePad = str_repeat('--', $level) . ' ';

            $traceAsString = preg_replace("/\r?\n/", "\n{$linePad}", $traceAsString);
        }

        $result = ""
            . "{$linePad}[E{$id}] `{$class}`{$withMessage} in {$file}:{$line}\n"
            . "{$linePad}Stack trace:\n"
            . "{$linePad}{$traceAsString}";

        if ( $e instanceof AggregateExceptionInterface ) {
            $linePad = str_repeat('--', $level + 1) . ' ';

            foreach ( $e->getErrors() as $ee ) {
                $id++;

                $result .= "\n"
                    . "{$linePad}\n"
                    . static::doToStringError($ee, $level + 1, $id);
            }

        } elseif ( $ePrevious = $e->getPrevious() ) {
            $id++;

            $linePad = str_repeat('--', $level + 1) . ' ';

            $result .= "\n"
                . "{$linePad}\n"
                . static::doToStringThrowable($ePrevious, $level + 1, $id);
        }

        return $result;
    }
}
