<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Err;


/**
 * @mixin \Throwable
 *
 * @see \Gzhegow\Ret\Exception\AggregateExceptionInterface
 */
trait AggregateExceptionTrait
{
    use ExceptionTrait;


    /**
     * @var \Gzhegow\Ret\Error\ErrorInterface
     */
    protected $errors = [];


    /**
     * @return \Gzhegow\Ret\Error\ErrorInterface[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * @return \Generator<array, \Gzhegow\Ret\Error\ErrorInterface[]>
     */
    public function getErrorsRecursive() : iterable
    {
        return Err::iterChildren($this);
    }
}
