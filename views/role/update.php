<?php

use yii\helpers\Html;

/**
 * @var yii\web\View                 $this
 * @var yii2mod\rbac\models\AuthItem $model
 */
$this->title = 'Update Role: ' . $model->name;
$this->params['breadcrumbs'][] = [
    'label' => 'Roles',
    'url' => ['index']
];
$this->params['breadcrumbs'][] = [
    'label' => $model->name,
    'url' => [
        'view',
        'id' => $model->name
    ]
];
$this->params['breadcrumbs'][] = 'Update';
$this->render('/layouts/_sidebar');
?>
<div class="auth-item-update">

    <h1><?php echo Html::encode($this->title); ?></h1>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]);
    ?>
</div>
