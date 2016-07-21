Yii2 RBAC
=========

Yii2-rbac provides a web interface for advanced access control and includes following features:

- Allows CRUD operations for roles, permissions, rules
- Allows to assign multiple roles or permissions to the user
- Integrated with [yii2mod/base](https://github.com/yii2mod/base)

[![Latest Stable Version](https://poser.pugx.org/yii2mod/yii2-rbac/v/stable)](https://packagist.org/packages/yii2mod/yii2-rbac) [![Total Downloads](https://poser.pugx.org/yii2mod/yii2-rbac/downloads)](https://packagist.org/packages/yii2mod/yii2-rbac) [![License](https://poser.pugx.org/yii2mod/yii2-rbac/license)](https://packagist.org/packages/yii2mod/yii2-rbac)
[![Build Status](https://travis-ci.org/yii2mod/yii2-rbac.svg?branch=master)](https://travis-ci.org/yii2mod/yii2-rbac)

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2mod/yii2-rbac "*"
```

or add

```json
"yii2mod/yii2-rbac": "*"
```

to the require section of your composer.json.

Usage
------------
Once the extension is installed, simply modify your application configuration as follows:

```php
return [
    //....
    'modules' => [
        .....
        'admin' => [
            'class' => 'app\modules\admin\Module',
            'modules' => [
                'rbac' => [
                    'class' => 'yii2mod\rbac\Module',
                    // Some controller property maybe need to change. 
                    'controllerMap' => [
                        'assignment' => [
                            'class' => 'yii2mod\rbac\controllers\AssignmentController',
                            'userIdentityClass' => 'app\models\User',
                            'searchClass' => 'Your own search model class',
                            'idField' => 'id',
                            'usernameField' => 'username',
                            'gridViewColumns' => [
                                 'id',
                                 'username',
                                 'email'
                             ]
                        ]
                    ]
                ],
            ]
        ],
    ],
  'components' => [
        ....
         'authManager' => [
            'class' => 'yii\rbac\DbManager',
            'defaultRoles' => ['guest', 'user'],
            'cache' => 'yii\caching\FileCache',
            'itemTable' => 'AuthItem',
            'itemChildTable' => 'AuthItemChild',
            'assignmentTable' => 'AuthAssignment',
            'ruleTable' => 'AuthRule',
        ],
    ]
];
```
If you use this extension separate from the [base template](https://github.com/yii2mod/base), then you need execute rbac init migration by the following command: 
```
php yii migrate/up --migrationPath=@yii2mod/rbac/migrations
```

You can then access Auth manager through the following URL:
```
http://localhost/path/to/index.php?r=admin/rbac/
http://localhost/path/to/index.php?r=admin/rbac/route
http://localhost/path/to/index.php?r=admin/rbac/permission
http://localhost/path/to/index.php?r=admin/rbac/role
http://localhost/path/to/index.php?r=admin/rbac/assignment
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
'modules' => [
    'rbac' => [
        'class' => 'yii2mod\rbac\Module',
        'as access' => [
            'class' => yii2mod\rbac\filters\AccessControl::class
        ],
    ]
]
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
