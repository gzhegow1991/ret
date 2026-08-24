<?php

namespace Gzhegow\Ret\Exception;

use Gzhegow\Ret\Core\Error\Err;
use Gzhegow\Ret\Core\Error\ErrorInterface;
use Gzhegow\Ret\Core\Error\AggregateErrorInterface;


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

        $instance = new static($err->children ?? [], $err->getMessageArray());
        $instance->traceShift(1);

        return $instance;
    }


    /**
     * @param (\Throwable|ErrorInterface)[] $errors
     *
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(array $errors, $message = null)
    {
        if ( [] === $errors ) {
            throw new LogicException(
                [ 'The `errors` should be array, non-empty', $errors ]
            );

        } else {
            foreach ( $errors as $e ) {
                if ( ! (false
                    || $e instanceof \Throwable
                    || $e instanceof ErrorInterface
                ) ) {
                    throw new LogicException(
                        [
                            ''
                            . 'Each of `errors` should be instance one of: '
                            . '[ '
                            . implode(' ][ ', [
                                \Throwable::class,
                                ErrorInterface::class,
                            ])
                            . ' ]',
                            //
                            $errors,
                        ]
                    );
                }
            }
        }

        $this->code = -1;

        if ( null === $message ) {
            $this->message = "[ AGGREGATE EXCEPTION # TOTAL " . count($errors) . " ]";
            $this->payload = null;

        } else {
            $err = Err::message($message);

            $this->message = $err->message;
            $this->payload = $err->payload;
        }

        $this->errors = $errors;
    }


    public function __toString() : string
    {
        // > @gzhegow, it should be...
        // trigger_error('Casting exceptions using `__toString` is deprecated, they MUST lead to fatal consequences when they hit an unexpected place', E_USER_DEPRECATED);

        return $this->toString();
    }
}
