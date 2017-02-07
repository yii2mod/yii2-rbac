<?php

namespace yii2mod\rbac;

/**
 * Class ConsoleModule
 *
 * Use [[\yii\base\Module::$controllerMap]] to change property of controller.
 *
 * ```php
 * 'controllerMap' => [
 *     'migrate' => [
 *         'class' => 'yii2mod\rbac\commands\MigrateController',
 *         'migrationTable' => '{{%auth_migration}}',
 *         'migrationPath' => '@app/rbac/migrations',
 *         'templateFile' => 'your own template file'
 *     ]
 * ]
 * ```
 */
class ConsoleModule extends Module
{
    /**
     * @var string the namespace that controller classes are in
     */
    public $controllerNamespace = 'yii2mod\rbac\commands';
}
