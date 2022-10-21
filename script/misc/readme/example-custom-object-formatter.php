<?php

declare(strict_types=1);

use Eboreum\Caster\Abstraction\Formatter\AbstractObjectFormatter;
use Eboreum\Caster\Caster;
use Eboreum\Caster\Collection\Formatter\ObjectFormatterCollection;
use Eboreum\Caster\Contract\CasterInterface;

require_once dirname(__DIR__, 2) . '/bootstrap.php'; // README.md.remove

$caster = Caster::create();

$caster = $caster->withCustomObjectFormatterCollection(new ObjectFormatterCollection([
    new class extends AbstractObjectFormatter
    {
        public function format(CasterInterface $caster, object $object): ?string
        {
            if (false === $this->isHandling($object)) {
                return null; // Pass on to next formatter or lastly DefaultObjectFormatter
            }

            assert($object instanceof DateTimeInterface);

            return sprintf(
                '%s (%s)',
                Caster::makeNormalizedClassName(new ReflectionObject($object)),
                $object->format('c'),
            );
        }

        public function isHandling(object $object): bool
        {
            return ($object instanceof DateTimeInterface);
        }
    },
    new class extends AbstractObjectFormatter
    {
        public function format(CasterInterface $caster, object $object): ?string
        {
            if (false === $this->isHandling($object)) {
                return null; // Pass on to next formatter or lastly DefaultObjectFormatter
            }

            assert($object instanceof Throwable);

            return sprintf(
                '%s {$code = %s, $file = %s, $line = %s, $message = %s}',
                Caster::makeNormalizedClassName(new ReflectionObject($object)),
                $caster->cast($object->getCode()),
                $caster->cast('.../' . basename($object->getFile())),
                $caster->cast($object->getLine()),
                $caster->cast($object->getMessage()),
            );
        }

        public function isHandling(object $object): bool
        {
            return ($object instanceof Throwable);
        }
    },
]));

echo $caster->cast(new stdClass()) . "\n";

echo $caster->cast(new DateTimeImmutable('2019-01-01T00:00:00+00:00')) . "\n";

echo $caster->cast(new RuntimeException('test', 1)) . "\n";
