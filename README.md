<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px">
    </a>
    <h1 align="center">Yii2 RBAC Extension</h1>
    <br>
</p>

Yii2-RBAC provides a web interface for advanced access control and includes following features:

- Allows CRUD operations for roles, permissions, rules
- Allows to assign multiple roles or permissions to the user
- Allows to create console migrations
- Integrated with [yii2mod/base](https://github.com/yii2mod/base)

[![Latest Stable Version](https://poser.pugx.org/yii2mod/yii2-rbac/v/stable)](https://packagist.org/packages/yii2mod/yii2-rbac) [![Total Downloads](https://poser.pugx.org/yii2mod/yii2-rbac/downloads)](https://packagist.org/packages/yii2mod/yii2-rbac) [![License](https://poser.pugx.org/yii2mod/yii2-rbac/license)](https://packagist.org/packages/yii2mod/yii2-rbac)
[![Build Status](https://travis-ci.org/yii2mod/yii2-rbac.svg?branch=master)](https://travis-ci.org/yii2mod/yii2-rbac)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/yii2mod/yii2-rbac/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/yii2mod/yii2-rbac/?branch=master)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2mod/yii2-rbac "*"
```

or add

```
"yii2mod/yii2-rbac": "*"
```

to the require section of your composer.json.

Usage
------------
Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    'modules' => [
        'rbac' => [
            'class' => 'yii2mod\rbac\Module',
        ],
    ],
    'components' => [
        'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest', 'user'],
        ],
    ],
];
```
After you downloaded and configured Yii2-rbac, the last thing you need to do is updating your database schema by 
applying the migration:
 
```bash
$ php yii migrate/up --migrationPath=@yii/rbac/migrations
```

You can then access Auth manager through the following URL:

```
http://localhost/path/to/index.php?r=rbac/
http://localhost/path/to/index.php?r=rbac/route
http://localhost/path/to/index.php?r=rbac/permission
http://localhost/path/to/index.php?r=rbac/role
http://localhost/path/to/index.php?r=rbac/assignment
```

or if you have enabled pretty URLs, you may use the following URL:

```
http://localhost/path/to/index.php/rbac
http://localhost/path/to/index.php/rbac/route
http://localhost/path/to/index.php/rbac/permission
http://localhost/path/to/index.php/rbac/role
http://localhost/path/to/index.php/rbac/assignment
```

**Applying rules:**

1) For applying rules only for `controller` add the following code:
```php
use yii2mod\rbac\filters\AccessControl;

class ExampleController extends Controller 
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'allowActions' => [
                    'index',
                    // The actions listed here will be allowed to everyone including guests.
                ]
            ],
        ];
    }
}
```
2) For applying rules for `module` add the following code:
```php

use Yii;
use yii2mod\rbac\filters\AccessControl;

/**
 * Class Module
 */
class Module extends \yii\base\Module
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            AccessControl::class
        ];
    }
}
```
3) Also you can apply rules via main configuration:
```php
// apply for single module

'modules' => [
    'rbac' => [
        'class' => 'yii2mod\rbac\Module',
        'as access' => [
            'class' => yii2mod\rbac\filters\AccessControl::class
        ],
    ]
]

// or apply globally for whole application

'modules' => [
    ...
],
'components' => [
    ...
],
'as access' => [
    'class' => yii2mod\rbac\filters\AccessControl::class,
    'allowActions' => [
        'site/*',
        'admin/*',
        // The actions listed here will be allowed to everyone including guests.
        // So, 'admin/*' should not appear here in the production, of course.
        // But in the earlier stages of your development, you may probably want to
        // add a lot of actions here until you finally completed setting up rbac,
        // otherwise you may not even take a first step.
    ]
 ],

```

## Internationalization

All text and messages introduced in this extension are translatable under category 'yii2mod.rbac'.
You may use translations provided within this extension, using following application configuration:

```php
return [
    'components' => [
        'i18n' => [
            'translations' => [
                'yii2mod.rbac' => [
                    'class' => 'yii\i18n\PhpMessageSource',
                    'basePath' => '@yii2mod/rbac/messages',
                ],
                // ...
            ],
        ],
        // ...
    ],
    // ...
];
```

## Migrations

You can create the console migrations for creating/updating RBAC items.

### Module setup

To be able create the migrations, you need to add the following code to your console application configuration:

```php
// console.php
'modules' => [
    'rbac' => [
        'class' => 'yii2mod\rbac\ConsoleModule'
    ]
]
```

### Methods

1. `createPermission()`: creating a permission
2. `updatePermission()`: updating a permission
3. `removePermission()`: removing a permission
4. `createRole()`: creating a role
5. `updateRole()`: updating a role
6. `removeRole()`: removing a role
7. `createRule()`: creating a rule
8. `updateRule()`: updating a rule
9. `removeRule()`: removing a rule
10. `addChild()`: creating a child
12. `removeChild()`: removing a child
13. `assign()`: assign a role to a user


### Creating Migrations

To create a new migration, run the following command:
```bash
$ php yii rbac/migrate/create <name>
```

The required `name` argument gives a brief description about the new migration. For example, if the migration is about creating a new role named admin, you may use the name `create_role_admin` and run the following command:

```bash
$ php yii rbac/migrate/create create_role_admin
```

The above command will create a new PHP class file named m160817_085702_create_role_admin.php in the @app/rbac/migrations directory. The file contains the following code which mainly declares a migration class m160817_085702_create_role_admin with the skeleton code:

```php
<?php

use yii2mod\rbac\migrations\Migration;

class m160817_085702_create_role_admin extends Migration
{
    public function safeUp()
    {

    }

    public function safeDown()
    {
        echo "m160817_085702_create_role_admin cannot be reverted.\n";

        return false;
    }
}
```

The following code shows how you may implement the migration class to create a `admin` role:

```php
<?php

use yii2mod\rbac\migrations\Migration;

class m160817_085702_create_role_admin extends Migration
{
    public function safeUp()
    {
        $this->createRole('admin', 'admin has all available permissions.');
    }

    public function safeDown()
    {
        $this->removeRole('admin');
    }
}
```
> You can see a complex example of migration [here.](https://github.com/yii2mod/base/blob/master/rbac/migrations/m160722_085418_init.php)

### Applying Migrations

To upgrade a database to its latest structure, you should apply all available new migrations using the following command:

```bash
$ php yii rbac/migrate
```

### Reverting Migrations

To revert (undo) one or multiple migrations that have been applied before, you can run the following command:

```bash
$ php yii rbac/migrate/down     # revert the most recently applied migration
$ php yii rbac/migrate/down 3   # revert the most 3 recently applied migrations
```

### Redoing Migrations

Redoing migrations means first reverting the specified migrations and then applying again. This can be done as follows:
```bash
$ php yii rbac/migrate/redo     # redo the last applied migration
$ php yii rbac/migrate/redo 3   # redo the last 3 applied migrations
```
