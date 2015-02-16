<?php
namespace yii2mod\rbac\commands;

use Yii;
use yii\console\Controller;
use yii\db\Query;
use yii\db\QueryBuilder;

/**
 * Class RbacCommand
 * @package yii2mod\rbac\commands
 */
class RbacCommand extends Controller
{

    /**
     * @var
     */
    public $basePath;
    /**
     * @var
     */
    public $authItemConfig;
    /**
     * @var
     */
    public $authItemChildConfig;
    /**
     * @var
     */
    public $authRuleConfig;

    /**
     *
     */
    public function init()
    {
        $this->basePath = Yii::getAlias('@app');
        $this->authItemConfig = $this->basePath . '/config/rbac/authItemConfig.php';
        $this->authItemChildConfig = $this->basePath . '/config/rbac/authItemChildConfig.php';
        $this->authRuleConfig = $this->basePath . '/config/rbac/authRule.php';
        if (!is_dir($this->basePath . '/config/rbac')) {
            mkdir($this->basePath . '/config/rbac/');
        }
    }

    /**
     * @return array behavior configurations.
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
     *
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
     * Deploying rbac rules
     */
    public function actionSyncDeploy()
    {

        $queryBuilder = new QueryBuilder(Yii::$app->db);
        Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS=0;")->execute();
        if (file_exists($this->authItemConfig)) {
            Yii::$app->db->createCommand()->delete('AuthItem')->execute();
            $authItem = require($this->authItemConfig);
            $insertAuthItemQuery = $queryBuilder->batchInsert('AuthItem', [
                'name', 'type', 'description', 'rule_name', 'data'
            ], $authItem);
            Yii::$app->db->createCommand($insertAuthItemQuery)->execute();
        }

        if (file_exists($this->authItemChildConfig)) {
            Yii::$app->db->createCommand()->delete('AuthItemChild')->execute();
            $authItemChild = require($this->authItemChildConfig);
            $insertAuthItemChildQuery = $queryBuilder->batchInsert('AuthItemChild', [
                'parent', 'child'
            ], $authItemChild);
            Yii::$app->db->createCommand($insertAuthItemChildQuery)->execute();
        }

        if (file_exists($this->authRuleConfig)) {
            Yii::$app->db->createCommand()->delete('AuthRule')->execute();
            $authRule = require($this->authRuleConfig);
            $insertAuthRuleQuery = $queryBuilder->batchInsert('AuthRule', [
                'name', 'data'
            ], $authRule);
            Yii::$app->db->createCommand($insertAuthRuleQuery)->execute();
        }
        Yii::$app->db->createCommand("SET FOREIGN_KEY_CHECKS=1;")->execute();
        Yii::$app->db->createCommand("DELETE aa FROM `AuthAssignment` aa LEFT JOIN AuthItem ai ON(aa.item_name = ai.name) WHERE ai.name IS NULL;")->execute();
        Yii::$app->cache->flush();
    }

}
