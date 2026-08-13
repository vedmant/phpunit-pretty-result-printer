<?php

declare(strict_types=1);

require_once __DIR__ . '/PrinterInit.php';

(new Codedungeon\PHPUnitPrettyResultPrinter\PrinterInit())->init('always', $_SERVER['argv'] ?? []);
