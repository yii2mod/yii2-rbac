<?php

namespace yii2mod\rbac\tests\models;

use Yii;
use yii\base\Exception;
use yii\rbac\Item;
use yii2mod\rbac\models\AssignmentModel;
use yii2mod\rbac\models\AuthItemModel;
use yii2mod\rbac\tests\data\User;
use yii2mod\rbac\tests\TestCase;

/**
 * Class AssignmentTest
 * @package yii2mod\rbac\tests\models
 */
class AssignmentTest extends TestCase
{
    /**
     * @var string
     */
    private $_roleName = 'admin';

    /**
     * @var string
     */
    private $_permissionName = 'viewArticles';

    // Tests :

    public function testAssignRole()
    {
        $this->createRole();

        $user = User::find()->one();
        $model = new AssignmentModel($user);

        $this->assertTrue($model->assign([$this->_roleName]));
        $this->assertArrayHasKey($this->_roleName, Yii::$app->authManager->getAssignments($user->id));

        return $model;
    }

    public function testAssignPermission()
    {
        $this->createPermission();

        $user = User::find()->one();
        $model = new AssignmentModel($user);

        $this->assertTrue($model->assign([$this->_permissionName]));
        $this->assertArrayHasKey($this->_permissionName, Yii::$app->authManager->getAssignments($user->id));

        return $model;
    }

    /**
     * @depends testAssignRole
     * @depends testAssignPermission
     * @param AssignmentModel $role
     * @param AssignmentModel $permission
     */
    public function testGetItems(AssignmentModel $role, AssignmentModel $permission)
    {
        $this->assertArrayHasKey($this->_roleName, $role->getItems()['assigned']);
        $this->assertArrayHasKey($this->_permissionName, $permission->getItems()['assigned']);
    }

    /**
     * @depends testAssignRole
     * @param AssignmentModel $model
     */
    public function testRevokeRole(AssignmentModel $model)
    {
        $this->assertTrue($model->revoke([$this->_roleName]));
    }

    /**
     * @depends testAssignPermission
     * @param AssignmentModel $model
     */
    public function testRevokePermission(AssignmentModel $model)
    {
        $this->assertTrue($model->revoke([$this->_permissionName]));
    }

    /**
     * Create role for testing purposes
     *
     * @return void
     *
     * @throws Exception
     */
    private function createRole()
    {
        $model = new AuthItemModel();
        $model->type = Item::TYPE_ROLE;
        $model->name = $this->_roleName;

        if (!$model->save()) {
            throw new Exception("A Role '{$this->_roleName}' has not been created.");
        }
    }

    /**
     * Create permission for testing purposes
     *
     * @return void
     *
     * @throws Exception
     */
    private function createPermission()
    {
        $model = new AuthItemModel();
        $model->type = Item::TYPE_ROLE;
        $model->name = $this->_permissionName;

        if (!$model->save()) {
            throw new Exception("A Permission '{$this->_permissionName}' has not been created.");
        }
    }
}