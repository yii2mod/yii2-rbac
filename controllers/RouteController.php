<?php

namespace yii2mod\rbac\controllers;

use Exception;
use yii\helpers\Html;
use yii\web\Controller;
use yii\web\Response;
use Yii;
use yii2mod\rbac\components\AccessHelper;
use yii2mod\rbac\models\Route;


/**
 * Class RouteController
 * @package yii2mod\rbac\controllers
 */
class RouteController extends Controller
{

    /**
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
     * @return string
     */
    public function actionCreate()
    {
        $model = new Route;
        if ($model->load(Yii::$app->getRequest()->post())) {
            if ($model->validate()) {
                $routes = explode(',', $model->route);
                $this->saveNew($routes);
                AccessHelper::refreshAuthCache();
                $this->redirect(['index']);
            }
        }
        return $this->render('create', ['model' => $model]);
    }

    /**
     * @param $action
     *
     * @return array
     */
    public function actionAssign($action)
    {
        $post = Yii::$app->getRequest()->post();
        $routes = $post['routes'];
        $manager = Yii::$app->getAuthManager();
        if ($action == 'assign') {
            $this->saveNew($routes);
        } else {
            foreach ($routes as $route) {
                $child = $manager->getPermission($route);
                try {
                    $manager->remove($child);
                } catch (Exception $e) {

                }
            }
        }
        AccessHelper::refreshAuthCache();
        Yii::$app->getResponse()->format = Response::FORMAT_JSON;
        return [
            $this->actionRouteSearch('available', $post['search_av']),
            $this->actionRouteSearch('assigned', $post['search_asgn'])
        ];
    }

    /**
     * @param $target
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
                if (empty($term) or strpos($name, $term) !== false) {
                    $result[$name] = $name;
                }
                if (!in_array($name, $routes)) {
                    $existsOptions[$name] = ['class' => 'lost'];
                }
            }
        }
        $options = $target == 'available' ? [] : ['options' => $existsOptions];
        return Html::renderSelectOptions('', $result, $options);
    }

    /**
     * @param $routes
     */
    private function saveNew($routes)
    {
        $manager = Yii::$app->getAuthManager();
        foreach ($routes as $route) {
            try {
                $manager->add($manager->createPermission('/' . trim($route, ' /')));
            } catch (Exception $e) {

            }
        }
    }
}