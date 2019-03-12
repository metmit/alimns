<?php

namespace Metmit\Alimns\Queue;

class ReceiveMessage extends Base
{
    protected $waitSeconds;

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
        if ($this->waitSeconds != NULL) {
            return http_build_query(array("waitseconds" => $this->waitSeconds));
        }
    }
}
