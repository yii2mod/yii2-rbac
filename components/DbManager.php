<?php

namespace yii2mod\rbac\components;

use Yii;
use yii\db\Connection;
use yii\db\Query;
use yii\di\Instance;
use yii\rbac\Item;
use yii\rbac\Permission;
use yii\rbac\Assignment;
use yii\rbac\Role;
use yii\rbac\Rule;
use yii\caching\Cache;
use yii\caching\TagDependency;


/**
 * DbManager represents an authorization manager that stores authorization information in database.
 *
 * The database connection is specified by [[$db]]. The database schema could be initialized by applying migration:
 *
 * You may change the names of the three tables used to store the authorization data by setting [[\yii\rbac\DbManager::$itemTable]],
 * [[\yii\rbac\DbManager::$itemChildTable]] and [[\yii\rbac\DbManager::$assignmentTable]].
 *
 * Class DbManager
 * @package yii2mod\rbac\components
 */

class DbManager extends \yii\rbac\DbManager
{
    /**
     * Tag - items, for invalidate tag dependency cache
     */
    const PART_ITEMS = 'items';
    /**
     * Tag - children, for invalidate tag dependency cache
     */
    const PART_CHILDREN = 'children';
    /**
     * Tag - rules, for invalidate tag dependency cache
     */
    const PART_RULES = 'rules';
    /**
     * @var boolean Enable caching
     */
    public $enableCaching = false;
    /**
     * @var string|Cache Cache component
     */
    public $cache = 'cache';
    /**
     * @var integer Cache duration
     */
    public $cacheDuration = 0;
    /**
     * @var Item[]
     * itemName => item
     */
    private $_items;
    /**
     * @var array
     * itemName => childName[]
     */
    private $_children;
    /**
     * @var array
     * userId => itemName[]
     */
    private $_assignments = [];
    /**
     * @var Rule[]
     * ruleName => rule
     */
    private $_rules;


    /**
     * Initializes the application component.
     * This method overrides the parent implementation by establishing the database connection.
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::className());
        if ($this->enableCaching) {
            $this->cache = Instance::ensure($this->cache, Cache::className());
        } else {
            $this->cache = null;
        }
    }

    /**
     * Checks if the user has the specified permission.
     * @param string|integer $userId the user ID. This should be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $permissionName the name of the permission to be checked against
     * @param array $params name-value pairs that will be passed to the rules associated
     * with the roles and permissions assigned to the user.
     * @return boolean whether the user has the specified permission.
     * @throws \yii\base\InvalidParamException if $permissionName does not refer to an existing permission
     */
    public function checkAccess($userId, $permissionName, $params = [])
    {
        $this->loadItems();
        $this->loadChildren();
        $this->loadRules();
        $assignments = $this->getAssignments($userId);
        return $this->checkAccessRecursive($userId, $permissionName, $params, $assignments);
    }

    /**
     * Returns all role assignment information for the specified user.
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @return Assignment[] the assignments indexed by role names. An empty array will be
     * returned if there is no role assigned to the user.
     */
    public function getAssignments($userId)
    {
        $this->loadAssignments($userId);
        return isset($this->_assignments[$userId]) ? $this->_assignments[$userId] : [];
    }

    /**
     * Performs access check for the specified user.
     * This method is internally called by [[checkAccess()]].
     * @param string|integer $user the user ID. This should can be either an integer or a string representing
     * the unique identifier of a user. See [[\yii\web\User::id]].
     * @param string $itemName the name of the operation that need access check
     * @param array $params name-value pairs that would be passed to rules associated
     * with the tasks and roles assigned to the user. A param with name 'user' is added to this array,
     * which holds the value of `$userId`.
     * @param Assignment[] $assignments the assignments to the specified user
     * @return boolean whether the operations can be performed by the user.
     */
    protected function checkAccessRecursive($user, $itemName, $params, $assignments)
    {
        if (!isset($this->_items[$itemName])) {
            return false;
        }
        /** @var Item $item */
        $item = $this->_items[$itemName];
        Yii::trace($item instanceof Role ? "Checking role: $itemName" : "Checking permission : $itemName", __METHOD__);
        if (!$this->executeRule($user, $item, $params)) {
            return false;
        }
        if (isset($assignments[$itemName]) || in_array($itemName, $this->defaultRoles)) {
            return true;
        }
        foreach ($this->_children as $parentName => $children) {
            if (in_array($itemName, $children) && $this->checkAccessRecursive($user, $parentName, $params, $assignments)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Adds an item as a child of another item.
     * @param Item $parent
     * @param Item $child
     * @return bool
     * @throws \yii\base\Exception if the parent-child relationship already exists or if a loop has been detected.
     */
    public function addChild($parent, $child)
    {
        $this->loadItems();
        $this->loadChildren();
        parent::addChild($parent, $child);
        $this->_children[$parent->name][] = $child->name;
        $this->invalidate(self::PART_CHILDREN);
        return true;
    }

    /**
     * Removes a child from its parent.
     * Note, the child item is not deleted. Only the parent-child relationship is removed.
     * @param Item $parent
     * @param Item $child
     * @return boolean whether the removal is successful
     */
    public function removeChild($parent, $child)
    {
        $result = parent::removeChild($parent, $child);
        if ($this->_children !== null) {
            $query = (new Query)
                ->select('child')
                ->from($this->itemChildTable)
                ->where(['parent' => $parent->name]);
            $this->_children[$parent->name] = $query->column($this->db);
        }
        $this->invalidate(self::PART_CHILDREN);
        return $result;
    }

    /**
     * Returns a value indicating whether the child already exists for the parent.
     * @param Item $parent
     * @param Item $child
     * @return boolean whether `$child` is already a child of `$parent`
     */
    public function hasChild($parent, $child)
    {
        $this->loadChildren();
        return isset($this->_children[$parent->name]) && in_array($child->name, $this->_children[$parent->name]);
    }

    /**
     * Assigns a role to a user.
     *
     * @param Role $role
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @return Assignment the role assignment information.
     * @throws \Exception if the role has already been assigned to the user
     */
    public function assign($role, $userId)
    {
        $assignment = parent::assign($role, $userId);
        if (isset($this->_assignments[$userId])) {
            $this->_assignments[$userId][$role->name] = $assignment;
        }
        return $assignment;
    }

    /**
     * Revokes a role from a user.
     * @param Role $role
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @return boolean whether the revoking is successful
     */
    public function revoke($role, $userId)
    {
        $result = parent::revoke($role, $userId);
        unset($this->_assignments[$userId]);
        return $result;
    }

    /**
     * Revokes all roles from a user.
     * @param mixed $userId the user ID (see [[\yii\web\User::id]])
     * @return boolean whether the revoking is successful
     */
    public function revokeAll($userId)
    {
        if (empty($userId)) {
            return false;
        }
        $result = parent::revokeAll($userId);
        $this->_assignments[$userId] = [];
        return $result;
    }

    /**
     * Returns the assignment information regarding a role and a user.
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @param string $roleName the role name
     * @return Item|null the assignment information. Null is returned if
     * the role is not assigned to the user.
     */
    public function getAssignment($roleName, $userId)
    {
        $this->loadItems();
        $this->loadAssignments($userId);
        if (isset($this->_assignments[$userId][$roleName], $this->_items[$roleName])) {
            return $this->_items[$roleName];
        }
        return null;
    }

    /**
     * Returns the items of the specified type.
     * @param integer $type the auth item type (either [[Item::TYPE_ROLE]] or [[Item::TYPE_PERMISSION]]
     * @return Item[] the auth items of the specified type.
     */
    protected function getItems($type)
    {
        $this->loadItems();
        $items = [];
        foreach ($this->_items as $name => $item) {
            /** @var Item $item */
            if ($item->type == $type) {
                $items[$name] = $item;
            }
        }
        return $items;
    }

    /**
     * Removes an auth item from the RBAC system.
     * @param Item $item
     * @return boolean whether the role or permission is successfully removed
     * @throws \Exception if data validation or saving fails (such as the name of the role or permission is not unique)
     */
    public function removeItem($item)
    {
        parent::removeItem($item);
        $this->_assignments = [];
        $this->_children = $this->_items = null;
        $this->invalidate([self::PART_ITEMS, self::PART_CHILDREN]);
        return true;
    }

    /**
     * Returns the named auth item.
     * @param string $name the auth item name.
     * @return Item the auth item corresponding to the specified name. Null is returned if no such item.
     */
    public function getItem($name)
    {
        $this->loadItems();
        return isset($this->_items[$name]) ? $this->_items[$name] : null;
    }

    /**
     * Updates a rule to the RBAC system.
     * @param string $name the old name of the rule
     * @param Rule $rule
     * @return boolean whether the rule is successfully updated
     * @throws \Exception if data validation or saving fails (such as the name of the rule is not unique)
     */
    public function updateRule($name, $rule)
    {
        parent::updateRule($name, $rule);
        if ($rule->name !== $name) {
            $this->_items = null;
            $this->invalidate(self::PART_ITEMS);
        }
        if ($this->_rules !== null) {
            unset($this->_rules[$name]);
            $this->_rules[$rule->name] = $rule;
        }
        $this->invalidate(self::PART_RULES);
        return true;
    }

    /**
     * Returns the rule of the specified name.
     * @param string $name the rule name
     * @return Rule the rule object, or null if the specified name does not correspond to a rule.
     */
    public function getRule($name)
    {
        $this->loadRules();
        return isset($this->_rules[$name]) ? $this->_rules[$name] : null;
    }

    /**
     * Returns all rules available in the system.
     * @return Rule[] the rules indexed by the rule names
     */
    public function getRules()
    {
        $this->loadRules();
        return $this->_rules;
    }

    /**
     * Returns the roles that are assigned to the user via [[assign()]].
     * Note that child roles that are not assigned directly to the user will not be returned.
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @return Role[] all roles directly or indirectly assigned to the user. The array is indexed by the role names.
     */
    public function getRolesByUser($userId)
    {
        $this->loadItems();
        $roles = [];
        foreach ($this->getAssignments($userId) as $name => $asgn) {
            $roles[$name] = $this->_items[$name];
        }
        return $roles;
    }

    /**
     * Returns all permissions that the specified role represents.
     * @param string $roleName the role name
     * @return Permission[] all permissions that the role represents. The array is indexed by the permission names.
     */
    public function getPermissionsByRole($roleName)
    {
        $childrenList = $this->getChildrenList();
        $result = [];
        $this->getChildrenRecursive($roleName, $childrenList, $result);
        if (empty($result)) {
            return [];
        }
        $this->loadItems();
        $permissions = [];
        foreach (array_keys($result) as $itemName) {
            if (isset($this->_items[$itemName]) && $this->_items[$itemName] instanceof Permission) {
                $permissions[$itemName] = $this->_items[$itemName];
            }
        }
        return $permissions;
    }

    /**
     * Returns the children for every parent.
     * @return array the children list. Each array key is a parent item name,
     * and the corresponding array value is a list of child item names.
     */
    protected function getChildrenList()
    {
        $this->loadChildren();
        return $this->_children;
    }

    /**
     * Returns all permissions that the user has.
     * @param string|integer $userId the user ID (see [[\yii\web\User::id]])
     * @return Permission[] all permissions that the user has. The array is indexed by the permission names.
     */
    public function getPermissionsByUser($userId)
    {
        $childrenList = $this->getChildrenList();
        $result = [];
        foreach ($this->getAssignments($userId) as $roleName => $asgn) {
            $this->getChildrenRecursive($roleName, $childrenList, $result);
        }
        if (empty($result)) {
            return [];
        }
        $this->loadItems();
        $permissions = [];
        foreach (array_keys($result) as $itemName) {
            if (isset($this->_items[$itemName]) && $this->_items[$itemName] instanceof Permission) {
                $permissions[$itemName] = $this->_items[$itemName];
            }
        }
        return $permissions;
    }

    /**
     * Returns the child permissions and/or roles.
     * @param string $name the parent name
     * @return Item[] the child permissions and/or roles
     */
    public function getChildren($name)
    {
        $this->loadItems();
        $this->loadChildren();
        $items = [];
        if (isset($this->_children[$name])) {
            foreach ($this->_children[$name] as $itemName) {
                $items[$itemName] = $this->_items[$itemName];
            }
        }
        return $items;
    }

    /**
     * Removes all authorization data, including roles, permissions, rules, and assignments.
     */
    public function removeAll()
    {
        $this->_children = [];
        $this->_items = [];
        $this->_assignments = [];
        $this->_rules = [];
        $this->removeAllAssignments();
        $this->db->createCommand()->delete($this->itemChildTable)->execute();
        $this->db->createCommand()->delete($this->itemTable)->execute();
        $this->db->createCommand()->delete($this->ruleTable)->execute();
        $this->invalidate([self::PART_ITEMS, self::PART_CHILDREN, self::PART_RULES]);
    }

    /**
     * Removes all auth items of the specified type.
     * @param integer $type the auth item type (either Item::TYPE_PERMISSION or Item::TYPE_ROLE)
     */
    protected function removeAllItems($type)
    {
        parent::removeAllItems($type);
        $this->_assignments = [];
        $this->_children = $this->_items = null;
        $this->invalidate([self::PART_ITEMS, self::PART_CHILDREN]);
    }

    /**
     * Removes all rules.
     * All roles and permissions which have rules will be adjusted accordingly.
     */
    public function removeAllRules()
    {
        parent::removeAllRules();
        $this->_rules = [];
        $this->_items = null;
        $this->invalidate([self::PART_ITEMS, self::PART_RULES]);
    }

    /**
     * Removes all role assignments.
     */
    public function removeAllAssignments()
    {
        parent::removeAllAssignments();
        $this->_assignments = [];
    }

    /**
     * Removes a rule from the RBAC system.
     * @param Rule $rule
     * @return boolean whether the rule is successfully removed
     * @throws \Exception if data validation or saving fails (such as the name of the rule is not unique)
     */
    protected function removeRule($rule)
    {
        parent::removeRule($rule);
        if ($this->_rules !== null) {
            unset($this->_rules[$rule->name]);
        }
        $this->_items = null;
        $this->invalidate([self::PART_ITEMS, self::PART_RULES]);
        return true;
    }

    /**
     * Adds a rule to the RBAC system.
     * @param Rule $rule
     * @return boolean whether the rule is successfully added to the system
     * @throws \Exception if data validation or saving fails (such as the name of the rule is not unique)
     */
    protected function addRule($rule)
    {
        parent::addRule($rule);
        if ($this->_rules !== null) {
            $this->_rules[$rule->name] = $rule;
        }
        $this->invalidate(self::PART_RULES);
        return true;
    }

    /**
     * Updates an auth item in the RBAC system.
     * @param string $name the old name of the auth item
     * @param Item $item
     * @return boolean whether the auth item is successfully updated
     * @throws \Exception if data validation or saving fails (such as the name of the role or permission is not unique)
     */
    protected function updateItem($name, $item)
    {
        parent::updateItem($name, $item);
        if ($item->name !== $name) {
            $this->_assignments = [];
            $this->_children = null;
            $this->invalidate(self::PART_CHILDREN);
        }
        $this->_items = null;
        $this->invalidate(self::PART_RULES);
        return true;
    }

    /**
     * Adds an auth item to the RBAC system.
     * @param Item $item
     * @return boolean whether the auth item is successfully added to the system
     * @throws \Exception if data validation or saving fails (such as the name of the role or permission is not unique)
     */
    protected function addItem($item)
    {
        parent::addItem($item);
        if ($this->_items !== null) {
            $this->_items[$item->name] = $item;
        }
        $this->invalidate(self::PART_ITEMS);
        return true;
    }

    /**
     * Invalidate cache
     * @param string $parts
     */
    private function invalidate($parts)
    {
        if ($this->enableCaching) {
            TagDependency::invalidate($this->cache, $parts);
        }
    }
    /**
     * Build key cache
     * @param string $part
     * @return string[]
     */
    private function buildKey($part)
    {
        return [__CLASS__, $part];
    }
    /**
     * Get data from cache
     * @param string $part
     * @return mixed
     */
    private function getFromCache($part)
    {
        if ($this->enableCaching) {
            return $this->cache->get($this->buildKey($part));
        }
        return false;
    }
    /**
     * Save data to cache
     * @param string $part
     * @param mixed $data
     */
    private function saveToCache($part, $data)
    {
        if ($this->enableCaching) {
            $this->cache->set($this->buildKey($part), $data, $this->cacheDuration, new TagDependency([
                'tags' => $part
            ]));
        }
    }
    /**
     * Load data. If avaliable in memory, get from memory
     * If no, get from cache. If no avaliable, get from database.
     */
    private function loadItems()
    {
        $part = self::PART_ITEMS;
        if ($this->_items === null && ($this->_items = $this->getFromCache($part)) === false) {
            $query = (new Query)->from($this->itemTable);
            $this->_items = [];
            foreach ($query->all($this->db) as $row) {
                $this->_items[$row['name']] = $this->populateItem($row);
            }
            $this->saveToCache($part, $this->_items);
        }
    }
    /**
     * Load data. If avaliable in memory, get from memory
     * If no, get from cache. If no avaliable, get from database.
     */
    private function loadChildren()
    {
        $this->loadItems();
        $part = self::PART_CHILDREN;
        if ($this->_children === null && ($this->_children = $this->getFromCache($part)) === false) {
            $query = (new Query)->from($this->itemChildTable);
            $this->_children = [];
            foreach ($query->all($this->db) as $row) {
                if (isset($this->_items[$row['parent']], $this->_items[$row['child']])) {
                    $this->_children[$row['parent']][] = $row['child'];
                }
            }
            $this->saveToCache($part, $this->_children);
        }
    }
    /**
     * Load data. If avaliable in memory, get from memory
     * If no, get from cache. If no avaliable, get from database.
     */
    private function loadRules()
    {
        $part = self::PART_RULES;
        if ($this->_rules === null && ($this->_rules = $this->getFromCache($part)) === false) {
            $query = (new Query)->from($this->ruleTable);
            $this->_rules = [];
            foreach ($query->all($this->db) as $row) {
                $rule = @unserialize($row['data']);
                if ($rule instanceof Rule) {
                    $this->_rules[$row['name']] = $rule;
                }
            }
            $this->saveToCache($part, $this->_rules);
        }
    }

    /**
     * Load data. If avaliable in memory, get from memory
     * If no, get from cache. If no avaliable, get from database.
     * @param $userId
     */
    private function loadAssignments($userId)
    {
        if (!isset($this->_assignments[$userId]) && !empty($userId)) {
            $query = (new Query)
                ->from($this->assignmentTable)
                ->where(['user_id' => (string) $userId]);
            $this->_assignments[$userId] = [];
            foreach ($query->all($this->db) as $row) {
                $this->_assignments[$userId][$row['item_name']] = new Assignment([
                    'userId' => $row['user_id'],
                    'roleName' => $row['item_name'],
                    'createdAt' => $row['created_at'],
                ]);
            }
        }
    }

    /**
     * Removed all children form their parent.
     * Note, the children items are not deleted. Only the parent-child relationships are removed.
     * @param Item $parent
     * @return boolean whether the removal is successful
     */
    public function removeChildren($parent)
    {
        $result = parent::removeChildren($parent);
        if ($this->_children !== null) {
            unset($this->_children[$parent->name]);
        }
        $this->invalidate(self::PART_CHILDREN);
        return $result;
    }
}
