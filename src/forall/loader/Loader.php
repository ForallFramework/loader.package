<?php

/**
 * @package forall.loader
 * @author Avaq <aldwin.vlasblom@gmail.com>
 */
namespace forall\loader;

use \forall\core\core\AbstractCore;
use \forall\core\singleton\SingletonTraits;

/**
 * The loader class.
 */
class Loader extends AbstractCore
{
  
  use SingletonTraits;
  
  /**
   * Set up auto-loading.
   *
   * @return void
   */
  public function init()
  {
    
    //Reference the core.
    $core = forall('core');
    
    //Set our descriptor.
    $this->setDescriptor($core->createPackageDescriptor('loader'));
    
  }
  
  /**
   * Find and return Loader classes.
   * 
   * Returns an array of strings that represent the names of classes extending AbstractLoader.
   *
   * @return LoaderDescriptor[]
   */
  public function findLoaders()
  {
    
    //Get the instance of Core.
    $core = forall('core');
    
    //Iterate packages and create the result array.
    $core->iteratePackages(function($name)use($core, &$result){
      
      //Get the name space.
      $ns = $core->convertPackageNameToNs($name);
      
      //Make the predicted Loader name.
      $className = "$ns\\Loader";
      
      //Append to the result if the class exists.
      if(class_exists($className) && is_subclass_of('forall\\loader\\AbstractLoader', $className)){
        $result[] = new LoaderDescriptor($className, $name);
      }
      
    });
    
    //Return the result.
    return $result;
    
  }
  
  /**
   * Does the loading sequence.
   *
   * @return self Chaining enabled.
   */
  public function activateLoaders()
  {
    
    //Find loaders.
    $loaders = $this->findLoaders();
    
    //If no loaders are found, we are done.
    if(empty($loaders)){
      return $this;
    }
    
    $loaders = $this->cleanLoaders($loaders);
    
    $loaders = $this->resolveLoadOrder($loaders);
    
    var_dump($loaders);
    
  }
  
  /**
   * Removes already loaded loaders from the given array of loaders.
   *
   * @param  LoaderDescriptor[]  $loaders The array of loaders.
   *
   * @return LoaderDescriptor[]           The cleaned array of loaders.
   */
  public function cleanLoaders(array $loaders)
  {
    
    //Create the result.
    $result = [];
    
    //Iterate the given loaders.
    foreach($loaders as $loader){
      
      //Skip already loaded loaders.
      if($loader::isActivated()){
        continue;
      }
      
      //Append the loader to the result.
      $result[] = $loader;
      
    }
    
    //Return the result.
    return $result;
    
  }
  
  /**
   * Resolves the load-order for the given array of loaders.
   *
   * @param  LoaderDescriptor[]  $loaders The array of loaders.
   *
   * @return LoaderDescriptor[]           The sorted array of loaders.
   */
  public function resolveLoadOrder(array $loaders)
  {
    
    //Get the instance of Core.
    $core = forall('core');
    
    //Attempt to resolve the load order.
    $success = usort($loaders, function($a, $b)use($core){
      
      //Get the package name of A.
      $package = $core->normalizePackageName($a->packageName);
      
      //Get the dependencies of B.
      $classB = $b->className;
      $dependencies = $classB::getNormalizedDependencies();
      
      //Check if A is a dependency of B, and if it is, sort it before B.
      return (in_array($package, $dependencies) ? -1 : 1);
      
    });
    
    var_dump($loaders);
    
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
  
}
