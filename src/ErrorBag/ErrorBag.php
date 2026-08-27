<?php

namespace Gzhegow\Ret\ErrorBag;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Error\SingleErrorInterface;
use Gzhegow\Ret\Error\AggregateErrorInterface;


class ErrorBag implements ErrorBagInterface
{
    /**
     * @var array<int, ErrorInterface>
     */
    protected $errors = [];
    /**
     * @var array<int, SingleErrorInterface>
     */
    protected $errorsChildren = [];

    /**
     * @var array<int, array<string, bool>>
     */
    protected $tags = [];


    public static function new()
    {
        return new static();
    }

    protected function __construct()
    {
    }


    /**
     * @param ErrorInterface[] $errors
     * @param string[]         $tags
     */
    public static function collect(array $errors = [], array $tags = [])
    {
        $instance = new static();

        if ( [] !== $errors ) {
            foreach ( $errors as $e ) {
                $instance->addError($e, $tags);
            }
        }

        return $instance;
    }

    /**
     * @param AggregateErrorInterface[] $parents
     * @param string[]                  $tags
     */
    public static function collectParents(array $parents = [], array $tags = [])
    {
        $instance = new static();

        if ( [] !== $parents ) {
            foreach ( $parents as $p ) {
                $instance->addParent($p, $tags);
            }
        }

        return $instance;
    }


    public function addError(ErrorInterface $error, array $tags = [])
    {
        $tagsIndex = ([] === $tags)
            ? []
            : $this->buildTagsIndex($tags);

        $splId = spl_object_id($error);

        if ( ! isset($this->errors[$splId]) ) {
            $this->errors[$splId] = $error;
        }

        if ( isset($this->tags[$splId]) ) {
            $this->tags[$splId] += $tagsIndex;

        } else {
            $this->tags[$splId] = $tagsIndex;
        }

        return $this;
    }

    public function addParent(AggregateErrorInterface $parent, array $tags = [])
    {
        $tagsIndex = ([] === $tags)
            ? []
            : $this->buildTagsIndex($tags);

        /**
         * @var ErrorInterface[] $stack
         */
        $stack = [];
        $stack[] = $parent;

        while ( [] !== $stack ) {
            $current = array_pop($stack);
            $currentSplId = spl_object_id($current);

            if ( $current instanceof AggregateErrorInterface ) {
                if ( ! isset($this->errors[$currentSplId]) ) {
                    $this->errors[$currentSplId] = $current;
                }

                if ( isset($this->tags[$currentSplId]) ) {
                    $this->tags[$currentSplId] += $tagsIndex;

                } else {
                    $this->tags[$currentSplId] = $tagsIndex;
                }

                $errorsReversed = array_reverse($current->errors, true);

                foreach ( $errorsReversed as $e ) {
                    $stack[] = $e;
                }

            } else {
                if ( ! isset($this->errorsChildren[$currentSplId]) ) {
                    $this->errorsChildren[$currentSplId] = $current;
                }

                if ( isset($this->tags[$currentSplId]) ) {
                    $this->tags[$currentSplId] += $tagsIndex;

                } else {
                    $this->tags[$currentSplId] = $tagsIndex;
                }

                if ( null !== $current->throwable ) {
                    if ( $ex = $current->throwable->getPrevious() ) {
                        $stack[] = Err::wrap($ex);
                    }
                }
            }
        }

        return $this;
    }


    /**
     * @return ErrorInterface[]
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return TaggedErrorInterface<ErrorInterface>[]
     */
    public function getTaggedErrors()
    {
        return $this->buildTaggedErrors($this->errors);
    }


    /**
     * @return SingleErrorInterface[]
     */
    public function getChildren()
    {
        return $this->errorsChildren;
    }

    /**
     * @return TaggedErrorInterface<SingleErrorInterface>[]
     */
    public function getTaggedChildren()
    {
        return $this->buildTaggedErrors($this->errorsChildren);
    }


    /**
     * @return ErrorInterface[]
     */
    public function findErrors(array ...$orTagFilters)
    {
        $errors = $this->doFindErrors(...$orTagFilters);

        return array_values($errors);
    }

    /**
     * @return TaggedErrorInterface<ErrorInterface>[]
     */
    public function findTaggedErrors(array ...$orTagFilters)
    {
        $errors = $this->doFindErrors(...$orTagFilters);

        $taggedErrors = $this->buildTaggedErrors($errors);

        return array_values($taggedErrors);
    }

    /**
     * @return ErrorInterface[]
     */
    protected function doFindErrors(array ...$orTagFilters)
    {
        if ( [] === $orTagFilters ) {
            $total = $this->errors;

        } else {
            $total = [];

            foreach ( $orTagFilters as $tagFilter ) {
                $tagFilterIndex = ([] === $tagFilter)
                    ? []
                    : $this->buildTagFilterIndex($tagFilter);

                foreach ( $this->errors as $i => $e ) {
                    $match = true;

                    if ( [] !== $tagFilterIndex ) {
                        foreach ( $tagFilterIndex as $tag => $bool ) {
                            $exists = isset($this->tags[$i][$tag]);

                            if ( $bool !== $exists ) {
                                $match = false;

                                break;
                            }
                        }
                    }

                    if ( $match ) {
                        $total[$i] = $e;
                    }
                }
            }
        }

        return $total;
    }


    /**
     * @return SingleErrorInterface[]
     */
    public function findChildren(array ...$orTagFilters)
    {
        $errors = $this->doFindChildren(...$orTagFilters);

        return array_values($errors);
    }

    /**
     * @return TaggedErrorInterface<SingleErrorInterface>[]
     */
    public function findTaggedChildren(array ...$orTagFilters)
    {
        $errors = $this->doFindChildren(...$orTagFilters);

        $taggedErrors = $this->buildTaggedErrors($errors);

        return array_values($taggedErrors);
    }

    /**
     * @return SingleErrorInterface[]
     */
    protected function doFindChildren(array ...$orTagFilters)
    {
        if ( [] === $orTagFilters ) {
            $total = $this->errorsChildren;

        } else {
            $total = [];

            foreach ( $orTagFilters as $tagFilter ) {
                $tagFilterIndex = ([] === $tagFilter)
                    ? []
                    : $this->buildTagFilterIndex($tagFilter);

                foreach ( $this->errorsChildren as $i => $e ) {
                    $match = true;

                    if ( [] !== $tagFilterIndex ) {
                        foreach ( $tagFilterIndex as $tag => $bool ) {
                            $exists = isset($this->tags[$i][$tag]);

                            if ( $bool !== $exists ) {
                                $match = false;

                                break;
                            }
                        }
                    }

                    if ( $match ) {
                        $total[$i] = $e;
                    }
                }
            }
        }

        return $total;
    }


    /**
     * @template-covariant T of ErrorInterface
     *
     * @param array<T> $errors
     *
     * @return TaggedErrorInterface<T>[]
     */
    protected function buildTaggedErrors(array $errors)
    {
        $taggedErrors = [];

        foreach ( $errors as $splId => $error ) {
            $taggedError = Err::tagged(
                $this->errors[$splId],
                $this->tags[$splId]
            );

            $taggedErrors[$splId] = $taggedError;
        }

        return $taggedErrors;
    }


    protected function buildTagsIndex(array $tags) : array
    {
        $tagsIndex = [];

        foreach ( $tags as $i => $tag ) {
            if ( is_string($i) ) {
                $tag = $i;
            }

            if ( ! (is_string($tag) && ('' !== $tag)) ) {
                throw new LogicException(
                    [ 'Each of `tags` should string, not-empty', $i, $tags ]
                );
            }

            $tagsIndex[$tag] = true;
        }

        return $tagsIndex;
    }

    protected function buildTagFilterIndex(array $tagFilter) : array
    {
        $tagsFilterIndex = [];

        foreach ( $tagFilter as $i => $bool ) {
            if ( ! is_int($i) ) {
                $tag = $i;

            } else {
                $tag = $bool;
                $bool = true;
            }

            if ( ! (is_string($tag) && ('' !== $tag)) ) {
                throw new LogicException(
                    [ 'Each of `tags` should string, not-empty', $i, $tagFilter ]
                );
            }

            $tagsFilterIndex[$tag] = (bool) $bool;
        }

        return $tagsFilterIndex;
    }
}
