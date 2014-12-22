<?php
use yii2mod\rbac\assets\RbacAsset;

RbacAsset::register($this);
$this->params['sidebar'] = [
    [
        'label' => 'Assignments',
        'url' => ['/admin/rbac/assignment/index'],
    ],
    [
        'label' => 'Roles',
        'url' => ['/admin/rbac/role/index'],
    ],
    [
        'label' => 'Permissions',
        'url' => ['/admin/rbac/permission/index'],
    ],
    [
        'label' => 'Routes',
        'url' => ['/admin/rbac/route/index'],
    ],
    [
        'label' => 'Rules',
        'url' => ['/admin/rbac/rule/index'],
    ]
];
