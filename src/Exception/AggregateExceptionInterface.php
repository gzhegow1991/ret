<?php

namespace Gzhegow\Ret\Exception;


interface AggregateExceptionInterface extends ExceptionInterface
{
    /**
     * @return (\Throwable|\Gzhegow\Ret\Core\Error\ErrorInterface)[]
     */
    public function getErrors() : array;
}
