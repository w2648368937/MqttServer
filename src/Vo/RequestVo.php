<?php

namespace Cherrain\MqttServer\Vo;

use Cherrain\MqttServer\Protocol\ProtocolInterface;
use Swoole\Server as SwooleServer;

class RequestVo
{
    private SwooleServer $server;
    private int $level = ProtocolInterface::MQTT_PROTOCOL_LEVEL_3_1_1;

    public function setServer(SwooleServer $server)
    {
        $this->server = $server;
    }

    public function getServer(){
        return $this->server;
    }

    public function setLevel(int $level){
        $this->level = $level;
    }

    public function getLevel(){
        return $this->level;
    }

}