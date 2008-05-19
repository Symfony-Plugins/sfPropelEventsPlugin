<?php

include dirname(__FILE__).'/../../bootstrap/functional.php';

// create a new test browser
$b = new sfTestBrowser();
$t = $b->test();

$b->getAndCheck('item', 'delete');

$b->getAndCheck('item', 'save');
$t->ok($b->getRequest()->getAttribute('isNew'), 'pre_save ok');

$b->getAndCheck('item', 'methodNotFound');
$t->is($b->getRequest()->getAttribute('returnValue'), 'BaseItem.method_not_found', 'method_not_found ok');

$b->getAndCheck('item', 'setFk');
$t->is($b->getRequest()->getAttribute('person_id'), null, 'set_fk ok');

$b->getAndCheck('item', 'getFk');
$t->isa_ok($b->getRequest()->getAttribute('person'), 'Person', 'get_fk ok');

$b->getAndCheck('item', 'getFksJoin');
$t->is_deeply($b->getRequest()->getAttribute('taggings'), array(1), 'get_fks_join ok');

$b->getAndCheck('item', 'initFkColl');
$t->todo('confirm init_fk_coll');

$b->getAndCheck('item', 'addFk');
$t->todo('confirm add_fk');

$b->getAndCheck('item', 'countFks');
$t->is($b->getRequest()->getAttribute('count'), 37, 'count_fks return value ok');

$b->getAndCheck('item', 'getFks');
$t->is_deeply($b->getRequest()->getAttribute('coll'), array(1), 'get_fks ok');
