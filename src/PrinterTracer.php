<?php

declare(strict_types=1);

namespace Codedungeon\PHPUnitPrettyResultPrinter;

use PHPUnit\Event\Event;
use PHPUnit\Event\Test\ConsideredRisky;
use PHPUnit\Event\Test\DeprecationTriggered;
use PHPUnit\Event\Test\Errored;
use PHPUnit\Event\Test\Failed;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\MarkedIncomplete;
use PHPUnit\Event\Test\NoticeTriggered;
use PHPUnit\Event\Test\PhpDeprecationTriggered;
use PHPUnit\Event\Test\PhpNoticeTriggered;
use PHPUnit\Event\Test\PhpunitDeprecationTriggered;
use PHPUnit\Event\Test\PhpunitWarningTriggered;
use PHPUnit\Event\Test\PhpWarningTriggered;
use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\Skipped;
use PHPUnit\Event\Test\WarningTriggered;
use PHPUnit\Event\TestRunner\ExecutionFinished;
use PHPUnit\Event\TestRunner\ExecutionStarted;
use PHPUnit\Event\Tracer\Tracer;

final class PrinterTracer implements Tracer
{
    public function __construct(private readonly Printer $printer)
    {
    }

    public function trace(Event $event): void
    {
        if ($event instanceof ExecutionStarted) {
            $this->printer->executionStarted();

            return;
        }

        if ($event instanceof Prepared) {
            $this->printer->testPrepared($event->test());

            return;
        }

        if ($event instanceof Skipped) {
            $this->printer->testSkipped($event->test());

            return;
        }

        if ($event instanceof Errored) {
            $this->printer->testErrored($event->test());

            return;
        }

        if ($event instanceof Failed) {
            $this->printer->testFailed();

            return;
        }

        if ($event instanceof MarkedIncomplete) {
            $this->printer->updateStatus('incomplete');

            return;
        }

        if ($event instanceof ConsideredRisky) {
            $this->printer->updateStatus('risky');

            return;
        }

        if ($event instanceof WarningTriggered
            || $event instanceof PhpWarningTriggered
            || $event instanceof PhpunitWarningTriggered) {
            $this->printer->updateStatus('warning');

            return;
        }

        if ($event instanceof DeprecationTriggered
            || $event instanceof PhpDeprecationTriggered
            || $event instanceof PhpunitDeprecationTriggered) {
            $this->printer->updateStatus('deprecation');

            return;
        }

        if ($event instanceof NoticeTriggered || $event instanceof PhpNoticeTriggered) {
            $this->printer->updateStatus('notice');

            return;
        }

        if ($event instanceof Finished) {
            $this->printer->testFinished();

            return;
        }

        if ($event instanceof ExecutionFinished) {
            $this->printer->executionFinished();
        }
    }
}
