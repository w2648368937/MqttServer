<?php

declare(strict_types=1);

namespace Cherrain\MqttServer;

use Cherrain\MqttServer\Version\V3;
use Cherrain\MqttServer\Version\V5;
use Cherrain\MqttServer\Vo\RequestVo;
use Psr\Container\ContainerInterface;
use Psr\SimpleCache\CacheInterface;
use Cherrain\MqttServer\Protocol\ProtocolInterface;
use Cherrain\MqttServer\Protocol\Types;
use Cherrain\MqttServer\Tools\UnPackTool;
use Swoole\Server as SwooleServer;


class Server
{
    protected $config;

    protected $unPackServer;

    protected $receiveCallbacks;

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

    public function onReceive(SwooleServer $server, $fd, $fromId, $data)
    {

        $cache = $this->container->get(CacheInterface::class);
        $protocolLevelKey = ProtocolInterface::class.":" . $fd;
        try {
            $protocolLevel = $cache->get($protocolLevelKey);
            if (UnPackTool::getType($data) == Types::CONNECT) {
                $cache->set($protocolLevelKey, $protocolLevel = UnPackTool::getLevel($data));
            }
            //echo "\033[0;31mProtocolLevel: {$protocolLevel}\033[0m\r\n";
            $class = $protocolLevel !== ProtocolInterface::MQTT_PROTOCOL_LEVEL_5_0 ? V3::class : V5::class;
            if (!$this->container->has($class)) {
                $server->close($fd);
            }

            $this->receiveCallbacks = $this->config['receiveCallbacks'];

            $request = new RequestVo();
            $request->setServer($server);
            $request->setLevel($protocolLevel);

            //$this->unPackServer->onReceive($server, $fd, $fromId, $data);
            $this->unPackServer = $this->container->get($class);
            $pack = $this->unPackServer->unpack($data);
            if (is_array($pack) && isset($pack['type'])) {
                switch ($pack['type']) {
                    case Types::PINGREQ: // 心跳请求
                        [$class, $func] = $this->receiveCallbacks[Types::PINGREQ];
                        $obj = new $class();
                        if ($obj->{$func}($request, $fd, $fromId, $pack)) {
                            // 返回心跳响应
                            $server->send($fd, $this->unPackServer->pack(['type' => Types::PINGRESP]));
                        }
                        break;
                    case Types::DISCONNECT: // 客户端断开连接
                        [$class, $func] = $this->receiveCallbacks[Types::DISCONNECT];
                        $obj = new $class();
                        if ($obj->{$func}($request, $fd, $fromId, $pack)) {
                            if ($server->exist($fd)) {
                                $server->close($fd);
                            }
                        }
                        break;
                    case Types::CONNECT: // 连接
                    case Types::PUBLISH: // 发布消息
                    case Types::SUBSCRIBE: // 订阅
                    case Types::UNSUBSCRIBE: // 取消订阅
                        [$class, $func] = $this->receiveCallbacks[$pack['type']];
                        $obj = new $class();
                        $obj->{$func}($request, $fd, $fromId, $pack);
                        break;
                }
            } else {
                $server->close($fd);
            }
        }catch (\Exception $exception){
            echo "\033[0;31mError: msg[$data]\033[0m\r\n";
            echo "\033[0;31mError: {$exception->getMessage()}\033[0m\r\n";
            $server->close($fd);
        }

    }

    public function onClose(SwooleServer $server, $fd, $fromId)
    {
        try {
            $cache = $this->container->get(CacheInterface::class);
            $protocolLevelKey = ProtocolInterface::class.":" . $fd;
            $cache->delete($protocolLevelKey);
            $request = new RequestVo();
            $request->setServer($server);
            [$class, $func] = $this->receiveCallbacks['close'];
            $obj = new $class($this->unPackServer);
            $obj->{$func}($request, $fd, $fromId);
        }catch (\Exception $exception){
            echo "\033[0;31mError: {$exception->getMessage()}\033[0m\r\n";
        }

    }
}
