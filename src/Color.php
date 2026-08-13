<?php

declare(strict_types=1);

namespace Codedungeon\PHPUnitPrettyResultPrinter;

final class Color
{
    /**
     * @var array<string, string>
     */
    private const ANSI_CODES = [
        'reset'      => '0',
        'bold'       => '1',
        'fg-black'   => '30',
        'fg-red'     => '31',
        'fg-green'   => '32',
        'fg-yellow'  => '33',
        'fg-blue'    => '34',
        'fg-magenta' => '35',
        'fg-cyan'    => '36',
        'fg-white'   => '37',
    ];

    public static function colorize(string $color, string $buffer): string
    {
        if (trim($buffer) === '') {
            return $buffer;
        }

        $styles = [];

        foreach (explode(',', $color) as $code) {
            $code = trim($code);

            if (isset(self::ANSI_CODES[$code])) {
                $styles[] = self::ANSI_CODES[$code];
            }
        }

        if ($styles === []) {
            return $buffer;
        }

        return sprintf("\033[%sm%s\033[0m", implode(';', $styles), $buffer);
    }
}
