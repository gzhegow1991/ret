<?php

namespace Gzhegow\Ret\Core\Error\PHP8;

use Gzhegow\Ret\Core\Error\ErrorInterface;
use Gzhegow\Ret\Core\Error\MainErrorInterface;
use Gzhegow\Ret\Exception\MainExceptionInterface;


class MainError extends AbstractError implements MainErrorInterface
{
    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static
     */
    public static function make($from, ?string $file = null, ?int $line = null) : static
    {
        $payloadValid = null;
        if ( is_array($from) ) {
            $maybeCode = $from[''] ?? null;
            $maybeMessage = $from[0] ?? null;
            unset($from['']);
            unset($from[0]);

            if ( [] !== $from ) {
                $payloadValid = $from;
            }

        } else {
            $maybeCode = null;
            $maybeMessage = $from;
        }

        if ( $maybeCode && $maybeMessage ) {
            if ( is_int($maybeCode) ) {
                $codeValid = $maybeCode;

            } elseif ( is_string($maybeCode) ) {
                $codeValid = $maybeCode;

            } elseif ( is_a($maybeCode, '\BackedEnum') && is_string($maybeCode->value) ) {
                $codeValid = $maybeCode;

            } else {
                throw new \LogicException('The `from[\'\']` should be int|\BackedEnum');
            }

            if ( is_string($maybeMessage) ) {
                $messageValid = $maybeMessage;

            } elseif ( is_int($maybeMessage) ) {
                $codeValid = -1;
                $messageValid = (string) $maybeMessage;

            } elseif ( is_scalar($maybeMessage) ) {
                $messageValid = var_export($maybeMessage, true);

            } elseif ( is_a($maybeCode, '\BackedEnum') && is_string($maybeCode->value) ) {
                $messageValid = $maybeCode->value;

            } else {
                throw new \LogicException('The `from[0]` should be string|scalar|\BackedEnum');
            }

        } elseif ( $maybeCode ) {
            if ( is_int($maybeCode) ) {
                $codeValid = $maybeCode;
                $messageValid = (string) $maybeCode;

            } elseif ( is_string($maybeCode) ) {
                $codeValid = $maybeCode;
                $messageValid = $maybeCode;

            } elseif ( is_a($maybeCode, '\BackedEnum') && is_string($maybeCode->value) ) {
                $codeValid = $maybeCode;
                $messageValid = $maybeCode->value;

            } else {
                throw new \LogicException('The `from[\'\']` should be int|string|\BackedEnum');
            }

        } elseif ( $maybeMessage ) {
            if ( is_string($maybeMessage) ) {
                $codeValid = -1;
                $messageValid = $maybeMessage;

            } elseif ( is_int($maybeMessage) ) {
                $codeValid = -1;
                $messageValid = (string) $maybeMessage;

            } elseif ( is_scalar($maybeMessage) ) {
                $codeValid = -1;
                $messageValid = var_export($maybeMessage, true);

            } elseif ( is_a($maybeMessage, '\BackedEnum') && is_string($maybeMessage->value) ) {
                $codeValid = -1;
                $messageValid = $maybeMessage->value;

            } else {
                throw new \LogicException('The `from[0]` should be string|scalar|\BackedEnum');
            }

        } else {
            throw new \LogicException('The `from[\'\']` or `from[0]` must be present');
        }

        $instance = new static();

        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = $codeValid;
        $instance->message = $messageValid;
        $instance->payload = $payloadValid;

        return $instance;
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static
     */
    public static function code($from, ?string $file = null, ?int $line = null) : static
    {
        $payloadValid = null;
        if ( is_array($from) ) {
            $maybeCode = $from[0] ?? null;
            unset($from[0]);

            if ( [] !== $from ) {
                $payloadValid = $from;
            }

        } else {
            $maybeCode = $from;
        }

        if ( is_int($maybeCode) ) {
            $codeValid = $maybeCode;
            $messageValid = (string) $maybeCode;

        } elseif ( is_string($maybeCode) ) {
            $codeValid = $maybeCode;
            $messageValid = $maybeCode;

        } elseif ( is_a($maybeCode, '\BackedEnum') && is_string($maybeCode->value) ) {
            $codeValid = $maybeCode;
            $messageValid = $maybeCode->value;

        } else {
            throw new \LogicException('The `from[0]` should be int|\BackedEnum');
        }

        $instance = new static();

        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = $codeValid;
        $instance->message = $messageValid;
        $instance->payload = $payloadValid;

        return $instance;
    }

    /**
     * @param mixed       $from
     * @param string|null $file
     * @param int|null    $line
     *
     * @return static
     */
    public static function message($from, ?string $file = null, ?int $line = null) : static
    {
        $payloadValid = null;
        if ( is_array($from) ) {
            $maybeMessage = $from[0] ?? null;
            unset($from[0]);

            if ( [] !== $from ) {
                $payloadValid = $from;
            }

        } else {
            $maybeMessage = $from;
        }

        if ( is_string($maybeMessage) ) {
            $codeValid = -1;
            $messageValid = $maybeMessage;

        } elseif ( is_int($maybeMessage) ) {
            $codeValid = -1;
            $messageValid = (string) $maybeMessage;

        } elseif ( is_scalar($maybeMessage) ) {
            $codeValid = -1;
            $messageValid = var_export($maybeMessage, true);

        } elseif ( is_a($maybeMessage, '\BackedEnum') && is_string($maybeMessage->value) ) {
            $codeValid = -1;
            $messageValid = $maybeMessage->value;

        } else {
            throw new \LogicException('The `from[0]` should be string or scalar (nor bool)');
        }

        $instance = new static();

        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = $codeValid;
        $instance->message = $messageValid;
        $instance->payload = $payloadValid;

        return $instance;
    }


    public static function wrap(\Throwable $e) : static
    {
        $instance = new static();

        $instance->throwable = $e;
        //
        $instance->file = $e->getFile();
        $instance->line = $e->getLine();
        //
        $instance->code = $e->getCode();
        $instance->message = $e->getMessage();

        if ( $e instanceof MainExceptionInterface ) {
            $instance->payload = $e->getPayload();
        }

        return $instance;
    }
}
