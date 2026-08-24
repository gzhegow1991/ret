<?php

namespace Gzhegow\Ret\Core\Error;


/**
 * @property-read \Throwable|null        $throwable
 *
 * @property-read string                 $file
 * @property-read int                    $line
 *
 * @property-read int|string|\BackedEnum $code
 * @property-read string                 $message
 * @property-read array|null             $payload
 */
interface ErrorInterface
{
    /**
     * @return array
     */
    public function getMessageArray();


    /**
     * @return bool
     */
    public function isCode($value);
}
