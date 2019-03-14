<?php

namespace Metmit\Alimns;

use Metmit\Alimns\Queue\BatchDeleteMessage;
use Metmit\Alimns\Queue\BatchReceiveMessage;
use Metmit\Alimns\Queue\BatchSendMessage;
use Metmit\Alimns\Queue\CreateQueue;
use Metmit\Alimns\Queue\DeleteMessage;
use Metmit\Alimns\Queue\ReceiveMessage;
use Metmit\Alimns\Queue\SendMessage;

class Mns
{
    private static $instance = [];
    private $config = [];
    private $request = null;

    private function __clone()
    {
    }

    private function __construct(array $config)
    {
        $this->config = $config;
        $this->request = new Request($config);
    }

    /**
     * 获取单例
     * @param array $config 队列配置
     * @return mixed
     * @throws \Exception
     */
    public static function getInstance(array $config)
    {
        self::checkConfig($config);
        ksort($config);
        $name = md5(json_encode($config));
        if (!isset(self::$instance[$name])) {
            self::$instance[$name] = new self($config);
        }
        return self::$instance[$name];
    }

    private static function checkConfig($config)
    {
        if (!isset($config['access_id'])) {
            throw new \Exception('Mns : [access_id] Incomplete configuration!');
        }

        if (!isset($config['access_key'])) {
            throw new \Exception('Mns : [access_key] Incomplete configuration!');
        }

        if (!isset($config['end_point'])) {
            throw new \Exception('Mns : [end_point] Incomplete configuration!');
        }
    }

    /**
     * 获取队列名称
     * @param $queue_name
     * @return string
     */
    public function getQueueName($queue_name)
    {
        if (!isset($this->config['prefix']) || !$this->config['prefix']) {
            return $queue_name;
        }

        if (substr($queue_name, 0, mb_strlen($this->config['prefix'])) == $this->config['prefix']) {
            return $queue_name;
        }

        return $this->config['prefix'] . $queue_name;
    }

    private function dispatch(string $operation, string $queue_name, array $data)
    {
        $queue_name = $this->getQueueName($queue_name);
        $class = $this->map($operation);

        if (!$class) return false;

        /*if (!$class instanceof Base) {
            return false;
        }*/

        return $this->request->send(new $class($queue_name, $data));
    }

    private function map($key)
    {
        $maps = [
            'create' => CreateQueue::class,
            'send' => SendMessage::class,
            'receive' => ReceiveMessage::class,
            'delete' => DeleteMessage::class,
            'batch_send' => BatchSendMessage::class,
            'batch_receive' => BatchReceiveMessage::class,
            'batch_delete' => BatchDeleteMessage::class
        ];
        return isset($maps[$key]) ? $maps[$key] : false;
    }

    /**
     * 创建队列
     * @param string $queue_name 队列名
     * @param array $attributes 属性
     * @return array|bool
     */
    public function createQueue($queue_name, $attributes = [])
    {
        return $this->dispatch('create', $queue_name, [
            'DelaySeconds' => $attributes['delay_seconds'], //消息延时(秒)
            'MaximumMessageSize' => $attributes['max_message_size'], //消息最大长度(Byte)
            'MessageRetentionPeriod' => $attributes['alive_seconds'], //消息存活时间(秒)
            'VisibilityTimeout' => $attributes['hide_seconds'],//取出消息隐藏时长(秒)
            'PollingWaitSeconds' => $attributes['wait_seconds'], //消息接收长轮询等待时间(秒)
            'LoggingEnabled' => $attributes['enable_log'], //开启logging
        ]);
    }

    /**
     * 发送消息
     * @param string $queue_name 队列名
     * @param string $message 消息体
     * @param int $delay 延时时间（秒）
     * @param int $priority 优先级
     * @return array|bool
     */
    public function sendMessage($queue_name, $message, $delay = 0, $priority = 8)
    {
        return $this->dispatch('send', $queue_name, [
            'messageBody' => $message,
            'delaySeconds' => $delay,
            'priority' => $priority
        ]);
    }

    /**
     * 消费消息
     * @param string $queue_name 队列名
     * @param int $seconds 等待时间（秒）
     * @return array|bool
     */
    public function receiveMessage($queue_name, $seconds = 0)
    {
        return $this->dispatch('receive', $queue_name, [
            'waitSeconds' => $seconds
        ]);
    }

    /**
     * 删除消息
     * @param string $queue_name 队列名
     * @param string $receiptHandle 消息句柄
     * @return array|bool
     */
    public function deleteMessage($queue_name, $receiptHandle)
    {
        return $this->dispatch('delete', $queue_name, [
            'receiptHandle' => $receiptHandle
        ]);
    }

    /**
     * 批量发送消息
     * @param string $queue_name 队列名
     * @param array $messages 消息体集合
     * @return array|bool
     */
    public function batchSendMessage($queue_name, $messages = [])
    {
        return $this->dispatch('batch_send', $queue_name, [
            'messageItems' => $messages
        ]);
    }

    /**
     * 批量消费消息
     * @param string $queue_name 队列名
     * @param int $number 接收消息数
     * @param int $seconds 等待时间（秒）
     * @return array|bool
     */
    public function batchReceiveMessage($queue_name, $number = 1, $seconds = 0)
    {
        return $this->dispatch('batch_receive', $queue_name, [
            'numOfMessages' => $number,
            'waitSeconds' => $seconds
        ]);
    }

    /**
     * 批量删除消息
     * @param string $queue_name 队列名
     * @param array $handles 消息句柄集合
     * @return array|bool
     */
    public function batchDeleteMessage($queue_name, $handles = [])
    {
        return $this->dispatch('batch_delete', $queue_name, [
            'receiptHandles' => $handles
        ]);
    }
}


