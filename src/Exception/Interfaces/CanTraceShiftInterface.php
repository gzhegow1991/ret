<?php

namespace Gzhegow\Ret\Exception\Interfaces;


interface CanTraceShiftInterface
{
    /**
     * @return static
     */
    public function traceShift(int $traceShift);

    /**
     * @return static
     */
    public function applyTraceShift();


    public function getFileShifted() : string;

    public function getLineShifted() : int;

    public function getTraceShifted() : array;
}
