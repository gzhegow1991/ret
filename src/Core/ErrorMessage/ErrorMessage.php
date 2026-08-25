<?php

namespace Gzhegow\Ret\Core\ErrorMessage;

use Gzhegow\Ret\Core\Err;
use Gzhegow\Ret\Core\Error\ErrorInterface;
use Gzhegow\Ret\Exception\ExceptionInterface;


class ErrorMessage implements ErrorMessageInterface
{
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


    /**
     * @param static|mixed $from
     *
     * @return static
     */
    public static function fromMixed($from)
    {
        if ( $from instanceof static ) {
            return $from;
        }

        $payloadValid = null;

        $maybeCode = null;
        $maybeMessage = null;

        if ( is_int($from) ) {
            $maybeCode = $from;

        } elseif ( is_string($from) ) {
            $maybeMessage = $from;

        } elseif ( is_array($from) ) {
            $maybeCode = $from[''] ?? null;
            $maybeMessage = $from[0] ?? null;
            unset($from['']);
            unset($from[0]);

            if ( [] !== $from ) {
                $payloadValid = $from;
            }

        } elseif ( is_object($from) ) {
            // > \BackedEnum
            $maybeCode = $from;
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

        $instance->code = $codeValid;
        $instance->message = $messageValid;
        $instance->payload = $payloadValid;

        return $instance;
    }

    /**
     * @param static|mixed $from
     *
     * @return static
     */
    public static function fromCode($from)
    {
        if ( $from instanceof static ) {
            return $from;
        }

        $payloadValid = null;

        if ( ! is_array($from) ) {
            $maybeCode = $from;

        } else {
            $maybeCode = $from[0] ?? null;
            unset($from[0]);

            if ( [] !== $from ) {
                $payloadValid = $from;
            }
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

        $instance->code = $codeValid;
        $instance->message = $messageValid;
        $instance->payload = $payloadValid;

        return $instance;
    }

    /**
     * @param mixed $from
     *
     * @return static
     */
    public static function fromMessage($from)
    {
        if ( $from instanceof static ) {
            $from->code = -1;

            return $from;
        }

        $payloadValid = null;

        if ( ! is_array($from) ) {
            $maybeMessage = $from;

        } else {
            $maybeMessage = $from[0] ?? null;
            unset($from[0]);

            if ( [] !== $from ) {
                $payloadValid = $from;
            }
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

        $instance->code = $codeValid;
        $instance->message = $messageValid;
        $instance->payload = $payloadValid;

        return $instance;
    }


    /**
     * @return static
     */
    public static function fromError(ErrorInterface $from)
    {
        $instance = new static();
        $instance->message = $from->message;
        $instance->payload = $from->payload;

        $eCode = Err::getCode($from);
        $instance->code = $eCode;

        return $instance;
    }

    /**
     * @return static
     */
    public static function fromThrowable(\Throwable $from)
    {
        $instance = new ErrorMessage();
        $instance->message = $from->getMessage();

        $eCode = Err::getCode($from);
        $instance->code = $eCode;

        if ( $from instanceof ExceptionInterface ) {
            $instance->payload = $from->getPayload();
        }

        return $instance;
    }


    protected function __construct()
    {
    }
}
