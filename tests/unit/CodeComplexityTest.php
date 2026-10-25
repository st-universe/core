<?php

declare(strict_types=1);

namespace Stu;

use FilesystemIterator;
use PhpParser\Error;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeFinder;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PHPUnit\Framework\TestCase;
use RecursiveCallbackFilterIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SebastianBergmann\Complexity\CyclomaticComplexityCalculatingVisitor;
use SplFileInfo;

final class CodeComplexityTest extends TestCase
{
    private const array LIMITS = [
        'methodComplexity' => 10,
        'classComplexity' => 50,
        'methodLength' => 60,
        'classLength' => 500
    ];

    public function testProjectDoesNotIntroduceOrIncreaseViolations(): void
    {
        $root = dirname(__DIR__, 2);
        $baseline = json_decode(file_get_contents($root . '/complexity-baseline.json'), true, 512, JSON_THROW_ON_ERROR);
        $files = $this->sourceFiles($root);
        self::assertNotEmpty($files);
        $cacheFile = $root . '/build/.complexity-cache.json';
        $version = hash('sha256', file_get_contents(__FILE__) . file_get_contents($root . '/composer.lock') . PHP_VERSION);
        $cache = is_file($cacheFile) ? json_decode(file_get_contents($cacheFile), true) : [];
        $cached = is_array($cache) && ($cache['version'] ?? null) === $version ? ($cache['files'] ?? []) : [];
        $updated = [];
        $violations = [];
        foreach ($files as $path => $file) {
            try {
                $updated[$path] = $this->measureCached(file_get_contents($file), $cached[$path] ?? []);
            } catch (Error $error) {
                self::fail($path . ': ' . $error->getMessage());
            }
            foreach ($this->violations($updated[$path]['elements'], $baseline[$path] ?? []) as $violation) {
                $violations[] = $path . ':' . $violation;
            }
        }
        if (!is_dir(dirname($cacheFile)) && !mkdir(dirname($cacheFile), 0o755, true)) {
            throw new \RuntimeException('Das Verzeichnis für den Komplexitätscache konnte nicht angelegt werden');
        }
        $temporary = $cacheFile . '.' . bin2hex(random_bytes(8));
        try {
            if (file_put_contents($temporary, json_encode(['version' => $version, 'files' => $updated], JSON_THROW_ON_ERROR)) === false
                || !rename($temporary, $cacheFile)) {
                throw new \RuntimeException('Der Komplexitätscache konnte nicht gespeichert werden');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }

        self::assertSame([], $violations, "Neue oder erhöhte Komplexitäts- oder Längenüberschreitungen:\n" . implode("\n", $violations));
    }

    public function testEnumsTraitsAndFunctionsIncludeBooleanBranches(): void
    {
        $elements = $this->measure(<<<'PHP'
            <?php
            namespace Example;
            enum State {
                case ACTIVE;
                public function label(): string {
                    return match ($this) { self::ACTIVE => 'active' };
                }
            }
            trait Selection {
                public function allows(bool $first, bool $second): bool {
                    if ($first && $second) {
                        return true;
                    }
                    return false;
                }
            }
            function choose(bool $condition): int {
                return $condition ? 1 : 0;
            }
            PHP);

        self::assertSame(2, $elements['Example\\State::label']['metrics']['methodComplexity']);
        self::assertSame(3, $elements['Example\\Selection::allows']['metrics']['methodComplexity']);
        self::assertSame(2, $elements['function Example\\choose']['metrics']['methodComplexity']);
        self::assertSame(2, $elements['Example\\State']['metrics']['classComplexity']);
    }

    public function testClassComplexitySumsMethodsAndLengthIncludesTheirBodies(): void
    {
        $elements = $this->measure(<<<'PHP'
            <?php
            class Example {
                public function first(bool $condition): int {
                    return $condition ? 1 : 0;
                }

                public function second(): int {
                    return 2;
                }
            }
            PHP);

        self::assertSame(3, $elements['Example']['metrics']['classComplexity']);
        self::assertSame(9, $elements['Example']['metrics']['classLength']);
        self::assertSame(3, $elements['Example::first']['metrics']['methodLength']);
        self::assertSame(3, $elements['Example::first']['line']);
    }

    public function testBaselineOnlyAllowsRecordedMetricsOnTheSameElement(): void
    {
        $elements = [
            'Example::existing' => ['line' => 10, 'metrics' => ['methodComplexity' => 15, 'methodLength' => 61]],
            'Example::added' => ['line' => 20, 'metrics' => ['methodComplexity' => 11]],
            'Example' => ['line' => 2, 'metrics' => ['classComplexity' => 50, 'classLength' => 500]]
        ];
        $baseline = ['Example::existing' => ['methodComplexity' => 14, 'methodLength' => 61]];
        $violations = $this->violations($elements, $baseline);

        self::assertCount(2, $violations);
        self::assertStringContainsString('Example::existing methodComplexity 15 > 14', $violations[0]);
        self::assertStringContainsString('Example::added methodComplexity 11 > 10', $violations[1]);
    }

    public function testBaselineDoesNotLowerGeneralLimitsOrRequireRemovedElements(): void
    {
        $elements = ['Example::existing' => ['line' => 10, 'metrics' => ['methodComplexity' => 10]]];
        $baseline = ['Example::existing' => ['methodComplexity' => 8], 'Example::removed' => ['methodComplexity' => 20]];

        self::assertSame([], $this->violations($elements, $baseline));
    }

    public function testCacheInvalidatesChangedContentsAndKeepsBaselineChecksActive(): void
    {
        $source = '<?php function select(bool $condition): int { return $condition ? 1 : 0; }';
        $cached = $this->measureCached($source, []);
        self::assertSame($cached, $this->measureCached($source, $cached));
        $changed = $this->measureCached(str_replace('$condition ? 1 : 0', '1', $source), $cached);
        self::assertSame(1, $changed['elements']['function select']['metrics']['methodComplexity']);
        self::assertNotSame($cached['hash'], $changed['hash']);
        $source = '<?php function select(): bool { return ' . implode(' && ', array_fill(0, 11, 'true')) . '; }';
        $cached = $this->measureCached($source, $cached);
        $cached = $this->measureCached($source, $cached);
        self::assertCount(1, $this->violations($cached['elements'], []));
        self::assertSame([], $this->violations($cached['elements'], ['function select' => ['methodComplexity' => 11]]));
    }

    public function testCacheDoesNotHideInvalidChangedSource(): void
    {
        $cached = $this->measureCached('<?php function select(): int { return 1; }', []);
        $this->expectException(Error::class);
        $this->measureCached('<?php function select(', $cached);
    }

    private function measureCached(string $source, array $cached): array
    {
        $hash = hash('sha256', $source);
        if (($cached['hash'] ?? null) === $hash && isset($cached['elements'])) {
            return $cached;
        }

        return ['hash' => $hash, 'elements' => $this->measure($source)];
    }

    private function sourceFiles(string $root): array
    {
        $excluded = [
            $root . '/src/html',
            $root . '/src/Migrations',
            $root . '/src/Public/assets',
            $root . '/src/Component/Refactor/RefactorRunner.php'
        ];
        $iterator = new RecursiveIteratorIterator(new RecursiveCallbackFilterIterator(
            new RecursiveDirectoryIterator($root . '/src', FilesystemIterator::SKIP_DOTS),
            static fn (SplFileInfo $entry): bool => !in_array($entry->getPathname(), $excluded, true)
        ));
        $files = [];
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $files[substr($file->getPathname(), strlen($root) + 1)] = $file->getPathname();
            }
        }
        ksort($files);

        return $files;
    }

    private function measure(string $source): array
    {
        $nodes = new ParserFactory()->createForHostVersion()->parse($source);
        $nodes = new NodeTraverser(new NameResolver())->traverse($nodes ?? []);
        $finder = new NodeFinder();
        $elements = [];
        $anonymous = 0;
        foreach ($finder->findInstanceOf($nodes, ClassLike::class) as $class) {
            $name = $class->name === null ? 'anonymous#' . ++$anonymous : $class->namespacedName->toString();
            $total = 0;
            foreach ($class->getMethods() as $method) {
                if ($method->getStmts() === null) {
                    continue;
                }
                $complexity = $this->complexity($method->getStmts());
                $total += $complexity;
                $elements[$name . '::' . $method->name->toString()] = [
                    'line' => $method->getStartLine(),
                    'metrics' => ['methodComplexity' => $complexity, 'methodLength' => $method->getEndLine() - $method->getStartLine() + 1]
                ];
            }
            $elements[$name] = [
                'line' => $class->getStartLine(),
                'metrics' => ['classComplexity' => $total, 'classLength' => $class->getEndLine() - $class->getStartLine() + 1]
            ];
        }
        foreach ($finder->findInstanceOf($nodes, Function_::class) as $function) {
            $elements['function ' . $function->namespacedName->toString()] = [
                'line' => $function->getStartLine(),
                'metrics' => ['methodComplexity' => $this->complexity($function->getStmts()), 'methodLength' => $function->getEndLine() - $function->getStartLine() + 1]
            ];
        }

        return $elements;
    }

    private function complexity(array $statements): int
    {
        $visitor = new CyclomaticComplexityCalculatingVisitor();
        new NodeTraverser($visitor)->traverse($statements);

        return $visitor->cyclomaticComplexity();
    }

    private function violations(array $elements, array $baseline): array
    {
        $violations = [];
        foreach ($elements as $name => $element) {
            foreach ($element['metrics'] as $metric => $value) {
                $limit = max(self::LIMITS[$metric], $baseline[$name][$metric] ?? 0);
                if ($value > $limit) {
                    $violations[] = sprintf('%d %s %s %d > %d', $element['line'], $name, $metric, $value, $limit);
                }
            }
        }

        return $violations;
    }
}
