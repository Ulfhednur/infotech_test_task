<?php
/** @var $this yii\web\View  */
/** @var $dataProvider yii\data\ArrayDataProvider */
/** @var $model app\models\Subscription */

use yii\helpers\Url;
use yii\grid\GridView;
use yii\helpers\Html;

$this->title = 'Подписки';
$this->params['breadcrumbs'][] = ['label' => $this->title];

?>

<?php $form = \yii\widgets\ActiveForm::begin(['action' => Url::to(['subscription/index']), 'method' => 'get']); ?>
    <?= Html::activeInput('text', $model, 'phone_num', ['value' => $model->phone_num]); ?>
    <?= Html::submitButton('Найти', ['name' => 'index', 'value' => 1, 'class' => 'btn btn-success']) ?>
<?php $form::end(); ?>

<?php if(!empty($dataProvider) && $dataProvider->getTotalCount() != 0) {?>
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
                    'header' => 'Автор',
                    'attribute' => 'fio',
                    'format' => 'raw',
                    'value' => function($providedModel) {
                        return '<a href="'. Url::to([
                            'books/index',
                            'author_id' => $providedModel->author_id
                        ]) .'">' . $providedModel->authors->fio . '</a>';
                    },
                ],
                [
                    'header' => 'Отписаться',
                    'format' => 'raw',
                    'value' => function($providedModel) {
                        return '<a href="'. Url::to([
                            'subscription/delete',
                            'author_id' => $providedModel->author_id,
                            'phone_num' => $providedModel->phone_num
                        ]) .'" class="btn btn-danger">Отписаться</a>';
                    },
                ]
            ]
        ]);
    ?>
<?php } elseif(!empty($model->phone_num)) { ?>
    <h3>Вы ни на что не подписаны</h3>
<?php } else { ?>
    <h3>Укажите номер телефона</h3>
<?php } ?>
