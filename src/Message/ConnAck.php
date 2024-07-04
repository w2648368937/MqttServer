<?php

declare(strict_types=1);

namespace Cherrain\MqttServer\Message;

use Cherrain\MqttServer\Hex\ReasonCode;
use Cherrain\MqttServer\Protocol\ProtocolInterface;
use Cherrain\MqttServer\Protocol\Types;
use Cherrain\MqttServer\Version\V3;
use Cherrain\MqttServer\Version\V5;

class ConnAck extends AbstractMessage
{
    protected $code = ReasonCode::SUCCESS;

    protected $sessionPresent = ProtocolInterface::MQTT_SESSION_PRESENT_0;

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getSessionPresent(): int
    {
        return $this->sessionPresent;
    }

    public function setSessionPresent(int $sessionPresent): self
    {
        $this->sessionPresent = $sessionPresent;

        return $this;
    }

    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::CONNACK,
            'code' => $this->getCode(),
            'session_present' => $this->getSessionPresent(),
        ];

        if ($this->isMQTT5()) {
            $buffer['properties'] = $this->getProperties();
        }

        if ($getArray) {
            return $buffer;
        }

        if ($this->isMQTT5()) {
            return V5::pack($buffer);
        }

        return V3::pack($buffer);
    }
}
