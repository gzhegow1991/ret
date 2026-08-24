<?php

namespace Gzhegow\Ret\Core\Error\PHP8;

use Gzhegow\Ret\Core\Error\ErrorInterface;


abstract class AbstractError implements ErrorInterface
{
    public \Throwable|null $throwable = null;

    public string $file;
    public int    $line;

    public string $message;

    /**
     * @var int|string|\BackedEnum
     */
    public $code = -1;

    public array|null $payload = null;
}
