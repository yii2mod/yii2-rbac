<?php

/* @var $this \yii\web\View */

$this->params['sidebar'] = [
    [
        'label' => Yii::t('yii2mod.rbac', 'Assignments'),
        'url' => ['/admin/rbac/assignment/index'],
    ],
    [
        'label' => Yii::t('yii2mod.rbac', 'Roles'),
        'url' => ['/admin/rbac/role/index'],
    ],
    [
        'label' => Yii::t('yii2mod.rbac', 'Permissions'),
        'url' => ['/admin/rbac/permission/index'],
    ],
    [
        'label' => Yii::t('yii2mod.rbac', 'Routes'),
        'url' => ['/admin/rbac/route/index'],
    ],
    [
        'label' => Yii::t('yii2mod.rbac', 'Rules'),
        'url' => ['/admin/rbac/rule/index'],
    ],
];
