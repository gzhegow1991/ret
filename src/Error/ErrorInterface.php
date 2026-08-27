<?php

namespace Gzhegow\Ret\Error;


/**
 * @property-read \Throwable|null        $throwable
 *
 * @property-read string                 $file
 * @property-read int                    $line
 *
 * @property-read string                 $message
 *
 * @property-read int|string|\BackedEnum $code
 *
 * @property-read array|null             $payload
 */
interface ErrorInterface
{
}
