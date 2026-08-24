<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Core\Err;


/**
 * @mixin \Throwable
 *
 * @see \Gzhegow\Ret\Exception\AggregateExceptionInterface
 */
trait AggregateExceptionTrait
{
    use ExceptionTrait;


    /**
     * @var \Gzhegow\Ret\Core\Error\ErrorInterface
     */
    protected $errors = [];


    /**
     * @return \Gzhegow\Ret\Core\Error\ErrorInterface[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * @return \Generator<array, \Gzhegow\Ret\Core\Error\ErrorInterface[]>
     */
    public function getErrorsRecursive() : iterable
    {
        return Err::getChildrenRecursive($this);
    }
}
