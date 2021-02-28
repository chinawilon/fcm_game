<?php

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
     * FCMTest constructor.
     *
     * @param null $name
     * @param array $data
     * @param string $dataName
     */
    public function __construct($name = null, array $data = [], $dataName = '')
    {
        $this->ai = md5('test');
        $this->fcm = new FCM($this->app_id, $this->biz_id, $this->key);
        parent::__construct($name, $data, $dataName);
    }

    public function testCheck()
    {
        $this->fcm->debug(false);
        $ret = $this->fcm->check($this->ai, '姜子牙', '4306199910113991');
        $this->assertStringContainsString('errcode', $ret);
    }

    public function testQuery()
    {
        $this->fcm->debug(false);
        $ret = $this->fcm->query($this->ai);
        $this->assertStringContainsString('errcode', $ret);
    }

    public function testDebugCheck()
    {
        $this->fcm->debug(true);
        $ret = $this->fcm->check($this->ai, '姜子牙', '4306199910113991');
        $this->assertStringContainsString('errcode', $ret);
    }

    public function testDebugQuery()
    {
        $this->fcm->debug(true);
        $ret = $this->fcm->query($this->ai);
        $this->assertStringContainsString('errcode', $ret);
    }

}