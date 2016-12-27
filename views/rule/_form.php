<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model \yii2mod\rbac\models\BizRuleModel */
/* @var $form ActiveForm */
?>

<div class="rule-item-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'name')->textInput(['maxlength' => 64]); ?>

    <?php echo $form->field($model, 'className')->textInput(); ?>

    <div class="form-group">
        <?php echo Html::submitButton(
            $model->getIsNewRecord() ? Yii::t('yii2mod.rbac', 'Create') : Yii::t('yii2mod.rbac', 'Update'),
            [
                'class' => $model->getIsNewRecord() ? 'btn btn-success' : 'btn btn-primary',
            ]
        ); ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
