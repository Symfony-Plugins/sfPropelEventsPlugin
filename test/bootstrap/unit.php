<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 * 
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$_test_dir = realpath(dirname(__FILE__).'/..');
$_root_dir = realpath(file_exists($_test_dir.'/../symfony') ? ($_test_dir.'/..') : ($_test_dir.'/../../..'));

if (false !== strpos(file_get_contents($_root_dir.'/symfony'), 'ProjectConfiguration'))
{
  // symfony 1.1 bootstrap
  require_once $_root_dir.'/config/ProjectConfiguration.class.php';
  $configuration = new ProjectConfiguration($_root_dir);
  $sf_symfony_lib_dir = $configuration->getSymfonyLibDir();
}
else
{
  // symfony 1.0 bootstrap
  define('SF_ROOT_DIR', $_root_dir);
  include SF_ROOT_DIR.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
}

require_once $sf_symfony_lib_dir.'/vendor/lime/lime.php';
require_once dirname(__FILE__).'/../../lib/util/sfPropelEventsToolkit.class.php';

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
  
  public static function add()
  {
    global $t;
    
    $t->pass('called sfPropelBehavior::add()');
  }
}
