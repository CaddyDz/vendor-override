<?php

namespace CaddyDz\VendorOverride;

use Exception;
use Composer\Script\Event;

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
        foreach ($classes as $class) {
            if (class_exists($class)) {
                $reflector = new \ReflectionClass($class);
                $relative_path = str_replace(getcwd() . '/', '', $reflector->getFileName());
                $namespace = $reflector->getNamespaceName();
                $namespace = str_replace('\\', '\\\\', $namespace);
                $content = file_get_contents($relative_path);
                $new_content = preg_replace("/$namespace/", __NAMESPACE__, $content, 1);
                file_put_contents('vendor/caddydz/vendor-override', $new_content);
            } else {
                throw new Exception('Class ' . $class . ' does not exist');
            }
        }
        $event->getComposer()->getPackage()->setAutoload($autoload);
    }

    /**
     * @param Event $event
     * @return array
     */
    protected static function getComposerAutoload(Event $event)
    {
        $autoload = $event->getComposer()->getPackage()->getAutoload();
        if (array_key_exists('files', $autoload) === false) {
            $autoload['files'] = [];
        }
        return $autoload;
    }
}
