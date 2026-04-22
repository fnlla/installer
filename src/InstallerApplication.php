<?php

declare(strict_types=1);

namespace Fnlla\Installer;

final class InstallerApplication
{
    /**
     * @param list<string> $argv
     */
    public function run(array $argv): int
    {
        array_shift($argv);
        $command = $argv[0] ?? '';

        if ($command === '' || $command === 'help' || $command === '--help' || $command === '-h') {
            $this->printHelp();
            return 0;
        }

        if ($command === '--version' || $command === '-V') {
            fwrite(STDOUT, "fnlla installer dev-main\n");
            return 0;
        }

        if ($command !== 'new') {
            fwrite(STDERR, "Unknown command: {$command}\n\n");
            $this->printHelp();
            return 1;
        }

        return $this->runNewCommand(array_slice($argv, 1));
    }

    /**
     * @param list<string> $args
     */
    private function runNewCommand(array $args): int
    {
        if ($args === []) {
            fwrite(STDERR, "Usage: fnlla new <project-name> [options]\n");
            return 1;
        }

        $projectName = '';
        $options = [];
        foreach ($args as $arg) {
            if ($projectName === '' && !str_starts_with($arg, '-')) {
                $projectName = $arg;
                continue;
            }
            $options[] = $arg;
        }

        if ($projectName === '') {
            fwrite(STDERR, "Project name is required.\n");
            return 1;
        }

        if (!$this->isCommandAvailable('composer --version')) {
            fwrite(STDERR, "Composer is required but was not found in PATH.\n");
            return 1;
        }

        $targetPath = $this->normalizeTargetPath($projectName);
        if ($targetPath === false) {
            fwrite(STDERR, "Invalid project path: {$projectName}\n");
            return 1;
        }

        $force = in_array('--force', $options, true);
        if (!$force && $this->directoryHasContents($targetPath)) {
            fwrite(STDERR, "Target directory is not empty: {$targetPath}\n");
            fwrite(STDERR, "Use --force to continue.\n");
            return 1;
        }

        $command = $this->buildCreateProjectCommand($projectName, $options, $force);
        fwrite(STDOUT, "Creating Fnlla application in {$projectName}\n");

        return $this->runShellCommand($command);
    }

    /**
     * @param list<string> $options
     */
    private function buildCreateProjectCommand(string $projectName, array $options, bool $force): array
    {
        $passthrough = [];
        foreach ($options as $option) {
            if ($option === '--force') {
                continue;
            }
            $passthrough[] = $option;
        }

        if (!in_array('--prefer-dist', $passthrough, true) && !in_array('--prefer-source', $passthrough, true)) {
            $passthrough[] = '--prefer-dist';
        }

        if (!in_array('--no-interaction', $passthrough, true) && !in_array('-n', $passthrough, true)) {
            $passthrough[] = '--no-interaction';
        }

        if ($force && !in_array('--remove-vcs', $passthrough, true)) {
            $passthrough[] = '--remove-vcs';
        }

        return [
            ...$this->composerPrefix(),
            'create-project',
            'fnlla/fnlla',
            $projectName,
            ...$passthrough,
        ];
    }

    /**
     * @param list<string> $command
     */
    private function runShellCommand(array $command): int
    {
        $descriptor = [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w'],
        ];

        $process = proc_open($command, $descriptor, $pipes, getcwd() ?: null);
        if (!is_resource($process)) {
            fwrite(STDERR, "Failed to start installer process.\n");
            return 1;
        }

        fclose($pipes[0]);

        while (!feof($pipes[1])) {
            $line = fgets($pipes[1]);
            if ($line === false) {
                break;
            }
            fwrite(STDOUT, $line);
        }

        while (!feof($pipes[2])) {
            $line = fgets($pipes[2]);
            if ($line === false) {
                break;
            }
            fwrite(STDERR, $line);
        }

        fclose($pipes[1]);
        fclose($pipes[2]);

        $exitCode = proc_close($process);
        return is_int($exitCode) ? $exitCode : 1;
    }

    private function directoryHasContents(string $path): bool
    {
        if (!is_dir($path)) {
            return false;
        }

        $items = scandir($path);
        if (!is_array($items)) {
            return true;
        }

        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            return true;
        }

        return false;
    }

    private function normalizeTargetPath(string $projectName): string|false
    {
        $candidate = trim($projectName);
        if ($candidate === '') {
            return false;
        }

        $candidate = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $candidate);
        if (preg_match('/[<>:"|?*]/', $candidate) === 1) {
            return false;
        }

        $base = getcwd();
        if (!is_string($base) || $base === '') {
            return false;
        }

        return $base . DIRECTORY_SEPARATOR . $candidate;
    }

    private function isCommandAvailable(string $command): bool
    {
        $result = [];
        $code = 0;
        exec($command . ' 2>&1', $result, $code);
        return $code === 0;
    }

    /**
     * @return list<string>
     */
    private function composerPrefix(): array
    {
        $composerBinary = getenv('COMPOSER_BINARY');
        if (is_string($composerBinary) && trim($composerBinary) !== '') {
            return [trim($composerBinary)];
        }

        $composerBin = getenv('COMPOSER_BIN');
        if (is_string($composerBin) && trim($composerBin) !== '') {
            return [trim($composerBin)];
        }

        if (PHP_OS_FAMILY === 'Windows') {
            $bat = $this->resolveWindowsComposerBatch();
            if ($bat !== null) {
                return ['cmd', '/c', $bat];
            }

            return ['cmd', '/c', 'composer.bat'];
        }

        return ['composer'];
    }

    private function resolveWindowsComposerBatch(): ?string
    {
        $output = [];
        $code = 0;
        exec('where composer.bat 2>nul', $output, $code);
        if ($code !== 0 || $output === []) {
            return null;
        }

        foreach ($output as $line) {
            $path = trim((string) $line);
            if ($path === '' || !is_file($path)) {
                continue;
            }
            return $path;
        }

        return null;
    }

    private function printHelp(): void
    {
        fwrite(STDOUT, <<<TXT
Fnlla Installer

Usage:
  fnlla new <project-name> [options]
  fnlla --help
  fnlla --version

Examples:
  fnlla new my-app
  fnlla new my-app --stability=stable
  fnlla new my-app --prefer-source

Notes:
  - Runs: composer create-project fnlla/fnlla <project-name>
  - Pass-through options are forwarded to Composer.
  - Use --force to allow creation in an existing non-empty directory.

TXT
        );
    }
}
