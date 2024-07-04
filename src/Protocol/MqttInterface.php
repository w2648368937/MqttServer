<?php

declare(strict_types=1);

namespace Cherrain\MqttServer\Protocol;

use Cherrain\MqttServer\Vo\RequestVo;

interface MqttInterface
{
    // 1
    public function onMqConnect(RequestVo $request, int $fd, $fromId, $data);

    // 12
    public function onMqPingreq(RequestVo $request, int $fd, $fromId, $data): bool;

    // 14
    public function onMqDisconnect(RequestVo $request, int $fd, $fromId, $data): bool;

    // 3
    public function onMqPublish(RequestVo $request, int $fd, $fromId, $data);

    // 8
    public function onMqSubscribe(RequestVo $request, int $fd, $fromId, $data);

    // 10
    public function onMqUnsubscribe(RequestVo $request, int $fd, $fromId, $data);

    public function onMqClose(RequestVo $request, int $fd, $fromId);
}
