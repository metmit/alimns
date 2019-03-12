<?php

namespace Metmit\Alimns\Queue;

use Metmit\Alimns\Constants;

class SendMessage extends Base
{
    protected $messageBody;
    protected $delaySeconds;
    protected $priority;
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
        $xmlWriter->startElementNS(null, "Message", Constants::MNS_XML_NAMESPACE);

        if ($this->messageBody != null) {
            if ($this->base64 == true) {
                $xmlWriter->writeElement(Constants::MESSAGE_BODY, base64_encode($this->messageBody));
            } else {
                $xmlWriter->writeElement(Constants::MESSAGE_BODY, $this->messageBody);
            }
        }
        if ($this->delaySeconds != null) {
            $xmlWriter->writeElement(Constants::DELAY_SECONDS, $this->delaySeconds);
        }
        if ($this->priority !== null) {
            $xmlWriter->writeElement(Constants::PRIORITY, $this->priority);
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
