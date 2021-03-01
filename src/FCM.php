<?php

namespace FCM;

use AES\AES;
use AES\AESException;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;


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
     * @var int $timeout
     */
    protected $timeout;

    /**
     * FCM constructor.
     *
     * @param string $appId
     * @param string $bizId
     * @param string $key
     * @param int $timeout
     */
    public function __construct(string $appId, string $bizId, string $key, $timeout = 10)
    {
        $this->aes = new AES($key, $this->cipher);
        $this->http = new Client();
        $this->key = $key;
        $this->timeout = $timeout;
        $this->setHeaders($appId, $bizId);
    }

    /**
     * check the name and idNum
     *
     * @param string $ai
     * @param string $name
     * @param string $idNum
     * @param string $uri
     * @return string
     * @throws AESException
     * @throws GuzzleException
     */
    public function check(string $ai, string $name, string $idNum, $uri = '')
    {
        $uri = $uri ?: 'https://api.wlc.nppa.gov.cn/idcard/authentication/check';
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];
        return $this->doRequest('POST', $uri, $headers, ['ai'=>$ai, 'name'=>$name, 'idNum'=>$idNum]);
    }

    /**
     * check for test
     *
     * @param string $ai
     * @param string $name
     * @param string $idNum
     * @param string $testCode
     * @return string
     * @throws AESException
     * @throws GuzzleException
     */
    public function testCheck(string $ai, string $name, string $idNum, string $testCode)
    {
        $this->debug = true;
        $uri = 'https://wlc.nppa.gov.cn/test/authentication/check/'.$testCode;
        return $this->check($ai, $name, $idNum, $uri);
    }

    /**
     * query the ai
     *
     * @param string $ai
     * @param string $uri
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function query(string $ai, $uri = '')
    {
        $uri = $uri ?: 'http://api2.wlc.nppa.gov.cn/idcard/authentication/query';
        return $this->doRequest('GET', $uri, [], ['ai' => $ai ]);
    }

    /**
     * query for test
     *
     * @param string $ai
     * @param $testCode
     * @return string
     * @throws Exception
     * @throws GuzzleException
     */
    public function testQuery(string $ai, string $testCode)
    {
        $this->debug = true;
        $uri = 'https://wlc.nppa.gov.cn/test/authentication/query/'.$testCode;
        return $this->query($ai, $uri);
    }

    /**
     * @param $uri
     * @param mixed $data
     * @return string
     * @throws AESException
     * @throws GuzzleException
     * @example $data =  [['bt'=>0, 'ct'=>0, 'pi'=>'1fffbjzos82bs9cnyj1dna7d6d29zg4esnh99u']]
     */
    public function loginOrOut(array $data, $uri)
    {
        $collections = [];
        foreach($data as $i => $d) {
            $tmp = [];
            $tmp['no'] = $i+1;
            $tmp['si'] = md5($d['pi']);
            $tmp['bt'] = $d['bt'];
            $tmp['ot'] = $d['ot'] ?? time();
            $tmp['ct'] = $d['ct'];
            $tmp['di'] = $d['di'];
            $tmp['pi'] = $d['pi'];
            $collections['collections'][] = $tmp;
        }
        $uri = $uri ?: 'http://api2.wlc.nppa.gov.cn/behavior/collection/loginout';
        $headers = ['Content-Type' => 'application/json; charset=utf-8'];
        return $this->doRequest('POST', $uri, $headers, $collections);
    }

    /**
     * @param array $data
     * @param $testCode
     * @return string
     * @throws AESException
     * @throws GuzzleException
     */
    public function testLoginOrOut(array $data, string $testCode)
    {
        $this->debug = true;
        $uri = 'https://wlc.nppa.gov.cn/test/collection/loginout/'.$testCode;
        return $this->loginOrOut($data, $uri);
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
        // sha256
        $this->info(sprintf("%s: %s", 'makeSign-before', $this->key.$ret.$body));
        $this->info(sprintf("%s: %s", 'makeSign-after', $sign = hash("sha256", $this->key.$ret.$body)));
        return $sign;
    }

    /**
     * common request headers
     *
     * @param string $appId
     * @param string $bizId
     */
    private function setHeaders(string $appId, string $bizId)
    {
        $this->headers = [
            'appId' => $appId,
            'bizId' => $bizId,
            'timestamps' => (int)(microtime(true)*1000)
        ];
    }

    /**
     * do request
     *
     * @param string $method
     * @param string $uri
     * @param array $headers
     * @param array $body
     * @param array $options
     * @return string
     * @throws AESException
     * @throws GuzzleException
     */
    private function doRequest(string $method, string $uri, array $headers=[], array $body = [], array $options = [])
    {
        $this->info(sprintf("%s: %s", $method, $uri));
        $raw = json_encode($body, JSON_UNESCAPED_UNICODE);
        $body = '{"data":"' . $this->aes->encrypt($raw) . '"}';
        $headers = array_merge($this->headers, $headers);
        $headers['sign'] = $this->makeSign($body);
        $options = array_merge(['headers'=>$headers, 'body'=>$body, 'timeout'=>$this->timeout], $options);
        $this->info(sprintf("%s: %s", 'raw', $raw));
        $this->info(sprintf("%s: %s", 'options', print_r($options, true)));
        $response = $this->http->request($method, $uri, $options);
        return $response->getBody()->getContents();
    }

}