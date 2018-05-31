<?php declare(strict_types=1);

namespace DaveRandom\Serial;

final class CommandResult
{
    private $exitCode;
    private $stdOut;
    private $stdErr;

    public function __construct(int $exitCode, string $stdOut, string $stdErr)
    {
        $stdOut = \trim($stdOut);
        $stdErr = \trim($stdErr);

        $this->exitCode = $exitCode;
        $this->stdOut = $stdOut !== '' ? $stdOut : null;
        $this->stdErr = $stdErr !== '' ? $stdErr : null;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    public function getStdOut(): ?string
    {
        return $this->stdOut;
    }

    public function getStdErr(): ?string
    {
        return $this->stdErr;
    }
}
