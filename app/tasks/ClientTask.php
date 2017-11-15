<?php

namespace app\tasks;

use app\libs\Application;
use app\models\Clients;
use Phalcon\Cli\Task;

class  ClientTask extends Task
{
    public function createAction()
    {
        $newClient = new Clients();
        $newClient->access_id = Application::getApp()->genRandomString(4);
        $newClient->access_token = Application::getApp()->genRandomString(24);
        $newClient->getWriteConnection()->begin();
        if ($newClient->save()) {
            $config = $this->getDI()->getConfig();
            $savePath = $config->save_path;
            if (!is_dir($savePath)) {
                if (!mkdir($savePath, 0755)) {
                    echo 'create new client failed',PHP_EOL;
                    $newClient->getWriteConnection()->rollback();
                    return ;
                }
            }
            if (!mkdir($savePath . '/' . $newClient->access_id, 0755)) {
                echo 'create new client failed',PHP_EOL;
                $newClient->getWriteConnection()->rollback();
                return ;
            }
            $newClient->getWriteConnection()->commit();
            echo 'create new client success',PHP_EOL;
            echo 'access_id:',$newClient->access_id,PHP_EOL;
            echo 'access_token:',$newClient->access_token,PHP_EOL;
        } else {
            $newClient->getWriteConnection()->rollback();
            echo 'create new client failed',PHP_EOL;
        }
    }
}