<?php

declare(strict_types=1);

namespace Test\Unit\Eboreum\Caster;

use Eboreum\Caster\Functions;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

use function array_merge;
use function assert;
use function count;
use function dirname;
use function escapeshellarg;
use function file_get_contents;
use function get_object_vars;
use function implode;
use function is_array;
use function is_file;
use function is_string;
use function json_decode;
use function ltrim;
use function mb_strtolower;
use function preg_match;
use function preg_match_all;
use function preg_quote;
use function sprintf;

/**
 * {@inheritDoc}
 *
 * Did we leave bogus comments, names, file paths, etc. lying around in the project?
 */
#[CoversNothing]
class BogusScannerTest extends TestCase
{
    public function testAllExampleScriptsWork(): void
    {
        $errorMessages = [];

        $filePaths = array_merge(
            Functions::rglob(dirname(TEST_ROOT_PATH) . '/src/*.php'),
            Functions::rglob(dirname(TEST_ROOT_PATH) . '/script/misc/readme/*.php'),
            Functions::rglob(TEST_ROOT_PATH . '/resources'),
            Functions::rglob(TEST_ROOT_PATH . '/tests/*Test.php'),
        );

        $contents = file_get_contents(dirname(TEST_ROOT_PATH) . '/composer.json');

        assert(is_string($contents)); // Make phpstan happy

        $composerJsonArray = json_decode($contents, true);

        assert(is_array($composerJsonArray)); // Make phpstan happy

        $authorNames = [];

        if ($composerJsonArray['authors'] ?? false) {
            assert(is_array($composerJsonArray['authors'])); // Make phpstan happy

            foreach ($composerJsonArray['authors'] as $author) {
                assert(is_array($author));

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

        $parser = (new ParserFactory())->createForVersion(PhpVersion::fromString('8.3'));

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

                        if ($matches[0]) {
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
     * @param array<Node> $ast
     *
     * @return array<Comment>
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
     * @return array<Comment>
     */
    private function handleNode(Node $node): array
    {
        $comments = $node->getComments();

        foreach (get_object_vars($node) as $var) {
            $vars = $var;

            if (false === is_array($var)) {
                $vars = [$var];
            }

            assert(is_array($vars)); // Make phpstan happy

            foreach ($vars as $var) {
                if ($var instanceof Node) {
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
