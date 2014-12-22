<?php

namespace yii2mod\rbac\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Class AssignmentSearch
 * @package yii2mod\rbac\models\search
 */
class AssignmentSearch extends Model
{
    /**
     * @var integer id
     */
    public $id;

    /**
     * @var string username
     */
    public $username;

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'username'], 'safe'],
        ];
    }

    /**
     * Returns the attribute labels.
     *
     * Attribute labels are mainly used for display purpose. For example, given an attribute
     * `firstName`, we can declare a label `First Name` which is more user-friendly and can
     * be displayed to end users.
     *
     * @return array attribute labels (name => label)
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'username' => 'Username',
        ];
    }

    /**
     * Search
     * @param array $params
     * @param \yii\db\ActiveRecord $class
     * @param string $usernameField
     *
     * @return \yii\data\ActiveDataProvider
     */
    public function search($params, $class, $usernameField)
    {
        $query = $class::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        if (!($this->load($params) && $this->validate())) {
            return $dataProvider;
        }
        $query->andFilterWhere(['id' => $this->id]);
        $query->andFilterWhere(['like', $usernameField, $this->username]);
        return $dataProvider;
    }
}