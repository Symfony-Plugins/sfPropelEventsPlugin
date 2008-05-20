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
    $dispatcher     = null,
    $behaviors      = array(),
    $addedBehaviors = array();
  
  /**
   * Add behaviors to a class.
   * 
   * @param   string $class A Propel OM class
   * @param   array $behaviors An array of behaviors and parameters
   */
  public static function addBehaviors($class, $behaviors)
  {
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
          foreach ($listeners as $listener)
          {
            self::getEventDispatcher()->connect('Base'.$class.'.'.$propelEvent, $listener);
          }
        }
      }
      
      // remember this class/behavior combo
      if (!isset(self::$addedBehaviors[$class]))
      {
        self::$addedBehaviors[$class] = array();
      }
      self::$addedBehaviors[$class][] = $name;
    }
    
    // add to sfPropelBehavior
    sfPropelBehavior::add($class, $behaviors);
  }
  
  /**
   * Test whether a class has added an event behavior.
   * 
   * @param   string $class
   * @param   string $behavior
   * 
   * @return  boolean
   */
  public static function hasBehavior($class, $behavior)
  {
    return isset(self::$addedBehaviors[$class]) && in_array($behavior, self::$addedBehaviors[$class]);
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
  
  /**
   * Get the event dispatcher.
   * 
   * @return  sfEventDispatcher
   */
  public static function getEventDispatcher()
  {
    if (is_null(self::$dispatcher))
    {
      $context = sfContext::getInstance();
      if (method_exists($context, 'getEventDispatcher'))
      {
        self::$dispatcher = $context->getEventDispatcher();
      }
      else
      {
        self::$dispatcher = new sfEventDispatcher;
      }
    }
    
    return self::$dispatcher;
  }
}
