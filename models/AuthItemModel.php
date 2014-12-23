<?php

namespace yii2mod\rbac\models;

use yii\base\Model;
use yii\helpers\VarDumper;
use yii\rbac\Item;
use Yii;

/**
 * Class AuthItemModel
 * This is the model class for table "AuthItem".
 *
 * @property string  $name
 * @property integer $type
 * @property string  $description
 * @property string  $ruleName
 * @property string  $data
 *
 * @property Item    $item
 */
class AuthItemModel extends Model
{
    /**
     * @var string
     */
    public $name;
    /**
     * @var int
     */
    public $type;
    /**
     * @var string
     */
    public $description;
    /**
     * @var string
     */
    public $ruleName;
    /**
     * @var null|string
     */
    public $data;

    /**
     *
     * @var Item
     */
    private $_item;

    /**
     *
     * @param Item  $item
     * @param array $config
     */
    public function __construct($item, $config = [])
    {
        $this->_item = $item;
        if ($item !== null) {
            $this->name = $item->name;
            $this->type = $item->type;
            $this->description = $item->description;
            $this->ruleName = $item->ruleName;
            $this->data = $item->data === null ? null : VarDumper::export($item->data);
        }
        parent::__construct($config);
    }

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     * @return array validation rules
     * @see scenarios()
     */
    public function rules()
    {
        return [
            [['ruleName'], 'in', 'range' => array_keys(Yii::$app->authManager->getRules()), 'message' => 'Rule not exists'],
            [['name', 'type'], 'required'],
            ['name', 'existAuthItem'],
            [['type'], 'integer'],
            [['description', 'data', 'ruleName'], 'default'],
            [['name'], 'string', 'max' => 64]
        ];
    }

    /**
     * Validate auth name
     * Check if auth name already exist
     */
    public function existAuthItem()
    {
        $authItem = Yii::$app->authManager->getItem($this->name);
        if (!empty($authItem) && $this->getIsNewRecord()) {
            $this->addError('name', "This name has already been taken.");
        }
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
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'ruleName' => 'Rule Name',
            'data' => 'Data',
        ];
    }

    /**
     * @return bool
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }

    /**
     * @static
     *
     * @param $id
     *
     * @return AuthItemModel|null
     */
    public static function find($id)
    {
        $item = Yii::$app->authManager->getRole($id);
        if ($item !== null) {
            return new self($item);
        }
        return null;
    }

    /**
     * @return bool
     */
    public function save()
    {
        if ($this->validate()) {
            $manager = Yii::$app->authManager;
            if ($this->_item === null) {
                if ($this->type == Item::TYPE_ROLE) {
                    $this->_item = $manager->createRole($this->name);
                } else {
                    $this->_item = $manager->createPermission($this->name);
                }
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;
            $this->_item->description = $this->description;
            $this->_item->ruleName = $this->ruleName;
            $this->_item->data = $this->data === null || $this->data === '' ? null : @eval('return ' . $this->data . ';');
            if ($isNew) {
                $manager->add($this->_item);
            } else {
                $manager->update($oldName, $this->_item);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * @static
     *
     * @param null $type
     *
     * @return array
     */
    public static function getTypeName($type = null)
    {
        $result = [
            Item::TYPE_PERMISSION => 'Permission',
            Item::TYPE_ROLE => 'Role'
        ];
        if ($type === null) {
            return $result;
        }
        return $result[$type];
    }
}