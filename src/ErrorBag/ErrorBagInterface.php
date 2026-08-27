<?php

namespace Gzhegow\Ret\ErrorBag;

use Gzhegow\Ret\Error\ErrorInterface;
use Gzhegow\Ret\Error\SingleErrorInterface;
use Gzhegow\Ret\Error\AggregateErrorInterface;


interface ErrorBagInterface
{
    public function addError(ErrorInterface $error, array $tags = []);

    public function addParent(AggregateErrorInterface $parent, array $tags = []);


    /**
     * @return ErrorInterface[]
     */
    public function getErrors();

    /**
     * @return TaggedErrorInterface<ErrorInterface>[]
     */
    public function getTaggedErrors();


    /**
     * @return SingleErrorInterface[]
     */
    public function getChildren();

    /**
     * @return TaggedErrorInterface<SingleErrorInterface>[]
     */
    public function getTaggedChildren();


    /**
     * @return ErrorInterface[]
     */
    public function findErrors(array ...$orTagFilters);

    /**
     * @return TaggedErrorInterface<ErrorInterface>[]
     */
    public function findTaggedErrors(array ...$orTagFilters);


    /**
     * @return SingleErrorInterface[]
     */
    public function findChildren(array ...$orTagFilters);

    /**
     * @return TaggedErrorInterface<SingleErrorInterface>[]
     */
    public function findTaggedChildren(array ...$orTagFilters);
}
