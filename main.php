<?php namespace forall\loader;

//After "native" core loading is done, we will start our own stuff.
$core->onMainFilesIncluded(function($core){
  
  //Include our loader.
  require_once __DIR__."/src/Loader.php";
  
  //Get the loader instance.
  $loader = Loader::getInstance();
  
  //Register the instance with the core.
  $core->registerInstance('loader', $loader);
  
  //Initialize (takes care of auto loading).
  $core->initializeInstance($loader);
  
});
