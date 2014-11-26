<?php

namespace yii2mod\rbac;
use yii2mod\rbac\assets\RbacAsset;


/**
 * Class Module
 * @package yii2mod\rbac
 */
class Module extends \yii\base\Module
{
    /**
     * @var string
     */
    public $defaultRoute = 'assignment';

    /**
     * @var string
     */
    public $controllerNamespace = 'yii2mod\rbac\controllers';

    /**
     *
     */
    public function init()
    {
        parent::init();
    }
}