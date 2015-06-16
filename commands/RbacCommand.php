<?php

namespace yii2mod\rbac\commands;

use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\db\QueryBuilder;
use yii\helpers\FileHelper;

/**
 * Class RbacCommand
 * @package yii2mod\rbac\commands
 */
class RbacCommand extends Controller
{

    /**
     * @var string path for place auth configs
     */
    public $configPath = '@app/config/rbac';

    /**
     * @var string auth item config name
     */
    public $authItemConfig = 'authItemConfig.php';

    /**
     * @var string auth item child config name
     */
    public $authItemChildConfig = 'authItemChildConfig.php';

    /**
     * @var string auth item rule config name
     */
    public $authRuleConfig = 'authRule.php';

    /**
     * Initializes the object.
     */
    public function init()
    {
        $basePath = Yii::getAlias($this->configPath);
        FileHelper::createDirectory($basePath);
        $this->authItemConfig = $basePath . DIRECTORY_SEPARATOR . $this->authItemConfig;
        $this->authItemChildConfig = $basePath . DIRECTORY_SEPARATOR . $this->authItemChildConfig;
        $this->authRuleConfig = $basePath . DIRECTORY_SEPARATOR . $this->authRuleConfig;
    }

    /**
     * Returns a list of behaviors that this component should behave as.
     */
    public function behaviors()
    {
        return array(
            'cronLogger' => array(
                'class' => 'yii2mod\cron\behaviors\CronLoggerBehavior',
                'actions' => array(
                    'sync'
                ),
            ),
        );
    }

    /**
     * Render array for auth configs
     * @param $array
     * @return string
     */
    public function renderArray($array)
    {
        $out = "<?php return [\n";
        foreach ($array as $item) {
            $out .= "    [\n";
            foreach (array_values($item) as $value) {
                if (is_string($value)) {
                    $value = '\'' . str_replace('\'', '\\\'', str_replace('\\', '\\\\', $value)) . '\'';
                } elseif (is_int($value) || is_float($value)) {
                    $value = (string)$value;
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif (is_null($value)) {
                    $value = 'NULL';
                } elseif (is_resource($value)) {
                    $value = 'resource';
                }
                $out .= "        {$value},\n";
            }

            $out .= "    ],\n";
        }
        $out .= '];';
        return $out;
    }

    /**
     * This command will create a new config files with data from auth tables
     */
    public function actionSyncSave()
    {
        $authItem = (new Query())->select('name, type, description, rule_name, data')->from('AuthItem')->all();
        $authItemChild = (new Query())->select('parent, child')->from('AuthItemChild')->all();
        $authRule = (new Query())->select('name,data')->from('AuthRule')->all();
        file_put_contents($this->authItemConfig, $this->renderArray($authItem));
        file_put_contents($this->authItemChildConfig, $this->renderArray($authItemChild, true));
        file_put_contents($this->authRuleConfig, $this->renderArray($authRule, true));
    }

    /**
     * This command will update auth tables with data from auth configs
     */
    public function actionSyncDeploy()
    {
        $queryBuilder = new QueryBuilder(Yii::$app->db);

        if ('pgsql' == Yii::$app->db->driverName) {
            Yii::$app->db->createCommand("ALTER TABLE \"AuthItem\" DISABLE TRIGGER ALL")->execute();
        } else {
            Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS=0;")->execute();
        }

        if (file_exists($this->authItemConfig)) {
            Yii::$app->db->createCommand()->delete('AuthItem')->execute();
            $authItem = require($this->authItemConfig);
            if (!empty($authItem)) {
                $insertAuthItemQuery = $queryBuilder->batchInsert('AuthItem', [
                    'name', 'type', 'description', 'rule_name', 'data'
                ], $authItem);
                Yii::$app->db->createCommand($insertAuthItemQuery)->execute();
            }
        }

        if (file_exists($this->authItemChildConfig)) {
            Yii::$app->db->createCommand()->delete('AuthItemChild')->execute();
            $authItemChild = require($this->authItemChildConfig);
            if (!empty($authItemChild)) {
                $insertAuthItemChildQuery = $queryBuilder->batchInsert('AuthItemChild', [
                    'parent', 'child'
                ], $authItemChild);
                Yii::$app->db->createCommand($insertAuthItemChildQuery)->execute();
            }
        }

        if (file_exists($this->authRuleConfig)) {
            Yii::$app->db->createCommand()->delete('AuthRule')->execute();
            $authRule = require($this->authRuleConfig);
            if (!empty($authRule)) {
                $insertAuthRuleQuery = $queryBuilder->batchInsert('AuthRule', [
                    'name', 'data'
                ], $authRule);
                Yii::$app->db->createCommand($insertAuthRuleQuery)->execute();
            }
        }

        if ('pgsql' == Yii::$app->db->driverName) {
            Yii::$app->db->createCommand("ALTER TABLE \"AuthItem\" ENABLE TRIGGER ALL")->execute();
        } else {
            Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS=1;")->execute();
        }

        $this->processAssignments();

        Yii::$app->cache->flush();
    }

    /**
     * Refresh assignment table(delete invalid data)
     * @param string $assignmentTable
     * @param string $userTable
     */
    public function actionRefreshAssignments($assignmentTable = 'AuthAssignment', $userTable = 'User')
    {
        $time = microtime(true);
        Yii::$app->db->createCommand("DELETE FROM {$assignmentTable} WHERE user_id NOT IN (SELECT id FROM {$userTable})")->execute();
        echo "Command finished (time: " . sprintf('%.3f', microtime(true) - $time) . "s)\n";
    }

    /**
     * Processes assignments on sync deploy
     */
    private function processAssignments() {
        if ('pgsql' == Yii::$app->db->getDriverName()) {
            if (!Yii::$app->db->createCommand("SELECT COUNT(*) FROM \"AuthAssignment\" WHERE \"item_name\"='admin' AND \"user_id\"=1")->queryScalar()) {
                Yii::$app->db->createCommand("INSERT INTO \"AuthAssignment\" (\"item_name\", \"user_id\") VALUES ('admin', '1');")->execute();
            }
            if (!Yii::$app->db->createCommand("SELECT COUNT(*) FROM \"AuthAssignment\" WHERE \"item_name\"='root' AND \"user_id\"=1")->queryScalar()) {
                Yii::$app->db->createCommand("INSERT INTO \"AuthAssignment\" (\"item_name\", \"user_id\") VALUES ('root', '1');")->execute();
            }
            Yii::$app->db->createCommand('DELETE FROM "AuthAssignment" WHERE item_name IN (SELECT item_name FROM "AuthAssignment" aa LEFT JOIN "AuthItem" ai ON aa.item_name = ai.name WHERE ai.name IS NULL)')->execute();
        } else {
            Yii::$app->db->createCommand("INSERT IGNORE INTO `AuthAssignment` (`item_name`, `user_id`) VALUES ('admin', '1');")->execute();
            Yii::$app->db->createCommand("INSERT IGNORE INTO `AuthAssignment` (`item_name`, `user_id`) VALUES ('root', '1');")->execute();
            Yii::$app->db->createCommand("DELETE aa FROM `AuthAssignment` aa LEFT JOIN AuthItem ai ON(aa.item_name = ai.name) WHERE ai.name IS NULL;")->execute();
        }
    }
}
