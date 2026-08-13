<?php

declare(strict_types=1);

namespace Codedungeon\PHPUnitPrettyResultPrinter;

use PHPUnit\Runner\Extension\Extension as PhpunitExtension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

final class Extension implements PhpunitExtension
{
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        if ($configuration->noOutput()) {
            return;
        }

        $stream = $configuration->outputToStandardErrorStream() ? STDERR : STDOUT;

        $printer = new Printer(
            $configuration->colors(),
            $configuration->columns(),
            $configuration->debug(),
            $stream,
        );

        $facade->replaceProgressOutput();
        $facade->registerTracer(new PrinterTracer($printer));
    }
}
