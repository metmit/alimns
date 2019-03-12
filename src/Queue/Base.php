<?php

namespace Metmit\Alimns\Queue;

abstract class Base
{
    protected $queueName = '';
    protected $data = [];

    protected $method = 'GET';
    protected $resourcePath;
    protected $headers;
    protected $body = null;
    protected $queryString = null;

    public function __construct(string $queueName, array $data)
    {
        foreach ($data as $key => $value) {
            if (in_array($key, ['method', 'data', 'resourcePath', 'headers', 'body', 'queryString'])) continue;
            $this->$key = $value;
        }

        $this->queueName = $queueName;

        $this->setResourcePath();
        $this->body = $this->generateBody();
        $this->queryString = $this->generateQueryString();
    }

    abstract protected function setResourcePath();

    abstract public function generateBody();

    abstract public function generateQueryString();

    public function getMethod()
    {
        return strtoupper($this->method);
    }

    public function getBody()
    {
        return $this->body;
    }

    public function getResourcePath()
    {
        return '/' . ltrim($this->resourcePath, '/');
    }

    public function getQueryString()
    {
        return $this->queryString;
    }

    public function isHeaderSet($header)
    {
        return isset($this->headers[$header]);
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;
    }

    public function __get($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : false;
    }

    public function __set($name, $value)
    {
        $this->data[$name] = $value;
    }
}