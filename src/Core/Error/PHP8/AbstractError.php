<?php

namespace Gzhegow\Ret\Core\Error\PHP8;

use Gzhegow\Ret\Core\Error\ErrorInterface;


abstract class AbstractError implements ErrorInterface
{
    public \Throwable|null $throwable = null;

    public string $file;
    public int    $line;

    /**
     * @var int|string|\BackedEnum
     */
    public $code;

    public string $message;

    public array|null $payload = null;


    public function getMessageArray() : array
    {
        return [ '' => $this->code, 0 => $this->message ] + ($this->payload ?? []);
    }


    public function isCode($value) : bool
    {
        $leftCode = $this->code;
        $rightCode = ($value instanceof AbstractError)
            ? $value->code
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
