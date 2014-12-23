<?php

use yii\helpers\Html;

/**
 * @var yii\web\View                 $this
 * @var yii2mod\rbac\models\AuthItem $model
 */

$this->title = 'Create BizRule';
$this->params['breadcrumbs'][] = [
    'label' => 'BizRules',
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<div class="auth-item-create">

    <h1><?php echo Html::encode($this->title); ?></h1>

    <?php echo $this->render('_form', [
        'model' => $model,
    ]); ?>

</div>
