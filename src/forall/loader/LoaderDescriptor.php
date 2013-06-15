<?php

/**
 * @package forall.loader
 * @author Avaq <aldwin.vlasblom@gmail.com>
 */
namespace forall\loader;

/**
 * Loader descriptor class.
 */
class LoaderDescriptor
{
  
  /**
   * The full name of the Loader class.
   * @var string
   */
  public $className;
  
  /**
   * The name of the package.
   * @var string
   */
  public $packageName;
  
  /**
   * Create an instance and set the parameters.
   *
   * @param string $className   {@see self::$className}
   * @param string $packageName {@see self::$packageName}
   */
  public function __construct($className, $packageName)
  {
    
    $this->className = $className;
    $this->packageName = $packageName;
    
  }
  
}
