<?php

namespace Gzhegow\Ret\Error\PHP8;

use Gzhegow\Ret\Err;
use Gzhegow\Ret\ErrorMessage\ErrorMessage;
use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Error\AggregateErrorInterface;
use Gzhegow\Ret\Exception\AggregateExceptionInterface;


class AggregateError extends AbstractError implements AggregateErrorInterface
{
    /**
     * @var ErrorInterface[]
     */
    public array $errors;


    /**
     * @param (\Throwable|ErrorInterface)[] $children
     * @param string|null                   $file
     * @param int|null                      $line
     * @param mixed|null                    $message
     *
     * @return static
     */
    public static function make(
        array $children,
        ?string $file = null, ?int $line = null, $message = null
    ) : static
    {
        if ( [] === $children ) {
            throw new \LogicException('The `children` should be array, non-empty');
        }

        $errorsArray = [];

        foreach ( $children as $c ) {
            if ( $c instanceof ErrorInterface ) {
                $errorsArray[] = $c;

            } elseif ( $c instanceof \Throwable ) {
                $errorsArray[] = Err::wrap($c);

            } else {
                throw new \LogicException(
                    ''
                    . 'Each of `children` should be instance one of: '
                    . '[ '
                    . implode(' ][ ', [
                        ErrorInterface::class,
                        \Throwable::class,
                    ])
                    . ' ]'
                );
            }
        }

        $instance = new static();

        $instance->errors = $errorsArray;
        //
        $instance->file = $file ?? 'unknown';
        $instance->line = $line ?? 0;
        //
        $instance->code = -1;

        if ( null === $message ) {
            $instance->message = "[ AGGREGATE ERROR # TOTAL " . count($children) . " ]";
            $instance->payload = null;

        } else {
            $err = ErrorMessage::fromMessage($message);

            $instance->message = $err->message;
            $instance->payload = $err->payload;
        }

        return $instance;
    }

    protected function __construct()
    {
    }


    public static function wrap(AggregateExceptionInterface $e) : static
    {
        $instance = new static();

        $instance->throwable = $e;
        //
        $instance->errors = $e->getErrors();
        //
        $instance->file = $e->getFile();
        $instance->line = $e->getLine();
        //
        $instance->code = $e->getCode();
        $instance->message = $e->getMessage();
        //
        $instance->payload = $e->getPayload();

        return $instance;
    }
}
