<?php

/** @var $this yii\web\View  */
/** @var $dataProvider yii\data\ArrayDataProvider */

use yii\helpers\Url;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Каталог';
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>

<?= GridView::widget(
    [
        'dataProvider' => $dataProvider,
        'layout' => Html::dropDownList(
                'per-page',
                Yii::$app->request->get('per-page'),
                [20 => 20, 50 => 50, 100 => 100, 200 => 200],
                ['id' => 'pageSize', 'style' => 'margin: 10px 0;']
            ).
            '{items}',
        'filterSelector' => '#pageSize',
        'columns' => [
            [
                'header' => 'ФИО',
                'attribute' => 'fio',
                'format' => 'raw',
                'value' => function($model) {
                    return '<a href="'. Url::to(['books/index', 'author_id' => $model->id]) .'">' . $model->fio . '</a>';
                },
            ]
        ]
    ]);
?>