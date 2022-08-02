<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractArrayFormatter;
use Eboreum\Caster\Contract\CasterInterface;

/**
 * @inheritDoc
 */
class DefaultArrayFormatter extends AbstractArrayFormatter
{
    /**
     * {@inheritDoc}
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

            $return = '[' . implode(', ', $segments);
            $surplusCount = (count($array) - $arraySampleSize);
            $isSample = ($surplusCount > 0);

            if ($isSample) {
                $return .= sprintf(
                    ', %s and %d more %s',
                    $caster->getSampleEllipsis(),
                    $surplusCount,
                    (1 === $surplusCount ? 'element' : 'elements')
                );
            }

            $return .= ']';
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
     * {@inheritDoc}
     */
    public function isHandling(array $array): bool
    {
        return true;
    }
}
