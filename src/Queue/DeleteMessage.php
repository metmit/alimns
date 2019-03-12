<?php

namespace Metmit\Alimns\Queue;

class DeleteMessage extends Base
{
    protected $receiptHandle;

    protected $method = 'DELETE';

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
        return http_build_query(array("ReceiptHandle" => $this->receiptHandle));
    }
}
