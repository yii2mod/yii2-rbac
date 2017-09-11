<?php

namespace yii2mod\rbac\tests\models;

use yii2mod\rbac\models\RouteModel;
use yii2mod\rbac\tests\TestCase;

/**
 * Class RouteTest
 *
 * @package yii2mod\rbac\tests\models
 */
class RouteTest extends TestCase
{
    public function testGetAppRoutes()
    {
        $model = new RouteModel();
        $routes = $model->getAppRoutes();

        $this->assertCount(34, $routes);
        $this->assertContains('/rbac/assignment/index', $routes);
        $this->assertContains('/rbac/permission/index', $routes);
        $this->assertContains('/rbac/role/index', $routes);
        $this->assertContains('/rbac/route/index', $routes);
        $this->assertContains('/rbac/rule/index', $routes);
        $this->assertContains('/rbac/*', $routes);
        $this->assertContains('/*', $routes);
    }

    public function testGetAvailableAndAssignedRoutes()
    {
        $model = new RouteModel();
        $routes = $model->getAvailableAndAssignedRoutes();

        $this->assertArrayHasKey('available', $routes);
        $this->assertArrayHasKey('assigned', $routes);
        $this->assertCount(34, $routes['available']);
        $this->assertCount(0, $routes['assigned']);
    }
}
