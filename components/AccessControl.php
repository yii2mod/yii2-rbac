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
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $actionId = $action->getUniqueId();
        $user = Yii::$app->getUser();
        if ($user->can('/' . $actionId)) {
            return true;
        }
        $obj = $action->controller;
        do {
            if ($user->can('/' . ltrim($obj->getUniqueId() . '/*', '/'))) {
                return true;
            }
            $obj = $obj->module;
        } while ($obj !== null);
        return parent::beforeAction($action);
    }

    /**
     * @inheritdoc
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