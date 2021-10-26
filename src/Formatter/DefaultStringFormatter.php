<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractStringFormatter;
use Eboreum\Caster\Contract\CasterInterface;

class DefaultStringFormatter extends AbstractStringFormatter
{
    /**
     * {@inheritDoc}
     */
    public function format(CasterInterface $caster, string $string): ?string
    {
        $length = mb_strlen(
            $string,
            (string)$caster->getCharacterEncoding(),
        );
        $return = '';
        $isSample = false;
        $encodingStr = (string)$caster->getCharacterEncoding();

        if ($caster->isMakingSamples()) {
            if ('' === $string) {
                $return = $caster->quoteAndEscape('');
            } elseif ($caster->getStringSampleSize()->toInteger() > 0) {
                if ($length > $caster->getStringSampleSize()->toInteger()) {
                    $ellipsisLength = mb_strlen($caster->getSampleEllipsis(), $encodingStr);
                    $return = mb_substr(
                        $string,
                        0,
                        max(
                            0,
                            (
                                $caster->getStringSampleSize()->toInteger()
                                - ($ellipsisLength + 1)
                            )
                        ),
                        $encodingStr
                    );

                    if ($return) {
                        $return .= ' ' . $caster->getSampleEllipsis();
                    } else {
                        $return = $caster->getSampleEllipsis();
                    }

                    $isSample = true;
                } else {
                    $return = $string;
                }

                $return = $caster->quoteAndEscape($return);
            } else {
                $return = $caster->quoteAndEscape($caster->getSampleEllipsis());
                $isSample = true;
            }
        } else {
            $return = $caster->quoteAndEscape($string);
        }

        if ($isSample) {
            $return .= ' (sample)';
        }

        return $return;
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(string $string): bool
    {
        return true;
    }
}
