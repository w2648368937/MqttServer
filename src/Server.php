<?php

declare(strict_types=1);

namespace Cherrain\MqttServer;

use Cherrain\MqttServer\Version\V3;
use Cherrain\MqttServer\Version\V5;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Cherrain\MqttServer\Protocol\ProtocolInterface;
use Cherrain\MqttServer\Protocol\Types;
use Cherrain\MqttServer\Tools\UnPackTool;


class Server
{
    protected $config;

    protected $unPackServer;

    /**
     * @var ContainerInterface
     */
    protected $container;

    // 通过在构造函数的参数上声明参数类型完成自动注入
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;

        if (! $this->config) {
            $servers = config('server.servers');
            $config = array_values(array_filter($servers, function ($arr) {
                return $arr['name'] == 'mqtt';
            }));
            if (! $config) {
                throw new \RuntimeException('ConfigInterface is missing in server mqtt.');
            }
            $this->config = $config[0];
        }
    }

    public function onReceive($server, $fd, $fromId, $data)
    {
        $cache = $this->container->get(CacheInterface::class);
        $protocolLevelKey = ProtocolInterface::class . $fd;
        try {
            $protocolLevel = $cache->get($protocolLevelKey);
            if (UnPackTool::getType($data) == Types::CONNECT) {
                $cache->set($protocolLevelKey, $protocolLevel = UnPackTool::getLevel($data));
            }
            //echo "\033[0;31mProtocolLevel: {$protocolLevel}\033[0m\r\n";
            $class = $protocolLevel !== ProtocolInterface::MQTT_PROTOCOL_LEVEL_5_0 ? V3::class : V5::class;
            if ($this->container->has($class)) {
                $this->unPackServer = $this->container->get($class);
                $this->unPackServer->setCallbacks($this->config['receiveCallbacks']);
            }
            $this->unPackServer->onReceive($server, $fd, $fromId, $data);
        }catch (\Exception $exception){
            echo "\033[0;31mError: {$exception->getMessage()}\033[0m\r\n";
        }

    }
}
