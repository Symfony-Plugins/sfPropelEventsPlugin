<?php

$nb = 40;

require dirname(__FILE__).'/../bootstrap/unit.php';

$t->diag('->registerListeners()');

require dirname(__FILE__).'/../fixtures/sfPropelEventsTest.class.php';
require dirname(__FILE__).'/../fixtures/myTestBehavior.class.php';

$stack = sfPropelEventsTest::getStack();
$t->ok(isset($stack['test_behavior']), 'stack includes test behavior');
$t->ok(isset($stack['test_behavior']['method_not_found']), 'stack includes method_not_found event');
$t->ok(in_array(array('myTestBehavior', 'listenForMethodNotFound'), $stack['test_behavior']['method_not_found']), 'stack includes callable');

$t->diag('->addBehaviors()');
sfPropelEventsTest::addBehaviors('Item', array(
  'test_behavior' => array('foo' => 'bar'),
));
