<?php

namespace Gzhegow\Ret\Ret;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Exception\RuntimeException;


class RetBag implements RetBagInterface
{
    /**
     * @var Ret[]
     */
    protected $rets = [];


    /**
     * @return static
     */
    public static function new()
    {
        return new static();
    }

    protected function __construct()
    {
    }


    /**
     * @return int|string|null
     */
    public function push(Ret $ret, $key = null)
    {
        if ( '' === $key ) $key = null;

        $key = $key ?? spl_object_hash($ret);

        if ( isset($this->rets[$key]) ) {
            return null;
        }

        $this->rets[$key] = $ret;

        return array_key_last($this->rets);
    }

    /**
     * @return int|string|null
     */
    public function add($key, Ret $ret)
    {
        if ( '' === $key ) $key = null;

        $key = $key ?? spl_object_hash($ret);

        if ( isset($this->rets[$key]) ) {
            return null;
        }

        $this->rets[$key] = $ret;

        return array_key_last($this->rets);
    }

    /**
     * @return int|string
     */
    public function set($key, Ret $ret)
    {
        if ( '' === $key ) $key = null;

        $key = $key ?? spl_object_hash($ret);

        $this->rets[$key] = $ret;

        return array_key_last($this->rets);
    }


    public function get($key, bool $orNull = false) : ?Ret
    {
        if ( '' === $key ) $key = null;

        $ret = (null !== $key)
            ? ($this->rets[$key] ?? null)
            : null;

        if ( null !== $ret ) {
            return $ret;
        }

        if ( ! $orNull ) {
            throw new RuntimeException([ 'Unable to `get`: item not found', $key ]);
        }

        return null;
    }

    public function pos(int $position, bool $orNull = false) : ?Ret
    {
        if ( [] !== $this->rets ) {
            $copy = $this->rets;

            if ( $isBackwards = ($position < 0) ) {
                reset($copy);

            } else {
                end($copy);
            }

            $posInt = $position;

            while ( 0 !== $posInt ) {
                if ( $isBackwards ) {
                    prev($copy);
                    $posInt++;

                } else {
                    next($copy);
                    $posInt--;
                }
            }

            if ( null !== ($key = key($copy)) ) {
                return $copy[$key];
            }
        }

        if ( ! $orNull ) {
            throw new RuntimeException([ 'Unable to `pos`: item not found', $position ]);
        }

        return null;
    }


    /**
     * @param Ret[] $rets
     *
     * @return static
     */
    public function merge(array $rets)
    {
        $list = [];

        foreach ( $rets as $i => $ret ) {
            if ( ! $ret instanceof Ret ) {
                throw new LogicException(
                    [ 'Each of `rets` should be instance of: ' . Ret::class, $i, $rets ]
                );
            }

            if ( '' === $i ) $i = null;

            $i = $i ?? spl_object_hash($ret);

            $list[$i] = $ret;
        }

        $this->rets = array_merge($this->rets, $list);

        return $this;
    }

    /**
     * @param Ret[] $rets
     *
     * @return static
     */
    public function replace(array $rets)
    {
        $list = [];

        foreach ( $rets as $i => $ret ) {
            if ( ! $ret instanceof Ret ) {
                throw new LogicException(
                    [ 'Each of `rets` should be instance of: ' . Ret::class, $i, $rets ]
                );
            }

            if ( '' === $i ) $i = null;

            $i = $i ?? spl_object_hash($ret);

            $list[$i] = $ret;
        }

        $this->rets = array_replace($this->rets, $list);

        return $this;
    }

    /**
     * @param Ret[] $rets
     *
     * @return static
     */
    public function append(array $rets)
    {
        $list = [];

        foreach ( $rets as $i => $ret ) {
            if ( ! $ret instanceof Ret ) {
                throw new LogicException(
                    [ 'Each of `rets` should be instance of: ' . Ret::class, $i, $rets ]
                );
            }

            if ( '' === $i ) $i = null;

            $i = $i ?? spl_object_hash($ret);

            $list[$i] = $ret;
        }

        $this->rets += $list;

        return $this;
    }


    public function isEmpty() : bool
    {
        return [] === $this->rets;
    }


    /**
     * @template T
     *
     * @param array{ 0: T[], 1: Ret<T[]> } $refs
     */
    public function isOk(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refValues =& $refs[0];
        if ( $withRet = array_key_exists(1, $refs) ) $refRet =& $refs[1];
        $refValues = [];
        $refRet = null;

        if ( [] === $this->rets ) {
            return false;
        }

        $i = -1;
        foreach ( $this->rets as $ret ) {
            $i++;

            if ( ! $ret->isOk([ &$refValue ]) ) {
                return false;
            }

            $refValues[$i] = $refValue;
        }

        if ( $withRet ) {
            $refRet = Ret::ok($refValues);
        }

        return true;
    }

    /**
     * @template T
     *
     * @param array{ 0: T, 1: Ret<T> } $refs
     */
    public function hasOk(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refValue =& $refs[0];
        if ( array_key_exists(1, $refs) ) $refRet =& $refs[1];
        $refValue = null;
        $refRet = null;

        foreach ( $this->rets as $ret ) {
            if ( $ret->isOk([ &$refValue ]) ) {
                $refRet = $ret;

                return true;
            }
        }

        return false;
    }


    /**
     * @param array{ 0?: \Gzhegow\Ret\Error\ErrorInterface[], 1?: Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface> } $refs
     */
    public function isFail(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refErrors =& $refs[0];
        if ( $withRet = array_key_exists(0, $refs) ) $refRet =& $refs[1];
        $refErrors = [];
        $refRet = null;

        if ( [] === $this->rets ) {
            return false;
        }

        $i = -1;
        foreach ( $this->rets as $ret ) {
            $i++;

            if ( ! $ret->isFail([ &$refError ]) ) {
                return false;
            }

            $refErrors[$i] = $refError;
        }

        if ( $withRet ) {
            $refRet = Ret::fail(Err::aggregate($refErrors));
        }

        return true;
    }

    /**
     * @param array{ 0?: \Gzhegow\Ret\Error\ErrorInterface, 1?: Ret<null, \Gzhegow\Ret\Error\ErrorInterface>  } $refs
     */
    public function hasFail(array $refs = []) : bool
    {
        if ( array_key_exists(0, $refs) ) $refError =& $refs[0];
        if ( array_key_exists(1, $refs) ) $refRet =& $refs[1];
        $refError = null;
        $refRet = null;

        foreach ( $this->rets as $ret ) {
            if ( $ret->isFail([ &$refError ]) ) {
                $refRet = $ret;

                return true;
            }
        }

        return false;
    }


    public function isMixed() : int
    {
        $hasOk = false;
        $hasFail = false;

        foreach ( $this->rets as $ret ) {
            if ( $ret->isOk() ) {
                $hasOk = true;

            } elseif ( $ret->isFail() ) {
                $hasFail = true;
            }

            if ( $hasOk && $hasFail ) {
                return true;
            }
        }

        return false;
    }


    /**
     * @return Ret<mixed, null>|Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function first(bool $orNull = false) : ?Ret
    {
        $key = array_key_first($this->rets);

        if ( null !== $key ) {
            return $this->rets[$key];
        }

        if ( ! $orNull ) {
            throw new RuntimeException([ 'Unable to `first`: item not found' ]);
        }

        return null;
    }

    /**
     * @return Ret<mixed, null>|null
     */
    public function firstOk(bool $orNull = false) : ?Ret
    {
        foreach ( $this->rets as $r ) {
            if ( $r->isOk() ) {
                return $r;
            }
        }

        if ( ! $orNull ) {
            throw new RuntimeException([ 'Unable to `firstOk`: item not found' ]);
        }

        return null;
    }

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function firstFail(bool $orNull = false) : ?Ret
    {
        foreach ( $this->rets as $ret ) {
            if ( $ret->isFail() ) {
                return $ret;
            }
        }

        if ( ! $orNull ) {
            throw new RuntimeException([ 'Unable to `firstFail`: item not found' ]);
        }

        return null;
    }

    /**
     * @return array{ 0: Ret<mixed, null>|null, 1: Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null }
     */
    public function firstPair() : array
    {
        $firstOk = null;
        $firstFail = null;

        foreach ( $this->rets as $ret ) {
            if ( (! $firstOk) && $ret->isOk() ) {
                $firstOk = $ret;

            } elseif ( (! $firstFail) && $ret->isFail() ) {
                $firstFail = $ret;
            }

            if ( $firstOk && $firstFail ) {
                break;
            }
        }

        return [ $firstOk, $firstFail ];
    }


    /**
     * @return Ret<mixed, null>|Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function last(bool $orNull = false) : ?Ret
    {
        $key = array_key_last($this->rets);

        if ( null !== $key ) {
            $this->rets[$key];
        }

        if ( ! $orNull ) {
            throw new RuntimeException([ 'Unable to `last`: item not found' ]);
        }

        return null;
    }

    /**
     * @return Ret<mixed, null>|null
     */
    public function lastOk(bool $orNull = false) : ?Ret
    {
        if ( [] !== $this->rets ) {
            $copy = $this->rets;
            end($copy);

            while ( null !== ($key = key($copy)) ) {
                $ret = $copy[$key];

                if ( $ret->isOk() ) {
                    return $ret;
                }

                prev($copy);
            }
        }

        if ( ! $orNull ) {
            throw new RuntimeException([ 'Unable to `lastOk`: item not found' ]);
        }

        return null;
    }

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function lastFail(bool $orNull = true) : ?Ret
    {
        if ( [] !== $this->rets ) {
            $copy = $this->rets;
            end($copy);

            while ( null !== ($key = key($copy)) ) {
                $ret = $copy[$key];

                if ( $ret->isFail() ) {
                    return $ret;
                }

                prev($copy);
            }
        }

        if ( ! $orNull ) {
            throw new RuntimeException([ 'Unable to `lastFail`: item not found' ]);
        }

        return null;
    }

    /**
     * @return array{ 0: Ret<mixed, null>|null, 1: Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null }
     */
    public function lastPair() : array
    {
        if ( [] === $this->rets ) {
            return [];
        }

        $lastOk = null;
        $lastFail = null;

        $copy = $this->rets;
        end($copy);

        while ( null !== key($copy) ) {
            $ret = current($copy);

            if ( (! $lastOk) && $ret->isOk() ) {
                $lastOk = $ret;

            } elseif ( (! $lastFail) && $ret->isFail() ) {
                $lastFail = $ret;
            }

            if ( $lastOk && $lastFail ) {
                break;
            }

            prev($copy);
        }

        return [ $lastOk, $lastFail ];
    }


    /**
     * @return (Ret<mixed, null>|Ret<null, \Gzhegow\Ret\Error\ErrorInterface>)[]
     */
    public function all() : array
    {
        return $this->rets;
    }


    /**
     * @return Ret<mixed, null>[]
     */
    public function allOk() : array
    {
        $rets = [];

        $i = -1;
        foreach ( $this->rets as $ret ) {
            $i++;

            if ( $ret->isOk() ) {
                $rets[$i] = $ret;
            }
        }

        return $rets;
    }

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\ErrorInterface>[]
     */
    public function allFail() : array
    {
        $rets = [];

        $i = -1;
        foreach ( $this->rets as $ret ) {
            $i++;

            if ( $ret->isFail() ) {
                $rets[$i] = $ret;
            }
        }

        return $rets;
    }

    /**
     * @return array{ 0: Ret<mixed, null>[], 1: Ret<null, \Gzhegow\Ret\Error\ErrorInterface>[] }
     */
    public function allPair() : array
    {
        $retsOk = [];
        $retsFail = [];

        $i = -1;
        foreach ( $this->rets as $ret ) {
            $i++;

            if ( $ret->isOk() ) {
                $retsOk[$i] = $ret;

            } elseif ( $ret->isFail() ) {
                $retsFail[$i] = $ret;
            }
        }

        return [ $retsOk, $retsFail ];
    }


    /**
     * @return Ret<array, null>|null
     */
    public function groupOk(bool $orNull = false) : ?Ret
    {
        if ( $this->hasOk() ) {
            return Ret::ok($this->getValues());
        }

        if ( ! $orNull ) {
            throw new LogicException(
                [ 'Unable to `groupOk`: The `registry` is not contain values', $this ]
            );
        }

        return null;
    }

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null
     */
    public function groupFail(bool $orNull = false) : ?Ret
    {
        if ( $this->hasFail() ) {
            return Ret::fail(Err::aggregate($this->getErrors()));
        }

        if ( ! $orNull ) {
            throw new LogicException(
                [ 'Unable to `groupFail`: The `registry` is not contain errors', $this ]
            );
        }

        return null;
    }

    /**
     * @return array{ 0: Ret<array, null>|null, 1: Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null }
     */
    public function groupPair() : array
    {
        [ $firstOk, $firstFail ] = $this->firstPair();

        $retOk = null;
        $retFail = null;

        if ( $firstOk ) {
            $retOk = Ret::ok($this->getValues());
        }

        if ( $firstFail ) {
            $retFail = Ret::fail(Err::aggregate($this->getErrors()));
        }

        return [ $retOk, $retFail ];
    }


    /**
     * @return Ret<array, null>|Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null
     */
    public function resolved(bool $orNull = false) : ?Ret
    {
        [ $firstOk, $firstFail ] = $this->firstPair();

        $e = null;
        $ret = null;

        if ( $firstOk && $firstFail ) {
            $e = new LogicException(
                [ 'Unable to `resolved`: The `ret` contains both an failed and a succeeded items', $this ]
            );

        } elseif ( $firstOk ) {
            $ret = Ret::ok($this->getValues());

        } elseif ( $firstFail ) {
            $ret = Ret::fail(Err::aggregate($this->getErrors()));

        } else {
            $e = new LogicException(
                [ 'Unable to `resolved`: The `ret` is empty', $this ]
            );
        }

        if ( null !== $e ) {
            if ( ! $orNull ) {
                throw $e;
            }

            return null;
        }

        return $ret;
    }

    /**
     * @return Ret<array, null>|null
     */
    public function resolvedOk(bool $orNull = false) : ?Ret
    {
        if ( $this->isOk() ) {
            return Ret::ok($this->getValues());
        }

        if ( ! $orNull ) {
            throw new LogicException(
                [ 'Unable to `resolvedOk`: The `registry` is empty or contain errors', $this ]
            );
        }

        return null;
    }

    /**
     * @return Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null
     */
    public function resolvedFail(bool $orNull = false) : ?Ret
    {
        if ( $this->isFail() ) {
            return Ret::fail(Err::aggregate($this->getErrors()));
        }

        if ( ! $orNull ) {
            throw new LogicException(
                [ 'Unable to `resolvedFail`: The `registry` is empty or contain values', $this ]
            );
        }

        return null;
    }


    /**
     * @return Ret<array, null>|Ret<null, \Gzhegow\Ret\Error\ErrorInterface>|null
     */
    public function firstFailOrResolvedOk(bool $orNull = false) : ?Ret
    {
        [ $firstOk, $firstFail ] = $this->firstPair();

        if ( $firstFail ) {
            return $firstFail;

        } elseif ( $firstOk ) {
            return Ret::ok($this->getValues());
        }

        if ( ! $orNull ) {
            throw new LogicException(
                [ 'Unable to `firstFailOrResolvedOk`: The `registry` is empty', $this ]
            );
        }

        return null;
    }

    /**
     * @return Ret<mixed, null>|Ret<null, \Gzhegow\Ret\Error\AggregateErrorInterface>|null
     */
    public function firstOkOrResolvedFail(bool $orNull = false) : ?Ret
    {
        [ $firstOk, $firstFail ] = $this->firstPair();

        if ( $firstOk ) {
            return $firstOk;

        } elseif ( $firstFail ) {
            return Ret::fail(Err::aggregate($this->getErrors()));
        }

        if ( ! $orNull ) {
            throw new LogicException(
                [ 'Unable to `firstOkOrResolvedFail`: The `registry` is empty', $this ]
            );
        }

        return null;
    }


    public function getValues() : array
    {
        $rets = $this->allOk();

        $values = [];

        foreach ( $rets as $i => $ret ) {
            $values[$i] = $ret->getValue();
        }

        return $values;
    }


    /**
     * @return \Gzhegow\Ret\Error\ErrorInterface[]
     */
    public function getErrors() : array
    {
        $rets = $this->allFail();

        $errors = [];

        foreach ( $rets as $i => $ret ) {
            $errors[$i] = $ret->getError();
        }

        return $errors;
    }

    /**
     * @return \Generator<array, \Gzhegow\Ret\Error\ErrorInterface>
     */
    public function iterErrors() : iterable
    {
        $rets = $this->allFail();

        foreach ( $rets as $i => $ret ) {
            $path = [ $i ];

            foreach ( $ret->iterErrors() as $childPath => $child ) {
                $fullpath = array_merge($path, $childPath);

                yield $fullpath => $child;
            }
        }
    }

    /**
     * @return array<string, \Gzhegow\Ret\Error\ErrorInterface>
     */
    public function dotErrors() : array
    {
        $errors = [];

        foreach ( $this->iterErrors() as $path => $error ) {
            $errors[implode('.', $path)] = $error;
        }

        return $errors;
    }
}
