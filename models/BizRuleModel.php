<?php

namespace yii2mod\rbac\models;

use Yii;
use yii\base\Model;
use yii\rbac\Rule;
use yii2mod\rbac\components\BizRule;


/**
 * Class BizRuleModel
 * @package yii2mod\rbac\models
 */
class BizRuleModel extends Model
{
    /**
     * @var string name of the rule
     */
    public $name;

    /**
     * @var string Simple PHP expression. Example: return Yii::$app->user->isGuest;
     */
    public $expression;

    /**
     * @var string class name
     */
    public $className;

    /**
     * @var object Rule
     */
    private $_item;

    /**
     * Constructor.
     *
     * @param array $item
     * @param array $config name-value pairs that will be used to initialize the object properties
     */
    public function __construct($item, $config = [])
    {
        $this->_item = $item;
        if ($item !== null) {
            $this->name = $item->name;
            $this->className = get_class($item);
            if ($this->className === BizRule::className()) {
                $this->expression = $item->expression;
            }
        }
        parent::__construct($config);
    }

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * @return array validation rules
     * @see scenarios()
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name'], 'existRuleName'],
            [['expression'], 'string'],
            [['className'], 'classExists']
        ];
    }

    /**
     * Validate rule name
     * Check if rule name already exist
     */
    public function existRuleName()
    {
        $rule = Yii::$app->authManager->getRule($this->name);
        if (!empty($rule) && $this->getIsNewRecord()) {
            $this->addError('name', "This name has already been taken.");
        }
    }

    /**
     * Validate Rule
     * Check if class exist
     */
    public function classExists()
    {
        if (!class_exists($this->className) || !is_subclass_of($this->className, Rule::className())) {
            $this->addError('className', "Unknown Class: {$this->className}");
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
            'expression' => 'Expression',
        ];
    }

    /**
     * Check if record is new
     *
     * @return bool
     */
    public function getIsNewRecord()
    {
        return $this->_item === null;
    }

    /**
     * Create object
     *
     * @param $id
     *
     * @return BizRuleModel|null
     */
    public static function find($id)
    {
        $item = Yii::$app->authManager->getRule($id);
        if ($item !== null) {
            return new self($item);
        }
        return null;
    }

    /**
     * Save biz rule
     *
     * @return bool
     */
    public function save()
    {
        if ($this->validate()) {
            $manager = Yii::$app->authManager;
            $this->className = $class = $this->className ? $this->className : BizRule::className();
            if ($this->_item === null) {
                $this->_item = new $class();
                $isNew = true;
            } else {
                $isNew = false;
                $oldName = $this->_item->name;
            }
            $this->_item->name = $this->name;
            if ($class === BizRule::className()) {
                $this->_item->expression = $this->expression;
            }

            if ($isNew) {
                $manager->add($this->item);
            } else {
                $manager->update($oldName, $this->item);
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get item
     *
     * @return Rule
     */
    public function getItem()
    {
        return $this->_item;
    }
}