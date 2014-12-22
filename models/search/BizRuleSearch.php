<?php

namespace yii2mod\rbac\models\search;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii2mod\rbac\models\BizRuleModel;

/**
 * Class BizRuleSearch
 * @package yii2mod\rbac\models\search
 */
class BizRuleSearch extends Model
{
    /**
     * @var string name of the rule
     */
    public $name;

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * @return array
     */
    public function rules()
    {
        return [
            [['name'], 'safe']
        ];
    }

    /**
     * Search
     *
     * @param array $params
     * @return \yii\data\ActiveDataProvider|\yii\data\ArrayDataProvider
     */
    public function search($params)
    {
        /* @var \yii\rbac\Manager $authManager */
        $authManager = Yii::$app->authManager;
        $models = [];
        $included = !($this->load($params) && $this->validate() && trim($this->name) !== '');
        foreach ($authManager->getRules() as $name => $item) {
            if ($included || stripos($item->name, $this->name) !== false) {
                $models[$name] = new BizRuleModel($item);
            }
        }
        return new ArrayDataProvider([
            'allModels' => $models,
        ]);
    }
}