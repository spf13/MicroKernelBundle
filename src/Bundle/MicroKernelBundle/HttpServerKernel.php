<?php

namespace Bundle\MicroKernelBundle;

use Symfony\Foundation\Kernel,
    Symfony\Components\DependencyInjection\BuilderConfiguration,
    Symfony\Components\DependencyInjection\Loader\YamlFileLoader as ContainerLoader,
    Symfony\Foundation\Bundle\Bundle,
    Symfony\Components\Routing\RouteCollection,
    Symfony\Components\Routing\Route;

/* 
 * This file is property of Bulat Shakirzyanov
 * to use other than in this project, email me
 * at Bulat Shakirzyanov<mallluhuct@gmail.com>
*/

/**
 * Class HttpServerKernel
 *
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class HttpServerKernel extends Kernel
{

    private $_rootDir;
    private $_configPath;
    private $_bundles = array();
    private $_bundleDirs = array();
    private $_routeCollection;
    private $_callbacks = array();

    public function __construct($environment, $debug)
    {
        parent::__construct($environment, $debug);
        $this->_routeCollection = new RouteCollection();
    }
    
    public function setRootDir($dir)
    {
        if(!is_dir($dir)) {
            throw new InvalidArgumentException($dir . ' is not a valid dir');
        }
        $this->_rootDir = $dir;
    }

    public function registerRootDir()
    {
        return $this->_rootDir;
    }

    public function addBundle($bundle)
    {
        $this->_bundles[] = $bundle;
    }

    public function registerBundles()
    {
        foreach ($this->_bundles as &$bundle) {
            $bundle = new $bundle;
        }
        return array_merge($this->_bundles, array(new \Bundle\MicroKernelBundle\Bundle()));
    }
    
    public function setBundleDir($bundle, $dir)
    {
        if(false === ($this->_bundleDirs[$bundle] = realpath($dir))) {
            unset ($this->_bundleDirs[$bundle]);
            throw new InvalidArgumentException($dir . ' is not a valid dir');
        }
    }

    public function registerBundleDirs()
    {
        return array_merge(array('Bundle\MicroKernelBundle' => __DIR__), $this->_bundleDirs);
    }

    public function setConfigPath($file)
    {
        if(!is_file($file)) {
            throw new \InvalidArgumentException($file . ' is not a valid file');
        }
        $this->_configPath = $file;
    }

    public function registerContainerConfiguration()
    {
        $loader = new ContainerLoader($this->getBundleDirs());

        return $loader->load($this->_configPath);
    }

    public function registerRoutes()
    {
        return $this->_routeCollection;
    }

    public function options($name, $pattern, $callback,
        array $defaults = array(),
        array $requirements = array()
    ) {
        $requirements['_method'] = 'OPTIONS';
        $this->parseRoute($name, $pattern, $defaults, $requirements, $callback, $this->_routeCollection);
    }
    
    public function get($name, $pattern, $callback,
        array $defaults = array(),
        array $requirements = array()
    ) {
        $requirements['_method'] = 'GET';
        $this->parseRoute($name, $pattern, $defaults, $requirements, $callback, $this->_routeCollection);
    }

    public function head($name, $pattern, $callback,
        array $defaults = array(),
        array $requirements = array()
    ) {
        $requirements['_method'] = 'HEAD';
        $this->parseRoute($name, $pattern, $defaults, $requirements, $callback, $this->_routeCollection);
    }

    public function post($name, $pattern, $callback,
        array $defaults = array(),
        array $requirements = array()
    ) {
        $requirements['_method'] = 'POST';
        $this->parseRoute($name, $pattern, $defaults, $requirements, $callback, $this->_routeCollection);
    }

    public function put($name, $pattern, $callback,
        array $defaults = array(),
        array $requirements = array()
    ) {
        $requirements['_method'] = 'PUT';
        $this->parseRoute($name, $pattern, $defaults, $requirements, $callback, $this->_routeCollection);
    }

    public function delete($name, $pattern, $callback,
        array $defaults = array(),
        array $requirements = array()
    ) {
        $requirements['_method'] = 'DELETE';
        $this->parseRoute($name, $pattern, $defaults, $requirements, $callback, $this->_routeCollection);
    }

    public function trace($name, $pattern, $callback,
        array $defaults = array(),
        array $requirements = array()
    ) {
        $requirements['_method'] = 'TRACE';
        $this->parseRoute($name, $pattern, $defaults, $requirements, $callback, $this->_routeCollection);
    }

    public function connect($name, $pattern, $callback,
        array $defaults = array(),
        array $requirements = array()
    ) {
        $requirements['_method'] = 'CONNECT';
        $this->parseRoute($name, $pattern, $defaults, $requirements, $callback, $this->_routeCollection);
    }

    protected function parseRoute($name, $pattern, $defaults, $requirements, $callback, RouteCollection $collection)
    {
        if (!is_callable($callback)) {
            throw new InvalidArgumentException('the function callback must be callable');
        }
        $this->_callbacks[$name] = $callback;
        $defaults['_callback'] = $name;
        $options = isset($config['options']) ? $config['options'] : array();
        $route = new Route($pattern, $defaults, $requirements, $options);
        $collection->addRoute($name, $route);
    }

    public function getCallback($name) {
        if (!isset ($this->_callbacks[$name])) {
            throw new \InvalidArgumentException(sprintf(
                'specified callback %s doesnt exist', $name
            ));
        }
        return $this->_callbacks[$name];
    }
}
