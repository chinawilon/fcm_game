# FCM 网络游戏防沉迷实名认证系统包
[![Build Status](https://travis-ci.org/chinawilon/fcm_game.svg?branch=main)](https://travis-ci.org/chinawilon/fcm_game)
[![codecov](https://codecov.io/gh/chinawilon/fcm_game/branch/main/graph/badge.svg?token=97TOvviWUH)](https://codecov.io/gh/chinawilon/fcm_game)
![Supported PHP versions: =7.1+](https://img.shields.io/badge/php-7.1+-blue.svg)

example:

```php
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
        $ret = json_decode($this->fcm->check($this->ai, '姜子牙', '4306199910113991'), true);
        $this->assertEquals(0, $ret['errcode']);
    }

    public function testQuery()
    {
        $this->fcm->debug(false);
        $ret = json_decode($this->fcm->query($this->ai));
        $this->assertEquals(0, $ret['errcode']);
    }

    public function testDebugCheck()
    {
        $this->fcm->debug(true);
        $ret = json_decode($this->fcm->check($this->ai, '姜子牙', '4306199910113991'), true);
        echo $this->fcm->flushInfo();
        $this->assertEquals(0, $ret['errcode']);
    }

    public function testDebugQuery()
    {
        $this->fcm->debug(true);
        $ret = json_decode($this->fcm->query($this->ai));
        echo $this->fcm->flushInfo();
        $this->assertEquals(0, $ret['errcode']);
    }

}

```

#LICENSE
MIT