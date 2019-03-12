<?php

namespace Metmit\Alimns;

use Metmit\Alimns\Queue\Base;

class Request
{
    private $config = [];

    /**
     * @var Base
     */
    private $queueInstance = null;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function send($queue)
    {
        $this->queueInstance = $queue;

        $this->buildHeaders();

        //TODO https
        $url = 'http://' . $this->config['end_point'] . $this->queueInstance->getResourcePath() . '?' . $this->queueInstance->getQueryString();

        $headers = $this->queueInstance->getHeaders();
        $_headers = array();
        foreach ($headers as $name => $value) $_headers[] = $name . ": " . $value;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->queueInstance->getMethod());
        curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $this->queueInstance->getBody());
        $res = curl_exec($ch);
        curl_close($ch);
        $data = explode("\r\n\r\n", $res);
        if (count($data) < 2) return false;
        $msg = array();
        $error = $this->errorHandle($data[0]);

        if ($error) {
            $msg['state'] = $error;
            $msg['msg'] = $this->getXmlData($data[1]);
        } else {
            $msg['state'] = "ok";
            $msg['msg'] = $this->getXmlData($data[1]);
        }

        $this->queueInstance = null;
        return $msg;
    }

    /**
     * 构造请求头
     */
    private function buildHeaders()
    {
        $body = $this->queueInstance->getBody();
        if ($body != NULL) {
            $this->queueInstance->setHeader(Constants::CONTENT_LENGTH, strlen($body));
        }

        $this->queueInstance->setHeader('Expect', '');
        $this->queueInstance->setHeader('Host', $this->config['end_point']);
        $this->queueInstance->setHeader('Date', gmdate(Constants::GMT_DATE_FORMAT));

        if (!$this->queueInstance->isHeaderSet('Content-Type')) {
            $this->queueInstance->setHeader('Content-Type', 'text/xml');
        }

        $this->queueInstance->setHeader(Constants::MNS_VERSION_HEADER, Constants::MNS_VERSION);

        /*if ($this->securityToken != NULL) {
            $this->queueInstance->setHeader(Constants::SECURITY_TOKEN, $this->securityToken);
        }*/

        $auth = Constants::MNS . " {$this->config['access_id']}:" . $this->sign();
        $this->queueInstance->setHeader(Constants::AUTHORIZATION, $auth);
    }

    public function sign()
    {
        $headers = $this->queueInstance->getHeaders();

        $contentMd5 = "";
        if (isset($headers['Content-MD5'])) {
            $contentMd5 = $headers['Content-MD5'];
        }

        $path = $this->queueInstance->getResourcePath();

        $queryString = $this->queueInstance->getQueryString();
        if ($queryString != NULL) {
            $path .= "?" . $queryString;
        }

        $tmpHeaders = array();
        foreach ($headers as $key => $value) {
            if (0 === strpos($key, Constants::MNS_HEADER_PREFIX)) {
                $tmpHeaders[$key] = $value;
            }
        }
        ksort($tmpHeaders);

        $mnsHeaders = implode("\n", array_map(function ($v, $k) {
            return $k . ":" . $v;
        }, $tmpHeaders, array_keys($tmpHeaders)));

        $stringToSign = $this->queueInstance->getMethod() . "\n" . $contentMd5 . "\n" . $headers['Content-Type'] . "\n" . $headers['Date'] . "\n" . $mnsHeaders . "\n" . $path;

        return base64_encode(hash_hmac("sha1", $stringToSign, $this->config['access_key'], $raw_output = TRUE));
    }

    //获取错误Handle
    protected function errorHandle($headers)
    {
        preg_match('/HTTP\/[\d]\.[\d] ([\d]+) /', $headers, $code);
        if ($code[1]) {
            if ($code[1] / 100 > 1 && $code[1] / 100 < 4) return false;
            else return $code[1];
        }
    }

    //解析xml
    protected function getXmlData($strXml)
    {
        $pos = strpos($strXml, 'xml');
        if ($pos) {
            $xmlCode = simplexml_load_string($strXml, 'SimpleXMLElement', LIBXML_NOCDATA);
            $arrayCode = $this->get_object_vars_final($xmlCode);
            return $arrayCode;
        } else {
            return '';
        }
    }

    //解析obj
    protected function get_object_vars_final($obj)
    {
        if (is_object($obj)) {
            $obj = get_object_vars($obj);
        }
        if (is_array($obj)) {
            foreach ($obj as $key => $value) {
                $obj[$key] = $this->get_object_vars_final($value);
            }
        }
        return $obj;
    }
}
