<?php

namespace app\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use app\models\Author;
use yii\db\Expression;
use yii\db\Query;

class AuthorsController extends Controller
{
    public $modelClass = Author::class;

    public function actionIndex(): string
    {
        $input = \Yii::$app->getRequest()->getQueryParams();

        if(empty($input['per-page'])){
            $input['per-page'] = 20;
        }
        if(empty($input['page'])){
            $input['page'] = 1;
        }
        Yii::$app->getRequest()->setQueryParams($input);
        $class = \Yii::$container->get($this->modelClass);

        /** @var ActiveDataProvider $obj */
        $dataProvider = \Yii::createObject(
            [
                'class' => ActiveDataProvider::class,
                'query' => $class::find()
                    ->orderBy(['fio' => SORT_ASC]),
                'pagination' => [
                    'params' => $input,
                    'pageSizeLimit' => [1, 200],
                    'defaultPageSize' => 20
                ],
                'sort' => ['params' => $input],
            ]
        );
        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    public function actionAutocomplete(): \yii\web\Response
    {
        $input = Yii::$app->getRequest()->get('q');

        $items = Author::find()
                    ->select('id, fio AS text')
                    ->where(['LIKE', 'fio', $input.'%', false])
                    ->asArray()
                    ->all();

        return $this->asJson(['results' => $items]);
    }

    /**
     * не вижу смысла заводить модель ради двух запросов фиксированной длины
     * @return string
     */
    public function actionReport(): string
    {
        $year = \Yii::$app->getRequest()->get('year', date('Y'));
        $items = (new Query())
            ->select(['a.fio', new Expression('COUNT(*) AS books_count')])
            ->from('book_author AS ba')
            ->innerJoin('authors AS a', '`a`.`id` = `ba`.`author_id`')
            ->innerJoin('books AS b', '`b`.`id` = `ba`.`book_id`')
            ->where(['b.year' => $year])
            ->groupBy('ba.author_id')
            ->orderBy('books_count')
            ->limit(10)
            ->all();

        $years = (new Query())
            ->select('year')
            ->distinct()
            ->from('books')
            ->orderBy(['year' => SORT_DESC])
            ->column();
        return $this->render('report', ['items' => $items, 'years' => $years]);
    }
}
