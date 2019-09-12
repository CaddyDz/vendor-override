<?php

/**
 * Change the namespace of an existing vendor class to allow extension
 * php version 5.6
 *
 * @file NamespaceChanger.php
 *
 * @category Composer_Plugin
 * @package  CaddyDz\VendorOverride
 * @author   Salim Djerbouh <caddydz4@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/CaddyDz/vendor-override
 */

namespace CaddyDz\VendorOverride;

use Composer\Script\Event;

/**
 * Namespace Changer
 * php version 5.6
 *
 * @category Composer_Plugin
 * @package  CaddyDz\VendorOverride
 * @author   Salim Djerbouh <caddydz4@gmail.com>
 * @license  MIT https://opensource.org/licenses/MIT
 * @link     https://github.com/CaddyDz/vendor-override
 */

class NamespaceChanger
{
    /**
     * @param Event $event
     * @throws \Exception
     */
    public static function override(Event $event)
    {
        $autoload = static::getComposerAutoload($event);
        $extra = $event->getComposer()->getPackage()->getExtra();
        $classes = $extra['classes-to-override'];
        foreach ($classes as $class => $file) {
            static::cloneClass($class);
            $autoload['files'] = $file;
        }
        $event->getComposer()->getPackage()->setAutoload($autoload);
    }

    protected static function cloneClass($class)
    {
        if (!static::alreadyLoaded($class)) {
            $content = file_get_contents(static::getClassInfo($class)['importPath']);
            $namespace = static::getClassInfo($class)['namespace'];
            $content = preg_replace("/$namespace/", __NAMESPACE__, $content, 1);
            $path = static::getClassInfo($class)['exportPath'];
            file_put_contents($path, $content);
        }
    }

    protected static function alreadyLoaded($class)
    {
        $pos = strrpos($class, '\\');
        $class = substr($class, $pos);
        return class_exists(__NAMESPACE__ . $class);
    }

    protected static function getClassInfo($class)
    {
        $map = require 'vendor/composer/autoload_classmap.php';
        $relative_path = str_replace(getcwd() . '/', '', $map[$class]);
        $namespace = str_replace('\\' . pathinfo($relative_path, PATHINFO_FILENAME), '', $class);
        $namespace = str_replace('\\', '\\\\', $namespace);
        $full_path = pathinfo(__FILE__)['dirname'] . DIRECTORY_SEPARATOR . pathinfo($relative_path, PATHINFO_BASENAME);
        return [
            'importPath' => $relative_path,
            'namespace' => $namespace,
            'exportPath' => $full_path
        ];
    }

    /**
     * @param Event $event
     * @return array
     */
    protected static function getComposerAutoload(Event $event)
    {
        $autoload = $event->getComposer()->getPackage()->getAutoload();
        if (!array_key_exists('files', $autoload)) {
            $autoload['files'] = [];
        }
        return $autoload;
    }
}
