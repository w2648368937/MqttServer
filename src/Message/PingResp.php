<?php

declare(strict_types=1);

namespace Cherrain\MqttServer\Message;

use Cherrain\MqttServer\Protocol\Types;
use Cherrain\MqttServer\Version\V3;
use Cherrain\MqttServer\Version\V5;

class PingResp extends AbstractMessage
{
    public function getContents(bool $getArray = false)
    {
        $buffer = [
            'type' => Types::PINGRESP,
        ];

        if ($getArray) {
            return $buffer;
        }

        if ($this->isMQTT5()) {
            return V5::pack($buffer);
        }

        return V3::pack($buffer);
    }
}
