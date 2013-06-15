<?php

/**
 * @package forall.loader
 * @author Avaq <aldwin.vlasblom@gmail.com>
 */
namespace forall\loader;

/**
 * Loader abstraction.
 */
abstract class AbstractLoader
{
  
  final public static function isActivated()
  {
    
    return (isset(static::$activated) && static::$activated === true);
    
  }
  
  /**
   * Should return an array of package names whose load methods should be called before this one's.
   *
   * @return string[] An array of package names.
   */
  abstract public static function getDependencies();
  
  /**
   * Return an array of normalized dependency names.
   * 
   * Returns the result of `getDependencies()` where every dependency name is first
   * normalized by `Core::normalizePackageName()`.
   *
   * @return array
   */
  final public static function getNormalizedDependencies()
  {
    
    $dependencies = static::getDependencies();
    
    $core = forall('core');
    
    array_walk($dependencies, function(&$val)use($core){
      $val = $core->normalizePackageName($val);
    });
    
    return $dependencies;
    
  }
  
  /**
   * No-op. Optional to implement.
   * 
   * When implemented, gets called before any of the packages' actual load methods are called.
   *
   * @return void Should return void.
   */
  public static function preLoad()
  {
    
  }
  
  /**
   * Should execute initializing logic.
   *
   * @return void Should return void.
   */
  abstract public static function load();
  
  /**
   * No-op. Optional to implement.
   * 
   * When implemented, gets called after all of the packages' actual load methods are called.
   *
   * @return void Should return void.
   */
  public static function postLoad()
  {
    
  }
  
}
