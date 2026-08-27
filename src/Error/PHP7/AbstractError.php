<?php

namespace Gzhegow\Ret\Error\PHP7;

use Gzhegow\Ret\Error\ErrorInterface;


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
     * @var string
     */
    public $message;

    /**
     * @var int|string|\BackedEnum
     */
    public $code = -1;

    /**
     * @var array|null
     */
    public $payload = null;
}
