<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/**
 * @var yii\web\View                 $this
 * @var yii2mod\rbac\models\AuthItem $model
 */
$this->title = $model->name;
$this->params['breadcrumbs'][] = [
    'label' => 'Rules',
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<div class="auth-item-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?=
        Html::a('Update', [
            'update',
            'id' => $model->name
        ], ['class' => 'btn btn-primary']) ?>
        <?php
        echo Html::a('Delete', [
            'delete',
            'id' => $model->name
        ], [
            'class' => 'btn btn-danger',
            'data-confirm' => Yii::t('app', 'Are you sure to delete this item?'),
            'data-method' => 'post',
        ]);
        ?>
    </p>

    <?php
    echo DetailView::widget([
        'model' => $model,
        'attributes' => [
            'name',
            'className',
            'expression:ntext',
        ],
    ]);
    ?>
</div>

