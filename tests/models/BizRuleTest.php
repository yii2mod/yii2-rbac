<?php

namespace yii2mod\rbac\tests\models;

use Yii;
use yii\rbac\Rule;
use yii2mod\rbac\models\BizRuleModel;
use yii2mod\rbac\rules\GuestRule;
use yii2mod\rbac\tests\TestCase;

/**
 * Class BizRuleTest
 * @package yii2mod\rbac\tests\models
 */
class BizRuleTest extends TestCase
{
    public function testCreateRule()
    {
        $model = new BizRuleModel;
        $model->name = 'guest';
        $model->className = GuestRule::className();

        $this->assertTrue($model->save());

        $rule = Yii::$app->authManager->getRule($model->name);
        $this->assertInstanceOf(Rule::className(), $rule);

        return $rule;
    }

    /**
     * @depends testCreateRule
     * @param $rule
     */
    public function testRemoveRule($rule)
    {
        $this->assertTrue(Yii::$app->authManager->remove($rule));
    }

    public function testTryToCreateRuleWithInvalidClassName()
    {
        $model = new BizRuleModel;
        $model->name = 'guest';
        $model->className = 'invalid className';

        $this->assertFalse($model->save());
        $this->assertArrayHasKey('className', $model->getErrors());
    }
}