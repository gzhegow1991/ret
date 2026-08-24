<?php

namespace Gzhegow\Ret\Exception\Traits;

use Gzhegow\Ret\Exception\RuntimeException;


/**
 * @mixin \Throwable
 *
 * @see \Gzhegow\Ret\Exception\Interfaces\CanTraceShiftInterface
 */
trait CanTraceShiftTrait
{
    /**
     * @var int
     */
    protected $traceShiftFrames = 0;

    /**
     * @var string
     */
    protected $fileShifted;
    /**
     * @var int
     */
    protected $lineShifted;
    /**
     * @var array
     */
    protected $traceShifted;


    /**
     * @return static
     */
    public function traceShift(int $traceShift)
    {
        $this->traceShiftFrames = max(0, $traceShift);

        $this->fileShifted = null;
        $this->lineShifted = null;
        $this->traceShifted = null;

        return $this;
    }

    /**
     * @return static
     */
    public function applyTraceShift()
    {
        static $rpFile;
        static $rpLine;
        static $rpTrace;

        if ( 0 === $this->traceShiftFrames ) {
            return $this;
        }

        $this->resolveTraceShift();

        if ( null === $rpFile ) {
            $rpFile = new \ReflectionProperty(\Exception::class, 'file');
            $rpFile->setAccessible(true);
        }
        if ( null === $rpLine ) {
            $rpLine = new \ReflectionProperty(\Exception::class, 'line');
            $rpLine->setAccessible(true);
        }
        if ( null === $rpTrace ) {
            $rpTrace = new \ReflectionProperty(\Exception::class, 'trace');
            $rpTrace->setAccessible(true);
        }

        $rpFile->setValue($this, $this->fileShifted);
        $rpLine->setValue($this, $this->lineShifted);
        $rpTrace->setValue($this, $this->traceShifted);

        $this->traceShiftFrames = 0;
        $this->fileShifted = null;
        $this->lineShifted = null;
        $this->traceShifted = null;

        return $this;
    }


    public function getFileShifted() : string
    {
        $this->resolveTraceShift();

        return $this->fileShifted ?? $this->getFile();
    }

    public function getLineShifted() : int
    {
        $this->resolveTraceShift();

        return $this->lineShifted ?? $this->getLine();
    }

    public function getTraceShifted() : array
    {
        $this->resolveTraceShift();

        return $this->traceShifted ?? $this->getTrace();
    }

    protected function resolveTraceShift() : void
    {
        if ( 0 === $this->traceShiftFrames ) {
            return;
        }

        if ( null === $this->traceShifted ) {
            $trace = $this->getTrace();

            $targetFrameIndex = $this->traceShiftFrames - 1;

            if ( ! isset($trace[$targetFrameIndex]) ) {
                throw new RuntimeException(
                    [ 'The `trace` has not enough frames to shift', $this ]
                );
            }

            $targetFrame = $trace[$targetFrameIndex];

            $this->fileShifted = $targetFrame['file'] ?? 'unknown';
            $this->lineShifted = $targetFrame['line'] ?? 0;
            $this->traceShifted = array_slice($trace, $targetFrameIndex + 1);
        }
    }
}
