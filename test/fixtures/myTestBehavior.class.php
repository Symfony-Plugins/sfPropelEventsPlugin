<?php

class myTestBehavior
{
  public static function listenForPreDelete(sfEvent $event)
  {
    $event->setReturnValue(true);
    
    return true;
  }
  
  public static function listenForPostDelete(sfEvent $event)
  {
    sfContext::getInstance()->getRequest()->setAttribute('post_delete', true);
  }
  
  public static function listenForPreSave(sfEvent $event)
  {
    $event->setReturnValue(1);
    
    return true;
  }
  
  public static function listenForPostSave(sfEvent $event)
  {
    sfContext::getInstance()->getRequest()->setAttribute('post_save', true);
  }
  
  public static function listenForMethodNotFound(sfEvent $event)
  {
    $event->setReturnValue($event->getName());
    
    return true;
  }
  
  public static function listenForSetFK(sfEvent $event)
  {
    $event->setReturnValue(null);
    
    return true;
  }
  
  public static function listenForGetFK(sfEvent $event)
  {
    $parameters = $event->getParameters();
    $class = $parameters['related_class'];
    $event->setReturnValue(new $class);
    
    return true;
  }
  
  public static function listenForGetFKsJoin(sfEvent $event)
  {
    $event->setReturnValue(array(1));
    
    return true;
  }
  
  public static function listenForInitFKColl(sfEvent $event)
  {
    $event->setReturnValue(array(1));
    
    return true;
  }
  
  public static function listenForAddFK(sfEvent $event)
  {
    return true;
  }
  
  public static function listenForCountFKs(sfEvent $event)
  {
    $event->setReturnValue(37);
    
    return true;
  }
  
  public static function listenForGetFKs(sfEvent $event)
  {
    $event->setReturnValue(array(1));
    
    return true;
  }
}

sfPropelEvents::registerListeners('test_behavior', array(
  'pre_delete' => array(
    array('myTestBehavior', 'listenForPreDelete'),
  ),
  'post_delete' => array(
    array('myTestBehavior', 'listenForPostDelete'),
  ),
  'pre_save' => array(
    array('myTestBehavior', 'listenForPreSave'),
  ),
  'post_save' => array(
    array('myTestBehavior', 'listenForPostSave'),
  ),
  'method_not_found' => array(
    array('myTestBehavior', 'listenForMethodNotFound'),
  ),
  'set_fk' => array(
    array('myTestBehavior', 'listenForSetFK'),
  ),
  'get_fk' => array(
    array('myTestBehavior', 'listenForGetFK'),
  ),
  'get_fks_join' => array(
    array('myTestBehavior', 'listenForGetFKsJoin'),
  ),
  'init_fk_coll' => array(
    array('myTestBehavior', 'listenForInitFKColl'),
  ),
  'add_fk' => array(
    array('myTestBehavior', 'listenForAddFK'),
  ),
  'count_fks' => array(
    array('myTestBehavior', 'listenForCountFKs'),
  ),
  'get_fks' => array(
    array('myTestBehavior', 'listenForGetFKs'),
  ),
));
