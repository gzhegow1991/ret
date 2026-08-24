<?php

namespace Gzhegow\Ret\Core\Error\PHP7;

use Gzhegow\Ret\Core\Error\ErrorInterface;


abstract class AbstractError implements ErrorInterface
{
    /**
     *
     * @var \Throwable|null
     */
    public $throwable = null;

    /**
     * @var string
     */
    public $file;
    /**
     * @var int
     */
    public $line;

    /**
     * @var int|string|\BackedEnum
     */
    public $code;
    /**
     * @var string
     */
    public $message;

    /**
     * @var array|null
     */
    public $payload = null;


    /**
     * @return array
     */
    public function getMessageArray()
    {
        return [ '' => $this->code, 0 => $this->message ] + ($this->payload ?? []);
    }


    /**
     * @return bool
     */
    public function isCode($value)
    {
        $leftCode = $this->code;
        $rightCode = ($value instanceof ErrorInterface)
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
