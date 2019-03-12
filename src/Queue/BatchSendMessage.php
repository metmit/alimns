<?php

namespace Metmit\Alimns\Queue;

use Metmit\Alimns\Constants;

class BatchSendMessage extends Base
{
    protected $messageItems = [];
    protected $base64 = true;
    protected $method = 'POST';

    protected function setResourcePath()
    {
        $this->resourcePath = 'queues/' . $this->queueName . '/messages';
    }

    public function generateBody()
    {
        $xmlWriter = new \XMLWriter;
        $xmlWriter->openMemory();
        $xmlWriter->startDocument("1.0", "UTF-8");
        $xmlWriter->startElementNS(NULL, "Messages", Constants::MNS_XML_NAMESPACE);
        foreach ($this->messageItems as $item) {
            $this->writeXML($xmlWriter, $item);
        }
        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        return $xmlWriter->outputMemory();
    }

    public function generateQueryString()
    {
        return NULL;
    }

    public function writeXML(\XMLWriter $xmlWriter, $data)
    {
        $xmlWriter->startELement('Message');

        if (isset($data['messageBody']) && $data['messageBody'] != null) {
            if ($this->base64 == true) {
                $xmlWriter->writeElement(Constants::MESSAGE_BODY, base64_encode($data['messageBody']));
            } else {
                $xmlWriter->writeElement(Constants::MESSAGE_BODY, $data['messageBody']);
            }
        }

        if (isset($data['delaySeconds']) && $data['delaySeconds'] != null) {
            $xmlWriter->writeElement(Constants::DELAY_SECONDS, $data['delaySeconds']);
        }
        if (isset($data['priority']) && $data['priority'] !== null) {
            $xmlWriter->writeElement(Constants::PRIORITY, $data['priority']);
        }
        $xmlWriter->endElement();
    }

}
