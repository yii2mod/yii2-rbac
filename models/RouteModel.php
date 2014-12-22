<?php

namespace yii2mod\rbac\models;

use yii\base\Model;

/**
 * Class RouteModel
 * @package yii2mod\rbac\models
 */
class RouteModel extends Model
{
    /**
     * @var string route
     */
    public $route;

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['route'], 'safe'],
        ];
    }

    /**
     * Save new routes
     *
     * @param $routes
     * @return bool
     */
    public function save($routes)
    {
        $manager = \Yii::$app->getAuthManager();
        foreach ($routes as $route) {
            $manager->add($manager->createPermission('/' . trim($route, ' /')));
        }
        return true;
    }
}