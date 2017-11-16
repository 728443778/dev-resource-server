<?php

namespace app\controllers;

use app\libs\Application;
use app\models\Clients;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;
use sevenUtils\resources\DevManager\Utils;

class IndexController extends Controller
{

    /**
     * @var Clients
     */
    protected $client;

    /**
     * @param $dispatcher Dispatcher
     * @return boolean
     */
    public function beforeExecuteRoute($dispatcher)
    {
        $clienId = $this->request->getPost('access_id');
        $token = $this->request->getPost('access_token');
        $accessAt = $this->request->getPost('access_at');
        if (empty($clienId) || empty($token) ||empty($accessAt)) {
            $this->responseJson(ERROR_APP_ACCESS_AUTH_FAILED);
            return false;
        }
        $client = Clients::findFirst([
            'conditions' => 'access_id=?0 and status=1',
            'limit' => 1,
            'bind' => [ 0 =>$clienId],
            'cache' => [
                'key' => 'client-' . $clienId,
                'lifetime' => 3600
            ],
        ]);
        if (!$client) {
            $this->responseJson(ERROR_NONE);
            return false;
        }
        if (($accessAt + 5) < Application::getApp()->getRequestTime()) {
            $this->responseJson(ERROR_APP_ACCESS_AUTH_FAILED);
            return false;
        }
        if ($token != md5($client->access_token . $accessAt)) {
            $this->responseJson(ERROR_APP_ACCESS_AUTH_FAILED);
            return false;
        }
        $this->client = $client;
        return true;
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
            case Utils::OPERATION_UPLOAD_OBJECT:
                return $this->uploadObject();
            case Utils::OPERATION_SIGN_URL:
                return $this->signUrl();
            default:
                return $this->responseJson(ERROR_OPERATION_FAILED);
        }
    }

    protected function getRequestBucket()
    {
        $bucket = $this->request->getPost('bucket');
        $bucket = str_replace('.', '', $bucket);
        $bucket = str_replace(' ', '', $bucket);
        $bucket = str_replace('/', '', $bucket);
        return $bucket;
    }

    protected function getRequestObject()
    {
        $object = $this->request->getPost('object');
        $object = str_replace('..', '', $object);
        return $object;
    }

    protected function signUrl()
    {
        $bucket = $this->getRequestBucket();
        if (empty($bucket)) {
            return $this->responseJson(ERROR_BUCKET_PARAM_INVALID);
        }
        $object = $this->getRequestObject();
        if (empty($object)) {
            return $this->responseJson(ERROR_OBJECT_NAME_INVALID);
        }
        $timeOut = (int)$this->request->getPost('timeout');
        //生成访问的url
        $host = $this->request->getHttpHost();
        //加密
        $url = [
            'access_id' => $this->client->access_id,
            'object' => $object,
            'bucket' => $bucket,
        ];
        if ($timeOut) {
            $url['timeout'] = $timeOut;
            $url['sign_at'] = Application::getApp()->getRequestTime();
        }
        $url = Application::getApp()->encrypt(json_encode($url));
        $url = 'http://' . $host . '/object/' . $url;
        return $this->responseJson(ERROR_NONE, ['url' => $url]);
    }

    protected function uploadObject()
    {
        $path = $this->client->save_root . '/' . $this->client->access_id;
        $bucket = $this->getRequestBucket();
        $objectName = $this->getRequestObject();
        $path = $path . '/' . $bucket;
        if (!is_dir($path)) {
            return $this->responseJson(ERROR_BUCKET_NOT_EXISTS);
        }
        $files = $this->request->getUploadedFiles();
        if (count($files) != 1) {
            return $this->responseJson(ERROR_UPLOAD_FILE_NUMBER_ERROR);
        }
        $file = $files[0];
        if ($file->getError()) {
            return $this->responseJson($file->getError());
        }
        if (empty($objectName)) {
            return $this->responseJson(ERROR_OBJECT_NAME_INVALID);
        }
        $objectName = str_replace('..', '', $objectName);
        $endpos = strpos($objectName, '/', -1);
        if ($endpos) {
            //找到最后一个／
            $prefix = substr($objectName, 0, $endpos);
            $objectName =  substr($objectName, $endpos+1);
            if (empty($objectName)) {
                return $this->responseJson(ERROR_OBJECT_NAME_INVALID);
            }
            if ($prefix) {
                $path = $path . '/' . $prefix;
                if (!is_dir($path) && !mkdir($path)) {
                    return $this->responseJson(ERROR_OPERATION_FAILED);
                }
            }
        }
        if (!$file->moveTo($path . '/' . $objectName)) {
            return $this->responseJson(ERROR_OPERATION_FAILED);
        }
        return $this->responseJson(ERROR_NONE);
    }

    protected function deleteBucket()
    {

    }

    protected function createBucket()
    {
        $bucket = $this->request->getPost('bucket');
        $bucket = str_replace('.', '', $bucket);
        $bucket = str_replace(' ', '', $bucket);
        $bucket = str_replace('/', '', $bucket);
        $acl = $this->request->getPost('acl');
        if (empty($bucket)) {
            return $this->responseJson(ERROR_REQUEST_DATA_INVALID);
        }
        $path = $this->client->save_root . '/' . $this->client->access_id;
        if (!is_dir($path)) {
            return $this->responseJson(ERROR_OPERATION_FAILED);
        }
        $mode = 0770;
        if ($acl == Utils::ACL_TYPE_PUBLIC_READ) {
            $mode = 0775;
        }
        $path = $path . '/' . $bucket;
        if (!mkdir($path, $mode)) {
            return $this->responseJson(ERROR_OPERATION_FAILED);
        }
        return $this->responseJson(ERROR_NONE);

    }

    protected function getObject()
    {
        $bucket = $this->request->getPost('bucket');
        $bucket = str_replace('.', '', $bucket);
        $bucket = str_replace(' ', '', $bucket);
        $bucket = str_replace('/', '', $bucket);
        $path = $this->client->save_root . '/' . $this->client->access_id . '/' . $bucket;
        $object = $this->request->getPost('object');
        $object = str_replace('..', '', $object);
        $file = $path . '/' . $object;
        return $this->response->setFileToSend($file);
    }
}