<?php

namespace Gzhegow\Ret\Exception;


interface AggregateExceptionInterface extends ExceptionInterface
{
    /**
     * @return \Gzhegow\Ret\Error\ErrorInterface[]
     */
    public function getErrors() : array;

    /**
     * @return \Generator<array, \Gzhegow\Ret\Error\ErrorInterface[]>
     */
    public function getErrorsRecursive() : iterable;
}
