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
     * @param array $config
     * @return static
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

    /**
     * @param $config
     * @throws \Exception
     */
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
     * @param $queueName
     * @return string
     */
    public function getQueueName($queueName)
    {
        if (!isset($this->config['prefix']) || !$this->config['prefix']) {
            return $queueName;
        }

        if (substr($queueName, 0, mb_strlen($this->config['prefix'])) == $this->config['prefix']) {
            return $queueName;
        }

        return $this->config['prefix'] . $queueName;
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

    public function createQueue($queue_name, $attributes = [])
    {
        return $this->dispatch('create', $queue_name, $attributes);
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

    public function receiveMessage($queue_name, $seconds = 0)
    {
        return $this->dispatch('receive', $queue_name, [
            'waitSeconds' => $seconds
        ]);
    }

    public function deleteMessage($queue_name, $receiptHandle)
    {
        return $this->dispatch('delete', $queue_name, [
            'receiptHandle' => $receiptHandle
        ]);
    }

    public function batchSendMessage($queue_name, $messages = [])
    {
        return $this->dispatch('batch_send', $queue_name, [
            'messageItems' => $messages
        ]);
    }

    public function batchReceiveMessage($queue_name, $number = 1, $seconds = 0)
    {
        return $this->dispatch('batch_receive', $queue_name, [
            'numOfMessages' => $number,
            'waitSeconds' => $seconds
        ]);
    }

    public function batchDeleteMessage($queue_name, $handles = [])
    {
        return $this->dispatch('batch_delete', $queue_name, [
            'receiptHandles' => $handles
        ]);
    }
}


