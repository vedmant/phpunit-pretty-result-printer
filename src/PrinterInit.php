<?php

declare(strict_types=1);

namespace Codedungeon\PHPUnitPrettyResultPrinter;

final class PrinterInit
{
    private const GREEN   = "\033[32m";
    private const RESET   = "\033[0m";
    private const CYAN    = "\033[36m";
    private const RED     = "\033[31m";
    private const YELLOW  = "\033[0;33m";
    private const LYELLOW = "\033[33;01m";
    private const LWHITE  = "\033[37;01m";

    private const EXTENSION_CLASS = Extension::class;

    /**
     * @param  list<string>  $options
     */
    public function init(string $use_colors = 'never', array $options = []): int
    {
        $phpunitXmlFile = './phpunit.xml';

        if (!is_file($phpunitXmlFile)) {
            $phpunitXmlFile = './phpunit.xml.dist';
        }

        echo self::CYAN . "\n==> Configuring phpunit-pretty-result-printer\n" . self::RESET;
        echo "\n    " . self::LWHITE . '[•  ]' . self::GREEN . " Gathering installation details\n" . self::RESET;

        $result = $this->addExtensionToPhpunitXml($phpunitXmlFile);
        $this->copyDefaultSettings('phpunit-printer.yml');

        echo self::CYAN . "\n==> Configuration Complete\n" . self::RESET;
        echo "\n";

        return $result;
    }

    private function addExtensionToPhpunitXml(string $file = './phpunit.xml'): int
    {
        if (!is_file($file)) {
            echo self::RED . '    [•• ] Unable to locate valid ' . self::YELLOW . $file . self::RED . ' file, you will need to register the extension manually' . "\n" . self::RESET;

            return -43;
        }

        $contents = file_get_contents($file);

        if ($contents === false) {
            return -43;
        }

        if (str_contains($contents, self::EXTENSION_CLASS)) {
            echo self::LWHITE . '    [•• ]' . self::LYELLOW . ' Pretty printer extension already configured in ' . self::CYAN . $file . " \n" . self::RESET;

            return 0;
        }

        $xml = simplexml_load_file($file);

        if ($xml === false) {
            echo self::RED . '    [•• ] Unable to parse ' . self::YELLOW . $file . "\n" . self::RESET;

            return -43;
        }

        if (isset($xml['printerClass'])) {
            unset($xml['printerClass']);
        }

        $extensions = $xml->extensions ?? $xml->addChild('extensions');
        $bootstrap  = $extensions->addChild('bootstrap');
        $bootstrap->addAttribute('class', self::EXTENSION_CLASS);

        $xml->asXML($file);

        echo self::LWHITE . '    [•• ]' . self::GREEN . ' Pretty printer extension added to ' . self::CYAN . $file . self::GREEN . " file\n" . self::RESET;

        return 1;
    }

    private function copyDefaultSettings(string $file = 'phpunit-printer.yml'): void
    {
        $packageDefaultSettingFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $file;

        if (!is_file($packageDefaultSettingFile)) {
            echo self::LWHITE . '    [••E]' . self::RED . " An error occurred preparing configuration file\n" . self::RESET;

            return;
        }

        if (is_file($file)) {
            echo self::LWHITE . '    [•••]' . self::LYELLOW . ' Configuration ' . self::CYAN . './' . $file . self::LYELLOW . " already exists\n" . self::RESET;

            return;
        }

        copy($packageDefaultSettingFile, $file);
        echo self::LWHITE . '    [•••]' . self::GREEN . ' Configuration ' . self::CYAN . './' . $file . self::GREEN . " copied to project root\n" . self::RESET;
    }
}
