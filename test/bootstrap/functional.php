<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// guess current application
if (!isset($app))
{
  $traces = debug_backtrace();
  $caller = $traces[0];

  $dirPieces = explode(DIRECTORY_SEPARATOR, dirname($caller['file']));
  $app = array_pop($dirPieces);
}

$_test_dir = realpath(dirname(__FILE__).'/..');
$_root_dir = realpath(file_exists($_test_dir.'/../symfony') ? ($_test_dir.'/..') : ($_test_dir.'/../../..'));

if (false !== strpos(file_get_contents($_root_dir.'/symfony'), 'ProjectConfiguration'))
{
  // symfony 1.1 bootstrap
  require_once $_root_dir.'/config/ProjectConfiguration.class.php';
  $configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
  sfContext::createInstance($configuration);
  
  sfToolkit::clearDirectory(sfConfig::get('sf_app_cache_dir'));
  
  $sf_symfony_lib_dir = $configuration->getSymfonyLibDir();
}
else
{
  // symfony 1.0 bootstrap
  define('SF_ROOT_DIR', $_root_dir);
  define('SF_APP', $app);
  define('SF_ENVIRONMENT', 'test');
  define('SF_DEBUG', isset($debug) ? $debug : true);
  
  require_once SF_ROOT_DIR.DIRECTORY_SEPARATOR.'apps'.DIRECTORY_SEPARATOR.SF_APP.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php';
  
  sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));
}

require_once $sf_symfony_lib_dir.'/vendor/lime/lime.php';
require_once dirname(__FILE__).'/../fixtures/sfPropelEventsBehaviorTest.class.php';
require_once dirname(__FILE__).'/../fixtures/myTestBehavior.class.php';
