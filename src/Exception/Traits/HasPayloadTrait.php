<?php

namespace Gzhegow\Ret\Exception\Traits;


/**
 * @mixin \Throwable
 *
 * @see \Gzhegow\Ret\Exception\Interfaces\HasPayloadInterface
 */
trait HasPayloadTrait
{
    /**
     * @var array|null
     */
    protected $payload;


    public function getPayload() : ?array
    {
        return $this->payload;
    }

    public function getPayloadArray() : array
    {
        return $this->payload ?? [];
    }
}
