<?php

declare(strict_types=1);

namespace Eboreum\Caster\Formatter;

use Eboreum\Caster\Abstraction\Formatter\AbstractStringFormatter;
use Eboreum\Caster\Contract\CasterInterface;

use function array_walk;
use function assert;
use function implode;
use function is_array;
use function is_string;
use function max;
use function mb_strlen;
use function mb_substr;
use function ord;
use function preg_replace_callback;
use function preg_split;
use function sprintf;

class DefaultStringFormatter extends AbstractStringFormatter
{
    public function convertASCIIControlCharactersToHexAnnotation(string $string): string
    {
        $string = preg_replace_callback(
            '/[\x00-\x1f\x7f]/',
            static function (array $matches): string {
                return sprintf('\\x%02x', ord($matches[0]));
            },
            $string,
        );

        assert(is_string($string));

        return $string;
    }

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

                if ($caster->isConvertingASCIIControlCharactersToHexAnnotationInStrings()) {
                    $return = $this->convertASCIIControlCharactersToHexAnnotation($return);
                } elseif ($caster->isWrapping()) {
                    /**
                     * We use an "elseif" here, because converting ASCII control characters also means any line break
                     * will disappear, making wrapping pointless.
                     */
                    $indented = $this->indent($caster, $return);

                    if ($indented !== $return) {
                        $indented .= ' (indented)';
                    }

                    $return = $indented;
                }
            } else {
                $return = $caster->quoteAndEscape($caster->getSampleEllipsis());
                $isSample = true;
            }
        } else {
            $return = $caster->quoteAndEscape($string);

            if ($caster->isConvertingASCIIControlCharactersToHexAnnotationInStrings()) {
                $return = $this->convertASCIIControlCharactersToHexAnnotation($return);
            } elseif ($caster->isWrapping()) {
                /**
                 * We use an "elseif" here, because converting ASCII control characters also means any line break will
                 * disappear, making wrapping pointless.
                 */
                $indented = $this->indent($caster, $return);

                if ($indented !== $return) {
                    $indented .= ' (indented)';
                }

                $return = $indented;
            }
        }

        if ($isSample) {
            $return .= ' (sample)';
        }

        return $return;
    }

    public function indent(CasterInterface $caster, string $string): string
    {
        $lines = preg_split('/(\r?\n|\r)/', $string);

        assert(is_array($lines));

        array_walk($lines, static function (string &$line, int $index) use ($caster): void {
            if (0 === $index) {
                return;
            }

            $line = $caster->getWrappingIndentationCharacters() . $line;
        });

        return implode("\n", $lines);
    }

    public function isHandling(string $string): bool
    {
        return true;
    }
}
