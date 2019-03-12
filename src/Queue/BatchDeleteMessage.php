<?php

namespace Metmit\Alimns\Queue;


use Metmit\Alimns\Constants;

class BatchDeleteMessage extends Base
{
    protected $receiptHandles = [];

    protected $method = 'DELETE';

    protected function setResourcePath()
    {
        $this->resourcePath = 'queues/' . $this->queueName . '/messages';
    }

    public function generateBody()
    {
        $xmlWriter = new \XMLWriter;
        $xmlWriter->openMemory();
        $xmlWriter->startDocument("1.0", "UTF-8");
        $xmlWriter->startElementNS(NULL, Constants::RECEIPT_HANDLES, Constants::MNS_XML_NAMESPACE);
        foreach ($this->receiptHandles as $receiptHandle) {
            $xmlWriter->writeElement(Constants::RECEIPT_HANDLE, $receiptHandle);
        }
        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        return $xmlWriter->outputMemory();
    }

    public function generateQueryString()
    {
        return NULL;
    }
}
