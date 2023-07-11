<?php

/** @var $this yii\web\View  */
/** @var $dataProvider yii\data\ArrayDataProvider */
/** @var $author app\models\Author */

use yii\helpers\Url;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = $author ? $author->fio : 'Книги';

$model = new \app\models\Subscription();
if ($author) {
    $this->params['breadcrumbs'][] = [
        'label' => 'Каталог',
        'url' => ['authors/index']
    ];
    $model->author_id = $author->id;
}
$this->params['breadcrumbs'][] = ['label' => $this->title];
?>
<?php if (\Yii::$app->user->identity) { ?>
<?= Html::a('Добавить книгу', ['create'], ['class' => 'btn btn-success']) ?>
<?php } ?>
<?php if ($author) { ?>
    <?php $form = \yii\widgets\ActiveForm::begin(['action' => Url::to(['subscription/save']), 'method' => 'post']); ?>
    <?= Html::activeHiddenInput($model, 'author_id'); ?>
    <?= Html::activeInput('text', $model, 'phone_num'); ?>
    <?= Html::submitButton('Подписаться на новые книги', ['name' => 'save', 'value' => 1, 'class' => 'btn btn-success']) ?>
    <?php $form::end(); ?>
<?php } ?>
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
                'header' => 'Название',
                'attribute' => 'description.title',
                'format' => 'raw',
                'value' => function($model) {
                    return '<a href="'. Url::to(['view', 'id' => $model->id]) .'">' . $model->getDescription()->one()->title . '</a>';
                },
            ],
            [
                'header' => 'ISBN',
                'attribute' => 'isbn',
                'value' => 'isbn'
            ],
            [
                'header' => 'Год',
                'attribute' => 'year',
                'value' => 'year',
            ],
            [
                'header' => 'Автор(ы)',
                'attribute' => 'authors.fio',
                'value' => function($model) {
                    $authors = [];
                    foreach ($model->getAuthors()->asArray()->all() as $row){
                        $authors[] = $row['authors']['fio'];
                    }
                    return implode(', ', $authors);
                },
            ],
            [
                'header' => 'удалить',
                'format' => 'raw',
                'value' => function($model) {
                    return '<a href="'. Url::to(['books/delete', 'id' => $model->id]) .'" class="btn btn-danger">X</a>';
                },
            ]
        ]
    ]);
?>

