<?php

use yii\db\Schema;
use yii\db\Migration;

class m141223_164316_init_rbac extends Migration
{
    public function up()
    {
        $tableOptions = null;

        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%auth_rule}}', [
            'name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'data' => Schema::TYPE_TEXT,
            'created_at' => Schema::TYPE_INTEGER,
            'updated_at' => Schema::TYPE_INTEGER,
            'PRIMARY KEY (name)',
        ], $tableOptions);

        $this->createTable('{{%auth_item}}', [
            'name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'type' => Schema::TYPE_INTEGER . ' NOT NULL',
            'description' => Schema::TYPE_TEXT,
            'rule_name' => Schema::TYPE_STRING . '(64)',
            'data' => Schema::TYPE_TEXT,
            'created_at' => Schema::TYPE_INTEGER,
            'updated_at' => Schema::TYPE_INTEGER,
            'PRIMARY KEY (name)',
            'FOREIGN KEY (rule_name) REFERENCES ' . '{{%auth_rule}}' . ' (name) ON DELETE SET NULL ON UPDATE CASCADE',
        ], $tableOptions);
        $this->createIndex('idx-auth_item-type', '{{%auth_item}}', 'type');

        $this->createTable('{{%auth_item_child}}', [
            'parent' => Schema::TYPE_STRING . '(64) NOT NULL',
            'child' => Schema::TYPE_STRING . '(64) NOT NULL',
            'PRIMARY KEY (parent, child)',
            'FOREIGN KEY (parent) REFERENCES ' . '{{%auth_item}}' . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
            'FOREIGN KEY (child) REFERENCES ' . '{{%auth_item}}' . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
        ], $tableOptions);

        $this->createTable('{{%auth_assignment}}', [
            'item_name' => Schema::TYPE_STRING . '(64) NOT NULL',
            'user_id' => Schema::TYPE_STRING . '(64) NOT NULL',
            'created_at' => Schema::TYPE_INTEGER,
            'PRIMARY KEY (item_name, user_id)',
            'FOREIGN KEY (item_name) REFERENCES ' . '{{%auth_item}}' . ' (name) ON DELETE CASCADE ON UPDATE CASCADE',
        ], $tableOptions);


        $this->execute('SET FOREIGN_KEY_CHECKS=0;');
        //Insert auth assignment
        $this->insert('auth_assignment', [
            'item_name' => 'admin',
            'user_id' => 1,
            'created_at' => 1417165845,
        ]);
        //insert auth item
        $this->batchInsert('auth_item', ['name', 'type', 'description', 'rule_name', 'data', 'created_at', 'updated_at'], [
            ['/admin/*', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/captcha', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/contact', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/error', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/index', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/login', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/logout', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/page', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/password-reset', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/request-password-reset', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['/site/signup', 2, NULL, NULL, NULL, 1417165845, 1417165845],
            ['admin', 1, 'admin role', NULL, NULL, 1417165845, 1417165845],
            ['adminManage', 2, 'user can manage admin settings', NULL, NULL, 1417165845, 1417165845],
            ['contactUs', 2, 'user can send email via contact form', NULL, NULL, 1417165845, 1417165845],
            ['error', 2, 'view error', NULL, NULL, 1417165845, 1417165845],
            ['guest', 1, 'guest role', 'guest', NULL, 1417165845, 1417165845],
            ['login', 2, 'user can login', NULL, NULL, 1417165845, 1417165845],
            ['logout', 2, 'user can logout', NULL, NULL, 1417165845, 1417165845],
            ['signup', 2, 'User can sign up', NULL, NULL, 1417165845, 1417165845],
            ['user', 1, 'default user role', 'user', NULL, 1417165845, 1417165845],
            ['viewCmsPage', 2, 'user can view cms pages', NULL, NULL, 1417165845, 1417165845],
            ['viewHomePage', 2, 'user can view home page', NULL, NULL, 1417165845, 1417165845],
            ['repairPassword', 2, 'user can repair own password', NULL, NULL, 1417165845, 1417165845],
        ]);

        $this->batchInsert('auth_item_child', ['parent', 'child'], [
            ['repairPassword', '/site/password-reset'],
            ['repairPassword', '/site/request-password-reset'],
            ['guest', 'repairPassword'],
            ['adminManage', '/admin/*'],
            ['contactUs', '/site/captcha'],
            ['contactUs', '/site/contact'],
            ['error', '/site/error'],
            ['viewHomePage', '/site/index'],
            ['login', '/site/login'],
            ['logout', '/site/logout'],
            ['viewCmsPage', '/site/page'],
            ['signup', '/site/signup'],
            ['admin', 'adminManage'],
            ['guest', 'contactUs'],
            ['user', 'contactUs'],
            ['guest', 'error'],
            ['user', 'error'],
            ['guest', 'login'],
            ['user', 'logout'],
            ['guest', 'signup'],
            ['guest', 'viewCmsPage'],
            ['user', 'viewCmsPage'],
            ['guest', 'viewHomePage'],
            ['user', 'viewHomePage']
        ]);

        $this->batchInsert('auth_rule', ['name', 'data', 'created_at', 'updated_at'], [
            [
                'guest',
                'O:31:"yii2mod\\rbac\\components\\BizRule":4:{s:10:"expression";s:32:"return Yii::$app->user->isGuest;";s:4:"name";s:5:"guest";s:9:"createdAt";i:1417110668;s:9:"updatedAt";i:1417110668;}',
                1417101427,
                1417101427
            ],
            [
                'user',
                'O:31:"yii2mod\\rbac\\components\\BizRule":4:{s:10:"expression";s:33:"return !Yii::$app->user->isGuest;";s:4:"name";s:4:"user";s:9:"createdAt";i:1417165484;s:9:"updatedAt";i:1417165484;}',
                1417101427,
                1417101427
            ]
        ]);
        $this->execute('SET FOREIGN_KEY_CHECKS=1;');
    }

    public function down()
    {
        $this->dropTable('{{%auth_assignment}}');
        $this->dropTable('{{%auth_item_child}}');
        $this->dropTable('{{%auth_item}}');
        $this->dropTable('{{%auth_rule}}');
    }
}
