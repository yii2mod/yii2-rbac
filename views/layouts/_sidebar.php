<?php

/* @var $this \yii\web\View */

$this->params['sidebar'] = [
    [
        'label' => Yii::t('yii2mod.rbac', 'Assignments'),
        'url' => ['assignment/index'],
    ],
    [
        'label' => Yii::t('yii2mod.rbac', 'Roles'),
        'url' => ['role/index'],
    ],
    [
        'label' => Yii::t('yii2mod.rbac', 'Permissions'),
        'url' => ['permission/index'],
    ],
    [
        'label' => Yii::t('yii2mod.rbac', 'Routes'),
        'url' => ['route/index'],
    ],
    [
        'label' => Yii::t('yii2mod.rbac', 'Rules'),
        'url' => ['rule/index'],
    ],
];
