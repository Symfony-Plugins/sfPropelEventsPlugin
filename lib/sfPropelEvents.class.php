<?php

/**
 * Utility class for sfPropelEventsPlugin.
 * 
 * @package     sfPropelEventsPlugin
 * @subpackage  util
 * @author      Kris Wallsmith <kris [dot] wallsmith [at] gmail [dot] com>
 * @version     SVN: $Id$
 */
class sfPropelEvents
{
  protected static
    $behaviors  = array();
  
  /**
   * Add behaviors to a class.
   * 
   * @param   string $class A Propel OM class
   * @param   array $behaviors An array of behaviors and parameters
   */
  public static function addBehaviors($class, $behaviors)
  {
    $dispatcher = sfContext::getInstance()->getEventDispatcher();
    foreach ($behaviors as $name => $parameters)
    {
      if (is_int($name))
      {
        $name = $parameters;
      }
      
      // connect listeners
      if (isset(self::$behaviors[$name]))
      {
        foreach (self::$behaviors[$name] as $propelEvent => $listeners)
        {
          $dispatcher->notify(new sfEvent(__CLASS__, 'application.log', array(sprintf('connecting "%s" behavior "%s" event for "%s" objects', $name, $propelEvent, $class))));
          
          foreach ($listeners as $listener)
          {
            $dispatcher->connect('Base'.$class.'.'.$propelEvent, $listener);
          }
        }
      }
    }
  }
  
  /**
   * Register Propel event listeners.
   * 
   * @param   string $name Your behavior's name
   * @param   array $listeners An array of events and callables
   */
  public static function registerListeners($name, $listeners)
  {
    if (!isset(self::$behaviors[$name]))
    {
      self::$behaviors[$name] = array();
      
      // make sure this behavior is also registered in sfPropelBehavior
      sfPropelBehavior::registerMethods($name, array(
        array('sfPropelEvents', 'sfPropelEventsEmptyFunction'),
      ));
    }
    
    foreach ($listeners as $propelEvent => $callables)
    {
      sfContext::getInstance()->getEventDispatcher()->notify(
        new sfEvent(__CLASS__, 'application.log', array(sprintf('registering listener for "%s" Propel event for "%s" behavior', $propelEvent, $name))));
      
      if (!isset(self::$behaviors[$name][$propelEvent]))
      {
        self::$behaviors[$name][$propelEvent] = array();
      }
      
      foreach ($callables as $callable)
      {
        self::$behaviors[$name][$propelEvent][] = $callable;
      }
    }
  }
  
  /**
   * An empty function.
   */
  public function sfPropelEventsEmptyFunction()
  {
  }
}
