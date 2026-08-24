<?php

namespace Gzhegow\Ret\Core\Ret;

use Gzhegow\Ret\Exception\LogicException;
use Gzhegow\Ret\Core\Error\ErrorInterface;
use Gzhegow\Ret\Exception\RuntimeException;


class RetWrapper
{
    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var mixed
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


    public function failIfSwitch(array $values)
    {
        $this->rules[] = [ 'runFailIfSwitch', $values ];

        return $this;
    }

    public function failIfMatch(array $values)
    {
        $this->rules[] = [ 'runFailIfMatch', $values ];

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

        $this->rules[] = [ 'runFailIfClass', $classes ];

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

        $this->rules[] = [ 'runFailIfInstanceOf', $classes ];

        return $this;
    }

    /**
     * @param callable $fn
     * @param array    $fnArgs
     *
     * @return static
     */
    public function failIfCallback($fn, array $fnArgs = [])
    {
        $this->rules[] = [ 'runFailIfCallback', $fn, $fnArgs ];

        return $this;
    }


    protected function runFailIfSwitch(array $values)
    {
        $value = $this->value[0];

        foreach ( $values as $v ) {
            if ( $v == $value ) {
                return Ret::fail('The `value` is switch to: ' . $this->var_dump($v), $this->file, $this->line);
            }
        }

        return null;
    }

    protected function runFailIfMatch(array $values)
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

    protected function runFailIfCallback($fn, array $fnArgs = [])
    {
        array_unshift($fnArgs, $this->value);

        $ret = call_user_func_array($fn, $fnArgs);

        if ( null === $ret ) {
            return null;

        } elseif ( ($ret instanceof Ret) && $ret->isFail() ) {
            return $ret;
        }

        throw new RuntimeException([ 'The `fn` must return failed Ret or NULL', $fn, $fnArgs ]);
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

        foreach ( $this->rules as $rule ) {
            $fn = array_shift($rule);
            $fnArgs = $rule;

            $ret = call_user_func_array([ $instance, $fn ], $fnArgs);

            if ( ($ret instanceof Ret) && $ret->isFail() ) {
                return $ret;
            }
        }

        return Ret::ok($value);
    }
}
