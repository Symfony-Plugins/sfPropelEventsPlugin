<?php

if (!class_exists('SfObjectBuilder'))
{
  require_once sfConfig::get('sf_symfony_lib_dir').'/addon/propel/builder/SfObjectBuilder.php';
}

/**
 * Add events to Propel OM classes.
 * 
 * @package     sfPropelEventsPlugin
 * @subpackage  builder
 * @author      Kris Wallsmith <kris [dot] wallsmith [at] gmail [dot] com>
 * @version     SVN: $Id$
 */
class SfPropelEventsObjectBuilder extends SfObjectBuilder
{
  /**
   * @see SfObjectBuilder
   */
  protected function addClassBody(& $script)
  {
    parent::addClassBody($script);
    
    if (DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      $this->addProcessEvent($script);
    }
  }
  
  /**
   * Add function to process modifications attached to sfPropelEvent objects.
   */
  protected function addProcessEvent(& $script)
  {
    $method = <<<EOF

  /**
   * Process any modifications attached to a sfPropelEvent object.
   * 
   * @param   sfPropelEvent \$event
   * 
   * @return  boolean Whether the event was processed
   */
  protected function processEvent(sfPropelEvent \$event)
  {
    if (\$event->isProcessed())
    {
      foreach (\$event->getModifications() as \$property => \$value)
      {
        \$this->\$property = \$value;
      }
      
      return true;
    }
    else
    {
      return false;
    }
  }

EOF;
    
    $script .= $method;
  }
  
  /**
   * Add dispatch of BaseXXX.pre_delete and BaseXXX.post_delete events.
   * 
   * @see SfObjectBuilder
   */
  protected function addDelete(& $script)
  {
    if (!DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      return parent::addDelete($script);
    }
    
    $tmp = '';
    parent::addDelete($tmp);
    
    // pre_delete
    $preDelete = <<<EOF

    \$arguments = func_get_args();
    \$dispatcher = sfPropelEventsToolkit::getEventDispatcher();
    \$event = \$dispatcher->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.pre_delete', array(
      'arguments'         => \$arguments,
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$this->processEvent(\$event) && \$event->getReturnValue())
    {
      return;
    }
EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$preDelete.substr($tmp, $pos);
    
    // post_delete
    $postDelete = <<<EOF

    \$dispatcher->notify(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.post_delete', array(
      'arguments'         => \$arguments,
      'modified_columns'  => \$this->modifiedColumns,
    )));
    
EOF;
    
    $pos = strrpos($tmp, 'foreach');
    $tmp = substr($tmp, 0, $pos).$postDelete.substr($tmp, $pos);
    
    $script .= $tmp;
  }
  
  /**
   * Add dispatch of BaseXXX.pre_save and BaseXXX.post_save events.
   * 
   * @see SfObjectBuilder
   */
  protected function addSave(& $script)
  {
    if (!DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      return parent::addSave($script);
    }
    
    $tmp = '';
    parent::addSave($tmp);
    
    // pre_save
    $preSave = <<<EOF

    \$arguments = func_get_args();
    \$dispatcher = sfPropelEventsToolkit::getEventDispatcher();
    \$wasNew = \$this->isNew();
    \$modifiedColumns = \$this->modifiedColumns;
    \$event = \$dispatcher->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.pre_save', array(
      'arguments'         => \$arguments,
      'modified_columns'  => \$modifiedColumns,
    )));
    if (\$this->processEvent(\$event) && is_int(\$event->getReturnValue()))
    {
      return \$event->getReturnValue();
    }
EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$preSave.substr($tmp, $pos);
    
    // post_save
    $postSave = <<<EOF

    \$dispatcher->notify(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.post_save', array(
      'arguments'         => \$arguments,
      'was_new'           => \$wasNew,
      'affected_rows'     => \$affectedRows,
      'modified_columns'  => \$modifiedColumns,
    )));
    
EOF;
    
    $pos = strrpos($tmp, 'foreach');
    $tmp = substr($tmp, 0, $pos).$postSave.substr($tmp, $pos);
    
    $script .= $tmp;
  }
  
  /**
   * @see PHP5ComplexObjectBuilder
   */
  protected function addDoSave(& $script)
	{
	  $tmp = '';
	  parent::addDoSave($tmp);
	  
	  $tmp = preg_replace('/if \(\$this\-\>(a\w+)\-\>isModified\(\)\)/', 'if ($this->\\1->isModified() || $this->\\1->isNew())', $tmp);
	  
	  $script .= $tmp;
	}
  
  /**
   * Add dispatch of BaseXXX.method_not_found event.
   * 
   * @see SfObjectBuilder
   */
  protected function addCall(& $script)
  {
    $tmp = '';
    parent::addCall($tmp);
    
    $format = '\'%s\' => $this->%1$s';
    
    $collections = array();
    $criteria = array();
    foreach ($this->getTable()->getReferrers() as $refFK)
    {
      $collections[] = sprintf($format, $this->getRefFKCollVarName($refFK));
      $criteria[] = sprintf($format, $this->getRefFKLastCriteriaVarName($refFK));
    }
    $collections = 'array('.join(', ', $collections).')';
    $criteria = 'array('.join(', ', $criteria).')';
    
    $fkObjects = array();
    foreach ($this->getTable()->getForeignKeys() as $fk)
    {
      $fkObjects[] = sprintf($format, $this->getFKVarName($fk));
    }
    $fkObjects = 'array('.join(', ', $fkObjects).')';
    
    $call = <<<EOF

    \$event = sfPropelEventsToolkit::getEventDispatcher()->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.method_not_found', array(
      'method'            => \$method,
      'arguments'         => \$arguments,
      'fk_objects'        => $fkObjects,
      'collections'       => $collections,
      'last_criteria'     => $criteria,
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$this->processEvent(\$event))
    {
      return \$event->getReturnValue();
    }

EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$call.substr($tmp, $pos);
    
    $script .= $tmp;
  }
  
  /**
   * Add dispatch of BaseXXX.set_fk event.
   * 
   * @see PHP5ComplexObjectBuilder
   */
  protected function addFKMutator(& $script, ForeignKey $fk)
  {
    if (!DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      return parent::addFKMutator($script, $fk);
    }
    
    $tmp = '';
    parent::addFKMutator($tmp, $fk);
    
    $localColumn = array_shift($fk->getLocalColumns());
    $localColumnUpper = strtoupper($localColumn);
    
    // set_fk
    $setFk = <<<EOF

    \$arguments = func_get_args();
    \$event = sfPropelEventsToolkit::getEventDispatcher()->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.set_fk', array(
      'method'            => __FUNCTION__,
      'arguments'         => \$arguments,
      'column'            => '$localColumn',
      'related_class'     => '{$this->getForeignTable($fk)->getPhpName()}',
      'in_object'         => \$this->{$this->getFKVarName($fk)},
      'in_object_var'     => '{$this->getFKVarName($fk)}',
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$this->processEvent(\$event) && (is_null(\$event->getReturnValue()) || \$event->getReturnValue() instanceof {$this->getForeignTable($fk)->getPhpName()}))
    {
      \$v = \$event->getReturnValue();
    }

EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$setFk.substr($tmp, $pos);
    
    $script .= $tmp;
  }
  
  /**
   * Add dispatch of BaseXXX.get_fk event.
   * 
   * @see PHP5ComplexObjectBuilder
   */
  protected function addFKAccessor(& $script, ForeignKey $fk)
  {
    if (!DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      return parent::addFKAccessor($script, $fk);
    }
    
    $tmp = '';
    parent::addFKAccessor($tmp, $fk);
    
    $localColumn = array_shift($fk->getLocalColumns());
    
    // get_fk
    $getFk = <<<EOF

    \$arguments = func_get_args();
    \$event = sfPropelEventsToolkit::getEventDispatcher()->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.get_fk', array(
      'method'            => __FUNCTION__,
      'arguments'         => \$arguments,
      'column'            => '{$localColumn}',
      'related_class'     => '{$this->getForeignTable($fk)->getPhpName()}',
      'in_object'         => \$this->{$this->getFKVarName($fk)},
      'in_object_var'     => '{$this->getFKVarName($fk)}',
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$this->processEvent(\$event) && (is_null(\$event->getReturnValue()) || \$event->getReturnValue() instanceof {$this->getForeignTable($fk)->getPhpName()}))
    {
      return \$event->getReturnValue();
    }

EOF;

    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$getFk.substr($tmp, $pos);

    $script .= $tmp;
  }
  
  /**
   * Add dispatch of BaseXXX.get_fks_join event.
   * 
   * @see PHP5ComplexObjectBuilder
   */
  protected function addRefFKGetJoinMethods(& $script, ForeignKey $refFK)
  {
    if (!DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      return parent::addRefFKGetJoinMethods($script, $refFK);
    }
    
    $tmp = '';
    parent::addRefFKGetJoinMethods($tmp, $refFK);
    
    $getFksJoin = <<<EOF

    \$arguments = func_get_args();
    \$event = sfPropelEventsToolkit::getEventDispatcher()->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.get_fks_join', array(
      'method'            => __FUNCTION__,
      'arguments'         => \$arguments,
      'middle_class'      => '{$refFK->getTable()->getPhpName()}',
      'related_class'     => '%s',
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'in_object_var'     => '{$this->getRefFKCollVarName($refFK)}',
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$this->processEvent(\$event) && is_array(\$event->getReturnValue()))
    {
      return \$event->getReturnValue();
    }

EOF;
    
    $relCol = $this->getRefFKPhpNameAffix($refFK, $plural = true);
    foreach ($refFK->getTable()->getForeignKeys() as $fk2)
    {
      $method = sprintf('get%sJoin%s', $relCol, $this->getFKPhpNameAffix($fk2, $plural = false));
      
      $pos = strpos($tmp, $method);
      if (false !== $pos)
      {
        $pos = $pos + strpos(substr($tmp, $pos), '{') + 1;
        $insert = sprintf($getFksJoin, $this->getForeignTable($fk2)->getPhpName());
        
        $tmp = substr($tmp, 0, $pos).$insert.substr($tmp, $pos);
      }
    }
    
    $script .= $tmp;
  }
  
  /**
   * Add dispatch of BaseXXX.init_fk_coll
   * 
   * @see PHP5ComplexObjectBuilder
   */
  protected function addRefFKInit(& $script, ForeignKey $refFK)
  {
    if (!DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      return parent::addRefFKInit($script, $refFK);
    }
    
    $tmp = '';
    parent::addRefFKInit($tmp, $refFK);
    
    // init_fk_coll
    $initFkColl = <<<EOF

    \$arguments = func_get_args();
    \$event = sfPropelEventsToolkit::getEventDispatcher()->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.init_fk_coll', array(
      'method'            => __FUNCTION__,
      'arguments'         => \$arguments,
      'related_class'     => '{$refFK->getTable()->getPhpName()}',
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'in_object_var'     => '{$this->getRefFKCollVarName($refFK)}',
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$this->processEvent(\$event) && is_array(\$event->getReturnValue()))
    {
      \$this->{$this->getRefFKCollVarName($refFK)} = \$event->getReturnValue();
      return;
    }

EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$initFkColl.substr($tmp, $pos);
    
    $script .= $tmp;
  }
  
  /**
   * Add dispatch of BaseXXX.add_fk event.
   * 
   * @see PHP5ComplexObjectBuilder
   */
  protected function addRefFKAdd(& $script, ForeignKey $refFK)
  {
    if (!DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      return parent::addRefFKAdd($script, $refFK);
    }
    
    $tmp = '';
    parent::addRefFKAdd($tmp, $refFK);
    
    // add_fk
    $addFk = <<<EOF

    \$arguments = func_get_args();
    \$event = sfPropelEventsToolkit::getEventDispatcher()->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.add_fk', array(
      'method'            => __FUNCTION__,
      'arguments'         => \$arguments,
      'related_class'     => '{$refFK->getTable()->getPhpName()}',
      'related_setter'    => 'set{$this->getFKPhpNameAffix($refFK, $plural = false)}',
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'in_object_var'     => '{$this->getRefFKCollVarName($refFK)}',
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$this->processEvent(\$event) && \$event->getReturnValue())
    {
      return;
    }

EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$addFk.substr($tmp, $pos);
    
    $script .= $tmp;
  }
  
  /**
   * Add dispatch of BaseXXX.count_fks event.
   * 
   * @see PHP5ComplexObjectBuilder
   */
  protected function addRefFKCount(& $script, ForeignKey $refFK)
  {
    if (!DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      return parent::addRefFKCount($script, $refFK);
    }
    
    $tmp = '';
    parent::addRefFKCount($tmp, $refFK);
    
    // count_fks
    $countFks = <<<EOF

    \$arguments = func_get_args();
    \$event = sfPropelEventsToolkit::getEventDispatcher()->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.count_fks', array(
      'method'            => __FUNCTION__,
      'arguments'         => \$arguments,
      'related_class'     => '{$refFK->getTable()->getPhpName()}',
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'in_object_var'     => '{$this->getRefFKCollVarName($refFK)}',
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$this->processEvent(\$event) && is_int(\$event->getReturnValue()))
    {
      return \$event->getReturnValue();
    }

EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$countFks.substr($tmp, $pos);
    
    $script .= $tmp;
  }
  
  /**
   * Add dispatch of BaseXXX.get_fks event.
   * 
   * @see PHP5ComplexObjectBuilder
   */
  protected function addRefFKGet(& $script, ForeignKey $refFK)
  {
    if (!DataModelBuilder::getBuildProperty('builderAddBehaviors'))
    {
      return parent::addRefFKGet($script, $refFK);
    }
    
    $tmp = '';
    parent::addRefFKGet($tmp, $refFK);
    
    // get_fks
    $getFks = <<<EOF

    \$arguments = func_get_args();
    \$event = sfPropelEventsToolkit::getEventDispatcher()->notifyUntil(new sfPropelEvent(\$this, 'Base{$this->getTable()->getPhpName()}.get_fks', array(
      'method'            => __FUNCTION__,
      'arguments'         => \$arguments,
      'related_class'     => '{$refFK->getTable()->getPhpName()}',
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'in_object_var'     => '{$this->getRefFKCollVarName($refFK)}',
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$this->processEvent(\$event) && is_array(\$event->getReturnValue()))
    {
      return \$event->getReturnValue();
    }

EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$getFks.substr($tmp, $pos);
    
    $script .= $tmp;
  }
}
