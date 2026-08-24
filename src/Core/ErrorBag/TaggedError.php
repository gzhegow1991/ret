<?php

namespace Gzhegow\Ret\Core\ErrorBag;

use Gzhegow\Ret\Core\Error\ErrorInterface;


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
