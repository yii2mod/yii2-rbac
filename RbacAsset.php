<?php

namespace yii2mod\rbac;

use yii\web\AssetBundle;

/**
 * Class RbacAsset
 *
 * @package yii2mod\rbac
 */
class RbacAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@vendor/yii2mod/yii2-rbac/assets';

    /**
     * @var array
     */
    public $js = [
        'js/rbac.js',
    ];

    public $css = [
        'css/rbac.css',
    ];

    /**
     * @var array
     */
    public $depends = [
        'yii\web\YiiAsset',
    ];

    /**
     * @var bool whether RbacAsset will be register only `rbac-route.js` without `js/rbac.js`
     */
    public $registerOnlyRouteScript = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->registerOnlyRouteScript) {
            $this->js = [
                'js/rbac-route.js',
            ];
        }
        parent::init();
    }
}
