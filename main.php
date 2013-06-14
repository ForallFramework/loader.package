<?php namespace forall\loader;

//After "native" core loading is done, we will start our own stuff.
$core->onMainFilesIncluded(function($core){
  
  //Get the loader instance.
  $loader = Loader::getInstance();
  
  //Register the instance with the core.
  $core->registerInstance('loader', $loader);
  
  //Initialize.
  $core->initializeInstance($loader);
  
  //Execute the loading sequence.
  $loader->activateLoaders();
  
});
