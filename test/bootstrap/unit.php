<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/..');

require_once(dirname(__FILE__).'/../../../../config/ProjectConfiguration.class.php');
$configuration = new ProjectConfiguration(realpath($_test_dir.'/../../..'));
include($configuration->getSymfonyLibDir().'/vendor/lime/lime.php');

$t = new lime_test($nb, new lime_output_color);

class sfContext
{
  public static function getInstance()
  {
    return new sfContext;
  }
  
  public function getEventDispatcher()
  {
    return new sfEventDispatcher;
  }
}

class sfEventDispatcher
{
  public function connect()
  {
    global $t;
    
    $t->pass('called sfEventDispatcher::connect()');
  }
  
  public function notify()
  {
    global $t;
    
    $t->pass('called sfEventDispatcher::notify()');
  }
}

class sfPropelBehavior
{
  public static function registerMethods()
  {
    global $t;
    
    $t->pass('called sfPropelBehavior::registerMethods()');
  }
}
