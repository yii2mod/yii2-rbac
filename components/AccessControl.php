<?php

namespace yii2mod\rbac\components;

use Yii;
use yii\base\ActionFilter;
use yii\web\ForbiddenHttpException;

/**
 * Class AccessControl
 * @package yii2mod\rbac\components
 */
class AccessControl extends ActionFilter
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
        $this->denyAccess($user);
        return parent::beforeAction($action);
    }

    /**
     * Denies the access of the user.
     * The default implementation will redirect the user to the login page if he is a guest;
     * if the user is already logged, a 403 HTTP exception will be thrown.
     *
     * @param \yii\web\User $user the current user
     *
     * @throws \yii\web\ForbiddenHttpException if the user is already logged in.
     */
    protected function denyAccess($user)
    {
        if ($user->getIsGuest()) {
            $user->loginRequired();
        } else {
            throw new ForbiddenHttpException(Yii::t('yii', 'You are not allowed to perform this action.'));
        }
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