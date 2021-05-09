<?php

declare(strict_types = 1);

namespace Eboreum\Caster\Test\Unit;

use PhpParser\Comment;
use PhpParser\NodeFinder;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;

/**
 * Did we leave bogus comments, names, file paths, etc. lying around in the project?
 */
class BogusScannerTest extends TestCase
{
    public function testAllExampleScriptsWork(): void
    {
        $errorMessages = [];

        $filePaths = array_merge(
            \Eboreum\Caster\rglob(dirname(TEST_ROOT_PATH) . "/src/*.php"),
            \Eboreum\Caster\rglob(dirname(TEST_ROOT_PATH) . "/script/misc/readme/*.php"),
            \Eboreum\Caster\rglob(TEST_ROOT_PATH . "/resources"),
            \Eboreum\Caster\rglob(TEST_ROOT_PATH . "/tests/*Test.php"),
        );

        $composerJsonArray = json_decode(file_get_contents(dirname(TEST_ROOT_PATH) . "/composer.json"), true);

        $authorNames = [];

        if ($composerJsonArray["authors"] ?? false) {
            foreach ($composerJsonArray["authors"] as $author) {
                if ($author["homepage"] ?? false) {
                    preg_match(
                        sprintf(
                            '/^%s\/([^\/]+)(\/|$)/',
                            preg_quote("https://github.com", "/"),
                        ),
                        $author["homepage"],
                        $match,
                    );

                    if ($match) {
                        $authorNames[] = $match[1];
                    }
                }
            }
        }

        $disallowedStringsInCommentsRegexes = [
            '/(^|\W)FIXME(\W|$)/i',
            '/(^|\W)TODO(\W|$)/i',
            '/(^|\W)XXX(\W|$)/i',
        ];

        $parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);

        foreach ($filePaths as $filePath) {
            if (false === is_file($filePath)) {
                continue;
            }

            if ($filePath === __FILE__) {
                continue;
            }

            $contents = file_get_contents($filePath);

            foreach ($authorNames as $authorName) {
                preg_match_all(
                    sprintf(
                        '/%s/i',
                        preg_quote(mb_strtolower($authorName), "/"),
                    ),
                    $contents,
                    $matches,
                );

                if ($matches && ($matches[0] ?? false)) {
                    $matchCount = count($matches[0]);
                    $errorMessages[] = sprintf(
                        "Found %d %s of the author name %s in file: %s",
                        $matchCount,
                        (
                            1 === $matchCount
                            ? "occurrence"
                            : "occurrences"
                        ),
                        escapeshellarg($authorName),
                        escapeshellarg($filePath),
                    );
                }
            }

            if (preg_match('/^\<\?php/', ltrim($contents))) {
                $ast = $parser->parse($contents);
                $comments = $this->_recursivelyFindAllCommentsInPHPFileAST($ast);

                foreach ($comments as $comment) {
                    foreach ($disallowedStringsInCommentsRegexes as $disallowedStringsInCommentRegex) {
                        preg_match_all(
                            $disallowedStringsInCommentRegex,
                            $comment->getText(),
                            $matches,
                        );

                        if ($matches && ($matches[0] ?? false)) {
                            $matchCount = count($matches[0]);
                            $errorMessages[] = sprintf(
                                "Found %d %s of the disallowed text (as a regular expression) %s %s in file: %s:%d",
                                $matchCount,
                                (
                                    1 === $matchCount
                                    ? "occurrence"
                                    : "occurrences"
                                ),
                                escapeshellarg($disallowedStringsInCommentRegex),
                                (
                                    1 === $matchCount
                                    ? "a comment"
                                    : "comments"
                                ),
                                escapeshellarg($filePath),
                                $comment->getStartLine(),
                            );
                        }
                    }
                }
            }
        }

        if ($errorMessages) {
            $this->fail(implode("\n", $errorMessages));
        }

        $this->assertCount(0, $errorMessages);
    }

    private function _recursivelyFindAllCommentsInPHPFileAST(array $ast): array
    {
        $comments = [];

        foreach ($ast as $node) {
            $comments = array_merge(
                $comments,
                $this->_handleNode($node)
            );
        }

        return $comments;
    }

    private function _handleNode(\PhpParser\Node $node): array
    {
        $comments = $node->getComments();

        foreach (get_object_vars($node) as $var) {
            $vars = $var;

            if (false === is_array($var)) {
                $vars = [$var];
            }

            foreach ($vars as $var) {
                if ($var instanceof \PhpParser\Node) {
                    $comments = array_merge(
                        $comments,
                        $this->_handleNode($var),
                    );
                }
            }
        }

        return $comments;
    }
}
