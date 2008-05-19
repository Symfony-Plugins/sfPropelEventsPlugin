<?php

require_once dirname(__FILE__).'/../../lib/sfPropelEvents.class.php';

class sfPropelEventsTest extends sfPropelEvents
{
  public static function getStack()
  {
    return self::$behaviors;
  }
}
