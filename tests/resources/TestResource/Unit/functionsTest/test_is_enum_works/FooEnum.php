<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\functionsTest\test_is_enum_works;

enum FooEnum: string
{
    case Lorem = 'Lorem';
    case Ipsum = 'Ipsum';
}