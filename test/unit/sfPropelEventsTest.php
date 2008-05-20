<?php

$nb = 19;

require dirname(__FILE__).'/../bootstrap/unit.php';

$t->diag('->registerListeners()');

require dirname(__FILE__).'/../fixtures/sfPropelEventsTest.class.php';
require dirname(__FILE__).'/../fixtures/myTestBehavior.class.php';

$stack = sfPropelEventsTest::getStack();
$t->ok(isset($stack['test_behavior']), 'stack includes test behavior');
$t->ok(isset($stack['test_behavior']['method_not_found']), 'stack includes method_not_found event');
$t->ok(in_array(array('myTestBehavior', 'listenForMethodNotFound'), $stack['test_behavior']['method_not_found']), 'stack includes callable');

$t->diag('->hasBehaviors(), ->addBehaviors()');
$t->ok(!sfPropelEventsTest::hasBehavior('Item', 'test_behavior'), 'returns false if behavior has not been added');
sfPropelEventsTest::addBehaviors('Item', array(
  'test_behavior' => array('foo' => 'bar'),
));
$t->ok(sfPropelEventsTest::hasBehavior('Item', 'test_behavior'), 'returns true if behavior has been added');
