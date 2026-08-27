<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\ErrorMessage\ErrorMessage;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Error\AggregateErrorInterface;


class AggregateException extends \Exception implements AggregateExceptionInterface
{
    use AggregateExceptionTrait;


    /**
     * @return static
     */
    public static function fromErr(AggregateErrorInterface $err)
    {
        if ( $ex = $err->throwable ) {
            if ( $ex instanceof static ) {
                return $ex;
            }
        }

        $instance = new static($err->errors, Err::getMessage($err));
        $instance->traceShift(1);

        return $instance;
    }


    /**
     * @param (\Throwable|ErrorInterface)[] $children
     * @param mixed|null                    $message
     *
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(array $children, $message = null)
    {
        if ( [] === $children ) {
            throw new LogicException(
                [ 'The `children` should be array, non-empty', $children ]
            );
        }

        $errorsArray = [];

        foreach ( $children as $i => $c ) {
            if ( $c instanceof ErrorInterface ) {
                $errorsArray[] = $c;

            } elseif ( $c instanceof \Throwable ) {
                $errorsArray[] = Err::wrap($c);

            } else {
                throw new LogicException(
                    [
                        ''
                        . 'Each of `children` should be instance one of: '
                        . '[ '
                        . implode(' ][ ', [
                            \Throwable::class,
                            ErrorInterface::class,
                        ])
                        . ' ]',
                        //
                        $i,
                        $children,
                    ]
                );
            }
        }

        $this->errors = $errorsArray;

        $this->code = -1;

        if ( null === $message ) {
            $this->message = "[ AGGREGATE EXCEPTION # TOTAL " . count($children) . " ]";
            $this->payload = null;

        } else {
            $msg = ErrorMessage::fromMessage($message);

            $this->message = $msg->message;
            $this->payload = $msg->payload;
        }
    }


    public function __toString() : string
    {
        // > @gzhegow, it should be...
        // trigger_error('Casting exceptions using `__toString` is deprecated, they MUST lead to fatal consequences when they hit an unexpected place', E_USER_DEPRECATED);

        return $this->toString();
    }
}
