<?php

namespace yii2mod\rbac\models\searchs;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * AssignmentSearch represents the model behind the search form about Assignment.
 */
class Assignment extends Model
{
    /**
     * @var
     */
    public $id;

    /**
     * @var
     */
    public $username;

    /**
     * Rules
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'username'], 'safe'],
        ];
    }

    /**
     * Attribute labels
     * @inheritdoc
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