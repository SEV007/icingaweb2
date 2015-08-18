<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | GPLv2+ */

namespace Icinga\Application;

use Icinga\Exception\ProgrammingError;

class ClassLoader
{
    /**
     * Namespace separator
     */
    const NAMESPACE_SEPARATOR = '\\';

    /**
     * List of namespaces
     *
     * @var array
     */
    private $namespaces = array();

    /**
     * Register new namespace for directory
     *
     * @param   string  $namespace
     * @param   string  $directory
     *
     * @return  $this
     * @throws  ProgrammingError
     */
    public function registerNamespace($namespace, $directory)
    {
        if (!is_dir($directory)) {
            throw new ProgrammingError(
                'Directory "%s" for namespace "%s" does not exist',
                $directory,
                $namespace
            );
        }

        $this->namespaces[$namespace] = $directory;

        return $this;
    }

    /**
     * Test if a namespace exists
     *
     * @param   string  $namespace
     *
     * @return  bool
     */
    public function hasNamespace($namespace)
    {
        return array_key_exists($namespace, $this->namespaces);
    }

    /**
     * Class loader
     *
     * Ignores all but classes in registered namespaces.
     *
     * @param   string  $class
     *
     * @return  boolean
     */
    public function loadClass($class)
    {
        $namespace = $this->getNamespaceForClass($class);

        if ($namespace) {
            $file = $this->namespaces[$namespace] . preg_replace('/^' . preg_quote($namespace) . '/', '', $class);
            $file = str_replace(self::NAMESPACE_SEPARATOR, '/', $file) . '.php';

            if (@file_exists($file)) {
                require_once $file;
                return true;
            }
        }

        return false;
    }

    /**
     * Test if we have a registered namespaces for this class
     *
     * Return is the longest match in the array found
     *
     * @param   string  $className
     *
     * @return  bool|string
     */
    private function getNamespaceForClass($className)
    {
        $testNamespace = '';
        $testLength = 0;

        foreach (array_keys($this->namespaces) as $namespace) {
            $stub = preg_replace(
                '/^' . preg_quote($namespace) . '(' . preg_quote(self::NAMESPACE_SEPARATOR) . '|$)/', '', $className
            );
            $length = strlen($className) - strlen($stub);
            if ($length > $testLength) {
                $testLength = $length;
                $testNamespace = $namespace;
            }
        }

        if ($testLength > 0) {
            return $testNamespace;
        }

        return false;
    }

    /**
     * Effectively registers the autoloader the PHP/SPL way
     */
    public function register()
    {
        // Think about to add class pathes to php include path
        // this could be faster (tg)
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Detach autoloader from spl registration
     */
    public function unregister()
    {
        spl_autoload_unregister(array($this, 'loadClass'));
    }

    /**
     * Detach spl autoload method from stack
     */
    public function __destruct()
    {
        $this->unregister();
    }
}
