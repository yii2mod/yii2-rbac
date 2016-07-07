<?php

namespace yii2mod\rbac\tests\models;

use Yii;
use yii\rbac\Rule;
use yii2mod\rbac\models\BizRuleModel;
use yii2mod\rbac\tests\TestCase;

/**
 * Class BizRuleTest
 * @package yii2mod\rbac\tests\models
 */
class BizRuleTest extends TestCase
{
    public function testCreateRule()
    {
        $model = new BizRuleModel(null);
        $model->name = 'guest';
        $model->expression = 'return Yii::$app->user->isGuest;';

        $this->assertTrue($model->save());

        $rule = Yii::$app->authManager->getRule($model->name);
        $this->assertInstanceOf(Rule::className(), $rule);
        
        return $rule;
    }

    /**
     * @depends testCreateRule
     * @param $rule
     */
    public function testRemoveRole($rule)
    {
        $this->assertTrue(Yii::$app->authManager->remove($rule));
    }
}