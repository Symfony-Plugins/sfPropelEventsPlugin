<?php

require_once dirname(__FILE__).'/../../lib/behavior/sfPropelEventsBehavior.class.php';

class sfPropelEventsBehaviorTest extends sfPropelEventsBehavior
{
  public static function getStack()
  {
    return self::$behaviors;
  }
}
