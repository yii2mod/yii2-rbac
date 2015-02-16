<?php

namespace yii2mod\rbac\controllers;

use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\Response;
use Yii;
use yii2mod\rbac\components\AccessHelper;
use yii2mod\rbac\models\RouteModel;


/**
 * Class RouteController
 * @package yii2mod\rbac\controllers
 */
class RouteController extends Controller
{

    /**
     * Index action
     * List of available & assigned routes
     * @return string
     */
    public function actionIndex()
    {
        $manager = Yii::$app->getAuthManager();

        $exists = $existsOptions = $routes = [];
        foreach (AccessHelper::getRoutes() as $route) {
            $routes[$route] = $route;
        }
        foreach ($manager->getPermissions() as $name => $permission) {
            if ($name[0] !== '/') {
                continue;
            }
            $exists[$name] = $name;
            if (isset($routes[$name])) {
                unset($routes[$name]);
            } else {
                $existsOptions[$name] = ['class' => 'lost'];
            }
        }

        return $this->render('index', [
            'new' => $routes,
            'exists' => $exists,
            'existsOptions' => $existsOptions
        ]);
    }

    /**
     * Create route action
     * @return string
     */
    public function actionCreate()
    {
        $model = new RouteModel;
        if ($model->load(Yii::$app->getRequest()->post())) {
            if ($model->validate()) {
                $routes = explode(',', $model->route);
                $model->save($routes);
                Yii::$app->session->setFlash('success', 'Route has been saved.');
                $this->redirect(['index']);
            }
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * Assign route action
     *
     * @param $action
     * @return string[]
     */
    public function actionAssign($action)
    {
        Yii::$app->getResponse()->format = Response::FORMAT_JSON;
        $post = Yii::$app->getRequest()->post();
        $routes = ArrayHelper::getValue($post, 'routes', []);
        $manager = Yii::$app->getAuthManager();
        $model = new RouteModel;
        if ($action == 'assign') {
            $model->save($routes);
        } else {
            foreach ($routes as $route) {
                $child = $manager->getPermission($route);
                $manager->remove($child);
            }
        }
        return [
            $this->actionRouteSearch('available', $post['search_av']),
            $this->actionRouteSearch('assigned', $post['search_asgn'])
        ];
    }

    /**
     * Route search action
     *
     * @param string $target
     * @param string $term
     * @param bool|string $refresh
     *
     * @return string
     */
    public function actionRouteSearch($target, $term = '', $refresh = true)
    {
        if ($refresh) {
            AccessHelper::refreshFileCache();
        }
        $result = [];
        $manager = Yii::$app->getAuthManager();

        $existsOptions = [];
        $exists = array_keys($manager->getPermissions());
        $routes = AccessHelper::getRoutes();
        if ($target == 'available') {
            foreach ($routes as $route) {
                if (in_array($route, $exists)) {
                    continue;
                }
                if (empty($term) or strpos($route, $term) !== false) {
                    $result[$route] = $route;
                }
            }
        } else {
            foreach ($exists as $name) {
                if ($name[0] !== '/') {
                    continue;
                }
                if (empty($term) or strpos($name, $term) !== false) {
                    $result[$name] = $name;
                }
                // extract route part from $name
                $r = explode('&', $name);
                if (empty($r[0]) || !in_array($r[0], $routes)) {
                    $existsOptions[$name] = ['class' => 'lost'];
                }
            }
        }
        $options = $target == 'available' ? [] : ['options' => $existsOptions];
        return Html::renderSelectOptions('', $result, $options);
    }

}