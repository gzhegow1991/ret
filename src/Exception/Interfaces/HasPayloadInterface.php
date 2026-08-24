<?php

namespace Gzhegow\Ret\Exception\Interfaces;


interface HasPayloadInterface
{
    public function getPayload() : ?array;

    public function getPayloadArray() : array;
}
