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
  
  public static $dependencies = [];
  
  final public static function isActivated()
  {
    
    return (isset(static::$activated) && static::$activated === true);
    
  }
  
  final public static function getNormalizedDependencies()
  {
    
    $dependencies = static::$dependencies;
    
    $core = forall('core');
    
    array_walk($dependencies, function(&$val)use($core){
      $val = $core->normalizePackageName($val);
    });
    
    return $dependencies;
    
  }
  
}
