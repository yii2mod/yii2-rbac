RBAC Manager for Yii 2
=========

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2mod/yii2-rbac "*"
php composer.phar require "yiisoft/yii2-jui": "~2.0@dev" //can't be installed via composer.json requiremtns because of DependencyResolver issue
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
                ],
            ]
        ],
    ],
  'components' => [
        ....
        'authManager' => [
            'class' => 'yii2mod\rbac\components\DbManager',
            'defaultRoles' => ['guest', 'user'],
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
http://localhost/path/to/index.php?r=admin/rbac/menu
http://localhost/path/to/index.php?r=admin/rbac/role
http://localhost/path/to/index.php?r=admin/rbac/assignment
```

For applying rules add to your controller following code:
```php
use yii2mod\rbac\components\AccessControl;

class ExampleController extends Controller 
{

/**
 * Returns a list of behaviors that this component should behave as.
 */
public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
            ],
            'verbs' => [
                ...
            ],
        ];
    }
  // Your actions
}
```
