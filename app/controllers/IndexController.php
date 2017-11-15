<?php

namespace app\controllers;

use app\models\Clients;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use SebastianBergmann\CodeCoverage\Util;
use sevenUtils\resources\DevManager\Utils;

class IndexController extends Controller
{
    protected $client;

    /**
     * @param $dispatcher Dispatcher
     */
    public function beforeExecuteRoute($dispatcher)
    {
        $clienId = $this->request->getPost('access_id');
        $token = $this->request->getPost('access_token');
        $accessAt = $this->request->getPost('access_at');
        $client = Clients::findFirst([
            'condition' => 'access_id=?0 and status=1',
            'limit' => 1,
            'bind' => [$clienId],
            'cache' => [
                'key' => 'client-' . $clienId,
                'lifetime' => 3600
            ],
        ]);
        if (!$client) {
            $this->responseJson(ERROR_NONE);
            return false;
        }
    }

    protected function responseJson($code = ERROR_NONE, $data = [])
    {
        $data['code'] = $code;
        return $this->response->setJsonContent($data);
    }

    public function indexAction()
    {
        $operation = $this->request->getPost('operation');
        switch ($operation) {
            case Utils::OPERATION_DELETE_BUCKET:
                return $this->deleteBucket();
            case Utils::OPERATION_CREATE_BUCKET:
                return $this->createBucket();
            case Utils::OPERATION_GET_OBJECT:
                return $this->getObject();
            default:
                return $this->responseJson(ERROR_OPERATION_FAILED);
        }
    }



    protected function deleteBucket()
    {

    }

    protected function createBucket()
    {
        $bucket = $this->request->getPost('bucket');
    }

    protected function getObject()
    {

    }
}