<?php

/**
 * Utility class for sfPropelEventsPlugin.
 * 
 * @package     sfPropelEventsPlugin
 * @subpackage  util
 * @author      Kris Wallsmith <kris [dot] wallsmith [at] gmail [dot] com>
 * @version     SVN: $Id$
 */
class sfPropelEventsToolkit
{
  protected static
    $dispatcher = null;
  
  /**
   * Get the event dispatcher.
   * 
   * @return  sfEventDispatcher
   */
  public static function getEventDispatcher()
  {
    if (is_null(self::$dispatcher))
    {
      if (class_exists('ProjectConfiguration'))
      {
        self::$dispatcher = ProjectConfiguration::getActive()->getEventDispatcher();
      }
      else
      {
        self::$dispatcher = new sfEventDispatcher;
      }
    }
    
    return self::$dispatcher;
  }
  
  /**
   * An empty function.
   */
  public function sfPropelEventsEmptyFunction()
  {
  }
}
