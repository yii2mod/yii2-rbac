<?php

namespace yii2mod\rbac\tests;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the base class for all yii framework unit tests.
 */
class TestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->mockApplication();

        $this->setupTestDbData();
    }

    /**
     * @inheritdoc
     */
    protected function tearDown()
    {
        $this->destroyApplication();
    }

    /**
     * Populates Yii::$app with a new application
     * The application will be destroyed on tearDown() automatically.
     *
     * @param array $config The application configuration, if needed
     * @param string $appClass name of the application class to create
     */
    protected function mockApplication($config = [], $appClass = '\yii\web\Application')
    {
        new $appClass(ArrayHelper::merge([
            'id' => 'testapp',
            'basePath' => __DIR__,
            'vendorPath' => $this->getVendorPath(),
            'components' => [
                'db' => [
                    'class' => 'yii\db\Connection',
                    'dsn' => 'sqlite::memory:',
                ],
                'authManager' => [
                    'class' => 'yii\rbac\DbManager',
                    'defaultRoles' => ['guest', 'user'],
                ],
                'user' => [
                    'identityClass' => 'yii2mod\rbac\tests\data\User',
                ],
                'request' => [
                    'hostInfo' => 'http://domain.com',
                    'scriptUrl' => 'index.php',
                ],
                'i18n' => [
                    'translations' => [
                        'yii2mod.rbac' => [
                            'class' => 'yii\i18n\PhpMessageSource',
                            'basePath' => '@yii2mod/rbac/messages',
                        ],
                    ],
                ],
            ],
        ], $config));
    }

    /**
     * @return string vendor path
     */
    protected function getVendorPath()
    {
        return dirname(__DIR__) . '/vendor';
    }

    /**
     * Destroys application in Yii::$app by setting it to null.
     */
    protected function destroyApplication()
    {
        Yii::$app = null;
    }

    /**
     * Setup tables for test ActiveRecord
     */
    protected function setupTestDbData()
    {
        $db = Yii::$app->getDb();

        // Structure :

        $db->createCommand()->createTable('{{%auth_rule}}', [
            'name' => 'string',
            'data' => 'text',
            'created_at' => 'integer',
            'updated_at' => 'integer',
            'PRIMARY KEY (name)',
        ])->execute();

        $db->createCommand()->createTable('{{%auth_item}}', [
            'name' => 'string',
            'type' => 'integer',
            'description' => 'text',
            'rule_name' => 'string',
            'data' => 'string',
            'created_at' => 'integer',
            'updated_at' => 'integer',
            'FOREIGN KEY (rule_name) REFERENCES ' . '{{%auth_rule}}' . ' (name) ON DELETE SET NULL ON UPDATE CASCADE',
        ])->execute();

        $db->createCommand()->createTable('{{%auth_item_child}}', [
            'parent' => 'string',
            'child' => 'string',
            'PRIMARY KEY (parent, child)',
            'FOREIGN KEY (parent) REFERENCES ' . '{{%auth_item}}' . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
            'FOREIGN KEY (child) REFERENCES ' . '{{%auth_item}}' . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
        ])->execute();

        $db->createCommand()->createTable('{{%auth_assignment}}', [
            'item_name' => 'string',
            'user_id' => 'integer',
            'created_at' => 'integer',
            'PRIMARY KEY (item_name, user_id)',
            'FOREIGN KEY (item_name) REFERENCES ' . '{{%auth_item}}' . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
        ])->execute();

        $db->createCommand()->createTable('{{%user}}', [
            'id' => 'pk',
            'username' => 'string not null unique',
            'auth_key' => 'string(32) not null',
            'password_hash' => 'string not null',
            'email' => 'string not null unique',
        ])->execute();

        // Data :

        $db->createCommand()->insert('{{%user}}', [
            'username' => 'demo',
            'auth_key' => Yii::$app->getSecurity()->generateRandomString(),
            'password_hash' => Yii::$app->getSecurity()->generatePasswordHash('password'),
            'email' => 'demo@mail.com',
        ])->execute();
    }
}
