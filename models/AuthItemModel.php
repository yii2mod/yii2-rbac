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
     * @var string auth item name
     */
    public $name;

    /**
     * @var int auth item type
     */
    public $type;

    /**
     * @var string auth item description
     */
    public $description;

    /**
     * @var string biz rule name
     */
    public $ruleName;

    /**
     * @var null|string additional data
     */
    public $data;

    /**
     * @var Item
     */
    private $_item;

    /**
     * Constructor.
     *
     * @param Item $item
     * @param array $config
     */
    public function __construct($item, $config = [])
    {
        $this->_item = $item;
        if ($item !== null) {
            $this->name = $item->name;
            $this->type = (int)$item->type;
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
        if ($this->type === Item::TYPE_PERMISSION) {
            $authItem = Yii::$app->authManager->getPermission($this->name);
        } else {
            $authItem = Yii::$app->authManager->getRole($this->name);
        }
        if ($this->getIsNewRecord()) {
            if (!empty($authItem)) {
                $this->addError('name', "This name has already been taken.");
            }
        } else {
            if (!empty($authItem) && $authItem->name !== $this->item->name) {
                $this->addError('name', "This name has already been taken.");
            }
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
     * Check is new record
     *
     * @return bool
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }

    /**
     * Find auth item
     *
     * @param $id
     * @return null|AuthItemModel
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
     * Save auth item
     *
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
     * Return item
     *
     * @return Item
     */
    public function getItem()
    {
        return $this->_item;
    }

    /**
     * Get type name
     *
     * @param null $type
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