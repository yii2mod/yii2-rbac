<?php

namespace yii2mod\rbac;


/**
 * Class Module
 * @package yii2mod\rbac
 */
class Module extends \yii\base\Module
{
    /**
     * @var string the default route of this module. Defaults to 'default'.
     * The route may consist of child module ID, controller ID, and/or action ID.
     * For example, `help`, `post/create`, `admin/post/create`.
     * If action ID is not given, it will take the default value as specified in
     * [[Controller::defaultAction]].
     */
    public $defaultRoute = 'assignment';

    /**
     * @var string the namespace that controller classes are in.
     * This namespace will be used to load controller classes by prepending it to the controller
     * class name.
     *
     * If not set, it will use the `controllers` sub-namespace under the namespace of this module.
     * For example, if the namespace of this module is "foo\bar", then the default
     * controller namespace would be "foo\bar\controllers".
     *
     * See also the [guide section on autoloading](guide:concept-autoloading) to learn more about
     * defining namespaces and how classes are loaded.
     */
    public $controllerNamespace = 'yii2mod\rbac\controllers';

    /**
     * Initializes the module.
     *
     * This method is called after the module is created and initialized with property values
     */
    public function init()
    {
        parent::init();
    }
}