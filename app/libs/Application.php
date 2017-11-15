<?php

namespace app\libs;

use app\models\User;
use app\traits\Services;
use Phalcon\DiInterface;

class Application extends \Phalcon\Mvc\Application
{

    use Services;

    /**
     * @var Application
     */
    public static $app;

    public $requestId;

    public function __construct(DiInterface $dependencyInjector = null)
    {
        parent::__construct($dependencyInjector);
        self::$app = $this;
    }

    public function getRandNumber($length = 6)
    {
        $random = $this->getRandom();
        $captcha = '';
        for ($i = 0; $i < $length; ++$i) {
            $captcha .= $random->number(9);
        }
        return $captcha;
    }

    public function getRequestId()
    {
        if ($this->requestId) {
            return $this->requestId;
        }
        $this->requestId = $this->genRandomString(16);
        return $this->requestId;
    }

    public function getUUid()
    {
        return $this->getRandom()->uuid();
    }

    public function genRandomString($binBytes = 16)
    {
        $bytes = $this->getRandom()->bytes($binBytes);
        return bin2hex($bytes);
    }

    public function encrypt($string)
    {
        $bin = $this->crypt->encrypt($string);
        return bin2hex($bin);
    }

    public function decrypt($string)
    {
        $bin = hex2bin($string);
        return $this->crypt->decrypt($bin);
    }

    public function genToken($prefix)
    {
        $token = $this->genRandomString();
        return $prefix . '-' . $token;
    }

    public function getRequestTime()
    {
        $time = $this->request->getServer('REQUEST_TIME');
        if (!$time) {
            $time = $this->request->getServer('request_time');
            if (!$time) {
                $time = time();
                $_SERVER['REQUEST_TIME'] = $time;
            }
        }
        return $time;
    }
}