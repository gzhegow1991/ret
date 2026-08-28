<?php

namespace Gzhegow\Ret;

use Gzhegow\Ret\ErrorBag\ErrorBag;
use Gzhegow\Ret\Exception\Exception;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Error\MainErrorInterface;
use Gzhegow\Ret\ErrorMessage\ErrorMessage;
use Gzhegow\Ret\ErrorBag\ErrorBagInterface;
use Gzhegow\Ret\Exception\TriggeredException;
use Gzhegow\Ret\Exception\AggregateException;
use Gzhegow\Ret\Error\AggregateErrorInterface;
use Gzhegow\Ret\Error\TriggeredErrorInterface;
use Gzhegow\Ret\ErrorMessage\ErrorMessageInterface;
use Gzhegow\Ret\Exception\AggregateExceptionInterface;


class ErrFacade implements ErrFacadeInterface
{
    public function __construct()
    {
        if ( ! Err::hasFacade() ) {
            Err::setFacade($this);
        }
    }


    /**
     * @var bool
     */
    public $isDebug = false;

    public function isDebug() : bool
    {
        return $this->isDebug();
    }

    /**
     * @return static
     */
    public function debug(bool $enable)
    {
        $this->isDebug = $enable;

        return $this;
    }


    public function bag() : ErrorBagInterface
    {
        return ErrorBag::new();
    }


    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function new($from, $file = null, $line = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Error\PHP8\MainError::make($from, $file, $line)
            : \Gzhegow\Ret\Error\PHP7\MainError::make($from, $file, $line);
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function code($from, $file = null, $line = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Error\PHP8\MainError::code($from, $file, $line)
            : \Gzhegow\Ret\Error\PHP7\MainError::code($from, $file, $line);
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return MainErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function message($from, $file = null, $line = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Error\PHP8\MainError::message($from, $file, $line)
            : \Gzhegow\Ret\Error\PHP7\MainError::message($from, $file, $line);
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
    public function aggregate(
        $children,
        $file = null, $line = null, $message = null
    )
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Error\PHP8\AggregateError::make($children, $file, $line, $message)
            : \Gzhegow\Ret\Error\PHP7\AggregateError::make($children, $file, $line, $message);
    }


    /**
     * @param int         $severity
     * @param string      $message
     * @param string|null $file
     * @param int|null    $line
     * @param array|null  $payload
     * @param int|null    $code
     *
     * @return TriggeredErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function triggered(
        $severity, $message,
        $file = null, $line = null,
        $payload = null, $code = null
    )
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\Error\PHP8\TriggeredError::make($severity, $message, $file, $line, $payload, $code)
            : \Gzhegow\Ret\Error\PHP7\TriggeredError::make($severity, $message, $file, $line, $payload, $code);
    }


    /**
     * @param ErrorInterface           $error
     * @param array<string, bool>|null $tags
     *
     * @return \Gzhegow\Ret\ErrorBag\TaggedErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function tagged($error, $tags = null)
    {
        return (PHP_VERSION_ID >= 80000)
            ? \Gzhegow\Ret\ErrorBag\PHP8\TaggedError::make($error, $tags)
            : \Gzhegow\Ret\ErrorBag\PHP7\TaggedError::make($error, $tags);
    }


    /**
     * @return ErrorInterface
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function wrap(\Throwable $e)
    {
        if ( $e instanceof AggregateExceptionInterface ) {
            $instance = (PHP_VERSION_ID >= 80000)
                ? \Gzhegow\Ret\Error\PHP8\AggregateError::wrap($e)
                : \Gzhegow\Ret\Error\PHP7\AggregateError::wrap($e);

        } elseif ( $e instanceof \ErrorException ) {
            $instance = (PHP_VERSION_ID >= 80000)
                ? \Gzhegow\Ret\Error\PHP8\TriggeredError::wrap($e)
                : \Gzhegow\Ret\Error\PHP7\TriggeredError::wrap($e);

        } else {
            $instance = (PHP_VERSION_ID >= 80000)
                ? \Gzhegow\Ret\Error\PHP8\MainError::wrap($e)
                : \Gzhegow\Ret\Error\PHP7\MainError::wrap($e);
        }

        return $instance;
    }

    /**
     * @return \Throwable
     */
    public function unwrap(ErrorInterface $err)
    {
        if ( $ex = $err->throwable ) {
            return $ex;
        }

        if ( $err instanceof AggregateErrorInterface ) {
            $instance = new AggregateException(
                $err->errors,
                ErrorMessage::fromError($err)
            );

        } else {
            // } elseif ($err instanceof SingleErrorInterface) {

            if ( $err instanceof TriggeredErrorInterface ) {
                $instance = new TriggeredException(
                    $err->severity, $err->message,
                    $err->file, $err->line,
                    $err->payload, $err->code
                );

            } else {
                $instance = new Exception(
                    ErrorMessage::fromError($err)
                );
            }
        }

        // > Err::unwrap()
        // > $this->unwrap()
        $instance->traceShift(2);

        return $instance;
    }


    /**
     * @param ErrorInterface|RetInterface|\Throwable $e
     *
     * @return int|string|\BackedEnum
     */
    public function getCode($e)
    {
        if ( $e instanceof ErrorInterface ) {
            return $e->code ?: -1;

        } elseif ( $e instanceof RetInterface ) {
            return $e->getCode() ?: -1;

        } elseif ( $e instanceof \Throwable ) {
            return $e->getCode() ?: -1;

        } else {
            throw new \LogicException('The `e` is unknown');
        }
    }

    /**
     * @param ErrorInterface|RetInterface|\Throwable $e
     * @param mixed                                  $code
     */
    public function isCode($e, $code) : bool
    {
        if ( $e instanceof ErrorInterface ) {
            $leftCode = $e->code ?: -1;

        } elseif ( $e instanceof RetInterface ) {
            $leftCode = $e->getCode() ?: -1;

        } elseif ( $e instanceof \Throwable ) {
            $leftCode = $e->getCode() ?: -1;

        } else {
            return false;
        }

        $rightCode = $code;

        $leftCode = $leftCode ?: -1;
        $rightCode = $rightCode ?: -1;

        if ( $leftCode === -1 ) return false;
        if ( $rightCode === -1 ) return false;

        return $leftCode === $rightCode;
    }


    /**
     * @param ErrorInterface|\Throwable $e
     */
    public function getMessage($e) : ErrorMessageInterface
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
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return ErrorInterface[]
     */
    public function getErrors($e) : array
    {
        if ( $e instanceof ErrorInterface ) {
            return [ $e ];

        } elseif ( $e instanceof \Throwable ) {
            return [ Err::wrap($e) ];

        } elseif ( $e instanceof ErrorBagInterface ) {
            return $e->getErrors();
        }

        throw new \LogicException('The `e` is unknown');
    }

    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return \Generator<array, ErrorInterface>
     */
    public function iterErrors($e) : iterable
    {
        $parents = $this->getErrors($e);
        if ( [] === $parents ) {
            return;
        }

        $parentsReversed = array_reverse($parents, true);

        /**
         * @var ErrorInterface[] $stack
         * @var int[][]          $stackPath
         */
        $stack = $parentsReversed;
        $stackPath = [];

        foreach ( $parentsReversed as $i => $c ) {
            $stackPath[] = [ $i ];
        }

        while ( [] !== $stack ) {
            $current = array_pop($stack);
            $currentPath = array_pop($stackPath);

            yield $currentPath => $current;

            if ( $current instanceof AggregateErrorInterface ) {
                $currentChildrenReversed = array_reverse($current->errors, true);

                foreach ( $currentChildrenReversed as $i => $child ) {
                    $currentFullpath = $currentPath;
                    $currentFullpath[] = $i;

                    $stack[] = $child;
                    $stackPath[] = $currentFullpath;
                }

            } else {
                if ( null !== $current->throwable ) {
                    if ( $ex = $current->throwable->getPrevious() ) {
                        $child = Err::wrap($ex);

                        $currentFullpath = $currentPath;
                        $currentFullpath[] = 0;

                        $stack[] = $child;
                        $stackPath[] = $currentFullpath;
                    }
                }
            }
        }
    }

    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return array<string, ErrorInterface>
     */
    public function dotErrors($e) : array
    {
        $errors = [];

        foreach ( $this->iterErrors($e) as $path => $error ) {
            $errors[implode('.', $path)] = $error;
        }

        return $errors;
    }


    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return \Gzhegow\Ret\Error\SingleErrorInterface[]
     */
    public function getErrorChildren($e) : array
    {
        if ( $e instanceof AggregateErrorInterface ) {
            return $e->errors;

        } elseif ( $e instanceof \Throwable ) {
            if ( $e instanceof AggregateExceptionInterface ) {
                return $e->getErrors();

            } elseif ( $ex = $e->getPrevious() ) {
                return [ Err::wrap($ex) ];
            }

            return [];

        } elseif ( $e instanceof ErrorBag ) {
            return $e->getErrors();
        }

        throw new \LogicException('The `e` is unknown');
    }

    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return \Generator<array, \Gzhegow\Ret\Error\SingleErrorInterface>
     */
    public function iterErrorChildren($e) : iterable
    {
        $children = $this->getErrorChildren($e);
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

            if ( $current instanceof AggregateErrorInterface ) {
                $currentChildrenReversed = array_reverse($current->errors, true);

                foreach ( $currentChildrenReversed as $i => $child ) {
                    $currentFullpath = $currentPath;
                    $currentFullpath[] = $i;

                    $stack[] = $child;
                    $stackPath[] = $currentFullpath;
                }

            } else {
                if ( null === $current->throwable ) {
                    yield $currentPath => $current;

                } else {
                    if ( null === ($ex = $current->throwable->getPrevious()) ) {
                        yield $currentPath => $current;

                    } else {
                        $child = Err::wrap($ex);

                        $currentFullpath = $currentPath;
                        $currentFullpath[] = 0;

                        $stack[] = $child;
                        $stackPath[] = $currentFullpath;
                    }
                }
            }
        }
    }

    /**
     * @param ErrorInterface|\Throwable|ErrorBagInterface $e
     *
     * @return array<string, \Gzhegow\Ret\Error\SingleErrorInterface>
     */
    public function dotErrorChildren($e) : array
    {
        $errors = [];

        foreach ( $this->iterErrorChildren($e) as $path => $error ) {
            $errors[implode('.', $path)] = $error;
        }

        return $errors;
    }


    /**
     * @param ErrorInterface|\Throwable $e
     */
    public function toString($e) : string
    {
        $id = 1;

        return $this->doToString($e, 0, $id);
    }

    /**
     * @param ErrorInterface|\Throwable $e
     */
    protected function doToString($e, $level, &$id) : string
    {
        $result = null
            ?? $this->doToStringError($e, $level, $id)
            ?? $this->doToStringThrowable($e, $level, $id);

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
    protected function doToStringError($e, $level, &$id) : ?string
    {
        if ( ! ($e instanceof ErrorInterface) ) {
            return null;
        }

        if ( $e->throwable ) {
            return $this->doToStringThrowable($e->throwable, $level, $id);
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
                    . $this->doToStringError($ee, $level + 1, $id);
            }
        }

        return $result;
    }

    /**
     * @param \Throwable $e
     */
    protected function doToStringThrowable($e, $level, &$id) : ?string
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
                    . $this->doToStringError($ee, $level + 1, $id);
            }

        } elseif ( $ePrevious = $e->getPrevious() ) {
            $id++;

            $linePad = str_repeat('--', $level + 1) . ' ';

            $result .= "\n"
                . "{$linePad}\n"
                . $this->doToStringThrowable($ePrevious, $level + 1, $id);
        }

        return $result;
    }
}
