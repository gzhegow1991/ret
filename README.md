# Ret

Про ошибки написано много, даже очень. Попытки говорить об этой теме заканчиваются на том, что это всё очень легко. Но как мы видим - разные языки, в которых "это легко" допускают грубейшие косяки, заставляя разработчиков плеваться и затыкая их потом нейросетями под плинтус.

Наилучшая концепция, которую мне удалось обсудить с другими разработчиками - концепция ReturnCode, которая изобретена ещё в далекие годы дедов, которые просто держали массивы вида КОД - СООБЩЕНИЕ. Для процессора это был сущий рай - он сравнивал два Integer между собой и знал что делать.

PHP пошел по другому пути. Пытаясь скопировать Java, были сделаны исключения, которые пришли на замену Warning. Разумеется, никто не собирал мнение завсегдатаев, решили обычным голосованием. Главный минус исключения - при его создании он копирует в оперативную память полный путь до функции. Вещь изумительно удобная, но очень дорогая. Как не пытались разработчики оптимизировать этот процесс - использование простых объектов ошибок все равно быстрее. Впрочем да, обычные ошибки не поймаешь через try/catch.

Общаясь с разработчиками, выгрызая зубами правду, мы таки пришли к тому, что исключение это "то что мешает коду делать полезную работу", а не "то, что не должно произойти". Исключения не есть запрет, но стремится стоит к тому, чтобы в коде исключения оставались только для тех случаев, когда разработчик действительно ничего не может с ними сделать.

Представляю вам концепцию Result<T,E> на языке PHP. В довесок к нему идут допиленные исключения, ошибки, позволяющие их логировать и выводить наравне с ними, а также ErrorBag, чтобы удобно их накапливать и работать с потомками. Посмотрите тесты, будет что вспомнить.

## Установить

```
composer require gzhegow/ret
```

## Запустить тесты

```
php test.php
```

## Примеры и тесты

```php
<?php

define('__DIR_ROOT__', __DIR__ . '/..');
//
error_reporting(E_ALL);


// >>> ТЕСТЫ

$theDebug = \Gzhegow\Lib\Lib::debug();
$theTest = \Gzhegow\Lib\Lib::test();

// > TEST
// > преобразуем стандартный warning в объект ошибки
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 1');
    echo "\n";

    @trigger_error('Hello');
    $err = error_get_last();

    $e = \Gzhegow\Ret\Core\Error\Err::triggered(
        $err['type'], $err['message'],
        $err['file'], $err['line']
    );

    $theDebug->dump_value($e);
    $theDebug->dump_value($e->message);
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80000, '
"TEST 1"

{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }
"Hello"
');
$test->expectStdoutIf(PHP_VERSION_ID < 80000, '
"TEST 1"

{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }
"Hello"
');
$test->run();


// > TEST
// > ошибки можно создавать по разному, а главное - удобно
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 2');
    echo "\n";

    $ee = [];
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message('message'); // > file: 'unknown', line: 0
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message('message', __FILE__, __LINE__);
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message([ 0 => 'message', 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
    foreach ( $ee as $e ) {
        $theDebug->dump_array([ $e, $e->message, $e->code, $e->payload ]);
    }
    echo "\n";

    $ee = [];
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::code(32767, __FILE__, __LINE__);
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::code([ 0 => 32767, 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::code([ 0 => 'MY_CODE', 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
    foreach ( $ee as $e ) {
        $theDebug->dump_array([ $e, $e->message, $e->code, $e->payload ]);
    }
    echo "\n";

    $ee = [];
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::new(32767, __FILE__, __LINE__);
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::new('message', __FILE__, __LINE__);
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::new([ '' => 32767, 0 => 'message', 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
    foreach ( $ee as $e ) {
        $theDebug->dump_array([ $e, $e->message, $e->code, $e->payload ]);
    }
    echo "\n";

    if ( PHP_VERSION_ID >= 80100 ) {
        require_once __DIR_ROOT__ . '/tests/src/MyEnum.php';

        // > receives code and message
        $ee = [];
        $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::code(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::code([ 0 => \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::new([ '' => \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, 0 => null, 'data' => 1, 1 => 'my_data2' ], __FILE__, __LINE__);
        foreach ( $ee as $e ) {
            $theDebug->dump_array([ $e, $e->message, $e->code, $e->payload ]);
        }
        echo "\n";

        // > receives only message, code will be -1
        $ee = [];
        $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message([ 0 => \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, 'data' => 1, 'my_data' ], __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::new(\Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, __FILE__, __LINE__);
        $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::new([ '' => null, 0 => \Gzhegow\Ret\Tests\MyEnum::ERR_FAIL, 'data' => 1, 'my_data2' ], __FILE__, __LINE__);
        foreach ( $ee as $e ) {
            $theDebug->dump_array([ $e, $e->message, $e->code, $e->payload ]);
        }
        echo "\n";
    }

    $ee = [];
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message('message 1');
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message('message 2');
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message('message 3');
    $ee = \Gzhegow\Ret\Core\Error\Err::aggregate($ee, __FILE__, __LINE__, 'My aggregate message');
    // $ee = \Gzhegow\Ret\Core\Error\Err::aggregate($ee, __FILE__, __LINE__); // > will generate generic message
    $theDebug->dump_value($ee);
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST 2"

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", -1, "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "32767", 32767, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "32767", 32767, "{ array(2) }" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "MY_CODE", "MY_CODE", "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "32767", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "message", 32767, "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", "{ object # Gzhegow\Ret\Tests\MyEnum }", NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", "{ object # Gzhegow\Ret\Tests\MyEnum }", "{ array(2) }" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", "{ object # Gzhegow\Ret\Tests\MyEnum }", "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", -1, "{ array(2) }" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "Error message about FAIL", -1, "{ array(2) }" ]

{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST 2"

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", -1, "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "32767", 32767, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "32767", 32767, "{ array(2) }" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "MY_CODE", "MY_CODE", "{ array(2) }" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "32767", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", -1, NULL ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "message", 32767, "{ array(2) }" ]

{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }
');
$test->run();


// > TEST
// > ошибки можно (и нужно) объединять в агрегаты, подбивая бизнес-задачу
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 3');
    echo "\n";

    $ee = [];
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message('message 1');
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message('message 2');
    $ee[] = $e = \Gzhegow\Ret\Core\Error\Err::message('message 3');

    // $ee = \Gzhegow\Ret\Core\Error\Err::aggregate($ee, __FILE__, __LINE__); // > will generate generic message
    $ee = \Gzhegow\Ret\Core\Error\Err::aggregate($ee, __FILE__, __LINE__, 'My aggregate message');
    $theDebug->dump_value($ee);

    $theDebug->dump_array_multiline($ee->children);
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST 3"

{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }
###
[
  "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }",
  "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }",
  "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }"
]
###
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST 3"

{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }
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
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 4');
    echo "\n";

    $ee = [];
    $ee[] = $e1 = \Gzhegow\Ret\Core\Error\Err::message('Hello 1', __FILE__, __LINE__);
    $ee[] = $e2 = \Gzhegow\Ret\Core\Error\Err::message('Hello 2', __FILE__, __LINE__);

    $ee = \Gzhegow\Ret\Core\Error\Err::aggregate($ee, __FILE__, __LINE__);

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
        $theDebug->dump_array([ $e, $e->message, $e->code, $e->payload ]);
    }
    foreach ( $bag->getTaggedErrors() as $e ) {
        $theDebug->dump_array([ $e, $e->error, $e->tags ], 2);
    }
    echo "\n";

    // > get only children if errors was aggregates, children NEVER contain AggregateErrorInterface
    foreach ( $bag->getChildren() as $e ) {
        $theDebug->dump_array([ $e, $e->message, $e->code, $e->payload ]);
    }
    foreach ( $bag->getTaggedChildren() as $e ) {
        $theDebug->dump_array([ $e, $e->error, $e->tags ], 2);
    }
    echo "\n";

    // > find errors by tags using and/or logic for search
    $query = [ [ 'tag3' => true, 'tag2' => false ], [ 'tag2' => true ] ];
    foreach ( $bag->findErrors(...$query) as $e ) {
        $theDebug->dump_array([ $e, $e->message, $e->code, $e->payload ]);
    }
    foreach ( $bag->findTaggedErrors(...$query) as $e ) {
        $theDebug->dump_array([ $e, $e->error, $e->tags ], 2);
    }
    echo "\n";

    // > find children by tags using and/or logic for search
    $query = [ [ 'tag3' => true, 'tag2' => false ], [ 'tag2' => true ] ];
    foreach ( $bag->findChildren(...$query) as $e ) {
        $theDebug->dump_array([ $e, $e->message, $e->code, $e->payload ]);
    }
    foreach ( $bag->findTaggedChildren(...$query) as $e ) {
        $theDebug->dump_array([ $e, $e->error, $e->tags ], 2);
    }
    echo "\n";
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST 4"

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
"TEST 4"

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
// > познакомьтесь, Ret (Result<T,E>)
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 5');
    echo "\n";

    // > as first, you should write own functions using Ret::ok() and Ret::fail() returns
    $fnStringNotEmpty = function ($value) : \Gzhegow\Ret\Core\Ret\Ret {
        if ( '' === $value ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ], __FILE__, __LINE__);
        }
        if ( ! is_string($value) ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ], __FILE__, __LINE__);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($value);
    };

    /**
     * @return \Gzhegow\Ret\Core\Ret\Ret<string>
     */
    $fnToStringNotEmpty = function ($value) : \Gzhegow\Ret\Core\Ret\Ret {
        try {
            $valueString = (string) $value;
        }
        catch ( \Throwable $e ) {
            // > you may pass exceptions directly to Ret::fail

            // > will use FILE_LINE from exception
            // return \Gzhegow\Ret\Core\Ret\Ret::fail($e);

            // > wrap original error with aggregate with new file/line, as breadcrumbs
            return \Gzhegow\Ret\Core\Ret\Ret::fail($e, __FILE__, __LINE__);
        }

        if ( '' === $value ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ], __FILE__, __LINE__);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($valueString);
    };


    // > magic! the function becomes context-controllable - you may throw, get error, or return fallback depends on your needs
    // > you write code once, but reuse it in few scenarios without copy/rewrite

    try {
        // > @return mixed|null or @throws \RuntimeException, actually null is returned if you call Ret::new()->orThrow() (no value and no errors)
        $result = $fnStringNotEmpty($value = 123)->orThrow([ 'The password is invalid', $value ]);
    }
    catch ( \Gzhegow\Ret\Exception\ExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage(), $e->getPayload() ], 2); // > [ object, 'The password is invalid', [ 1 => 123 ] ]
    }

    // > @return mixed|ErrorInterface or @throws \RuntimeException, actually throws if you call Ret::new()->orError() (no value and no errors)
    $e = $fnStringNotEmpty($value = 123)->orError([ 'The password is invalid', $value ], __FILE__, __LINE__);
    $theDebug->dump_array([ $e, $e->message, $e->payload ], 2);    // > [ object, 'The password is invalid', [ 1 => 123 ] ]

    // > NAN if error, false if empty
    $result = $fnStringNotEmpty($value = 123)->orFallback($fb = NAN, $def = false);
    $theDebug->dump_value($result);

    // > false if error, false if empty
    $result = $fnStringNotEmpty($value = 123)->orDefault($def = false);
    $theDebug->dump_value($result);

    // > null if error, null if empty
    $result = $fnStringNotEmpty($value = 123)->orNull();
    $theDebug->dump_value($result);
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST 5"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => 123 ] ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The password is invalid", [ 1 => 123 ] ]
NAN
FALSE
NULL
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST 5"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => 123 ] ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The password is invalid", [ 1 => 123 ] ]
NAN
FALSE
NULL
');
$test->run();


// > TEST
// > ещё чуть-чуть волшебства Ret
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 6');
    echo "\n";

    /**
     * @return \Gzhegow\Ret\Core\Ret\Ret<string>
     * @throws \Gzhegow\Ret\Exception\TriggeredException
     */
    $fnToStringNotEmpty = function ($value) : \Gzhegow\Ret\Core\Ret\Ret {
        set_error_handler(function (...$args) { throw new \Gzhegow\Ret\Exception\TriggeredException(...array_slice($args, 0, 4)); });
        try {
            $valueString = (string) $value;
        }
        catch ( \Throwable $e ) {
            // > you may pass exceptions directly to Ret::fail

            // > will use FILE_LINE from exception (wrap original \Throwable as ErrorInterface)
            // return \Gzhegow\Ret\Core\Ret\Ret::fail($e);

            // > wrap original \Throwable, and then wrap ErrorInterface with AggregateErrorInterface with new file/line, as breadcrumbs
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

    try {
        // > @return mixed|null or @throws \RuntimeException, actually null is returned if you call Ret::new()->orThrow() (no value and no errors)
        $result = $fnToStringNotEmpty($value = new \stdClass())->orThrow([ 'The password is invalid', $value ]);
    }
    catch ( \Gzhegow\Ret\Exception\ExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage(), $e->getPayload() ], 2); // > [ object, 'The password is invalid', [ 1 => 123 ] ]
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST 6"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => "{ object # stdClass }" ] ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST 6"

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => "{ object # stdClass }" ] ]
');
$test->run();


// > TEST
// > и ещё немножко волшебства Ret
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 7');
    echo "\n";

    /**
     * @return \Gzhegow\Ret\Core\Ret\Ret<string>
     */
    $fnString = function ($value) : \Gzhegow\Ret\Core\Ret\Ret {
        if ( ! is_string($value) ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ]);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($value);
    };

    /**
     * @return \Gzhegow\Ret\Core\Ret\Ret<string>
     */
    $fnStringNotEmpty = function ($value) use ($fnString) : \Gzhegow\Ret\Core\Ret\Ret {
        if ( '' === $value ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be string, non-empty', $value ]);
        }

        $ret = $fnString($value);

        // > $valueString exists is non-null only if condition ->isOk() returned TRUE
        if ( ! $ret->isOk([ &$valueString ]) ) {
            // > pass old ret object to parent scope

            return \Gzhegow\Ret\Core\Ret\Ret::pass($ret);

            // > will wrap old object with instance that implements AggregateErrorInterface
            // return \Gzhegow\Ret\Core\Ret\Ret::pass($ret, 'My custom message if needed', __FILE__, __LINE__);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($valueString);
    };

    /**
     * @return \Gzhegow\Ret\Core\Ret\Ret<string>
     */
    $fnArrayNotEmpty = function ($value) : \Gzhegow\Ret\Core\Ret\Ret {
        if ( [] === $value ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be array, non-empty', $value ]);
        }
        if ( ! is_array($value) ) {
            return \Gzhegow\Ret\Core\Ret\Ret::fail([ 'The `value` should be array, non-empty', $value ]);
        }

        return \Gzhegow\Ret\Core\Ret\Ret::ok($value);
    };

    // > hmm, whats this? is it "chaining" to get "first success"?
    $ret = \Gzhegow\Ret\Core\Ret\Ret::new();
    $value = 123;
    $valueValid = null
        ?? $fnStringNotEmpty($value)->orNull($ret)
        ?? $fnArrayNotEmpty($value)->orNull($ret);
    $theDebug->dump_value($valueValid); // > null, cause `123` is not string and not an array
    echo "\n";

    try {
        $ret->orThrow([ 'The password is invalid', $value ]);
    }
    catch ( \Gzhegow\Ret\Exception\AggregateExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage(), $e->getPayload() ], 2); // > [ object, 'The password is invalid', [ 1 => 123 ] ]

        // > ->getErrors() can contain \Throwable or ErrorInterface objects
        foreach ( $e->getErrors() as $e ) {
            if ( $e instanceof \Gzhegow\Ret\Core\Error\ErrorInterface ) {
                $theDebug->dump_array([ $e, $e->message, $e->payload ], 2);

            } elseif ( $e instanceof \Throwable ) {
                $theDebug->dump_array([ $e, $e->getMessage() ]);
            }
        }
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST 7"

NULL

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => 123 ] ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "The `value` should be string, non-empty", [ 1 => 123 ] ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\MainError }", "The `value` should be array, non-empty", [ 1 => 123 ] ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST 7"

NULL

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The password is invalid", [ 1 => 123 ] ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "The `value` should be string, non-empty", [ 1 => 123 ] ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\MainError }", "The `value` should be array, non-empty", [ 1 => 123 ] ]
');
$test->run();


// > TEST
// > обнаглеем в конец вместе с Ret
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 8');
    echo "\n";

    // > we may prefer safe call internal PHP functions that may accidentally trigger warning
    $ret = \Gzhegow\Ret\Core\Ret\Ret::fnCall('fopen', [ '1.txt', 'r' ]);
    foreach ( $ret->getErrors() as $ee ) {
        $theDebug->dump_array([ $ee, $ee->message ], 2);

        if ( $ee instanceof \Gzhegow\Ret\Core\Error\AggregateErrorInterface ) {
            foreach ( $ee->children as $eee ) {
                $theDebug->dump_array([ $eee, $eee->message ], 2);
            }
        }
    }
    echo "\n";

    // > ok, let just create \Closure with this function to be shorter
    $fn = \Gzhegow\Ret\Core\Ret\Ret::fn('fopen');

    // > old good "keep first success" but without null-coalesce
    $ret = \Gzhegow\Ret\Core\Ret\Ret::new()
        ->tryAny($fn([ '1.txt', 'r' ]))
        ->tryAny($fn([ '2.txt', 'r' ]))
        ->tryAny($fn([ '3.txt', 'r' ]))
    ;
    foreach ( $ret->getErrors() as $ee ) {
        $theDebug->dump_array([ $ee, $ee->message ], 2);

        if ( $ee instanceof \Gzhegow\Ret\Core\Error\AggregateErrorInterface ) {
            foreach ( $ee->children as $eee ) {
                $theDebug->dump_array([ $eee, $eee->message ], 2);
            }
        }
    }
    echo "\n";

    // > collects all errors and keep first received value
    $ret = \Gzhegow\Ret\Core\Ret\Ret::new()
        ->tryAllFirst($fn([ '1.txt', 'r' ]))
        ->tryAllFirst($fn([ '2.txt', 'r' ]))
        ->tryAllFirst($fn([ '3.txt', 'r' ]))
    ;
    foreach ( $ret->getErrors() as $ee ) {
        $theDebug->dump_array([ $ee, $ee->message ], 2);

        if ( $ee instanceof \Gzhegow\Ret\Core\Error\AggregateErrorInterface ) {
            foreach ( $ee->children as $eee ) {
                $theDebug->dump_array([ $eee, $eee->message ], 2);
            }
        }
    }

    // > collects all errors and replaces value to next success
    $ret = \Gzhegow\Ret\Core\Ret\Ret::new()
        ->tryAllLast($fn([ '1.txt', 'r' ]))
        ->tryAllLast($fn([ '2.txt', 'r' ]))
        ->tryAllLast($fn([ '3.txt', 'r' ]))
    ;
    foreach ( $ret->getErrors() as $ee ) {
        $theDebug->dump_array([ $ee, $ee->message ], 2);

        if ( $ee instanceof \Gzhegow\Ret\Core\Error\AggregateErrorInterface ) {
            foreach ( $ee->children as $eee ) {
                $theDebug->dump_array([ $eee, $eee->message ], 2);
            }
        }
    }
    echo "\n";

    // > all errors or first value
    $ret = \Gzhegow\Ret\Core\Ret\Ret::any([
        $fn([ '1.txt', 'r' ]),
        $fn([ '2.txt', 'r' ]),
        $fn([ '3.txt', 'r' ]),
    ]);
    foreach ( $ret->getErrors() as $ee ) {
        $theDebug->dump_array([ $ee, $ee->message ], 2);

        if ( $ee instanceof \Gzhegow\Ret\Core\Error\AggregateErrorInterface ) {
            foreach ( $ee->children as $eee ) {
                $theDebug->dump_array([ $eee, $eee->message ], 2);
            }
        }
    }
    echo "\n";

    // > all errors or all values
    $ret = \Gzhegow\Ret\Core\Ret\Ret::all([
        $fn([ '1.txt', 'r' ]),
        $fn([ '2.txt', 'r' ]),
        $fn([ '3.txt', 'r' ]),
    ]);
    foreach ( $ret->getErrors() as $ee ) {
        $theDebug->dump_array([ $ee, $ee->message ], 2);

        if ( $ee instanceof \Gzhegow\Ret\Core\Error\AggregateErrorInterface ) {
            foreach ( $ee->children as $eee ) {
                $theDebug->dump_array([ $eee, $eee->message ], 2);
            }
        }
    }
    echo "\n";

    // > both errors and values
    $fn = \Gzhegow\Ret\Core\Ret\Ret::fn('fopen');
    $ret = \Gzhegow\Ret\Core\Ret\Ret::some([
        $fn([ '1.txt', 'r' ]),
        $fn([ '2.txt', 'r' ]),
        $fn([ '3.txt', 'r' ]),
    ]);
    $result = $ret->getResult();
    $theDebug->dump_array_multiline($result, 2);
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST 8"

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(2.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(3.txt): Failed to open stream: No such file or directory" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(2.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(3.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(2.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(3.txt): Failed to open stream: No such file or directory" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(2.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(3.txt): Failed to open stream: No such file or directory" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(1.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(2.txt): Failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP8\TriggeredError }", "fopen(3.txt): Failed to open stream: No such file or directory" ]

###
[
  "errors" => [
    "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }",
    "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }",
    "{ object # Gzhegow\Ret\Core\Error\PHP8\AggregateError }"
  ],
  "values" => []
]
###
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST 8"

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(2.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(3.txt): failed to open stream: No such file or directory" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(2.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(3.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(2.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(3.txt): failed to open stream: No such file or directory" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(2.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(3.txt): failed to open stream: No such file or directory" ]

[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(1.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(2.txt): failed to open stream: No such file or directory" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }", "The `fnCall` intercepted warnings" ]
[ "{ object # Gzhegow\Ret\Core\Error\PHP7\TriggeredError }", "fopen(3.txt): failed to open stream: No such file or directory" ]

###
[
  "errors" => [
    "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }",
    "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }",
    "{ object # Gzhegow\Ret\Core\Error\PHP7\AggregateError }"
  ],
  "values" => []
]
###
');
$test->run();


// > TEST
// > и на добивочку передадим привет Wordpress и legacy-коду
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 9');
    echo "\n";

    $wrapper = \Gzhegow\Ret\Core\Ret\Ret::wrapper()
        // > $value == $val
        ->failIfSwitch([ '\WP_Error' ])
        // > $value === $val
        ->failIfMatch([ '\WP_Error' ])
        // > $class === get_class($val)
        ->failIfClass([ '\WP_Error' ])
        // > is_a($val, $class)
        ->failIfInstanceOf([ '\WP_Error' ])
        // > callable
        ->failIfCallback(static function ($val) { return \Gzhegow\Ret\Core\Ret\Ret::fail('Error'); })
    ;
    $theDebug->dump_value($wrapper);
    echo "\n";

    $fnSomeWordpressFunction = function ($arg) {
        return $arg;
    };
    //
    // > wrap function
    $ffnSomeWordpressFunction = \Gzhegow\Ret\Core\Ret\Ret::fn($fnSomeWordpressFunction, $wrapper);
    //
    // > or just call it:
    // $ret = \Gzhegow\Ret\Core\Ret\Ret::fnCall($fnSomeWordpressFunction, [], $wrapper);
    //
    $ret = $ffnSomeWordpressFunction([ $arg = '\WP_Error' ]);
    try {
        $ret->orThrow([ 'The result should be any, but \WP_Error', $arg ]);
    }
    catch ( \Gzhegow\Ret\Exception\ExceptionInterface $e ) {
        $theDebug->dump_array([ $e, $e->getMessage(), $e->getPayload() ], 2);
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST 9"

{ object # Gzhegow\Ret\Core\Ret\RetWrapper }

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The result should be any, but \WP_Error", [ 1 => "\WP_Error" ] ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST 9"

{ object # Gzhegow\Ret\Core\Ret\RetWrapper }

[ "{ object(stringable) # Gzhegow\Ret\Exception\AggregateRuntimeException }", "The result should be any, but \WP_Error", [ 1 => "\WP_Error" ] ]
');
$test->run();


// > TEST
// > маленькая справочка - для чего нужны Error's, когда есть \Throwable's
$fn = function () use ($theDebug) {
    $theDebug->dump_value('TEST 10');
    echo "\n";

    try {
        // > disable stack trace args (reduce memory and processor overhead)
        $old = ini_set('zend.exception_ignore_args', 1);
    }
    finally {
        ini_set('zend.exception_ignore_args', $old);
    }

    $fnRecursive = function ($level, $mode, $currentLevel = 1) use (&$fnRecursive) {
        if ( $currentLevel === $level ) {
            return null
                ?? (($mode === 1) ? new \Exception('1', -1) : null)
                ?? (($mode === 2) ? \Gzhegow\Ret\Core\Error\Err::message([ '1', 'my_data' ], __FILE__, __LINE__) : null);
        }

        return $fnRecursive($level, $mode, $currentLevel + 1);
    };

    $objectsCount = 10000;
    $traceLevelList = [
        5,
        15,
        30,
        100,
    ];
    $timeExpectedList = [
        [ 0.008, 0.005 ],
        [ 0.015, 0.008 ],
        [ 0.026, 0.12 ],
        [ 0.09, 0.035 ],
    ];

    foreach ( $traceLevelList as $i => $traceLevel ) {
        [ $timeExpectedException, $timeExpectedError ] = $timeExpectedList[$i];

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
        $timeApprox = max($time, $timeExpectedException);
        $theDebug->dump_value([ 'Exception -> Object count | Trace level | Time', $objectsCount, $traceLevel, $timeApprox ]);

        $mt = microtime(true);
        for ( $i = 0; $i < $objectsCount; $i++ ) {
            $fnRecursive($rands[$i], 2);
        }
        $time = microtime(true) - $mt;
        $timeApprox = max($time, $timeExpectedError);
        $theDebug->dump_value([ 'Error -> Object count | Trace level | Time', $objectsCount, $traceLevel, $timeApprox ]);
    }
};
$test = $theTest->newCase($fn);
$test->expectStdoutIf(PHP_VERSION_ID >= 80100, '
"TEST 10"

[ "Exception -> Object count | Trace level | Time", 10000, 5, 0.008 ]
[ "Error -> Object count | Trace level | Time", 10000, 5, 0.005 ]
[ "Exception -> Object count | Trace level | Time", 10000, 15, 0.015 ]
[ "Error -> Object count | Trace level | Time", 10000, 15, 0.008 ]
[ "Exception -> Object count | Trace level | Time", 10000, 30, 0.026 ]
[ "Error -> Object count | Trace level | Time", 10000, 30, 0.12 ]
[ "Exception -> Object count | Trace level | Time", 10000, 100, 0.09 ]
[ "Error -> Object count | Trace level | Time", 10000, 100, 0.035 ]
');
$test->expectStdoutIf(PHP_VERSION_ID < 80100, '
"TEST 10"

[ "Exception -> Object count | Trace level | Time", 10000, 5, 0.008 ]
[ "Error -> Object count | Trace level | Time", 10000, 5, 0.005 ]
[ "Exception -> Object count | Trace level | Time", 10000, 15, 0.015 ]
[ "Error -> Object count | Trace level | Time", 10000, 15, 0.008 ]
[ "Exception -> Object count | Trace level | Time", 10000, 30, 0.026 ]
[ "Error -> Object count | Trace level | Time", 10000, 30, 0.12 ]
[ "Exception -> Object count | Trace level | Time", 10000, 100, 0.09 ]
[ "Error -> Object count | Trace level | Time", 10000, 100, 0.035 ]
');
$test->run();
```

