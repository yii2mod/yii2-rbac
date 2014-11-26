<?php

namespace yii2mod\rbac\models\searchs;

use yii\base\Model;
use yii\data\ArrayDataProvider;
use yii\rbac\Item;
use Yii;

/**
 * AuthItemSearch represents the model behind the search form about AuthItem.
 */
class AuthItem extends Model
{

    /**
     * Type route
     */
    const TYPE_ROUTE = 101;

    /**
     * @var
     */
    public $name;
    /**
     * @var
     */
    public $type;
    /**
     * @var
     */
    public $description;
    /**
     * @var
     */
    public $rule;
    /**
     * @var
     */
    public $data;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['name', 'description'], 'safe'],
            [['type'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule' => 'Rule',
            'data' => 'Data',
        ];
    }

    /**
     *
     * @param array $params
     *
     * @return \yii\data\ActiveDataProvider|\yii\data\ArrayDataProvider
     */
    public function search($params)
    {
        /* @var \yii\rbac\Manager $authManager */
        $authManager = Yii::$app->authManager;
        if ($this->type == Item::TYPE_ROLE) {
            $items = $authManager->getRoles();
        } else {
            $items = [];
            if ($this->type == Item::TYPE_PERMISSION) {
                foreach ($authManager->getPermissions() as $name => $item) {
                    if ($name[0] !== '/') {
                        $items[$name] = $item;
                    }
                }
            } else {
                foreach ($authManager->getPermissions() as $name => $item) {
                    if ($name[0] === '/') {
                        $items[$name] = $item;
                    }
                }
            }
        }
        if ($this->load($params) && $this->validate() && (trim($this->name) !== '' || trim($this->description) !== '')) {
            $search = strtolower(trim($this->name));
            $desc = strtolower(trim($this->description));
            $items = array_filter($items, function ($item) use ($search, $desc) {
                return (empty($search) || strpos(strtolower($item->name), $search) !== false) && (empty($desc) || strpos(strtolower($item->description), $desc) !== false);
            });
        }
        return new ArrayDataProvider([
            'allModels' => $items,
        ]);
    }

}
