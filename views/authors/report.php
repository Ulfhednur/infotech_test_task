<?php

/** @var $this yii\web\View  */
/** @var $items array */
/** @var $years array */

use kartik\select2\Select2;
use yii\helpers\Url;

$this->title = 'Каталог';
$this->params['breadcrumbs'] = [['label' => $this->title]];
$selectOptions = [];
foreach($years as $year){
    $selectOptions[$year] = $year;
}
?>
<?php $form = \yii\widgets\ActiveForm::begin(['action' => Url::to(['authors/report']), 'method' => 'get', 'id'=>'select-year-form']); ?>
<?= Select2::widget([
    'name' => 'year',
    'data' => $selectOptions,
    'options' => [
        'placeholder' => 'Выберите год',
    ],
    'value' => \Yii::$app->getRequest()->get('year', date('Y')),
    'pluginEvents' => [
        "change" => 'function(data) { 
            $("#select-year-form").submit();
        }',
    ]
]); ?>
<?php $form::end(); ?>
<table class="table table-striped table-bordered">
    <thead>
    <tr>
        <th>Автор</th>
        <th>Кол-во книг</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($items as $item){ ?>
        <tr>
            <td><?= $item['fio']?></td>
            <td><?= $item['books_count']?></td>
        </tr>
    <?php } ?>
    </tbody>
</table>