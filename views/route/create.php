<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View              $this
 * @var yii2mod\rbac\models\Route $model
 * @var ActiveForm                $form
 */

$this->title = 'Create Route';
$this->params['breadcrumbs'][] = [
    'label' => 'Routes',
    'url' => ['index']
];
$this->params['breadcrumbs'][] = $this->title;
$this->render('/layouts/_sidebar');
?>
<h1>Create Route</h1>

<div class="create">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'route'); ?>

    <div class="form-group">
        <?php echo Html::submitButton('Submit', ['class' => 'btn btn-primary']); ?>
    </div>
    <?php ActiveForm::end(); ?>

</div><!-- create -->
