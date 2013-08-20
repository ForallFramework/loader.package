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
   * Does the loading sequence.
   *
   * @return self Chaining enabled.
   */
  public function activateLoaders()
  {
    
    //Get the system logger.
    $logger = forall('core')->getSystemLogger();
    
    //Find loaders.
    $descriptors = $this->findLoaders();
    
    //If no loaders are found, we are done.
    if(empty($descriptors)){
      $logger->info('No package loaders found. Nothing loaded by forall.loader.');
      return $this;
    }
    
    //Get rid of already activated loaders.
    $descriptors = $this->cleanLoaders($descriptors);
    
    //If no loaders are found, we are done.
    if(empty($descriptors)){
      $logger->info('No new package loaders found. Nothing new loaded by forall.loader.');
      return $this;
    }
    
    //Sort the loaders based on load-order.
    $descriptors = $this->resolveLoadOrder($descriptors);
    
    //Iterate the descriptors and map their loaders.
    $loaders = [];
    foreach($descriptors as $descriptor){
      $loaders[] = $descriptor->className;
    }
    
    //Iterate the loaders and execute their preLoad methods.
    foreach($loaders as $loader){
      $logger->debug(sprintf('Calling preLoad for %s.', $loader));
      $loader::preLoad();
    }
    
    //Iterate the loaders and execute their load methods.
    foreach($loaders as $loader){
      $loader::load();
    }
    
    //Iterate the loaders and execute their postLoad methods.
    foreach($loaders as $loader){
      $loader::postLoad();
    }
    
    //Enable chaining.
    return $this;
    
  }
  
  /**
   * Find and return Loader classes.
   *
   * Returns an array LoaderDescriptors containing info about classes extending AbstractLoader.
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
      if(class_exists($className) && is_subclass_of($className, 'forall\\loader\\AbstractLoader')){
        $result[] = new LoaderDescriptor($className, $name);
      }
      
    });
    
    //Return the result.
    return $result;
    
  }
  
  /**
   * Removes already loaded loaders from the given array of loader descriptors.
   *
   * @param  LoaderDescriptor[]  $descriptors The array of loader descriptors.
   *
   * @return LoaderDescriptor[]               The cleaned array of loader descriptors.
   */
  public function cleanLoaders(array $descriptors)
  {
    
    //Create the result.
    $result = [];
    
    //Iterate the given loaders.
    foreach($descriptors as $descriptor){
      
      //Get the loader.
      $loader = $descriptor->className;
      
      //Skip already loaded loaders.
      if($loader::isActivated()){
        continue;
      }
      
      //Append the loader to the result.
      $result[] = $descriptor;
      
    }
    
    //Return the result.
    return $result;
    
  }
  
  /**
   * Resolves the load-order for the given array of loader descriptors.
   *
   * @param  LoaderDescriptor[]  $descriptors The array of loader descriptors.
   *
   * @return LoaderDescriptor[]               The sorted array of loader descriptors.
   */
  public function resolveLoadOrder(array $descriptors)
  {
    
    //Get the instance of Core.
    $core = forall('core');
    
    //Attempt to resolve the load order.
    $success = usort($descriptors, function($a, $b)use($core){
      
      //Get the package name of A.
      $package = $core->normalizePackageName($a->packageName);
      
      //Get the dependencies of B.
      $classB = $b->className;
      $dependencies = $classB::getNormalizedDependencies();
      
      //Check if A is a dependency of B, and if it is, sort it before B.
      return (in_array($package, $dependencies) ? -1 : 1);
      
    });
    
    //Make sure it worked.
    if(!$success){
      throw new LoaderException(
        'The load-order of entry points could not be resolved. Possible circular dependency.'
      );
    }
    
    //Return the sorted array.
    return $descriptors;
    
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
