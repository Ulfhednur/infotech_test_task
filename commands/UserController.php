<?php

namespace app\commands;

use app\models\User;
use yii\console\Controller;
use yii\console\ExitCode;


class UserController extends Controller
{
    public function actionCreate($login, $password)
    {
        $user = new User(['username' => $login, 'password_hash' => $password]);
        if ($user->save()) {
            return ExitCode::OK;
        }
        return ExitCode::DATAERR;
    }
}
