<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

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
            \Eboreum\Caster\functions\rglob(dirname(TEST_ROOT_PATH) . '/src/*.php'),
            \Eboreum\Caster\functions\rglob(dirname(TEST_ROOT_PATH) . '/script/misc/readme/*.php'),
            \Eboreum\Caster\functions\rglob(TEST_ROOT_PATH . '/resources'),
            \Eboreum\Caster\functions\rglob(TEST_ROOT_PATH . '/tests/*Test.php'),
        );

        $contents = file_get_contents(dirname(TEST_ROOT_PATH) . '/composer.json');

        assert(is_string($contents)); // Make phpstan happy

        $composerJsonArray = json_decode($contents, true);

        assert(is_array($composerJsonArray)); // Make phpstan happy

        $authorNames = [];

        if ($composerJsonArray['authors'] ?? false) {
            assert(is_array($composerJsonArray['authors'])); // Make phpstan happy

            foreach ($composerJsonArray['authors'] as $author) {
                if ($author['homepage'] ?? false) {
                    assert(is_string($author['homepage'])); // Make phpstan happy

                    preg_match(
                        sprintf(
                            '/^%s\/([^\/]+)(\/|$)/',
                            preg_quote('https://github.com', '/'),
                        ),
                        $author['homepage'],
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

        $parser = (new ParserFactory())->create(ParserFactory::PREFER_PHP7);

        foreach ($filePaths as $filePath) {
            if (false === is_file($filePath)) {
                continue;
            }

            if ($filePath === __FILE__) {
                continue;
            }

            $contents = file_get_contents($filePath);

            assert(is_string($contents)); // Make phpstan happy

            foreach ($authorNames as $authorName) {
                preg_match_all(
                    sprintf(
                        '/%s/i',
                        preg_quote(mb_strtolower($authorName), '/'),
                    ),
                    $contents,
                    $matches,
                );

                if ($matches && ($matches[0] ?? false)) {
                    $matchCount = count($matches[0]);
                    $errorMessages[] = sprintf(
                        'Found %d %s of the author name %s in file: %s',
                        $matchCount,
                        (
                            1 === $matchCount
                            ? 'occurrence'
                            : 'occurrences'
                        ),
                        escapeshellarg($authorName),
                        escapeshellarg($filePath),
                    );
                }
            }

            if (preg_match('/^\<\?php/', ltrim($contents))) {
                $ast = $parser->parse($contents);

                assert(is_array($ast)); // Make phpstan happy

                $comments = $this->recursivelyFindAllCommentsInPHPFileAST($ast);

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
                                'Found %d %s of the disallowed text (as a regular expression) %s %s in file: %s:%d',
                                $matchCount,
                                (
                                    1 === $matchCount
                                    ? 'occurrence'
                                    : 'occurrences'
                                ),
                                escapeshellarg($disallowedStringsInCommentRegex),
                                (
                                    1 === $matchCount
                                    ? 'a comment'
                                    : 'comments'
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

    /**
     * @param array<\PhpParser\Node> $ast
     * @return array<\PhpParser\Comment>
     */
    private function recursivelyFindAllCommentsInPHPFileAST(array $ast): array
    {
        $comments = [];

        foreach ($ast as $node) {
            $comments = array_merge(
                $comments,
                $this->handleNode($node)
            );
        }

        return $comments;
    }

    /**
     * @return array<\PhpParser\Comment>
     */
    private function handleNode(\PhpParser\Node $node): array
    {
        $comments = $node->getComments();

        foreach (get_object_vars($node) as $var) {
            $vars = $var;

            if (false === is_array($var)) {
                $vars = [$var];
            }

            assert(is_array($vars)); // Make phpstan happy

            foreach ($vars as $var) {
                if ($var instanceof \PhpParser\Node) {
                    $comments = array_merge(
                        $comments,
                        $this->handleNode($var),
                    );
                }
            }
        }

        return $comments;
    }
}
