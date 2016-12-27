<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model yii2mod\rbac\models\AuthItemModel */
?>
<div class="auth-item-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php echo $form->field($model, 'name')->textInput(['maxlength' => 64]); ?>

    <?php echo $form->field($model, 'description')->textarea(['rows' => 2]); ?>

    <?php echo $form->field($model, 'ruleName')->widget('yii\jui\AutoComplete', [
        'options' => [
            'class' => 'form-control',
        ],
        'clientOptions' => [
            'source' => array_keys(Yii::$app->authManager->getRules()),
        ],
    ]);
    ?>

    <?php echo $form->field($model, 'data')->textarea(['rows' => 6]); ?>

    <div class="form-group">
        <?php echo Html::submitButton($model->getIsNewRecord() ? Yii::t('yii2mod.rbac', 'Create') : Yii::t('yii2mod.rbac', 'Update'), ['class' => $model->getIsNewRecord() ? 'btn btn-success' : 'btn btn-primary']); ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>
