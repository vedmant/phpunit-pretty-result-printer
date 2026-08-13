<?php

declare(strict_types=1);

namespace Codedungeon\PHPUnitPrettyResultPrinter;

use Bakyt\Console\Phanybar;
use PHPUnit\Event\Code\Test;
use PHPUnit\Event\Code\TestMethod;
use Symfony\Component\Yaml\Yaml;

final class Printer
{
    /**
     * @var array<string, int>
     */
    private const STATUS_PRIORITY = [
        'pass'         => 0,
        'skipped'      => 1,
        'incomplete'   => 2,
        'risky'        => 3,
        'notice'       => 4,
        'deprecation'  => 5,
        'warning'      => 6,
        'fail'         => 7,
        'error'        => 8,
    ];

    private static bool $bannerPrinted = false;

    private string $className = '';

    private string $lastClassName = '';

    private int $column = 0;

    private int $maxClassNameLength = 50;

    private int $maxNumberOfColumns;

    private bool $hideClassName = false;

    private bool $simpleOutput = false;

    private bool $showConfig = true;

    private bool $hideNamespace = true;

    private bool $dontFormatClassName = false;

    private bool $anyBarEnabled = false;

    private int $anyBarPort = 1738;

    private string $configFileName = '';

    /**
     * @var array<string, string>
     */
    private array $markers = [];

    private ?string $status = null;

    private bool $prepared = false;

    private int $failureCount = 0;

    private int $errorCount = 0;

    /**
     * @param resource $stream
     */
    public function __construct(
        private readonly bool $colors,
        int $columns,
        private readonly bool $debug,
        private $stream,
    ) {
        $this->loadConfiguration();

        $this->maxNumberOfColumns = max(16, $columns - 5);
        $this->maxClassNameLength = min((int) ($this->maxNumberOfColumns / 2), $this->maxClassNameLength);

        if ($this->hideNamespace) {
            $this->maxClassNameLength = 32;
        }
    }

    public function executionStarted(): void
    {
        $this->printBanner();
    }

    public function testPrepared(Test $test): void
    {
        $this->className = $this->classNameFromTest($test);
        $this->prepared  = true;
        $this->status    = null;
    }

    public function updateStatus(string $status): void
    {
        if ($this->status !== null && self::STATUS_PRIORITY[$this->status] >= self::STATUS_PRIORITY[$status]) {
            return;
        }

        $this->status = $status;
    }

    public function testSkipped(Test $test): void
    {
        if (!$this->prepared) {
            $this->className = $this->classNameFromTest($test);
            $this->printProgress('skipped');

            return;
        }

        $this->updateStatus('skipped');
    }

    public function testErrored(Test $test): void
    {
        $this->errorCount++;

        if (!$this->prepared) {
            $this->className = $this->classNameFromTest($test);
            $this->printProgress('error');

            return;
        }

        $this->updateStatus('error');
    }

    public function testFailed(): void
    {
        $this->failureCount++;
        $this->updateStatus('fail');
    }

    public function testFinished(): void
    {
        if (!$this->prepared) {
            return;
        }

        $this->printProgress($this->status ?? 'pass');
        $this->prepared = false;
        $this->status   = null;
    }

    public function executionFinished(): void
    {
        $this->write(PHP_EOL);

        if (!$this->anyBarEnabled) {
            return;
        }

        $phanyBar = new Phanybar();

        if ($this->failureCount > 0 || $this->errorCount > 0) {
            $phanyBar->send('exclamation', $this->anyBarPort);

            return;
        }

        $phanyBar->send('green', $this->anyBarPort);
    }

    public function packageName(): string
    {
        return $this->composerValue('description', 'PHPUnit Pretty Result Printer');
    }

    public function version(): string
    {
        return $this->composerValue('version', '<unknown>');
    }

    public function getVersion(): string
    {
        return $this->version();
    }

    public function getConfigurationFile(string $configFileName = 'phpunit-printer.yml'): string
    {
        $defaultConfigFilename = $this->packageRoot() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $configFileName;
        $configPath            = getcwd() ?: $this->packageRoot();

        while (true) {
            $filename = $configPath . DIRECTORY_SEPARATOR . $configFileName;

            if (is_file($filename)) {
                return $filename;
            }

            if (($this->isWindows() && strlen($configPath) === 3) || $configPath === '/') {
                return $defaultConfigFilename;
            }

            $configPath = dirname($configPath);
        }
    }

    public function formatClassName(string $className): string
    {
        $prefix   = ' ==> ';
        $ellipsis = '...';
        $suffix   = '   ';

        if ($this->hideNamespace && strrpos($className, '\\') !== false) {
            $className = substr($className, strrpos($className, '\\') + 1);
        }

        if ($this->dontFormatClassName) {
            return $prefix . $className . $suffix;
        }

        $formattedClassName = $prefix . $className . $suffix;

        if (strlen($formattedClassName) <= $this->maxClassNameLength) {
            return str_pad($formattedClassName, $this->maxClassNameLength);
        }

        $maxLength = $this->maxClassNameLength - strlen($prefix . $ellipsis . $suffix);

        return $prefix . $ellipsis . substr($className, strlen($className) - $maxLength, $maxLength) . $suffix;
    }

    public function markerFor(string $status): string
    {
        return $this->markers[$status] ?? $status;
    }

    private function printProgress(string $status): void
    {
        if (!$this->debug) {
            $this->printClassName();
        }

        $this->printTestCaseStatus($status);
    }

    private function printClassName(): void
    {
        if ($this->hideClassName || $this->lastClassName === $this->className) {
            return;
        }

        $this->write(PHP_EOL);
        $formatted = $this->formatClassName($this->className);
        $this->write($this->colorize('fg-cyan,bold', $formatted));
        $this->column        = strlen($formatted) + 1;
        $this->lastClassName = $this->className;
    }

    private function printTestCaseStatus(string $status): void
    {
        if ($this->column >= $this->maxNumberOfColumns) {
            $this->write(PHP_EOL . str_pad(' ', $this->maxClassNameLength));
            $this->column = $this->maxClassNameLength;
        }

        $color  = $this->colorFor($status);
        $buffer = $this->simpleOutput ? $this->simpleMarkerFor($status) : $this->markerFor($status);

        if ($this->debug) {
            $buffer .= ' ' . ucfirst($status);
        }

        $buffer .= ' ';

        $this->write($this->colorize($color, $buffer));

        if ($this->debug) {
            $this->write(PHP_EOL);
        }

        $this->column += 3;
    }

    private function printBanner(): void
    {
        if (self::$bannerPrinted) {
            return;
        }

        self::$bannerPrinted = true;

        $this->write(PHP_EOL);
        $this->write($this->colorize('fg-green', $this->packageName() . ' ' . $this->version() . ' by Codedungeon and contributors.') . PHP_EOL);

        if (!$this->showConfig) {
            $this->write(PHP_EOL);

            return;
        }

        $home     = getenv('HOME') ?: '';
        $filename = $home !== '' ? str_replace($home, '~', $this->configFileName) : $this->configFileName;

        $this->write($this->colorize('fg-yellow', '==> Configuration: ' . $filename) . PHP_EOL . PHP_EOL);
    }

    private function loadConfiguration(): void
    {
        $defaults = $this->parseYaml($this->packageRoot() . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'phpunit-printer.yml');
        $this->configFileName = $this->getConfigurationFile();
        $userConfig           = is_file($this->configFileName) ? $this->parseYaml($this->configFileName) : [];

        $options = array_merge($defaults['options'] ?? [], $userConfig['options'] ?? []);
        $markers = array_merge($defaults['markers'] ?? [], $userConfig['markers'] ?? []);

        $this->hideClassName       = (bool) ($options['cd-printer-hide-class'] ?? false);
        $this->simpleOutput        = (bool) ($options['cd-printer-simple-output'] ?? false);
        $this->showConfig          = (bool) ($options['cd-printer-show-config'] ?? true);
        $this->hideNamespace       = (bool) ($options['cd-printer-hide-namespace'] ?? true);
        $this->anyBarEnabled       = (bool) ($options['cd-printer-anybar'] ?? false);
        $this->anyBarPort          = (int) ($options['cd-printer-anybar-port'] ?? 1738);
        $this->dontFormatClassName = (bool) ($options['cd-printer-dont-format-classname'] ?? false);

        if (!str_contains(php_uname(), 'Darwin')) {
            $this->anyBarEnabled = false;
        }

        $this->markers = [
            'pass'        => (string) ($markers['cd-pass'] ?? '✔ '),
            'fail'        => (string) ($markers['cd-fail'] ?? '✖ '),
            'error'       => (string) ($markers['cd-error'] ?? '⚈ '),
            'skipped'     => (string) ($markers['cd-skipped'] ?? '⇢ '),
            'incomplete'  => (string) ($markers['cd-incomplete'] ?? '∅ '),
            'risky'       => (string) ($markers['cd-risky'] ?? '⌽ '),
            'warning'     => (string) ($markers['cd-warning'] ?? '⚠ '),
            'deprecation' => (string) ($markers['cd-deprecation'] ?? '▲ '),
            'notice'      => (string) ($markers['cd-notice'] ?? 'ℹ '),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function parseYaml(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        $parsed = Yaml::parseFile($path);

        return is_array($parsed) ? $parsed : [];
    }

    private function classNameFromTest(Test $test): string
    {
        if ($test instanceof TestMethod) {
            return $test->className();
        }

        return $test->id();
    }

    private function colorFor(string $status): string
    {
        return match ($status) {
            'pass'        => 'fg-green',
            'skipped'     => 'fg-yellow,bold',
            'incomplete'  => 'fg-blue,bold',
            'fail', 'error' => 'fg-red,bold',
            'risky'       => 'fg-magenta,bold',
            'warning', 'deprecation', 'notice' => 'fg-yellow,bold',
            default       => 'fg-white',
        };
    }

    private function simpleMarkerFor(string $status): string
    {
        return match ($status) {
            'pass'        => '.',
            'skipped'     => 'S',
            'incomplete'  => 'I',
            'fail'        => 'F',
            'error'       => 'E',
            'risky'       => 'R',
            'warning'     => 'W',
            'deprecation' => 'D',
            'notice'      => 'N',
            default       => '?',
        };
    }

    private function colorize(string $color, string $buffer): string
    {
        if (!$this->colors) {
            return $buffer;
        }

        return Color::colorize($color, $buffer);
    }

    private function write(string $buffer): void
    {
        fwrite($this->stream, $buffer);
    }

    private function composerValue(string $key, string $default): string
    {
        $content = file_get_contents($this->packageRoot() . DIRECTORY_SEPARATOR . 'composer.json');

        if ($content === false) {
            return $default;
        }

        $decoded = json_decode($content, true);

        if (!is_array($decoded) || !isset($decoded[$key]) || !is_string($decoded[$key])) {
            return $default;
        }

        return $decoded[$key];
    }

    private function packageRoot(): string
    {
        return dirname(__DIR__);
    }

    private function isWindows(): bool
    {
        return strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
    }
}
