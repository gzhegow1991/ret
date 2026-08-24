<?php

namespace Gzhegow\Ret\Exception\Interfaces;


interface CanIsSameInterface
{
    public static function isSame($left, $right) : bool;

    public function isSameAs($value) : bool;
}
