<?php

declare(strict_types=1);

namespace Cherrain\MqttServer\Message;

use Cherrain\MqttServer\Protocol\ProtocolInterface;
use Cherrain\MqttServer\Protocol\Types;
use Cherrain\MqttServer\Version\V3;
use Cherrain\MqttServer\Version\V5;

class Publish extends AbstractMessage
{
    protected $topic = '';

    protected $message = '';

    protected $qos = ProtocolInterface::MQTT_QOS_0;

    protected $dup = ProtocolInterface::MQTT_DUP_0;

    protected $retain = ProtocolInterface::MQTT_RETAIN_0;

    protected $messageId = 0;

    public function getTopic(): string
    {
        return $this->topic;
    }

    public function setTopic(string $topic): self
    {
        $this->topic = $topic;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getQos(): int
    {
        return $this->qos;
    }

    public function setQos(int $qos): self
    {
        $this->qos = $qos;

        return $this;
    }

    public function getDup(): int
    {
        return $this->dup;
    }

    public function setDup(int $dup): self
    {
        $this->dup = $dup;

        return $this;
    }

    public function getRetain(): int
    {
        return $this->retain;
    }

    public function setRetain(int $retain): self
    {
        $this->retain = $retain;

        return $this;
    }

    public function getMessageId(): int
    {
        return $this->messageId;
    }

    public function setMessageId(int $messageId): self
    {
        $this->messageId = $messageId;

        return $this;
    }

    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::PUBLISH,
            'topic' => $this->getTopic(),
            'message' => $this->getMessage(),
            'dup' => $this->getDup(),
            'qos' => $this->getQos(),
            'retain' => $this->getRetain(),
            'message_id' => $this->getMessageId(),
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
