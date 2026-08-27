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
}
