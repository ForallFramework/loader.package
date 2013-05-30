<?php

/**
 * @package forall.loader
 * @author Avaq <aldwin.vlasblom@gmail.com>
 */
namespace forall\loader;

require_once __DIR__."/ClassLoader.php";

use \forall\core\core\Core;
use \forall\core\core\AbstractCore;
use \forall\core\core\PackageDescriptor;
use \forall\core\core\FileIncluder;
use \forall\core\singleton\SingletonTraits;

/**
 * The loader class.
 */
class Loader extends AbstractCore
{
  
  use SingletonTraits;
  
  /**
   * Contains the names of packages that have been initialized by the loader.
   * @var string[]
   */
  private $initializedPackages = [];
  
  /**
   * Contains the names of packages that have been loaded by the loader.
   * @var string[]
   */
  private $loadedPackages = [];
  
  /**
   * Contains the instance of FileIncluder that is able to do all "includes" including.
   * @var FileIncluder
   */
  private $includer;
  
  /**
   * Set up auto-loading.
   *
   * @return void
   */
  public function init()
  {
    
    //Get package info from core.
    $core = Core::getInstance();
    $packages = $core->getPackages();
    
    //The core doesn't need us.
    $this->initializedPackages[] = $this->loadedPackages[] = 'forall.core';
    
    //Iterate the packages.
    foreach($packages as $package)
    {
      
      //Get the settings.
      $settings = $this->getLoadingSettings($package);
      
      //Should the package automatically initialize?
      if($settings['autoInit'] == true){
        $this->initPackage($package);
      }
      
    }
    
  }
  
  /**
   * Set up auto-loading and core classes for the given package.
   *
   * @param  PackageDescriptor $package The package to initialize.
   *
   * @return self                       Chaining enabled.
   */
  public function initPackage(PackageDescriptor $package)
  {
    
    //Don't perform operation if the given package has already been initialized.
    if(in_array($package->getName(), $this->initializedPackages)){
      return $this;
    }
    
    //This package has now been initialized.
    $this->initializedPackages[] = $package->getName();
    
    //Get the directory where we expect the source code to be.
    $sourceDir = $this->getPackageSourceDir($package);
    
    //Get the namespace separator.
    $nssep = $this->getPackageNamespaceSeparator($package);
    
    //Make the name space.
    $namespace = $nssep.str_replace(['.', '/', '\\', '_'], $nssep, $package->getName());
    
    //Create a class loader for the package.
    $classLoader = new ClassLoader($namespace, $sourceDir);
    
    //When the class loader loads a class, we want the package it's in to load.
    $classLoader->onLoad(function()use($package){
      $this->loadDependencies($package);
    });
    
    //Register the loader on the auto-load stack.
    $classLoader->register();
    
    //Get the package loading settings.
    $settings = $this->getLoadingSettings($package);
    
    //Register Core class loaders.
    foreach($settings['coreClasses'] as $name => $className)
    {
      
      //Append the class name to the root namespace.
      $className = $namespace.$nssep.$className;
      
      //Register a loader for the class.
      $core->registerInstanceLoader($name, function()use($className, $package){
        $this->loadDependencies($package);
        return $className::getInstance();
      });
      
    }
    
    //Plain includes (for functions, constants and entry point).
    $this->loadIncludes($package, $settings['staticIncludes']);
    
    //Enable chaining.
    return $this;
    
  }
  
  /**
   * Loads all dependency packages, extensions and includes of a given package.
   *
   * @param  PackageDescriptor $package The package to load the dependencies of.
   * 
   * @throws LoaderException If a PHP extension is missing and could not be loaded.
   *
   * @return self                       Chaining enabled.
   */
  public function loadDependencies(PackageDescriptor $package)
  {
    
    //Don't perform operation if the given package has already been loaded.
    if(in_array($package->getName(), $this->loadedPackages)){
      return $this;
    }
    
    //This package has now had its dependencies loaded.
    $this->loadedPackages[] = $package->getName();
    
    //Get the instance of core, loading settings and source directory.
    $core = Core::getInstance();
    $settings = $this->getLoadingSettings($package);
    $sourceDir = $this->getPackageSourceDir($package);
    
    //Iterate the dependencies so we may initialize them one by one.
    foreach($settings['dependencies'] as $packageName){
      $this->initPackage($core->getPackageByName($packageName));
    }
    
    //Iterate the required PHP extensions to load them.
    foreach($settings['extensions'] as $extensionName){
      if(!$this->loadExtension($extensionName)){
        throw new LoaderException(sprintf(
          'The "%s" package requires the "%s" PHP-extension. Please contact your server administrator.',
          $package->getMeta()['title'], $extensionName
        ));
      }
    }
    
    //Lazy includes (for initializing sequence).
    $this->loadIncludes($package, $settings['includes']);
    
    //Enable chaining.
    return $this;
    
  }
  
  /**
   * Normalizes relative paths and includes the files.
   *
   * @param  PackageDescriptor $package  The package that the files reside in.
   * @param  array             $includes An array of relative paths to the files.
   *
   * @return self                        Chaining enabled.
   */
  private function loadIncludes(PackageDescriptor $package, array $includes)
  {
    
    //Get the FileIncluder if it already exists.
    if($this->includer instanceof FileIncluder){
      $includer =& $this->includer;
    }
    
    //Create the FileIncluder.
    else
    {
      
      //Create a new instance.
      $includer = $this->includer = new FileIncluder;
      
      //Set its environment.
      $includer->setEnv([
        'core' => Core::getInstance(),
        'loader' => $this
      ]);
      
    }
    
    //Get the package root.
    $root = $package->getRoot();
    
    //Iterate the includes.
    foreach($includes as $path)
    {
      
      //We prepend a directory separator and make absolute paths relative as well.
      if($path{0} !== '/'){
        $path = "/$path";
      }
      
      //We prepend the package root to the path.
      $path = "$root$path";
      
      //We include the file.
      $includer($path);
      
    }
    
  }
  
  /**
   * Load a PHP-extension.
   * 
   * This method returns `true` when the extension has successfully loaded and false if
   * the attempt to load it failed.
   *
   * @return bool Whether the extension has been, or could be loaded.
   */
  public function loadExtension($name)
  {
    
    //Return true for already loaded extensions.
    if(extension_loaded($name)){
      return true;
    }
    
    //Can we possible load the extension? If not, return false.
    if(!function_exists('dl')){
      return false;
    }
    
    //Do we prefix the extension name?
    if(PHP_SHLIB_SUFFIX === 'dll'){
      $name = "php_$name";
    }
    
    //Add the suffix.
    $name = "$name.".PHP_SHLIB_SUFFIX;
    
    //Return a boolean indicating whether the runtime package inclusion worked.
    return !! @dl($name);
    
  }
  
  /**
   * Return the settings as defined in `yourPackage/settings.json`, with default values applied.
   *
   * @param  PackageDescriptor $package The package to get the settings from.
   *
   * @return array                      The array of settings.
   */
  public function getLoadingSettings(PackageDescriptor $package)
  {
    
    //Path to the JSON file.
    $loader_json = $package->getFullPath()."/loader.json";
    
    //Get loader settings from file.
    if(is_file($loader_json)){
      $settings = Core::getInstance()->parseJsonFromFile($loader_json);
    }
    
    //Use default settings.
    else{
      $settings = [];
    }
    
    //Apply default values.
    $settings = array_merge([
      'sourceDirectory' => '',
      'staticIncludes' => [],
      'includes' => [],
      'coreClasses' => [],
      'dependencies' => [],
      'extensions' => [],
      'autoInit' => (! is_file($loader_json))
    ], $settings);
    
    //Return the settings.
    return $settings;
    
  }
  
  /**
   * Return the source directory as given by the package meta data.
   *
   * @param  PackageDescriptor $package The package of which the source directory is returned.
   *
   * @return string                     The path to the source directory.
   */
  public function getPackageSourceDir(PackageDescriptor $package)
  {
    
    return $package->getFullPath().$this->getLoadingSettings($package)['sourceDirectory'];
    
  }
  
  /**
   * Return the namespace separator as given by the package meta data.
   *
   * @param  PackageDescriptor $package The package of which the source directory is returned.
   *
   * @return string                     The namespace separator.
   */
  public function getPackageNamespaceSeparator(PackageDescriptor $package)
  {
    
    return (array_key_exists('namespaceSeparator', $package->getMeta())
      ? $package->getMeta()['namespaceSeparator']
      : '\\'
    );
    
  }
  
}
