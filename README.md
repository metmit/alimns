# Alimns
阿里云消息服务（MNS）的PHP客户端。

## 安装
```
$ composer require metmit/alimns
```
## 使用
阿里云MNS提供`队列`和`主题`两种方案，可按需选择。
```php
$config = [
    'access_id'  => '123456',
    'access_key' => 'ABCDE',
    'end_point'  => '123.mns.cn-beijing.aliyuncs.com',
    'prefix'     => 'test-'
];
//获取实例
$instance = \Metmit\Alimns\Mns::getInstance($config);
$queue_name = 'ruesin-queue';
```

### 队列
- 创建队列
```php
$attributes = [
     'delay_seconds' => 0, //默认消息延时(秒)
     'max_message_size' => 65536, //消息最大长度(Byte)
     'alive_seconds' => 1296000, //消息存活时间(秒)
     'hide_seconds'  => 90, //取出消息隐藏时长(秒)
     'wait_seconds' => 10, //消息接收长轮询等待时间(秒)
     'enable_log' => false //开启logging
 ];
$result = $instance->createQueue($queue_name, $attributes);
```
- 发送消息
```php
$messageBody = 'sin '. date('Y-m-d H:i:s');
$result = $instance->sendMessage($queue_name, $messageBody);
$result = $instance->sendMessage($queue_name, $messageBody, 500, 1);
```
- 消费消息 & 删除消息
```php
$message = $instance->receiveMessage($queue_name, 10);
if (!$message) return;
if ($message['state'] == 'ok') {
    print_r($message);
    echo base64_decode($message['msg']['MessageBody']);
    $instance->deleteMessage($queue_name, $message['msg']['ReceiptHandle']);
}
```

### 主题
TODO