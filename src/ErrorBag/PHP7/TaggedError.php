<?php

namespace Gzhegow\Ret\ErrorBag\PHP7;

use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\ErrorBag\TaggedErrorInterface;


class TaggedError implements TaggedErrorInterface
{
    /**
     * @var ErrorInterface
     */
    public $error;
    /**
     * @var array<string, bool>
     */
    public $tags = [];


    /**
     * @param ErrorInterface           $error
     * @param array<string, bool>|null $tags
     *
     * @return static
     */
    public static function make($error, $tags = null)
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
