<?php

/** @var $this yii\web\View  */
/* @var $item app\models\Book */

use yii\helpers\Url;
use yii\helpers\Html;
$description = $item->getDescription()->one();
$authors = [];
$bc = '';
$bcId = 0;
foreach ($item->getAuthors()->asArray()->all() as $row){
    $authors[] = $row['authors']['fio'];
    if (empty($bc)) {
        $bc = $row['authors']['fio'];
        $bcId = $row['authors']['id'];
    }
}

$authors = implode(', ', $authors);
$this->title = $description->title;
$this->params['breadcrumbs'] = [
    [
        'label' => 'Каталог',
        'url' => ['authors/index']
    ],
    [
        'label' => $bc,
        'url' => ['books/index', 'author_id' => $bcId]
    ],
    [
        'label' => $this->title
    ],
];
?>

<div class="row">
    <div class="col-lg-6">
        <img src="/<?= $description->cover_image ?>" alt="<?= $description->title ?>" class="img-fluid">
    </div>
    <div class="col-lg-6">
        <h1><?= $description->title ?></h1>
        <h3><?= $authors ?></h3>
        <dl class="row">
            <dt class="col-3">ISBN:</dt>
            <dd class="col-9"><?= $item->isbn ?></dd>
            <dt class="col-3">Год издания:</dt>
            <dd class="col-9"><?= $item->year ?></dd>
        </dl>
        <div><?= $description->description ?></div>
        <?php if (\Yii::$app->user->identity) { ?>
            <div><?= Html::a('Редактировать книгу', ['update', 'id' => $item->id], ['class' => 'btn btn-success']) ?></div>
        <?php } ?>
    </div>
</div>

