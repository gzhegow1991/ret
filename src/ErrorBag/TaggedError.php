<?php

namespace Gzhegow\Ret\ErrorBag;

use Gzhegow\Ret\Error\ErrorInterface;


/**
 * @template-covariant T of ErrorInterface
 */
class TaggedError implements TaggedErrorInterface
{
    /**
     * @var T
     */
    public $error;
    /**
     * @var array<string, bool>
     */
    public $tags = [];


    /**
     * @param T                        $error
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
