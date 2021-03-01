<?php

use AES\AESException;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use FCM\FCM;


class FCMTest extends TestCase
{
    protected $biz_id = '1101999999';

    protected $app_id = 'e44158030c7341819aedf04a147f3e8a';

    protected $key = 'd59bbdefd68b71f906c4d67e52841700';

    /**
     * @var FCM
     */
    protected $fcm;
    /**
     * @var string
     */

    private $ai;

    /**
     * FCMTest constructor.
     *
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->fcm = new FCM($this->app_id, $this->biz_id, $this->key);
        parent::__construct($name, $data, $dataName);
    }

    /**
     * phpunit 测试
     * @throws AESException
     * @throws GuzzleException
     */
    public function testExample()
    {
        $check = $this->fcm->testCheck('100000000000000001', '某一一', '110000190101010001', 'yA2RxS');
        $this->assertStringContainsString('errcode', $check);

        $query = $this->fcm->testQuery('100000000000000001', 'HHatGD');
        $this->assertStringContainsString('errcode', $query);
    }


    /**
     * check
     *
     * @throws AESException|GuzzleException
     */
    public function testCheck()
    {
        // 认证成功
        echo "\n";
        echo $this->fcm->testCheck('100000000000000001', '某一一', '110000190101010001', 'yA2RxS');
        echo $this->fcm->flushInfo();

        // 认证中
        echo $this->fcm->testCheck('200000000000000001', '某二一', '110000190201010009', '3xTBoG');
        echo $this->fcm->flushInfo();

        // 认证失败
        echo $this->fcm->testCheck('300000000000000001', '某三一', '110000190201010009', 'hkqdzR');
        echo $this->fcm->flushInfo();

    }

    /**
     * query
     *
     * @throws Exception|GuzzleException
     */
    public function testQuery()
    {
        // 认证成功
        echo "\n";
        echo $this->fcm->testQuery('100000000000000001', 'HHatGD');
        echo $this->fcm->flushInfo();

        // 认证中
        echo $this->fcm->testQuery('200000000000000001', 'BwgbTE');
        echo $this->fcm->flushInfo();

        // 认证失败
        echo $this->fcm->testQuery('300000000000000001', 'whzSne');
        echo $this->fcm->flushInfo();

    }


}