<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

// create a new test browser
$b = new sfTestBrowser;
if (is_null($t = $b->test()))
{
  $b->initialize();
  $t = $b->test();
}

$context = sfContext::getInstance();

$rcEvent = new ReflectionClass('sfEvent');
$rcDispatcher = new ReflectionClass('sfEventDispatcher');

if (method_exists($context, 'getConfiguration'))
{
  $libDir = $context->getConfiguration()->getSymfonyLibDir();
  
  $t->diag('symfony 1.1');
}
else
{
  $libDir = realpath(dirname(__FILE__).'/../../../lib');
  
  $t->diag('symfony 1.0');
}

$t->ok(0 === strpos($rcEvent->getFileName(), $libDir), 'sfEvent autoloaded ok');
$t->ok(0 === strpos($rcDispatcher->getFileName(), $libDir), 'sfEventDispatcher autoloaded ok');
