<?php

declare(strict_types=1);

namespace Cherrain\MqttServer\Message;

use Cherrain\MqttServer\Hex\ReasonCode;
use Cherrain\MqttServer\Protocol\Types;
use Cherrain\MqttServer\Version\V5;

class Auth extends AbstractMessage
{
    protected $code = ReasonCode::SUCCESS;

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    // AUTH type is only available in MQTT5
    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::AUTH,
            'code' => $this->getCode(),
            'properties' => $this->getProperties(),
        ];

        if ($getArray) {
            return $buffer;
        }

        return V5::pack($buffer);
    }
}
