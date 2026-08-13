# PHPUnit Pretty Result Printer

Version 1.0.0 — updated for **PHPUnit 11 / 12** and **PHP 8.2+** (including PHP 8.3 / 8.4 / 8.5).

📦 Packagist: [vedmant/phpunit-pretty-result-printer](https://packagist.org/packages/vedmant/phpunit-pretty-result-printer)

This is a maintained fork of [`codedungeon/phpunit-result-printer`](https://github.com/mikeerickson/phpunit-pretty-result-printer). The original package stopped at PHPUnit 9. PHPUnit 10 removed `printerClass` / `--printer`, so this rewrite is a PHPUnit **extension** that still prints the same compact progress line.

```
PHPUnit Pretty Result Printer 1.0.0 by Codedungeon and contributors.
==> Configuration: ~/project/phpunit-printer.yml

 ==> RefreshExternalDomainsCommandTest   ✔ ✔ ✖ ⇢
 ==> SearchTest                          ✔ ✔
```

PHPUnit still prints its own failure details and summary. Only **progress** is replaced (`$facade->replaceProgressOutput()`), so you do **not** need `--no-progress`.

The class-name column uses about 60% of the terminal width (40–60 characters) so typical test names stay readable. Markers are one space apart (`✔ ✔`). Very long names still get a leading ellipsis.

---

## Installation

Install from [Packagist](https://packagist.org/packages/vedmant/phpunit-pretty-result-printer):

```bash
composer require --dev vedmant/phpunit-pretty-result-printer
```

Requires **PHP 8.2+** and **PHPUnit 11 or 12**.

If you previously used the abandoned `codedungeon/phpunit-result-printer` package, this fork `replace`s it so Composer can swap them cleanly.

### Register the extension

Add the following to `phpunit.xml` (or `phpunit.xml.dist`):

```xml
<extensions>
    <bootstrap class="Codedungeon\PHPUnitPrettyResultPrinter\Extension"/>
</extensions>
```

### Optional initialization script

Copies default `phpunit-printer.yml` to the project root and writes the `<extensions><bootstrap …>` block into `phpunit.xml` (and removes a leftover `printerClass` attribute if present):

```bash
php vendor/vedmant/phpunit-pretty-result-printer/src/init.php
```

Do **not** use `--printer=…` or `printerClass=` — those options no longer exist in PHPUnit 10+.

### AnyBar Integration

If you have [AnyBar](https://github.com/tonsky/AnyBar) installed, enable it with `cd-printer-anybar: true` in `phpunit-printer.yml`. It is **off** by default (CI-friendly). AnyBar is only used on macOS.

### Configuration Options

Create a `phpunit-printer.yml` file in your application root to override the package default (or anywhere up the parent tree. It will search recursively up the tree until a configuration file is found. If not found, the package default in `src/phpunit-printer.yml` is used).

#### Options

| **Property Name**                  | **Default** | **Description**                                                      |
| ---------------------------------- | ----------- | -------------------------------------------------------------------- |
| `cd-printer-hide-class`            | false       | Hides the display of the test class name                             |
| `cd-printer-simple-output`         | false       | Uses the default PHPUnit markers (but still uses this printer)       |
| `cd-printer-show-config`           | true        | Show path to used configuration file                                 |
| `cd-printer-hide-namespace`        | true        | Hide test class namespaces (will only show the class name)           |
| `cd-printer-anybar`                | false       | Enable AnyBar (ignored if AnyBar is not installed / not macOS)       |
| `cd-printer-anybar-port`           | 1738        | Define AnyBar port number                                            |
| `cd-printer-dont-format-classname` | false       | Show entire classname without padding / ellipsis                     |

- Class names are padded and, if needed, ellipsized to about 60% of the terminal width (40–60 characters) unless `cd-printer-dont-format-classname` is `true`
- If `cd-printer-dont-format-classname` is `true`, nothing is formatted and the full classname is displayed

#### Markers

Customize markers by modifying `phpunit-printer.yml`.

| **Marker**       | **Value** \* |
| ---------------- | ------------ |
| cd-pass          | "✔ "         |
| cd-fail          | "✖ "         |
| cd-error         | "⚈ "         |
| cd-skipped       | "⇢ "         |
| cd-incomplete    | "∅ "         |
| cd-risky         | "⌽ "         |
| cd-warning       | "⚠ "         |
| cd-deprecation   | "▲ "         |
| cd-notice        | "ℹ "         |

\* Notice the single space after each marker. That is the gap between checks (`✔ ✔`); keep it when creating your own custom markers..

## License

Copyright &copy; 2017-2026 Mike Erickson and contributors  
Released under the MIT license

## Credits

Original package by Mike Erickson

E-Mail: [codedungeon@gmail.com](mailto:codedungeon@gmail.com)

Twitter: [@codedungeon](http://twitter.com/codedungeon)

Website: [https://github.com/mikeerickson](https://github.com/mikeerickson)

Fork: [https://github.com/vedmant/phpunit-pretty-result-printer](https://github.com/vedmant/phpunit-pretty-result-printer)

### Screenshot

![Screenshot](sample.png)
