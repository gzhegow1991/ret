<?php

namespace Gzhegow\Ret\ErrorBag\PHP8;

use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\ErrorBag\TaggedErrorInterface;


class TaggedError implements TaggedErrorInterface
{
    public ErrorInterface $error;

    /**
     * @var array<string, bool>
     */
    public array $tags = [];


    /**
     * @param ErrorInterface           $error
     * @param array<string, bool>|null $tags
     *
     * @return static
     */
    public static function make(ErrorInterface $error, ?array $tags = null)
    {
        $tags = $tags ?? [];

        $instance = new static();
        $instance->error = $error;
        $instance->tags = $tags;

        return $instance;
    }

    protected function __construct()
    {
    }
}
