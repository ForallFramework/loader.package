<?php

/**
 * @package forall.loader
 * @author Avaq <aldwin.vlasblom@gmail.com>
 */
namespace forall\loader;

use \Closure;

/**
 * ClassLoader class.
 */
class ClassLoader
{
  
  /**
   * Contains the file extension that is appended to the class name automatically.
   * @var string
   */
  private $fileExtension = '.php';
  
  /**
   * Contains the namespace separator for compatibility with underscores and the likes.
   * @var string
   */
  private $namespaceSeparator = '\\';
  
  /**
   * Contains the base name space to check for. Is set by the constructor.
   * @var string
   */
  private $namespace;
  
  /**
   * Contains the base include directory. Can be set by the constructor.
   * @var string
   */
  private $includePath;
  
  
  /**
   * A container for callbacks that will get called every time a class is auto-loaded.
   * @var Closure[]
   */
  private $callbacks = [];

  /**
   * @param string $namespace The namespace to use.
   * @param string $includePath The include path.
   * 
   * @see ClassLoader::$namespace for more information on the namespace.
   * @see ClassLoader::$includePath for more information on the include path.
   */
  public function __construct($namespace = null, $includePath = null)
  {
    
    //Set properties.
    $this->namespace = $namespace;
    $this->includePath = $includePath;
    
  }

  /**
   * Setter for the namespace separator.
   * 
   * @param string $separator The separator to use.
   * 
   * @see ClassLoader::$namespaceSeparator for more information.
   * 
   * @return self Chaining enabled.
   */
  public function setNamespaceSeparator($separator)
  {
    
    //Set the property.
    $this->namespaceSeparator = $separator;
    
    //Enable chaining.
    return $this;
    
  }

  /**
   * Getter for the namespace separator.
   * 
   * @see ClassLoader::$namespaceSeparator for more information.
   *
   * @return string The namespace separator used.
   */
  public function getNamespaceSeparator()
  {
    
    return $this->namespaceSeparator;
    
  }

  /**
   * Setter for the include path.
   * 
   * @see ClassLoader::$includePath for more information.
   * 
   * @param string $includePath
   * 
   * @return self Chaining enabled.
   */
  public function setIncludePath($includePath)
  {
    
    //Set the property.
    $this->includePath = $includePath;
    
    //Enable chaining.
    return $this;
    
  }

  /**
   * Setter for the include path.
   * 
   * @see ClassLoader::$includePath for more information.
   * 
   * @return string The include path used.
   */
  public function getIncludePath()
  {
    
    return $this->includePath;
    
  }

  /**
   * Setter for the file extension.
   * 
   * @see ClassLoader::$fileExtension for more information.
   * 
   * @param string $fileExtension
   * 
   * @return self Chaining enabled.
   */
  public function setFileExtension($fileExtension)
  {
    
    //Set the property.
    $this->fileExtension = $fileExtension;
    
    //Enable chaining.
    return $this;
    
  }

  /**
   * Getter for the file extension.
   * 
   * @see ClassLoader::$fileExtension for more information.
   * 
   * @return string The file extension used.
   */
  public function getFileExtension()
  {
    
    return $this->fileExtension;
    
  }
  
  /**
   * Adds a callback to our stack of callbacks that are called when a class is successfully auto-loaded.
   *
   * @param  Closure $callback
   *         The closure will receive 3 parameters. The instance of the ClassLoader
   *         calling it, the full name of the class and the file that was found for it.
   *
   * @return self            Chaining enabled.
   */
  public function onLoad(Closure $callback)
  {
    
    //Store in callbacks.
    $this->callbacks[] = $callback;
    
    //Enable chaining.
    return $this;
    
  }
  
  /**
   * Registers this ClassLoader with the SPL auto-load stack.
   *
   * @return bool Whether the registry succeeded.
   */
  public function register()
  {
    
    return spl_autoload_register([$this, 'loadClass']);
    
  }
  
  /**
   * Unregisters this ClassLoader from the SPL auto-load stack.
   *
   * @return bool Whether the unregistry succeeded.
   */
  public function unregister()
  {
    
    return spl_autoload_unregister([$this, 'loadClass']);
    
  }
  
  /**
   * Loads the given class, trait or interface according to FIG standards described in PSR-0.
   * 
   * @param  string $className The full name (including namespace) of the class to load.
   *
   * @return void
   */
  public function loadClass($className)
  {
    
    //Fail to load the class when the namespace does not match what we're looking for.
    if(is_string($this->namespace) && strpos($className, $this->namespace.$this->namespaceSeparator) !== 0){
      return;
    }
    
    //Prepare the file name for concatenation.
    $fileName = '';
    
    //If the class name has a separator in it, translate that to a file structure.
    if($lastNsPos = strripos($className, $this->namespaceSeparator)){
      $namespace = substr($className, 0, $lastNsPos);
      $className = substr($className, $lastNsPos + 1);
      $fileName = str_replace($this->namespaceSeparator, DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
    }
    
    //Figure out an include path.
    $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className).$this->fileExtension;
    $filePath = (is_string($this->includePath) ? $this->includePath.DIRECTORY_SEPARATOR : '');
    $include = "$filePath$fileName";
    
    //Include the file.
    require $include;
    
    //The included file must contain the given class for the code to proceed.
    if(!class_exists($className)){
      return;
    }
    
    //Notify callbacks.
    foreach($this->callbacks as $callback){
      $callback($this, $className, $include);
    }
    
  }
  
}
