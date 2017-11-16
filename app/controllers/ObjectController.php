<?php

namespace app\controllers;

use app\libs\Application;
use app\models\Clients;
use Phalcon\Mvc\Controller;

class ObjectController extends Controller
{
    public function getAction($params)
    {
        $params =  Application::getApp()->decrypt($params);
        $params = json_decode($params, true);
        if (!isset($params['access_id']) || !isset($params['object']) || !isset($params['timeout'])
            || !isset($params['sign_at'])) {
            return $this->responseJson(ERROR_ACCESS_FORBIDDEN);
        }
        if (!$params['timeout']) {
            $time = Application::getApp()->getRequestTime();
            $duration = $params['timeout'] + $params['sign_at'];
            if ($duration < $time) {
                return $this->responseJson(ERROR_ACCESS_FORBIDDEN);
            }
        }
        $client = new Clients();
        $path = $client->save_root . '/' . $params['access_id'] . '/' . $params['object'];
        return $this->response->setFileToSend($path);
    }

    protected function responseJson($code = ERROR_NONE, $data = [])
    {
        $data['code'] = $code;
        return $this->response->setJsonContent($data);
    }
}
