<?php

namespace app\controllers;

use app\models\Author;
use app\models\Book;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\helpers\Url;

class BooksController extends Controller
{
    public $modelClass = Book::class;

    public function beforeAction($action): bool
    {
        $res = parent::beforeAction($action);
        switch ($action->id){
            case 'view':
            case 'index':
                return $res;
            default:
                if(empty(Yii::$app->user->identity)){
                    throw new ForbiddenHttpException();
                }
                return $res;
        }
    }

    /**
     * @throws \yii\di\NotInstantiableException
     * @throws \yii\base\InvalidConfigException
     */
    public function actionIndex(): string
    {
        $input = \Yii::$app->getRequest()->getQueryParams();

        Yii::$app->getRequest()->setQueryParams($input);

        /** @var Book $class */
        $class = \Yii::$container->get($this->modelClass);
        $query = $class::find()
            ->joinWith('description')
            ->joinWith('authors');
        $author = null;
        if (!empty($input['author_id'])) {
            $author = Author::findOne(['id' =>$input['author_id']]);
            if(!$author){
                return new NotFoundHttpException();
            }
            $query->where(['author_id' => $input['author_id']]);

        }
        /** @var ActiveDataProvider $dataProvider */
        $dataProvider = \Yii::createObject(
            [
                'class' => ActiveDataProvider::class,
                'query' => $query,
                'pagination' => [
                    'params' => $input,
                    'pageSizeLimit' => [1, 200],
                    'defaultPageSize' => 20
                ],
                'sort' => ['params' => $input],
            ]
        );

        return $this->render('index', ['dataProvider' => $dataProvider, 'author' => $author]);
    }

    public function actionView(int $id = null): string
    {
        if (!$id) {
            $id = \Yii::$app->getRequest()->get('id');
        }

        if ($id) {
            $class = \Yii::$container->get($this->modelClass);

            /** @var Book $item */
            if ($item = $class::findOne(['id' => $id])) {
                return $this->render('view', ['item' => $item]);
            }
        }
        return new NotFoundHttpException();
    }

    public function actionCreate(): string
    {
        return $this->showEditForm();
    }

    public function actionUpdate(): string
    {
        return $this->showEditForm(false);
    }

    protected function showEditForm(bool $new = true):string
    {
        if ($new) {
            $model = new Book();
        } else {
            $model = Book::findOne(['id' => \Yii::$app->getRequest()->get('id')]);
            if (empty($model->id)) {
                return new NotFoundHttpException();
            }
        }
        return $this->render('edit', ['model' => $model]);
    }

    /**
     * @throws \yii\base\InvalidConfigException
     * @throws BadRequestHttpException
     */
    public function actionSave(): BadRequestHttpException|Response
    {
        $input = \Yii::$app->getRequest()->getBodyParams();
        if (empty($input['id'])) {
            $model = new Book();
        } else {
            $model = Book::findOne(['id' => $input['id']]);
        }

        $model->load($input, '');
        if($model->validate()) {
            $model->save(false);
        }

        if(!empty($model->errors)){
            return new BadRequestHttpException($model->getErrorSummary(true)[0], 417);
        }

        return $this->redirect(Url::to(['books/view', 'id' => $model->id]));
    }

    /**
     * @throws \yii\di\NotInstantiableException
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete(): NotFoundHttpException|Response
    {
        $id = \Yii::$app->getRequest()->get('id');
        if ($id) {
            $class = \Yii::$container->get($this->modelClass);

            /** @var Book $item */
            if ($item = $class::findOne(['id' => $id])) {
                if ($item->delete()) {
                    return $this->redirect(Url::to(['books/index']));
                }
            }
        }
        return new NotFoundHttpException();
    }
}
