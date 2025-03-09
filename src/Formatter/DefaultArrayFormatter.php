<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractArrayFormatter;
use Eboreum\Caster\Contract\CasterInterface;

use function array_walk;
use function count;
use function implode;
use function sprintf;

class DefaultArrayFormatter extends AbstractArrayFormatter
{
    /**
     * @inheritDoc
     */
    public function format(CasterInterface $caster, array $array): ?string
    {
        $arraySampleSize = $caster->getArraySampleSize()->toInteger();
        $return = '';
        $isSample = false;

        if ($arraySampleSize > 0) {
            $segments = [];
            $index = 1;

            foreach ($array as $k => $v) {
                $segments[] = sprintf(
                    '%s => %s',
                    $caster->cast($k),
                    $caster->cast($v),
                );

                if ($index >= $arraySampleSize) {
                    break;
                }

                $index++;
            }

            $surplusCount = (count($array) - $arraySampleSize);
            $isSample = ($surplusCount > 0);

            if ($isSample) {
                $segments[] = sprintf(
                    '%s and %d more %s',
                    $caster->getSampleEllipsis(),
                    $surplusCount,
                    (1 === $surplusCount ? 'element' : 'elements')
                );
            }

            if ($array && $caster->isWrapping()) {
                array_walk($segments, static function (string &$segment) use ($caster): void {
                    $segment = $caster->getWrappingIndentationCharacters() . $segment;
                });

                $return = sprintf(
                    "[\n%s\n]",
                    implode(",\n", $segments),
                );
            } else {
                $return = '[' . implode(', ', $segments) . ']';
            }
        } else {
            $return = sprintf('[%s]', $caster->getSampleEllipsis());
            $isSample = true;
        }

        if ($isSample) {
            $return .= ' (sample)';
        }

        return $return;
    }

    /**
     * @inheritDoc
     */
    public function isHandling(array $array): bool
    {
        return true;
    }
}
