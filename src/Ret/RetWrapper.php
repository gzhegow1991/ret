<?php

namespace Gzhegow\Ret\Ret;

use Gzhegow\Ret\Ret;
use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Exception\RuntimeException;


class RetWrapper implements RetWrapperInterface
{
    /**
     * @var array{ 0: callable, 1: array }[]
     */
    protected $rules = [];

    /**
     * @var array{ 0?: mixed }
     */
    protected $value = [];
    /**
     * @var string|null
     */
    protected $file;
    /**
     * @var int|null
     */
    protected $line;


    /**
     * @return static
     */
    public static function new()
    {
        return new static();
    }

    protected function __construct()
    {
    }


    public function okSwitch(array $values)
    {
        $this->rules[] = [ [ $this, 'runOkSwitch' ], [ $values ] ];

        return $this;
    }

    public function okMatch(array $values)
    {
        $this->rules[] = [ [ $this, 'runOkMatch' ], [ $values ] ];

        return $this;
    }

    /**
     * @param string[] $classes
     */
    public function okIfClass(array $classes)
    {
        foreach ( $classes as $i => $c ) {
            if ( ! (is_string($c) && ('' !== $c)) ) {
                throw new LogicException(
                    [ 'Each of `classes` should be string, non-empty', $i, $classes ]
                );
            }
        }

        $this->rules[] = [ [ $this, 'runOkIfClass' ], [ $classes ] ];

        return $this;
    }

    /**
     * @param string[] $classes
     */
    public function okIfInstanceOf(array $classes)
    {
        foreach ( $classes as $i => $c ) {
            if ( ! (is_string($c) && ('' !== $c)) ) {
                throw new LogicException(
                    [ 'Each of `classes` should be string, non-empty', $i, $classes ]
                );
            }
        }

        $this->rules[] = [ [ $this, 'runOkIfInstanceOf' ], [ $classes ] ];

        return $this;
    }


    public function failSwitch(array $values)
    {
        $this->rules[] = [ [ $this, 'runFailSwitch' ], [ $values ] ];

        return $this;
    }

    public function failMatch(array $values)
    {
        $this->rules[] = [ [ $this, 'runFailMatch' ], [ $values ] ];

        return $this;
    }

    /**
     * @param string[] $classes
     */
    public function failIfClass(array $classes)
    {
        foreach ( $classes as $i => $c ) {
            if ( ! (is_string($c) && ('' !== $c)) ) {
                throw new LogicException(
                    [ 'Each of `classes` should be string, non-empty', $i, $classes ]
                );
            }
        }

        $this->rules[] = [ [ $this, 'runFailIfClass' ], [ $classes ] ];

        return $this;
    }

    /**
     * @param string[] $classes
     */
    public function failIfInstanceOf(array $classes)
    {
        foreach ( $classes as $i => $c ) {
            if ( ! (is_string($c) && ('' !== $c)) ) {
                throw new LogicException(
                    [ 'Each of `classes` should be string, non-empty', $i, $classes ]
                );
            }
        }

        $this->rules[] = [ [ $this, 'runFailIfInstanceOf' ], [ $classes ] ];

        return $this;
    }


    /**
     * @param callable $fn
     * @param array    $fnArgs
     *
     * @return static
     */
    public function useCallback($fn, array $fnArgs = [])
    {
        $this->rules[] = [ [ $this, 'runUseCallback' ], [ $fn, $fnArgs ] ];

        return $this;
    }


    protected function runOkSwitch(array $values)
    {
        $value = $this->value[0];

        if ( in_array($value, $values) ) {
            return Ret::ok($value);
        }

        return null;
    }

    protected function runOkMatch(array $values)
    {
        $value = $this->value[0];

        if ( in_array($value, $values, true) ) {
            return Ret::ok($value);
        }

        return null;
    }

    protected function runOkIfClass(array $classes)
    {
        $value = $this->value[0];

        if ( ! is_object($value) ) {
            return null;
        }

        $valueClass = get_class($value);

        if ( in_array($valueClass, $classes, true) ) {
            return Ret::ok($value);
        }

        return null;
    }

    protected function runOkIfInstanceOf(array $classes)
    {
        $value = $this->value[0];

        if ( ! is_object($value) ) {
            return null;
        }

        foreach ( $classes as $c ) {
            if ( is_a($value, $c) ) {
                return Ret::ok($value);
            }
        }

        return null;
    }


    protected function runFailSwitch(array $values)
    {
        $value = $this->value[0];

        foreach ( $values as $v ) {
            if ( $v == $value ) {
                return Ret::fail('The `value` is switch to: ' . $this->var_dump($v), $this->file, $this->line);
            }
        }

        return null;
    }

    protected function runFailMatch(array $values)
    {
        $value = $this->value[0];

        foreach ( $values as $v ) {
            if ( $v === $value ) {
                return Ret::fail('The `value` is match to: ' . $this->var_dump($v), $this->file, $this->line);
            }
        }

        return null;
    }

    protected function runFailIfClass(array $classes)
    {
        $value = $this->value[0];

        if ( ! is_object($value) ) {
            return null;
        }

        $valueClass = get_class($value);

        foreach ( $classes as $c ) {
            if ( $c === $valueClass ) {
                return Ret::fail('The `value` class is match to: ' . $c, $this->file, $this->line);
            }
        }

        return null;
    }

    protected function runFailIfInstanceOf(array $classes)
    {
        $value = $this->value[0];

        if ( ! is_object($value) ) {
            return null;
        }

        foreach ( $classes as $c ) {
            if ( is_a($value, $c) ) {
                return Ret::fail('The `value` class is instanceof: ' . $c, $this->file, $this->line);
            }
        }

        return null;
    }


    protected function runUseCallback($fn, array $fnArgs = [])
    {
        array_unshift($fnArgs, $this->value);

        $res = call_user_func_array($fn, $fnArgs);

        if ( null === $res ) {
            return null;

        } elseif ( $res instanceof Ret ) {
            return $res;
        }

        throw new RuntimeException([ 'The `fn` must return Ret or NULL', $fn, $fnArgs ]);
    }


    protected function var_dump($value) : string
    {
        if ( is_null($value) ) {
            return '{ NULL }';
        }

        if ( is_bool($value) ) {
            return $value ? '{ bool # true }' : '{ bool # false }';
        }

        if ( is_string($value) ) {
            $len = preg_match_all('/./us', $value);

            if ( $len <= 15 ) {
                return "{ string({$len}) # '{$value}' }";
            }

            $truncated = preg_replace('/^.{15}\K.*/us', '...', $value);

            return "{ string({$len}) # '{$truncated}...' }";
        }

        if ( is_array($value) ) {
            return '{ array(' . count($value) . ') }';
        }

        if ( is_object($value) ) {
            return '{ object(' . get_class($value) . ') #' . spl_object_id($value) . ' }';
        }

        if ( is_resource($value) ) {
            return '{ resource(' . get_resource_type($value) . ') }';
        }

        return (string) $value;
    }


    public function run($value, $file = null, $line = null) : Ret
    {
        $instance = clone $this;
        $instance->value[0] = $value;
        $instance->file = $file;
        $instance->line = $line;

        foreach ( $this->rules as [$fn, $fnArgs] ) {
            $ret = call_user_func_array([ $instance, $fn[1] ], $fnArgs);

            if ( $ret instanceof Ret ) {
                return $ret;
            }
        }

        return Ret::ok($value);
    }
}
