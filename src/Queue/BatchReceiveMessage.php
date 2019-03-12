<?php

namespace Metmit\Alimns\Queue;

class BatchReceiveMessage extends Base
{
    protected $waitSeconds;
    protected $numOfMessages;

    protected $method = 'GET';

    protected function setResourcePath()
    {
        $this->resourcePath = 'queues/' . $this->queueName . '/messages';
    }

    public function generateBody()
    {
        return NULL;
    }

    public function generateQueryString()
    {
        $params = array("numOfMessages" => $this->numOfMessages);
        if ($this->waitSeconds != NULL) {
            $params["waitseconds"] = $this->waitSeconds;
        }
        return http_build_query($params);
    }
}
