<?php

declare(strict_types=1);

namespace TestResource\Unit\Eboreum\Caster\Formatter\DefaultEnumFormatterTest\testBasics;

enum StringEnum: string
{
    case Lorem = 'Lorem';
    case Ipsum = 'Ipsum';
}
