<?php

namespace Gzhegow\Ret\Ret;

use Gzhegow\Ret\Ret;


interface RetWrapperInterface
{
    /**
     * @return static
     */
    public function okSwitch(array $values);

    /**
     * @return static
     */
    public function okMatch(array $values);


    /**
     * @param string[] $classes
     *
     * @return static
     */
    public function okIfClass(array $classes);

    /**
     * @param string[] $classes
     *
     * @return static
     */
    public function okIfInstanceOf(array $classes);


    /**
     * @return static
     */
    public function failSwitch(array $values);

    /**
     * @return static
     */
    public function failMatch(array $values);


    /**
     * @param string[] $classes
     *
     * @return static
     */
    public function failIfClass(array $classes);

    /**
     * @param string[] $classes
     *
     * @return static
     */
    public function failIfInstanceOf(array $classes);


    /**
     * @param callable $fn
     * @param array    $fnArgs
     *
     * @return static
     */
    public function useCallback($fn, array $fnArgs = []);


    public function run($value, $file = null, $line = null) : Ret;
}
