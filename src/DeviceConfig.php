<?php declare(strict_types=1);

namespace DaveRandom\Serial;

use DaveRandom\Serial\Exceptions\InvalidArgumentException;

final class DeviceConfig
{
    private $baudRate;
    private $parityMode;
    private $dataBits;
    private $stopBits;
    private $timeout;
    private $xOnXOff;
    private $ctsHandshaking;
    private $dsrHandshaking;
    private $dsrSensitivity;
    private $dtrMode;
    private $rtsMode;

    public function __construct(
        int $baudRate = 1200,
        int $parityMode = ParityMode::NONE,
        int $dataBits = 7,
        int $stopBits = StopBits::ONE,
        bool $timeout = false,
        bool $xOnXOff = false,
        bool $ctsHandshaking = false,
        bool $dsrHandshaking = false,
        bool $dsrSensitivity = false,
        int $dtrMode = DtrMode::ON,
        int $rtsMode = RtsMode::ON
    ) {
        $this->baudRate = $baudRate;
        $this->parityMode = $parityMode;
        $this->dataBits = $dataBits;
        $this->stopBits = $stopBits;
        $this->timeout = $timeout;
        $this->xOnXOff = $xOnXOff;
        $this->ctsHandshaking = $ctsHandshaking;
        $this->dsrHandshaking = $dsrHandshaking;
        $this->dsrSensitivity = $dsrSensitivity;
        $this->dtrMode = $dtrMode;
        $this->rtsMode = $rtsMode;
    }

    public function getBaudRate(): int
    {
        return $this->baudRate;
    }

    public function setBaudRate(int $baudRate): self
    {
        $this->baudRate = $baudRate;

        return $this;
    }

    public function getParityMode(): int
    {
        return $this->parityMode;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setParityMode(int $parityMode): self
    {
        if (!ParityMode::valueExists($parityMode)) {
            throw new InvalidArgumentException("Unknown parity mode: {$parityMode}");
        }

        $this->parityMode = $parityMode;

        return $this;
    }

    public function getDataBits(): int
    {
        return $this->dataBits;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDataBits(int $dataBits): self
    {
        if ($dataBits < 5 || $dataBits > 8) {
            throw new InvalidArgumentException("Unsupported data bits value: {$dataBits}");
        }

        $this->dataBits = $dataBits;

        return $this;
    }

    public function getStopBits(): int
    {
        return $this->stopBits;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setStopBits(int $stopBits): self
    {
        if (!StopBits::valueExists($stopBits)) {
            throw new InvalidArgumentException("Unsupported stop bits value: {$stopBits}");
        }

        $this->stopBits = $stopBits;

        return $this;
    }

    public function isTimeoutEnabled(): bool
    {
        return $this->timeout;
    }

    public function setTimeoutEnabled(bool $timeout): self
    {
        $this->timeout = $timeout;

        return $this;
    }

    public function isXOnXOffEnabled(): bool
    {
        return $this->xOnXOff;
    }

    public function setXOnXOffEnabled(bool $xOnXOff): self
    {
        $this->xOnXOff = $xOnXOff;

        return $this;
    }

    public function isCtsHandshakingEnabled(): bool
    {
        return $this->ctsHandshaking;
    }

    public function setCtsHandshakingEnabled(bool $ctsHandshaking): self
    {
        $this->ctsHandshaking = $ctsHandshaking;

        return $this;
    }

    public function isDsrHandshakingEnabled(): bool
    {
        return $this->dsrHandshaking;
    }

    public function setDsrHandshakingEnabled(bool $dsrHandshaking): self
    {
        $this->dsrHandshaking = $dsrHandshaking;

        return $this;
    }

    public function isDsrSensitivityEnabled(): bool
    {
        return $this->dsrSensitivity;
    }

    public function setDsrSensitivityEnabled(bool $dsrSensitivity): self
    {
        $this->dsrSensitivity = $dsrSensitivity;

        return $this;
    }

    public function getDtrMode(): int
    {
        return $this->dtrMode;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setDtrMode(int $dtrMode): self
    {
        if (!DtrMode::valueExists($dtrMode)) {
            throw new InvalidArgumentException("Unknown DTR mode: {$dtrMode}");
        }

        $this->dtrMode = $dtrMode;

        return $this;
    }

    public function getRtsMode(): int
    {
        return $this->rtsMode;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setRtsMode(int $rtsMode): self
    {
        if (!RtsMode::valueExists($rtsMode)) {
            throw new InvalidArgumentException("Unknown DTR mode: {$rtsMode}");
        }

        $this->rtsMode = $rtsMode;

        return $this;
    }

    public function getControlFlowMode(): int
    {
        if (!$this->xOnXOff && !$this->ctsHandshaking && $this->rtsMode === RtsMode::ON) {
            return ControlFlowMode::NONE;
        }

        if (!$this->xOnXOff && $this->ctsHandshaking && $this->rtsMode === RtsMode::HANDSHAKE) {
            return ControlFlowMode::RTS_CTS;
        }

        if ($this->xOnXOff && !$this->ctsHandshaking && $this->rtsMode === RtsMode::ON) {
            return ControlFlowMode::XON_XOFF;
        }

        return ControlFlowMode::CUSTOM;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function setControlFlowMode(int $controlFlowMode): self
    {
        switch ($controlFlowMode) {
            case ControlFlowMode::NONE:
                $this->xOnXOff = false;
                $this->ctsHandshaking = false;
                $this->rtsMode = RtsMode::ON;
                break;

            case ControlFlowMode::RTS_CTS:
                $this->xOnXOff = false;
                $this->ctsHandshaking = true;
                $this->rtsMode = RtsMode::HANDSHAKE;
                break;

            case ControlFlowMode::XON_XOFF:
                $this->xOnXOff = true;
                $this->ctsHandshaking = false;
                $this->rtsMode = RtsMode::ON;
                break;

            case ControlFlowMode::CUSTOM:
                throw new InvalidArgumentException(
                    "Cannot set control flow mode to CUSTOM, configure individual parameters instead"
                );

            default:
                throw new InvalidArgumentException("Unknown control flow mode: {$controlFlowMode}");
        }

        return $this;
    }
}
