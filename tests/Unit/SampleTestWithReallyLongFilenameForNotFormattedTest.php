<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SampleTestWithReallyLongFilenameForNotFormattedTest extends TestCase
{
    public function testItPassesAsVisualCheckForLongClassName(): void
    {
        $this->assertTrue(true);
    }
}
