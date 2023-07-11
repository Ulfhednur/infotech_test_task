<?php

namespace app\controllers;

use app\models\Book;
use app\models\Subscription;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\Url;

class SubscriptionController extends Controller
{
    public $modelClass = Subscription::class;

    public function actionIndex(): string
    {
        $input = \Yii::$app->getRequest()->getQueryParams();

        /** @var Subscription $model */
        $model = \Yii::$container->get($this->modelClass);
        if(!empty($input['phone_num'])) {
            $model->phone_num = (int)preg_replace("/[^0-9]/", '', $input['phone_num']);
            /** @var ActiveDataProvider $dataProvider */
            $dataProvider = \Yii::createObject(
                [
                    'class' => ActiveDataProvider::class,
                    'query' => $model::find()
                        ->joinWith('authors')
                        ->where(['phone_num' => (int)preg_replace("/[^0-9]/", '', $input['phone_num'])]),
                    'pagination' => [
                        'params' => $input,
                        'pageSizeLimit' => [1, 2000],
                        'defaultPageSize' => 200
                    ],
                    'sort' => ['params' => $input],
                ]
            );

            return $this->render('index', ['dataProvider' => $dataProvider, 'model' => $model]);
        }
        return $this->render('index', ['model' => $model]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionSave(): BadRequestHttpException|Response
    {
        $input = [
            'author_id' => \Yii::$app->getRequest()->post('author_id'),
            'phone_num' => \Yii::$app->getRequest()->post('phone_num'),
        ];
        $model = new Subscription($input);

        if($model->validate()) {
            $model->save(false);
        }

        if(!empty($model->errors)){
            return new BadRequestHttpException($model->getErrorSummary(true)[0], 417);
        }

        return $this->redirect(Url::to(['books/index', 'author_id' => $input['author_id']]));
    }

    /**
     * @throws \yii\di\NotInstantiableException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete(): NotFoundHttpException|Response
    {
        $input = [
            'author_id' => \Yii::$app->getRequest()->get('author_id'),
            'phone_num' => \Yii::$app->getRequest()->get('phone_num'),
        ];
        if ($input['author_id'] && $input['phone_num']) {

            /** @var Subscription $model */
            $model = \Yii::$container->get($this->modelClass);

            /** @var Book $item */
            if ($item = $model::findOne(['author_id' => $input['author_id'], 'phone_num' => $input['phone_num']])) {
                $item->delete();
                return $this->redirect(Url::to(['subscription/index', 'phone_num' => $input['phone_num']]));
            }
        }
        return new NotFoundHttpException();
    }
}
