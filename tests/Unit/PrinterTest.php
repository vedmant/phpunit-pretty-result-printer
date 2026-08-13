<?php

declare(strict_types=1);

use Codedungeon\PHPUnitPrettyResultPrinter\Printer;
use PHPUnit\Framework\TestCase;

final class PrinterTest extends TestCase
{
    /** @var resource */
    private $stream;

    private Printer $printer;

    protected function setUp(): void
    {
        $this->stream  = fopen('php://memory', 'r+');
        $this->printer = new Printer(false, 80, false, $this->stream);
    }

    protected function tearDown(): void
    {
        if (is_resource($this->stream)) {
            fclose($this->stream);
        }
    }

    public function testPackageName(): void
    {
        $this->assertSame('PHPUnit Pretty Result Printer', $this->printer->packageName());
    }

    public function testVersion(): void
    {
        $this->assertNotSame('', $this->printer->version());
        $this->assertSame($this->printer->version(), $this->printer->getVersion());
    }

    public function testGetConfigurationFileFindsPhpunitPrinterYml(): void
    {
        $path = $this->printer->getConfigurationFile();

        $this->assertStringContainsString('phpunit-printer.yml', $path);
        $this->assertFileExists($path);
    }

    public function testFormatClassNameAddsPrefixAndPadding(): void
    {
        $formatted = $this->printer->formatClassName('FooTest');

        $this->assertStringStartsWith(' ==> ', $formatted);
        $this->assertStringContainsString('FooTest', $formatted);
        $this->assertSame(47, strlen($formatted));
    }

    public function testFormatClassNameKeepsTypicalLongClassNames(): void
    {
        $formatted = $this->printer->formatClassName('RefreshExternalDomainsCommandTest');

        $this->assertStringStartsWith(' ==> RefreshExternalDomainsCommandTest', $formatted);
        $this->assertStringNotContainsString('...', $formatted);
    }

    public function testFormatClassNameKeepsNamespaceWhenConfigured(): void
    {
        $formatted = $this->printer->formatClassName('App\\Tests\\ExampleTest');

        $this->assertStringStartsWith(' ==> ', $formatted);
        $this->assertStringContainsString('App\\Tests\\ExampleTest', $formatted);
    }

    public function testFormatClassNameHidesNamespaceWhenConfigured(): void
    {
        $property = new ReflectionProperty(Printer::class, 'hideNamespace');
        $property->setValue($this->printer, true);

        $formatted = $this->printer->formatClassName('App\\Tests\\ExampleTest');

        $this->assertStringStartsWith(' ==> ', $formatted);
        $this->assertStringContainsString('ExampleTest', $formatted);
        $this->assertStringNotContainsString('App\\Tests\\', $formatted);
    }

    public function testMarkerForPass(): void
    {
        $this->assertSame('✔ ', $this->printer->markerFor('pass'));
    }
}
