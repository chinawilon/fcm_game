<?php

namespace FCM;

use AES\AES;
use AES\AESException;
use Exception;
use GuzzleHttp\Client;


class FCM
{
    /**
     * @var string
     */
    protected $cipher = "aes-128-gcm";

    /**
     * @var AES
     */
    protected $aes;

    /**
     * @var Client
     */
    protected $http;

    /**
     * @var array
     */
    protected $headers;

    /**
     * @var string
     */
    protected $key;

    /**
     * @var bool
     */
    protected $debug = false;

    /**
     * @var $info string debug info
     */
    protected $info;

    /**
     * test uris
     * @var string[]
     */
    protected $tests = [
        'check' => 'https://wlc.nppa.gov.cn/test/authentication/check',
        'query' => 'https://wlc.nppa.gov.cn/test/authentication/query',
        //'consumption' => 'https://wlc.nppa.gov.cn/test/collection/consumption',
        'logout' => 'https://wlc.nppa.gov.cn/test/collection/loginout',
    ];

    /**
     * @var $normals string[] normal uris
     */
    protected $normals = [
        'check' => 'https://api.wlc.nppa.gov.cn/idcard/authentication/check',
        'query' => 'http://api2.wlc.nppa.gov.cn/idcard/authentication/query',
        'logout' => 'http://api2.wlc.nppa.gov.cn/behavior/collection/loginout'
    ];

    /**
     * FCM constructor.
     *
     * @param string $appId
     * @param string $bizId
     * @param string $key
     */
    public function __construct(string $appId, string $bizId, string $key)
    {
        $this->aes = new AES($key, $this->cipher);
        $this->http = new Client();
        $this->key = $key;
        $this->setHeaders($appId, $bizId);
    }

    /**
     * common request headers
     *
     * @param string $appId
     * @param string $bizId
     */
    public function setHeaders(string $appId, string $bizId)
    {
        $this->headers = [
            'appId' => $appId,
            'bizId' => $bizId,
            'timestamps' => time()
        ];
    }


    /**
     * check the name and idNum
     *
     * @param string $ai
     * @param string $name
     * @param string $idNum
     * @param callable $callback
     * @return string
     * @throws AESException | Exception
     */
    public function check(string $ai, string $name, string $idNum, callable $callback = null)
    {
        $uri = $this->getUri('check');
        $headers = array_merge(
            $this->headers,
            ['Content-Type' => 'application/json; charset=utf-8']
        );
        $body = json_encode(['ai'=>$ai, 'name'=>$name, 'idNum'=>$idNum], JSON_UNESCAPED_UNICODE);
        $body = '{"data":"' . $this->aes->encrypt($body) . '"}';
        $headers['sign'] = $this->makeSign($body);

        //debug info
        $options = ['headers'=>$headers, 'body'=>$body];
        $this->info(sprintf("%s: %s", 'post', $uri));
        $this->info(sprintf("%s: %s", 'options', print_r($options, true)));
        $response = $this->http->post($uri, $options);

        //user define process callback
        if (! is_null($callback)) {
            return call_user_func($callback, $response);
        }
        return $response->getBody()->getContents();
    }


    /**
     * query the ai
     *
     * @param string $ai
     * @param callable $callback
     * @return string
     */
    public function query(string $ai, callable $callback = null)
    {
        $uri = $this->getUri('query');
        $options['query'] = ['ai' => $ai ];
        $options['headers'] = array_merge($this->headers, [
            'sign' => $this->makeSign("", $options['query'])
        ]);

        //debug info
        $this->info(sprintf("%s: %s", 'post', $uri));
        $this->info(sprintf("%s: %s", 'options', print_r($options, true)));
        $response = $this->http->get($this->getUri('query'), $options);

        //user define process callback
        if (! is_null($callback)) {
            return call_user_func($callback, $response);
        }
        return $response->getBody()->getContents();
    }


    /**
     * debug mode
     *
     * @param bool $bool
     */
    public function debug($bool = true)
    {
        $this->debug = $bool;
    }

    /**
     * fetch the debug info
     *
     * @param string $msg
     * @return string
     */
    public function info($msg="")
    {
        if ($this->debug) {
            $this->info .= sprintf("\n[%s] %s", date('Y-m-d H:i:s'), $msg) ;
        }
    }

    /**
     * fetch the debug info
     *
     * @return string
     */
    public function flushInfo()
    {
        $info = $this->info;
        $this->info = "";
        return $info;
    }

    /**
     * make the sign
     *
     * @param $body
     * @param array $query
     * @return string
     */
    private function makeSign($body, $query = [])
    {
        $request = array_merge($this->headers, $query);
        ksort($request);
        $ret = '';
        foreach( $request as $r => $v) {
            $ret .= $r.$v;
        }
        //sha256
        return hash("sha256", $this->key.$ret.$body);
    }

    /**
     * fetch the api uri
     *
     * @param $behavior
     * @return string
     */
    private function getUri($behavior)
    {
        if ($this->debug) {
            return $this->tests[$behavior];
        }
        return $this->normals[$behavior];
    }

}