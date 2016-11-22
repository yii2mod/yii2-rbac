<?php

namespace yii2mod\rbac\controllers;

use Yii;
use yii\filters\VerbFilter;
use yii\web\Controller;
use yii\web\Response;
use yii2mod\rbac\models\RouteModel;

/**
 * Class RouteController
 *
 * @package yii2mod\rbac\controllers
 */
class RouteController extends Controller
{
    /**
     * Returns a list of behaviors that this component should behave as.
     *
     * @return array
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'index' => ['get', 'post'],
                    'create' => ['post'],
                    'assign' => ['post'],
                    'remove' => ['post'],
                    'refresh' => ['post'],
                ],
            ],
            'contentNegotiator' => [
                'class' => 'yii\filters\ContentNegotiator',
                'only' => ['assign', 'remove', 'refresh'],
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                ],
            ],
        ];
    }

    /**
     * Lists all Route models.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $model = new RouteModel();

        return $this->render('index', ['routes' => $model->getRoutes()]);
    }

    /**
     * Assign routes
     *
     * @return array
     */
    public function actionAssign()
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        $model = new RouteModel();
        $model->addNew($routes);

        return $model->getRoutes();
    }

    /**
     * Remove routes
     *
     * @return array
     */
    public function actionRemove()
    {
        $routes = Yii::$app->getRequest()->post('routes', []);
        $model = new RouteModel();
        $model->remove($routes);

        return $model->getRoutes();
    }

    /**
     * Refresh cache of routes
     */
    public function actionRefresh()
    {
        $model = new RouteModel();
        $model->invalidate();

        return $model->getRoutes();
    }
}
