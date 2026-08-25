<?php

error_reporting(E_ALL);

define('__DIR_ROOT__', __DIR__ . '/..');


// >>> ТЕСТЫ

$theDebug = \Gzhegow\Lib\Lib::debug();
$theTest = \Gzhegow\Lib\Lib::test();

$testN = 0;

// > TEST
// > преобразуем стандартный warning в объект ошибки
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    @trigger_error('Hello');
    $err = error_get_last();

    $e = \Gzhegow\Ret\Core\Err::triggered(
        $err['type'], $err['message'],
        $err['file'], $err['line']
    );

    $theDebug->dump_value([ $e, $e->message ]);
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80000, '
"TEST ' . $testN . '"

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "Hello" ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80000, '
"TEST ' . $testN . '"

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "Hello" ]
');
$test->run();


// > TEST
// > ошибки можно создавать по разному, а главное - удобно
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    $ee = [];
    $ee[] = $e = \Gzhegow\Ret\Core\Err::message('message'); // > file: 'unknown', line: 0
    $ee[] = $e = \Gzhegow\Ret\Core\Err::message('message', __FILE__, __LINE__);
    $ee[] = $e = \Gzhegow\Ret\Core\Err::message([ 0 => 'message', 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
    foreach ( $ee as $e ) {
        $theDebug->dump_value([ $e, $e->message, $e->code, $e->payload ]);
    }
    echo "\n";

    $ee = [];
    $ee[] = $e = \Gzhegow\Ret\Core\Err::code(32767, __FILE__, __LINE__);
    $ee[] = $e = \Gzhegow\Ret\Core\Err::code([ 0 => 32767, 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
    $ee[] = $e = \Gzhegow\Ret\Core\Err::code([ 0 => 'MY_CODE', 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
    foreach ( $ee as $e ) {
        $theDebug->dump_value([ $e, $e->message, $e->code, $e->payload ]);
    }
    echo "\n";

    $ee = [];
    $ee[] = $e = \Gzhegow\Ret\Core\Err::new(32767, __FILE__, __LINE__);     // > int, seems as code
    $ee[] = $e = \Gzhegow\Ret\Core\Err::new('message', __FILE__, __LINE__); // > string, seems as message
    $ee[] = $e = \Gzhegow\Ret\Core\Err::new([ '' => 32767, 0 => 'message', 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
    foreach ( $ee as $e ) {
        $theDebug->dump_value([ $e, $e->message, $e->code, $e->payload ]);
    }
    echo "\n";

    if ( PHP_VERSION_ID >= 80100 ) {
        require_once __DIR_ROOT__ . '/tests/src/MyEnum.php';

        // > receives code and message
        $ee = [];
        $ee[] = $e = \Gzhegow\Ret\Core\Err::code(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Err::code([ 0 => \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Err::new(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Err::new([ '' => \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, 0 => null, 'data' => 1, 1 => 'my_data2' ], __FILE__, __LINE__);
        foreach ( $ee as $e ) {
            $theDebug->dump_value([ $e, $e->message, $e->code, $e->payload ]);
        }
        echo "\n";

        // > receives only message, code will be -1
        $ee = [];
        $ee[] = $e = \Gzhegow\Ret\Core\Err::message(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Err::message([ 0 => \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, 'data' => 1, 'my_data' ], __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Err::new([ '' => null, 0 => \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
        foreach ( $ee as $e ) {
            $theDebug->dump_value([ $e, $e->message, $e->code, $e->payload ]);
        }
        echo "\n";
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", -1, "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "32767", 32767, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "32767", 32767, "{ array(2) }" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "MY_CODE", "MY_CODE", "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "32767", 32767, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", 32767, "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", "{ object # Gzhegow\Ret\Tests\MyEnum }", NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", "{ object # Gzhegow\Ret\Tests\MyEnum }", "{ array(2) }" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", "{ object # Gzhegow\Ret\Tests\MyEnum }", NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", "{ object # Gzhegow\Ret\Tests\MyEnum }", "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", -1, "{ array(2) }" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", -1, "{ array(2) }" ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", -1, "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "32767", 32767, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "32767", 32767, "{ array(2) }" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "MY_CODE", "MY_CODE", "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "32767", 32767, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", 32767, "{ array(2) }" ]
');
$test->run();


// > TEST
// > ошибки можно (и нужно) объединять в агрегаты, подбивая бизнес-задачу
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    $ee = [];
    $ee[] = $e = \Gzhegow\Ret\Core\Err::message('message 1');
    $ee[] = $e = \Gzhegow\Ret\Core\Err::message('message 2');
    $ee[] = $e = \Gzhegow\Ret\Core\Err::message('message 3');

    // $ee = \Gzhegow\Ret\Core\Error\Err::aggregate($ee, __FILE__, __LINE__); // > will generate generic message
    $ee = \Gzhegow\Ret\Core\Err::aggregate($ee, __FILE__, __LINE__, 'My aggregate message');

    $theDebug->dump_value([ $ee, $ee->message, $ee->code, $ee->payload ]);
    $theDebug->dump_array_multiline($ee->errors);
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "My aggregate message", -1, NULL ]
###
[
  "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }",
  "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }",
  "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }"
]
###
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "My aggregate message", -1, NULL ]
###
[
  "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }",
  "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }",
  "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }"
]
###
');
$test->run();


// > TEST
// > познакомьтесь, ErrorBag
// > мы отлично применили его при batch-обработке очередей, выгружаешь 100 задач, выполняешь 10 запросов (дедубликация), сохраняешь 10 цепочек. По тегам превращаешь 10 в 100 и сохраняешь отчет по 100 задачам
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    $ee = [];
    $ee[] = $e1 = \Gzhegow\Ret\Core\Err::message('Hello 1', __FILE__, __LINE__);
    $ee[] = $e2 = \Gzhegow\Ret\Core\Err::message('Hello 2', __FILE__, __LINE__);

    $ee = \Gzhegow\Ret\Core\Err::aggregate($ee, __FILE__, __LINE__);

    $bag = new \Gzhegow\Ret\Core\ErrorBag\ErrorBag();

    // > add error and mark it using tags
    $bag->addError($e1, [ 'tag1' ]);
    $bag->addError($e2, [ 'tag2' ]);

    // > append new tags to already added errors
    $bag->addError($e1, [ 'tag3' ]);

    // > add parent, recursive extract single nodes, append new tags to children, so they have 'tag1' and 'tag3' in both cases
    $bag->addParent($ee, [ 'tag3' ]);

    // > get errors
    foreach ( $bag->getErrors() as $e ) {
        $theDebug->dump_value([ $e, $e->message, $e->code, $e->payload ]);
    }
    foreach ( $bag->getTaggedErrors() as $e ) {
        $theDebug->dump_array([ $e, $e->error, $e->tags ], 2);
    }
    echo "\n";

    // > get only children if errors was aggregates, children NEVER contain AggregateErrorInterface
    foreach ( $bag->getChildren() as $e ) {
        $theDebug->dump_value([ $e, $e->message, $e->code, $e->payload ]);
    }
    foreach ( $bag->getTaggedChildren() as $e ) {
        $theDebug->dump_array([ $e, $e->error, $e->tags ], 2);
    }
    echo "\n";

    // > find errors by tags using and/or logic for search
    $query = [ [ 'tag3' => true, 'tag2' => false ], [ 'tag2' => true ] ];
    foreach ( $bag->findErrors(...$query) as $e ) {
        $theDebug->dump_value([ $e, $e->message, $e->code, $e->payload ]);
    }
    foreach ( $bag->findTaggedErrors(...$query) as $e ) {
        $theDebug->dump_array([ $e, $e->error, $e->tags ], 2);
    }
    echo "\n";

    // > find children by tags using and/or logic for search
    $query = [ [ 'tag3' => true, 'tag2' => false ], [ 'tag2' => true ] ];
    foreach ( $bag->findChildren(...$query) as $e ) {
        $theDebug->dump_value([ $e, $e->message, $e->code, $e->payload ]);
    }
    foreach ( $bag->findTaggedChildren(...$query) as $e ) {
        $theDebug->dump_array([ $e, $e->error, $e->tags ], 2);
    }
    echo "\n";
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Hello 1", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Hello 2", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "[ AGGREGATE ERR # TOTAL 2 ]", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", [ "tag1" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", [ "tag2" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", [ "tag3" => TRUE ] ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Hello 1", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Hello 2", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", [ "tag1" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", [ "tag2" => TRUE, "tag3" => TRUE ] ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Hello 1", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "[ AGGREGATE ERR # TOTAL 2 ]", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Hello 2", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", [ "tag1" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", [ "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", [ "tag2" => TRUE, "tag3" => TRUE ] ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Hello 1", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Hello 2", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", [ "tag1" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", [ "tag2" => TRUE, "tag3" => TRUE ] ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "Hello 1", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "Hello 2", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "[ AGGREGATE ERR # TOTAL 2 ]", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", [ "tag1" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", [ "tag2" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", [ "tag3" => TRUE ] ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "Hello 1", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "Hello 2", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", [ "tag1" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", [ "tag2" => TRUE, "tag3" => TRUE ] ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "Hello 1", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "[ AGGREGATE ERR # TOTAL 2 ]", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "Hello 2", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", [ "tag1" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", [ "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", [ "tag2" => TRUE, "tag3" => TRUE ] ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "Hello 1", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "Hello 2", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", [ "tag1" => TRUE, "tag3" => TRUE ] ]
[ "{ object # Gzhegow\Ret\Core\ErrorBag\TaggedError }", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", [ "tag2" => TRUE, "tag3" => TRUE ] ]
');
$test->run();


// > TEST
// > познакомьтесь: Ret (Result<T,E> # Result/Either)
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    // > as first, you should write own function using Ret::ok() and Ret::fail() in returns
    $fnStringNotEmpty = function ($value) : \Gzhegow\Ret\Core\Ret\Ret {
        if ( '' === $value ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ], __FILE__, __LINE__);
        }

        if ( ! is_string($value) ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ], __FILE__, __LINE__);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($value);
    };

    $value = 123;
    $ret = $fnStringNotEmpty($value);

    // > magic! the function becomes context-controllable - you may throw, get error, or return fallback depends on your needs
    // > you write code once, but reuse it in few scenarios without copy/rewrite
    try {
        // > @return mixed|null or @throws \RuntimeException
        // > NULL is returned if you call Ret::new()->orThrow() (ret contains no value/errors)
        $maybeResultMaybeNull = $ret->orThrow([ 'The password is invalid', $value ]);
    }
    catch ( \Gzhegow\Ret\Exception\ExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage(), $e->getPayload() ], 2); // > [ object, 'The password is invalid', [ 1 => 123 ] ]
    }

    // > @return mixed|ErrorInterface or @throws \RuntimeException
    // > THROW if you call Ret::new()->orError() (ret contains no value/errors)
    $maybeResultMaybeError = $ret->orError([ 'The password is invalid', $value ], __FILE__, __LINE__);
    $theDebug->dump_array([ $maybeResultMaybeError, $maybeResultMaybeError->message, $maybeResultMaybeError->payload ], 2);                    // > [ object, 'The password is invalid', [ 1 => 123 ] ]

    // > NAN if error, FALSE if empty
    $maybeResultMaybeNanMaybeFalse = $ret->orFallback($fallback = NAN, $default = false);
    $theDebug->dump_value($maybeResultMaybeNanMaybeFalse);

    // > NULL if error, NULL if empty
    $maybeResultMaybeNull = $ret->orNull();
    $theDebug->dump_value($maybeResultMaybeNull);
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => 123 ] ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The password is invalid", [ 1 => 123 ] ]
NAN
NULL
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => 123 ] ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The password is invalid", [ 1 => 123 ] ]
NAN
NULL
');
$test->run();


// > TEST
// > так-то у ошибок бывают коды, их удобно проверять IF-ом
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    $e1 = \Gzhegow\Ret\Core\Err::code(1);
    $e2 = \Gzhegow\Ret\Core\Err::code(2);
    $ee = \Gzhegow\Ret\Core\Err::aggregate([ $e1, $e2 ]);
    $theDebug->dump_value([ 'e1', '1', \Gzhegow\Ret\Core\Err::isCode($e1, 1) ]);
    $theDebug->dump_value([ 'e2', '2', \Gzhegow\Ret\Core\Err::isCode($e2, 2) ]);
    $theDebug->dump_value([ 'ee', '1', \Gzhegow\Ret\Core\Err::isCode($ee, 1) ]);
    $theDebug->dump_value([ 'ee', '2', \Gzhegow\Ret\Core\Err::isCode($ee, 2) ]);
    echo "\n";

    $ret1 = \Gzhegow\Ret\Core\Ret\Ret::fail(1);
    $ret2 = \Gzhegow\Ret\Core\Ret\Ret::fail(2);
    $rret = \Gzhegow\Ret\Core\Ret\Ret::new()
        ->or($ret1)
        ->or($ret2)
    ;
    $theDebug->dump_value([ 'ret1', '1', \Gzhegow\Ret\Core\Err::isCode($ret1, 1) ]);
    $theDebug->dump_value([ 'ret2', '2', \Gzhegow\Ret\Core\Err::isCode($ret2, 2) ]);
    $theDebug->dump_value([ 'rret', '1', \Gzhegow\Ret\Core\Err::isCode($rret, 1) ]);
    $theDebug->dump_value([ 'rret', '2', \Gzhegow\Ret\Core\Err::isCode($rret, 2) ]);
    echo "\n";

    if ( PHP_VERSION_ID >= 80100 ) {
        $e1 = \Gzhegow\Ret\Core\Err::code(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_1);
        $e2 = \Gzhegow\Ret\Core\Err::code(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_2);
        $ee = \Gzhegow\Ret\Core\Err::aggregate([ $e1, $e2 ]);
        $theDebug->dump_value([ 'e1', 'ERR_FAIL_1', \Gzhegow\Ret\Core\Err::isCode($e1, \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_1) ]);
        $theDebug->dump_value([ 'e2', 'ERR_FAIL_2', \Gzhegow\Ret\Core\Err::isCode($e2, \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_2) ]);
        $theDebug->dump_value([ 'ee', 'ERR_FAIL_1', \Gzhegow\Ret\Core\Err::isCode($ee, \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_1) ]);
        $theDebug->dump_value([ 'ee', 'ERR_FAIL_2', \Gzhegow\Ret\Core\Err::isCode($ee, \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_2) ]);
        echo "\n";

        $ret1 = \Gzhegow\Ret\Core\Ret\Ret::fail(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_1);
        $ret2 = \Gzhegow\Ret\Core\Ret\Ret::fail(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_2);
        $rret = \Gzhegow\Ret\Core\Ret\Ret::new()
            ->or($ret1)
            ->or($ret2)
        ;
        $theDebug->dump_value([ 'ret1', 'ERR_FAIL_1', \Gzhegow\Ret\Core\Err::isCode($ret1, \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_1) ]);
        $theDebug->dump_value([ 'ret2', 'ERR_FAIL_2', \Gzhegow\Ret\Core\Err::isCode($ret2, \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_2) ]);
        $theDebug->dump_value([ 'rret', 'ERR_FAIL_1', \Gzhegow\Ret\Core\Err::isCode($rret, \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_1) ]);
        $theDebug->dump_value([ 'rret', 'ERR_FAIL_2', \Gzhegow\Ret\Core\Err::isCode($rret, \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL_2) ]);
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

[ "e1", "1", TRUE ]
[ "e2", "2", TRUE ]
[ "ee", "1", FALSE ]
[ "ee", "2", FALSE ]

[ "ret1", "1", TRUE ]
[ "ret2", "2", TRUE ]
[ "rret", "1", FALSE ]
[ "rret", "2", FALSE ]

[ "e1", "ERR_FAIL_1", TRUE ]
[ "e2", "ERR_FAIL_2", TRUE ]
[ "ee", "ERR_FAIL_1", FALSE ]
[ "ee", "ERR_FAIL_2", FALSE ]

[ "ret1", "ERR_FAIL_1", TRUE ]
[ "ret2", "ERR_FAIL_2", TRUE ]
[ "rret", "ERR_FAIL_1", FALSE ]
[ "rret", "ERR_FAIL_2", FALSE ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

[ "e1", "1", TRUE ]
[ "e2", "2", TRUE ]
[ "ee", "1", FALSE ]
[ "ee", "2", FALSE ]

[ "ret1", "1", TRUE ]
[ "ret2", "2", TRUE ]
[ "rret", "1", FALSE ]
[ "rret", "2", FALSE ]
');
$test->run();


// > TEST
// > волшебство Ret
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    /**
     * @return \Gzhegow\Ret\Core\Ret\Ret<string>
     * @noinspection PhpDocMissingThrowsInspection
     */
    $fnToStringNotEmpty = function ($value) : \Gzhegow\Ret\Core\Ret\Ret {
        set_error_handler(static function (...$args) {
            // > btw, triggered exception has same argument order as `set_error_handler` callback
            // > ps. `[5]errcontext` is deprecated since PHP 7.2.0, and removed in 8.0.0
            $ex = (PHP_VERSION_ID >= 80000)
                ? new \Gzhegow\Ret\Exception\TriggeredException(...$args)
                : new \Gzhegow\Ret\Exception\TriggeredException(...array_slice($args, 0, 4));

            throw $ex;
        });
        try {
            $valueString = (string) $value;
        }
        catch ( \Throwable $e ) {
            // > you may pass exceptions directly to Ret::fail

            // > will use FILE_LINE from exception (wrap original \Throwable as ErrorInterface)
            // return \Gzhegow\Ret\Core\Ret\Ret::fail($e);

            // > wrap original \Throwable, AND THEN wrap ErrorInterface with AggregateErrorInterface with new file/line, as breadcrumbs
            return \Gzhegow\Ret\Core\Ret\Ret::fail($e, __FILE__, __LINE__);
        }
        finally {
            restore_error_handler();
        }

        if ( '' === $value ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ], __FILE__, __LINE__);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($valueString);
    };

    $value = new \stdClass();
    $ret = $fnToStringNotEmpty($value);

    try {
        // > @return mixed|null or @throws \RuntimeException, actually null is returned if you call Ret::new()->orThrow() (no value and no errors)
        $maybeResultMaybeNull = $ret->orThrow([ 'The password is invalid', $value ]);
    }
    catch ( \Gzhegow\Ret\Exception\ExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage(), $e->getPayload() ], 2); // > [ object, 'The password is invalid', [ 1 => 123 ] ]
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => "{ object # stdClass }" ] ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => "{ object # stdClass }" ] ]
');
$test->run();


// > TEST
// > ещё немножко волшебства Ret
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    $fnArrayNotEmpty = function ($value) : \Gzhegow\Ret\Core\Ret\Ret {
        if ( [] === $value ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be array, non-empty', $value ]);
        }
        if ( ! is_array($value) ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be array, non-empty', $value ]);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($value);
    };

    $fnString = function ($value) : \Gzhegow\Ret\Core\Ret\Ret {
        if ( ! is_string($value) ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ]);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($value);
    };

    // > reuse previously created function is pass its ret to parent scope!
    $fnStringNotEmpty = function ($value) use ($fnString) : \Gzhegow\Ret\Core\Ret\Ret {
        if ( '' === $value ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ]);
        }

        $ret = $fnString($value);

        // > the `$valueString` is filled only if condition ->isOk() resulted to TRUE
        if ( ! $ret->isOk([ &$valueString ]) ) {
            // > pass old ret object to parent scope without changes
            return \Gzhegow\Ret\Core\Ret\Ret::pass($ret);

            // > will wrap old object with instance that implements AggregateErrorInterface
            // return \Gzhegow\Ret\Core\Ret\Ret::pass($ret, 'My custom message if needed', __FILE__, __LINE__);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($valueString);
    };

    // > how about `chaining`...
    $ret = \Gzhegow\Ret\Core\Ret\Ret::new();
    //
    $value = 123;
    $valueValid = null
        ?? $fnStringNotEmpty($value)->fillInto($ret)->orNull()
        ?? $fnArrayNotEmpty($value)->fillInto($ret)->orNull();;
    //
    $theDebug->dump_value($valueValid); // > null, cause `123` is not a string or an array
    echo "\n";

    try {
        $ret->orThrow([ 'The password is invalid', $value ]);
    }
    catch ( \Gzhegow\Ret\Exception\AggregateExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage(), $e->getPayload() ], 2); // > [ object, 'The password is invalid', [ 1 => 123 ] ]

        foreach ( $e->getErrorsRecursive() as $path => $e ) {
            $theDebug->dump_array([ implode('.', $path), $e, $e->message, $e->payload ], 2);
        }
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

NULL

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => 123 ] ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "The `value` should be string, non-empty", [ 1 => 123 ] ]
[ "1", "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "The `value` should be array, non-empty", [ 1 => 123 ] ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

NULL

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => 123 ] ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "The `value` should be string, non-empty", [ 1 => 123 ] ]
[ "1", "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "The `value` should be array, non-empty", [ 1 => 123 ] ]
');
$test->run();


// > TEST
// > обнаглеем в конец вместе с Ret
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    // > we may prefer safe call internal PHP functions that may accidentally trigger warning
    $ret = \Gzhegow\Ret\Core\Ret\Ret::fnCall('fopen', [ '1.txt', 'r' ]);
    try {
        $ret->orThrow();
    }
    catch ( \Gzhegow\Ret\Exception\AggregateExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage() ], 2);

        foreach ( $e->getErrorsRecursive() as $path => $ee ) {
            $theDebug->dump_array([ implode('.', $path), $ee, $ee->message ], 2);
        }
    }
    echo "\n";

    // > ok, let just create \Closure with this function to be shorter
    $fnFopen = \Gzhegow\Ret\Core\Ret\Ret::fn('fopen');

    // > old good "keep first success" but without null-coalesce
    $ret = \Gzhegow\Ret\Core\Ret\Ret::new()
        ->or($fnFopen([ '1.txt', 'r' ]))
        ->or($fnFopen([ '2.txt', 'r' ]))
        ->or($fnFopen([ '3.txt', 'r' ]))
    ;
    try {
        $maybeValueMaybeNull = $ret->orThrow();
    }
    catch ( \Gzhegow\Ret\Exception\AggregateExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage() ], 2);

        foreach ( $e->getErrorsRecursive() as $path => $ee ) {
            $theDebug->dump_array([ implode('.', $path), $ee, $ee->message ], 2);
        }
    }
    echo "\n";

    // > `first success` makes $ret succesful
    $ret = \Gzhegow\Ret\Core\Ret\Ret::new()
        ->or($fnFopen([ '1.txt', 'r' ]))  // > errors: 1, value: empty
        ->or($fnFopen([ __FILE__, 'r' ])) // > errors: 0, value: resource
        ->or($fnFopen([ '3.txt', 'r' ])) // > errors: 0, value: resource, ignored cause of value exists
    ;
    $fh = $ret->orThrow();
    $theDebug->dump_value($fh);
    fclose($fh);
    echo "\n";

    // > all errors or first value
    $ret = \Gzhegow\Ret\Core\Ret\Ret::any([
        $fnFopen([ '1.txt', 'r' ]),
        $fnFopen([ '2.txt', 'r' ]),
    ]);
    try {
        $ret->orThrow();
    }
    catch ( \Gzhegow\Ret\Exception\AggregateExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage() ], 2);

        foreach ( $e->getErrorsRecursive() as $path => $ee ) {
            $theDebug->dump_array([ implode('.', $path), $ee, $ee->message ], 2);
        }
    }
    echo "\n";

    // > all errors or all values (Ret is filled with indexed array ONLY IF ALL SUCCEEDED)
    $ret = \Gzhegow\Ret\Core\Ret\Ret::all([
        $fnFopen([ '1.txt', 'r' ]),
        $fnFopen([ '2.txt', 'r' ]),
    ]);
    try {
        $ret->orThrow();
    }
    catch ( \Gzhegow\Ret\Exception\AggregateExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage() ], 2);

        foreach ( $e->getErrorsRecursive() as $path => $ee ) {
            $theDebug->dump_array([ implode('.', $path), $ee, $ee->message ], 2);
        }
    }
    echo "\n";
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The `orThrow` caused exception" ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "0.0", "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The `orThrow` caused exception" ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "0.0", "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]
[ "1", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "1.0", "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(2.txt): Failed to open stream: No such file or directory" ]
[ "2", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "2.0", "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(3.txt): Failed to open stream: No such file or directory" ]

{ resource(opened) # stream }

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The `orThrow` caused exception" ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "0.0", "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]
[ "1", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "1.0", "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(2.txt): Failed to open stream: No such file or directory" ]

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The `orThrow` caused exception" ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "0.0", "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]
[ "1", "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "1.0", "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(2.txt): Failed to open stream: No such file or directory" ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The `orThrow` caused exception" ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "0.0", "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The `orThrow` caused exception" ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "0.0", "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]
[ "1", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "1.0", "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(2.txt): failed to open stream: No such file or directory" ]
[ "2", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "2.0", "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(3.txt): failed to open stream: No such file or directory" ]

{ resource(opened) # stream }

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The `orThrow` caused exception" ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "0.0", "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]
[ "1", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "1.0", "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(2.txt): failed to open stream: No such file or directory" ]

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The `orThrow` caused exception" ]
[ "0", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "0.0", "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]
[ "1", "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "1.0", "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(2.txt): failed to open stream: No such file or directory" ]
');
$test->run();


// > TEST
// > и на добивочку передадим привет Wordpress и legacy-коду
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    $wrapper = \Gzhegow\Ret\Core\Ret\Ret::wrapper()
        // // > $value == $val
        ->failIfSwitch($values = [ '\WP_Error' ])
        // // > $value === $val
        // ->failIfMatch($values = [ '\WP_Error' ])
        // // > $class === get_class($val)
        // ->failIfClass($classes = [ '\WP_Error' ])
        // // > is_a($val, $class)
        // ->failIfInstanceOf($classes = [ '\WP_Error' ])
        // // > callable
        // ->failIfCallback(
        //     static function ($val) {
        //         return \Gzhegow\Ret\Core\Ret\Ret::fail('Error');
        //     }
        // )
    ;
    $theDebug->dump_value($wrapper);
    echo "\n";

    $fnSomeWordpressFunction = function ($arg) {
        return $arg;
    };

    // > wrap function, then call
    $ffnSomeWordpressFunction = \Gzhegow\Ret\Core\Ret\Ret::fn($fnSomeWordpressFunction, $wrapper);
    $ret = $ffnSomeWordpressFunction([ $arg = '\WP_Error' ]);
    //
    // > or just call it directly
    // $ret = \Gzhegow\Ret\Core\Ret\Ret::fnCall($fnSomeWordpressFunction, [ $arg = '\WP_Error' ], $wrapper);

    try {
        $ret->orThrow([ 'The result should be any, but \WP_Error', $arg ]);
    }
    catch ( \Gzhegow\Ret\Exception\ExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage(), $e->getPayload() ], 2);
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

{ object # Gzhegow\Ret\Core\Ret\RetWrapper }

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The result should be any, but \WP_Error", [ 1 => "\WP_Error" ] ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

{ object # Gzhegow\Ret\Core\Ret\RetWrapper }

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The result should be any, but \WP_Error", [ 1 => "\WP_Error" ] ]
');
$test->run();


// > TEST
// > ДЛЯ ЛЮБОЗНАТЕЛЬНЫХ - для чего нужны Error's, когда есть \Throwable's
$testN++;
$fn = function () use ($theDebug, $testN) {
    $theDebug->dump_value('TEST ' . $testN);
    echo "\n";

    $fnRecursive = function ($level, $mode, $currentLevel = 1) use (&$fnRecursive) {
        if ( $currentLevel === $level ) {
            return null
                ?? (($mode === 1) ? new \Exception('1', -1) : null)
                ?? (($mode === 2) ? \Gzhegow\Ret\Core\Err::message([ '1', 'my_data' ], __FILE__, __LINE__) : null);
        }

        return $fnRecursive($level, $mode, $currentLevel + 1);
    };

    // > PRODUCTION MODE
    // > disable stack trace args (reduce memory and processor overhead)
    $old = ini_set('zend.exception_ignore_args', 1);

    try {
        $phpVersion = ((PHP_VERSION_ID >= 80000) ? '^8.0' : '^7.3');

        $objectsCount = 10000;
        $traceLevelList = [
            5,
            15,
            30,
            100,
        ];

        $classException = \Exception::class;
        $classError = get_class(\Gzhegow\Ret\Core\Err::message(''));

        $timeMinList = [
            '^8.0' => [
                [ 0.005, 0.004 ],
                [ 0.010, 0.007 ],
                [ 0.017, 0.012 ],
                [ 0.050, 0.030 ],
            ],
            '^7.3' => [
                [ 0.005, 0.004 ],
                [ 0.010, 0.007 ],
                [ 0.017, 0.012 ],
                [ 0.050, 0.030 ],
            ],
        ];
        $timeMaxList = [
            '^8.0' => [
                [ 0.007, 0.006 ],
                [ 0.012, 0.009 ],
                [ 0.020, 0.014 ],
                [ 0.058, 0.036 ],
            ],
            '^7.3' => [
                [ 0.007, 0.006 ],
                [ 0.015, 0.009 ],
                [ 0.025, 0.014 ],
                [ 0.078, 0.036 ],
            ],
        ];

        $table = [];
        foreach ( $traceLevelList as $i => $traceLevel ) {
            [ $timeMinException, $timeMinError ] = $timeMinList[$phpVersion][$i];
            [ $timeMaxException, $timeMaxError ] = $timeMaxList[$phpVersion][$i];

            $rands = [];
            for ( $i = 0; $i < $objectsCount; $i++ ) {
                // > stack trace size for each exception
                $rands[] = $traceLevel;
            }

            $mt = microtime(true);
            for ( $i = 0; $i < $objectsCount; $i++ ) {
                $fnRecursive($rands[$i], 1);
            }
            $time = microtime(true) - $mt;

            $timeMin = min($time, $timeMinException);
            $timeMax = max($time, $timeMaxException);
            $table[] = [
                'PHP Version'   => $phpVersion,
                'Object Type'   => $classException,
                'Objects Count' => $objectsCount,
                'Trace Level'   => $traceLevel,
                'Time Min'      => $timeMin,
                'Time Max'      => $timeMax,
            ];

            $mt = microtime(true);
            for ( $i = 0; $i < $objectsCount; $i++ ) {
                $fnRecursive($rands[$i], 2);
            }
            $time = microtime(true) - $mt;

            $timeMin = min($time, $timeMinError);
            $timeMax = max($time, $timeMaxError);
            $table[] = [
                'PHP Version'   => $phpVersion,
                'Object Type'   => $classError,
                'Objects Count' => $objectsCount,
                'Trace Level'   => $traceLevel,
                'Time Min'      => $timeMin,
                'Time Max'      => $timeMax,
            ];
        }

        $theDebug->dump_table($table);
    }
    finally {
        ini_set('zend.exception_ignore_args', $old);
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST ' . $testN . '"

+---+-------------+---------------------------------------+---------------+-------------+----------+----------+
|   | PHP Version | Object Type                           | Objects Count | Trace Level | Time Min | Time Max |
+---+-------------+---------------------------------------+---------------+-------------+----------+----------+
| 0 | ^8.0        | Exception                             | 10000         | 5           | 0.005    | 0.007    |
| 1 | ^8.0        | Gzhegow\Ret\Core\Error\PHP8\MainError | 10000         | 5           | 0.004    | 0.006    |
| 2 | ^8.0        | Exception                             | 10000         | 15          | 0.01     | 0.012    |
| 3 | ^8.0        | Gzhegow\Ret\Core\Error\PHP8\MainError | 10000         | 15          | 0.007    | 0.009    |
| 4 | ^8.0        | Exception                             | 10000         | 30          | 0.017    | 0.02     |
| 5 | ^8.0        | Gzhegow\Ret\Core\Error\PHP8\MainError | 10000         | 30          | 0.012    | 0.014    |
| 6 | ^8.0        | Exception                             | 10000         | 100         | 0.05     | 0.058    |
| 7 | ^8.0        | Gzhegow\Ret\Core\Error\PHP8\MainError | 10000         | 100         | 0.03     | 0.036    |
+---+-------------+---------------------------------------+---------------+-------------+----------+----------+
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST ' . $testN . '"

+---+-------------+---------------------------------------+---------------+-------------+----------+----------+
|   | PHP Version | Object Type                           | Objects Count | Trace Level | Time Min | Time Max |
+---+-------------+---------------------------------------+---------------+-------------+----------+----------+
| 0 | ^7.3        | Exception                             | 10000         | 5           | 0.005    | 0.007    |
| 1 | ^7.3        | Gzhegow\Ret\Core\Error\PHP7\MainError | 10000         | 5           | 0.004    | 0.006    |
| 2 | ^7.3        | Exception                             | 10000         | 15          | 0.01     | 0.015    |
| 3 | ^7.3        | Gzhegow\Ret\Core\Error\PHP7\MainError | 10000         | 15          | 0.007    | 0.009    |
| 4 | ^7.3        | Exception                             | 10000         | 30          | 0.017    | 0.025    |
| 5 | ^7.3        | Gzhegow\Ret\Core\Error\PHP7\MainError | 10000         | 30          | 0.012    | 0.014    |
| 6 | ^7.3        | Exception                             | 10000         | 100         | 0.05     | 0.078    |
| 7 | ^7.3        | Gzhegow\Ret\Core\Error\PHP7\MainError | 10000         | 100         | 0.03     | 0.036    |
+---+-------------+---------------------------------------+---------------+-------------+----------+----------+
');
$test->run();
