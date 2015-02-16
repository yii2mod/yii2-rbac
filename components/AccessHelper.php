<?php

namespace yii2mod\rbac\components;

use ReflectionClass;
use yii\caching\TagDependency;
use yii\helpers\Inflector;
use Yii;

/**
 * Class AccessHelper
 * @package yii2mod\rbac\components
 */
class AccessHelper
{
    /**
     * Tag - file, for invalidate tag dependency cache
     */
    const FILE_GROUP = 'file';

    /**
     * Get routes
     *
     * @param bool $refresh
     * @return array|mixed
     */
    public static function getRoutes($refresh = false)
    {
        $key = static::buildKey(__METHOD__);
        if ($refresh || ($cache = Yii::$app->getCache()) === null || ($result = $cache->get($key)) === false) {
            $result = [];
            self::getRouteRecursive(Yii::$app, $result);
            if ($cache !== null) {
                $cache->set($key, $result, 0, new TagDependency([
                    'tags' => static::getGroup(static::FILE_GROUP)
                ]));
            }
        }
        return $result;
    }

    /**
     * Get route recursive
     *
     * @param $module
     * @param $result
     * @throws \yii\base\InvalidConfigException
     */
    private static function getRouteRecursive($module, &$result)
    {
        foreach ($module->getModules() as $id => $child) {
            if (($child = $module->getModule($id)) !== null) {
                self::getRouteRecursive($child, $result);
            }
        }
        /* @var $controller \yii\base\Controller */
        foreach ($module->controllerMap as $id => $value) {
            $controller = Yii::createObject($value, [
                $id,
                $module
            ]);
            self::getActionRoutes($controller, $result);
            $result[] = '/' . $controller->uniqueId . '/*';
        }

        $namespace = trim($module->controllerNamespace, '\\') . '\\';
        self::getControllerRoutes($module, $namespace, '', $result);
        $result[] = ($module->uniqueId === '' ? '' : '/' . $module->uniqueId) . '/*';
    }

    /**
     * Get controller routes
     * @param $module
     * @param $namespace
     * @param $prefix
     * @param $result
     * @throws \yii\base\InvalidConfigException
     */
    private static function getControllerRoutes($module, $namespace, $prefix, &$result)
    {
        $path = Yii::getAlias('@' . str_replace('\\', '/', $namespace));
        if (!is_dir($path)) {
            return;
        }
        foreach (scandir($path) as $file) {
            if ($file == '.' || $file == '..') {
                continue;
            }
            if (is_dir($path . '/' . $file)) {
                self::getControllerRoutes($module, $namespace . $file . '\\', $prefix . $file . '/', $result);
            } elseif (strcmp(substr($file, -14), 'Controller.php') === 0) {
                $id = Inflector::camel2id(substr(basename($file), 0, -14));
                $className = $namespace . Inflector::id2camel($id) . 'Controller';
                if (strpos($className, '-') === false && class_exists($className) && is_subclass_of($className, 'yii\base\Controller')) {
                    $controller = Yii::createObject($className, [
                        $prefix . $id,
                        $module
                    ]);
                    self::getActionRoutes($controller, $result);
                    $result[] = '/' . $controller->uniqueId . '/*';
                }
            }
        }
    }

    /**
     * Get action routes
     *
     * @param $controller
     * @param $result
     */
    private static function getActionRoutes($controller, &$result)
    {
        $prefix = '/' . $controller->uniqueId . '/';
        foreach ($controller->actions() as $id => $value) {
            $result[] = $prefix . $id;
        }
        $class = new ReflectionClass($controller);
        foreach ($class->getMethods() as $method) {
            $name = $method->getName();
            if ($method->isPublic() && !$method->isStatic() && strpos($name, 'action') === 0 && $name !== 'actions') {
                $result[] = $prefix . Inflector::camel2id(substr($name, 6));
            }
        }
    }

    /**
     * Get saved Routes
     *
     * @param bool $refresh
     * @return array|mixed
     */
    public static function getSavedRoutes($refresh = false)
    {
        $key = static::buildKey(__METHOD__);
        if ($refresh || ($cache = Yii::$app->getCache()) === null || ($result = $cache->get($key)) === false) {
            $result = [];
            foreach (Yii::$app->getAuthManager()->getPermissions() as $name => $value) {
                if ($name[0] === '/' && substr($name, -1) != '*') {
                    $result[] = $name;
                }
            }
            if ($cache !== null) {
                $cache->set($key, $result, 0, new TagDependency([
                    'tags' => static::getGroup(static::AUTH_GROUP)
                ]));
            }
        }
        return $result;
    }


    /**
     * Get group
     *
     * @param $group
     * @return string
     */
    private static function getGroup($group)
    {
        return md5(serialize([__CLASS__, $group]));
    }

    /**
     * Build key
     *
     * @param $key
     * @return array
     */
    private static function buildKey($key)
    {
        return [__CLASS__, $key];
    }

    /**
     * Refresh file cache
     * @static
     */
    public static function refreshFileCache()
    {
        if (($cache = Yii::$app->getCache()) !== null) {
            TagDependency::invalidate($cache, static::getGroup(static::FILE_GROUP));
        }
    }
}