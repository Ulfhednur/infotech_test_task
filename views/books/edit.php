<?php

/** @var $this yii\web\View  */
/* @var  $model \app\models\Book */
/* @var  $descriptionModel \app\models\BookDescription */

use app\models\BookDescription;
use yii\helpers\Url;
use yii\helpers\Html;
use kartik\select2\Select2;
use yii\web\JsExpression;

$descriptionModel = $model->getDescription()->one() ?? new BookDescription;
$authors = [];
$values = [];
if(!empty($model->id)) {
    foreach ($model->getAuthors()->asArray()->all() as $row) {
        $authors[$row['authors']['id']] = $row['authors']['fio'];
        $values[] = $row['authors']['id'];
    }
}
$authorModel = new \app\models\BookAuthor();

if (!empty($model->title)) {
    $this->title = 'Книга ' . $model->title;
} else {
    $this->title = 'Новая книга';
}

?>
<h1><?=$this->title;?></h1>
<?php $form = \yii\widgets\ActiveForm::begin(['action' => Url::to(['books/save']), 'options' => ['enctype' => 'multipart/form-data']]); ?>
<?= Html::activeHiddenInput($model, 'id'); ?>
<?= $form->field($descriptionModel, 'title'); ?>
<?= $form->field($authorModel, 'author_id')->widget(Select2::class, [
    'data' => $authors,
    'options' => [
        'class' => 'form-control',
        'placeholder' => 'Выберите автора',
        'multiple' => true,
        'value' => $values,
    ],
    'bsVersion' => '5.x',
    'pluginOptions' => [
        'allowClear' => false,
        'minimumInputLength' => 2,
        'ajax' => [
            'url' => Url::to(['authors/autocomplete']),
            'dataType' => 'json',
            'data' => new JsExpression('function(params) { return {q:params.term}; }')
        ],
    ],
])->label('Автор(ы)');
?>
<?= $form->field($model, 'isbn'); ?>
<?= $form->field($model, 'year'); ?>
<?= $form->field($descriptionModel, 'cover_image')->fileInput(); ?>
<?= $form->field($descriptionModel, 'description')->textarea(['rows' => '12']); ?>
<div class="form-group">
    <?= Html::submitButton('Сохранить', ['name' => 'save', 'value' => 1, 'class' => 'btn btn-success']) ?>
</div>
<?php $form::end(); ?>

