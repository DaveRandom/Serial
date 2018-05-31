<?php declare(strict_types=1);

namespace DaveRandom\Serial;

use DaveRandom\Serial\Exceptions\DeviceAlreadyClosedException;
use DaveRandom\Serial\Exceptions\ReadOperationFailedException;
use DaveRandom\Serial\Exceptions\WriteOperationFailedException;

final class Device
{
    public const DEFAULT_READ_SIZE = 1024;

    private $id;
    private $fp;

    public function __construct(int $id, $fp)
    {
        if (!\is_resource($fp) || \get_resource_type($fp) !== 'stream') {
            throw new \TypeError("File pointer must be resource of type 'stream'");
        }

        $this->id = $id;
        $this->fp = $fp;
    }

    public function __destruct()
    {
        if ($this->fp !== null) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->close();
        }
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @throws ReadOperationFailedException
     * @throws DeviceAlreadyClosedException
     */
    public function read(int $length = self::DEFAULT_READ_SIZE): string
    {
        if ($this->fp === null) {
            throw new DeviceAlreadyClosedException('Cannot read from device that is already closed');
        }

        $result = \DaveRandom\Serial\capture_errors(function() use($length) {
            return \fread($this->fp, $length);
        }, $errNo, $errMessage);

        if ($errNo !== null) {
            throw new ReadOperationFailedException(\trim($errMessage), $errNo);
        }

        if ($result === false) {
            throw new ReadOperationFailedException('Unknown error');
        }

        return $result;
    }

    /**
     * @throws WriteOperationFailedException
     * @throws DeviceAlreadyClosedException
     */
    public function write(string $data, int $length = null): int
    {
        if ($this->fp === null) {
            throw new DeviceAlreadyClosedException('Cannot write to device that is already closed');
        }

        $result = \DaveRandom\Serial\capture_errors(function() use($data, $length) {
            return \fwrite($this->fp, $data, $length ?? \strlen($data));
        }, $errNo, $errMessage);

        if ($errNo !== null) {
            throw new WriteOperationFailedException(\trim($errMessage), $errNo);
        }

        if ($result === false) {
            throw new WriteOperationFailedException('Unknown error');
        }

        return $result;
    }

    /**
     * @throws DeviceAlreadyClosedException
     */
    public function close(): void
    {
        if ($this->fp === null) {
            throw new DeviceAlreadyClosedException('Cannot close device that is already closed');
        }

        @\fclose($this->fp);
        $this->fp = null;
    }
}
