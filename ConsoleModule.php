<?php

namespace yii2mod\rbac;

use yii\base\BootstrapInterface;

/**
 * Class ConsoleModule
 *
 * Use [[\yii\base\Module::$controllerMap]] to change property of controller.
 *
 * ~~~
 * 'controllerMap' => [
 *     'migrate' => [
 *         'class' => 'yii2mod\rbac\commands\MigrateController',
 *         'migrationTable' => '{{%auth_migration}}',
 *         'migrationPath' => '@app/rbac/migrations',
 *         'templateFile' => 'your own template file'
 *     ]
 * ]
 * ~~~
 */
class ConsoleModule extends Module implements BootstrapInterface
{
    /**
     * @var string the namespace that controller classes are in.
     */
    public $controllerNamespace = 'yii2mod\rbac\commands';

    /**
     * @param $app
     */
    public function bootstrap($app)
    {
        if ($app instanceof \yii\console\Application) {
            $this->controllerNamespace = 'yii2mod\rbac\commands';
        }
    }
}