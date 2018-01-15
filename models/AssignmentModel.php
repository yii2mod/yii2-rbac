<?php

namespace yii2mod\rbac\models;

use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\web\IdentityInterface;

/**
 * Class AssignmentModel
 *
 * @package yii2mod\rbac\models
 */
class AssignmentModel extends BaseObject
{
    /**
     * @var IdentityInterface
     */
    public $user;

    /**
     * @var int User id
     */
    public $userId;

    /**
     * @var \yii\rbac\ManagerInterface
     */
    protected $manager;

    /**
     * AssignmentModel constructor.
     *
     * @param IdentityInterface $user
     * @param array $config
     *
     * @throws InvalidConfigException
     */
    public function __construct(IdentityInterface $user, $config = [])
    {
        $this->user = $user;
        $this->userId = $user->getId();
        $this->manager = Yii::$app->authManager;

        if ($this->userId === null) {
            throw new InvalidConfigException('The "userId" property must be set.');
        }

        parent::__construct($config);
    }

    /**
     * Assign a roles and permissions to the user.
     *
     * @param array $items
     *
     * @return bool
     */
    public function assign(array $items): bool
    {
        foreach ($items as $name) {
            $item = $this->manager->getRole($name);
            $item = $item ?: $this->manager->getPermission($name);
            $this->manager->assign($item, $this->userId);
        }

        return true;
    }

    /**
     * Revokes a roles and permissions from the user.
     *
     * @param array $items
     *
     * @return bool
     */
    public function revoke(array $items): bool
    {
        foreach ($items as $name) {
            $item = $this->manager->getRole($name);
            $item = $item ?: $this->manager->getPermission($name);
            $this->manager->revoke($item, $this->userId);
        }

        return true;
    }

    /**
     * Get all available and assigned roles and permissions
     *
     * @return array
     */
    public function getItems(): array
    {
        $available = [];
        $assigned = [];

        foreach (array_keys($this->manager->getRoles()) as $name) {
            $available[$name] = 'role';
        }

        foreach (array_keys($this->manager->getPermissions()) as $name) {
            if ($name[0] != '/') {
                $available[$name] = 'permission';
            }
        }

        foreach ($this->manager->getAssignments($this->userId) as $item) {
            $assigned[$item->roleName] = $available[$item->roleName];
            unset($available[$item->roleName]);
        }

        return [
            'available' => $available,
            'assigned' => $assigned,
        ];
    }
}
