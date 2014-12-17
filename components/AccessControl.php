<?php

namespace yii2mod\rbac\components;

use Yii;

/**
 * Class AccessControl
 * @package yii2mod\rbac\components
 */
class AccessControl extends \yii\filters\AccessControl
{

    /**
     * This method is invoked right before an action is to be executed (after all possible filters.)
     * You may override this method to do last-minute preparation for the action.
     * @param Action $action the action to be executed.
     * @return boolean whether the action should continue to be executed.
     */
    public function beforeAction($action)
    {
        $actionId = $action->getUniqueId();
        $user = Yii::$app->getUser();
        if ($user->can('/' . $actionId)) {
            return true;
        }
        $controller = $action->controller;
        do {
            if ($user->can('/' . ltrim($controller->getUniqueId() . '/*', '/'))) {
                return true;
            }
            $controller = $controller->module;
        } while ($controller !== null);

        return parent::beforeAction($action);
    }


    /**
     * Returns a value indicating whether the filer is active for the given action.
     * @param Action $action the action being filtered
     * @return boolean whether the filer is active for the given action.
     */
    protected function isActive($action)
    {
        $uniqueId = $action->getUniqueId();
        if ($uniqueId === Yii::$app->getErrorHandler()->errorAction) {
            return false;
        } else if (Yii::$app->user->isGuest && Yii::$app->user->loginUrl == $uniqueId) {
            return false;
        }
        return parent::isActive($action);
    }

}