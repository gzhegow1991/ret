<?php

namespace Gzhegow\Ret\Exception\Interfaces;

use Gzhegow\Ret\Core\Error\ErrorInterface;


interface CanToStringInterface
{
    public function toString() : string;


    /**
     * @param \Throwable|ErrorInterface $e
     */
    public static function castToString($e) : string;
}
