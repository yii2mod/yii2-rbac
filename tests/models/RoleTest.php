<?php

namespace yii2mod\rbac\tests\models;

use Yii;
use yii\rbac\Item;
use yii\rbac\Role;
use yii2mod\rbac\models\AuthItemModel;
use yii2mod\rbac\tests\TestCase;

/**
 * Class RoleTest
 * @package yii2mod\rbac\tests\models
 */
class RoleTest extends TestCase
{
    public function testCreateRole()
    {
        $model = new AuthItemModel(null);
        $model->type = Item::TYPE_ROLE;
        $model->name = 'admin';
        $model->description = 'admin role';

        $this->assertTrue($model->save());
        $this->assertInstanceOf(Role::className(), Yii::$app->authManager->getRole('admin'));
        
        return Yii::$app->authManager->getRole('admin');
    }

    /**
     * @depends testCreateRole
     * @param $role
     */
    public function testRemoveRole($role)
    {
        $this->assertTrue(Yii::$app->authManager->remove($role));
    }
}