<?php

declare(strict_types=1);

namespace Cherrain\MqttServer\Message;

use Cherrain\MqttServer\Hex\ReasonCode;
use Cherrain\MqttServer\Protocol\Types;
use Cherrain\MqttServer\Version\V3;
use Cherrain\MqttServer\Version\V5;

class PubComp extends AbstractMessage
{
    protected $messageId = 0;

    protected $code = ReasonCode::SUCCESS;

    public function getMessageId(): int
    {
        return $this->messageId;
    }

    public function setMessageId(int $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::PUBCOMP,
            'message_id' => $this->getMessageId(),
        ];

        if ($this->isMQTT5()) {
            $buffer['code'] = $this->getCode();
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
