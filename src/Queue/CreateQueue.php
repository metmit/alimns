<?php
namespace Metmit\Alimns\Queue;

use Metmit\Alimns\Constants;

class CreateQueue extends Base
{
    protected $DelaySeconds;
    protected $MaximumMessageSize;
    protected $MessageRetentionPeriod;
    protected $VisibilityTimeout;
    protected $PollingWaitSeconds;
    protected $LoggingEnabled;

    protected $method = 'PUT';

    protected function setResourcePath()
    {
        $this->resourcePath = 'queues/' . $this->queueName;
    }

    public function generateQueryString()
    {
        return NULL;
    }

    public function generateBody()
    {
        $xmlWriter = new \XMLWriter;
        $xmlWriter->openMemory();
        $xmlWriter->startDocument("1.0", "UTF-8");
        $xmlWriter->startElementNS(NULL, "Queue", Constants::MNS_XML_NAMESPACE);

        if ($this->DelaySeconds != null) {
            $xmlWriter->writeElement(Constants::DELAY_SECONDS, $this->DelaySeconds);
        }
        if ($this->MaximumMessageSize != null) {
            $xmlWriter->writeElement(Constants::MAXIMUM_MESSAGE_SIZE, $this->MaximumMessageSize);
        }
        if ($this->MessageRetentionPeriod != null) {
            $xmlWriter->writeElement(Constants::MESSAGE_RETENTION_PERIOD, $this->MessageRetentionPeriod);
        }
        if ($this->VisibilityTimeout != null) {
            $xmlWriter->writeElement(Constants::VISIBILITY_TIMEOUT, $this->VisibilityTimeout);
        }
        if ($this->PollingWaitSeconds != null) {
            $xmlWriter->writeElement(Constants::POLLING_WAIT_SECONDS, $this->PollingWaitSeconds);
        }
        if ($this->LoggingEnabled != null) {
            $xmlWriter->writeElement(Constants::LOGGING_ENABLED, $this->LoggingEnabled ? "True" : "False");
        }

        $xmlWriter->endElement();
        $xmlWriter->endDocument();
        return $xmlWriter->outputMemory();
    }
}
