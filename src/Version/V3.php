<?php

declare(strict_types=1);

namespace Cherrain\MqttServer\Version;

use Cherrain\MqttServer\Exception\InvalidArgumentException;
use Cherrain\MqttServer\Exception\RuntimeException;
use Cherrain\MqttServer\Packet\Pack;
use Cherrain\MqttServer\Packet\UnPack;
use Cherrain\MqttServer\Protocol\ProtocolInterface;
use Cherrain\MqttServer\Protocol\Types;
use Cherrain\MqttServer\Tools\PackTool;
use Cherrain\MqttServer\Tools\UnPackTool;
use Throwable;
use TypeError;

class V3 implements ProtocolInterface
{

    public static function pack(array $array): string
    {
        try {
            if (! $array['type']) {
                return '';
            }
            $type = $array['type'];
            switch ($type) {
                case Types::CONNECT:
                    $package = Pack::connect($array);
                    break;
                case Types::CONNACK:
                    $package = Pack::connAck($array);
                    break;
                case Types::PUBLISH:
                    $package = Pack::publish($array);
                    break;
                case Types::PUBACK:
                case Types::PUBREC:
                case Types::PUBREL:
                case Types::PUBCOMP:
                case Types::UNSUBACK:
                    $body = PackTool::shortInt($array['message_id']);
                    if ($type === Types::PUBREL) {
                        $head = PackTool::packHeader($type, strlen($body), 0, 1);
                    } else {
                        $head = PackTool::packHeader($type, strlen($body));
                    }
                    $package = $head . $body;
                    break;
                case Types::SUBSCRIBE:
                    $package = Pack::subscribe($array);
                    break;
                case Types::SUBACK:
                    $package = Pack::subAck($array);
                    break;
                case Types::UNSUBSCRIBE:
                    $package = Pack::unSubscribe($array);
                    break;
                case Types::PINGREQ:
                case Types::PINGRESP:
                case Types::DISCONNECT:
                    $package = PackTool::packHeader($type, 0);
                    break;
                default:
                    throw new InvalidArgumentException('MQTT Type not exist');
            }
        } catch (TypeError $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (Throwable $e) {
            throw $e;
        }

        return $package;
    }

    public static function unpack(string $data): array
    {
        try {
            $type = UnPackTool::getType($data);
            $remaining = UnPackTool::getRemaining($data);
            switch ($type) {
                case Types::CONNECT:
                    $package = UnPack::connect($remaining);
                    break;
                case Types::CONNACK:
                    $package = UnPack::connAck($remaining);
                    break;
                case Types::PUBLISH:
                    $dup = ord($data[0]) >> 3 & 0x1;
                    $qos = ord($data[0]) >> 1 & 0x3;
                    $retain = ord($data[0]) & 0x1;
                    $package = UnPack::publish($dup, $qos, $retain, $remaining);
                    break;
                case Types::PUBACK:
                case Types::PUBREC:
                case Types::PUBREL:
                case Types::PUBCOMP:
                case Types::UNSUBACK:
                    $package = ['type' => $type, 'message_id' => UnPackTool::shortInt($remaining)];
                    break;
                case Types::PINGREQ:
                case Types::PINGRESP:
                case Types::DISCONNECT:
                    $package = ['type' => $type];
                    break;
                case Types::SUBSCRIBE:
                    $package = UnPack::subscribe($remaining);
                    break;
                case Types::SUBACK:
                    $package = UnPack::subAck($remaining);
                    break;
                case Types::UNSUBSCRIBE:
                    $package = UnPack::unSubscribe($remaining);
                    break;
                default:
                    $package = [];
            }
        } catch (TypeError $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode());
        } catch (Throwable $e) {
            throw $e;
        }

        return $package;
    }


}
