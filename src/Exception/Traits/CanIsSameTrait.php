<?php

namespace Gzhegow\Ret\Exception\Traits;

use Gzhegow\Ret\Exception\Interfaces\CanIsSameInterface;


/**
 * @mixin \Throwable
 *
 * @see \Gzhegow\Ret\Exception\Interfaces\CanIsSameInterface
 */
trait CanIsSameTrait
{
    public static function isSame($left, $right) : bool
    {
        if ( $left instanceof CanIsSameInterface ) {
            return $left->isSameAs($right);
        }

        if ( $right instanceof CanIsSameInterface ) {
            return $right->isSameAs($left);
        }

        return false;
    }

    public function isSameAs($value) : bool
    {
        $leftCode = $this->getCode();
        $rightCode = ($value instanceof \Throwable)
            ? $value->getCode()
            : $value;

        $leftCode = $leftCode ?: null;
        $rightCode = $rightCode ?: null;

        if ( $leftCode === null ) return false;
        if ( $rightCode === null ) return false;

        if ( $leftCode === -1 ) return false;
        if ( $rightCode === -1 ) return false;

        return $leftCode === $rightCode;
    }
}
