<?php
namespace yii2mod\rbac\models;

use yii\base\Model;

/**
 * Class Route
 * @package yii2mod\rbac\models
 */
class Route extends Model
{
    /**
     * @var
     */
    public $route;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['route'], 'safe'],
        ];
    }
}