<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var yii2mod\rbac\models\AuthItem $model
 * @var yii\widgets\ActiveForm $form
 */
?>

<div class="auth-item-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'name')->textInput(['maxlength' => 64]); ?>

    <?php echo $form->field($model, 'className')->textInput(); ?>

    <?php echo $form->field($model, 'expression')->textarea([
        'rows' => 2,
        'disabled' => $model->className != '' && $model->className != 'yii2mod\rbac\components\BizRule'
    ])->hint('Simple PHP expression. Example: return Yii::$app->user->isGuest;');
    ?>

    <div class="form-group">
        <?php echo Html::submitButton($model->isNewRecord ? 'Create' : 'Update', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']); ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

