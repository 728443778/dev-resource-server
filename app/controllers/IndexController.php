<?php

namespace app\controllers;

use Phalcon\Mvc\Controller;
use sevenUtils\resources\DevManager\Utils;

class IndexController extends Controller
{
    protected function responseJson($code = ERROR_NONE, $data = [])
    {
        $data['code'] = $code;
    }

    public function indexAction()
    {
        $operation = $this->request->getPost('operation');
        switch ($operation) {
            case Utils::OPERATION_DELETE_BUCKET:
                return $this->deleteBucket();
            case Utils::OPERATION_CREATE_BUCKET:
                return $this->createBucket();
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
}