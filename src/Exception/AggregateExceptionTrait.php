<?php

namespace Gzhegow\Ret\Exception;


/**
 * @mixin \Throwable
 *
 * @see \Gzhegow\Ret\Exception\AggregateExceptionInterface
 */
trait AggregateExceptionTrait
{
    use ExceptionTrait;


    /**
     * @var (\Throwable|\Gzhegow\Ret\Core\Error\ErrorInterface)[]
     */
    protected $errors = [];


    /**
     * @return (\Throwable|\Gzhegow\Ret\Core\Error\ErrorInterface)[]
     */
    public function getErrors() : array
    {
        return $this->errors;
    }


    public function isSameAs($value) : bool
    {
        // > aggregates cannot be compared
        return false;
    }
}
