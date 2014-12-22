<?php

use yii\helpers\Html;

/**
 * @var yii\web\View                 $this
 * @var yii2mod\rbac\models\AuthItem $model
 */

$this->title = 'Create Permission';
$this->params['breadcrumbs'][] = [
    'label' => 'Permissions',
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<div class="auth-item-create">
    <h1><?php echo Html::encode($this->title); ?></h1>
    <blockquote><p>A permission can be assigned to many operations.</p></blockquote>
    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>
</div>
