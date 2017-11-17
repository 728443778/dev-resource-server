<?php

namespace app\controllers;

use app\libs\Application;
use app\models\Clients;
use Phalcon\Mvc\Controller;
use sevenUtils\resources\DevManager\Utils;

class ObjectController extends Controller
{
    public function getAction($params)
    {
        $params =  Application::getApp()->decrypt($params);
        $params = json_decode($params, true);
        if (!isset($params['access_id']) || !isset($params['object']) || !isset($params['timeout']) || !isset($params['bucket'])
            || !isset($params['sign_at'])) {
            return $this->responseJson(ERROR_ACCESS_FORBIDDEN);
        }
        if ($params['timeout']) {
            $time = Application::getApp()->getRequestTime();
            $duration = $params['timeout'] + $params['sign_at'];
            if ($duration < $time) {
                return $this->responseJson(ERROR_ACCESS_FORBIDDEN);
            }
        }
        $client = new Clients();
        $path = $client->save_root . '/' . $params['access_id'] . '/' .$params['bucket'] . '/' . $params['object'];
        $mime = Utils::getMimeTypeByExtension($path);
        $this->response->setFileToSend($path);
        $this->response->setRawHeader('Content-Type:' . $mime);
        $this->response->sendHeaders();
        return $this->response;
    }

    public function indexAction($accessId, $bucket, $object)
    {
        $accessId = str_replace('.', '', $accessId);
        $bucket = str_replace('.', '', $bucket);
        $object = str_replace('..', '', $object);
        if (empty($accessId) || empty($bucket) || empty($object)) {
            return $this->responseJson(ERROR_ACCESS_FORBIDDEN);
        }
        $client = new Clients();
        $path = $client->save_root . '/' . $accessId . '/' . $bucket;
        if (!is_dir($path)) {
            return $this->responseJson(ERROR_ACCESS_FORBIDDEN);
        }
        //判断是否公共读 公共进入
        $perms = Utils::getFilePerms($path, false);
        if (!Utils::otherUserHasRPerm($perms) || !Utils::otherUserHasXPerm($perms)) {
            return $this->responseJson(ERROR_ACCESS_FORBIDDEN);
        }
        $file = $path . '/' . $object;
        if (!is_file($file)) {
            return $this->responseJson(ERROR_OBJECTS_DOES_NOT_EXISTS);
        }
        Utils::phpSendFile($file);
        return $this->response;
    }

    protected function responseJson($code = ERROR_NONE, $data = [])
    {
        $data['code'] = $code;
        return $this->response->setJsonContent($data);
    }
}
