<?php

namespace Gzhegow\Ret\Exception;


interface TriggeredExceptionInterface extends SingleExceptionInterface
{
    /**
     * @return int
     */
    public function getSeverity();
}
