<?php

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

    \$dispatcher = sfContext::getInstance()->getEventDispatcher();
    \$event = \$dispatcher->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.pre_delete', array(
      'connection'        => \$con,
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$event->isProcessed() && \$event->getReturnValue())
    {
      return;
    }
EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$preDelete.substr($tmp, $pos);
    
    // post_delete
    $postDelete = <<<EOF

    \$dispatcher->notify(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.post_delete', array(
      'connection'        => \$con,
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

    \$dispatcher = sfContext::getInstance()->getEventDispatcher();
    \$wasNew = \$this->isNew();
    \$modifiedColumns = \$this->modifiedColumns;
    \$event = \$dispatcher->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.pre_save', array(
      'connection'        => \$con,
      'modified_columns'  => \$modifiedColumns,
    )));
    if (\$event->isProcessed() && is_int(\$event->getReturnValue()))
    {
      return \$event->getReturnValue();
    }
EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$preSave.substr($tmp, $pos);
    
    // post_save
    $postSave = <<<EOF

    \$dispatcher->notify(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.post_save', array(
      'connection'        => \$con,
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
   * Add dispatch of BaseXXX.method_not_found event.
   * 
   * @see SfObjectBuilder
   */
  protected function addCall(& $script)
  {
    $tmp = '';
    parent::addCall($tmp);
    
    $call = <<<EOF

    \$event = sfContext::getInstance()->getEventDispatcher()->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.method_not_found', array(
      'method'            => \$method,
      'arguments'         => \$arguments,
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$event->isProcessed())
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
    
    // set_fk
    $setFk = <<<EOF

    \$event = sfContext::getInstance()->getEventDispatcher()->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.set_fk', array(
      'column'            => '$localColumn',
      'related_class'     => '{$this->getForeignTable($fk)->getPhpName()}',
      'old_value'         => \$this->{$this->getFKVarName($fk)},
      'new_value'         => \$v,
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$event->isProcessed() && (is_null(\$event->getReturnValue()) || \$event->getReturnValue() instanceof {$this->getForeignTable($fk)->getPhpName()}))
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

    \$event = sfContext::getInstance()->getEventDispatcher()->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.get_fk', array(
      'connection'        => \$con,
      'column'            => '{$localColumn}',
      'related_class'     => '{$this->getForeignTable($fk)->getPhpName()}',
      'in_object'         => \$this->{$this->getFKVarName($fk)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$event->isProcessed() && (is_null(\$event->getReturnValue()) || \$event->getReturnValue() instanceof {$this->getForeignTable($fk)->getPhpName()}))
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

    \$event = sfContext::getInstance()->getEventDispatcher()->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.get_fks_join', array(
      'criteria'          => \$criteria,
      'connection'        => \$con,
      'middle_class'      => '{$refFK->getTable()->getPhpName()}',
      'related_class'     => '%s',
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$event->isProcessed() && is_array(\$event->getReturnValue()))
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
        $insert = sprintf($getFksJoin, $this->getFKPhpNameAffix($fk2, $plural = false));
        
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

    \$event = sfContext::getInstance()->getEventDispatcher()->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.init_fk_coll', array(
      'related_class'     => '{$this->getRefFKPhpNameAffix($refFK, $plural = false)}',
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$event->isProcessed() && is_array(\$event->getReturnValue()))
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

    \$event = sfContext::getInstance()->getEventDispatcher()->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.add_fk', array(
      'related_class'     => '{$this->getRefFKPhpNameAffix($refFK, $plural = false)}',
      'added_value'       => \$l,
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$event->isProcessed() && \$event->getReturnValue())
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

    \$event = sfContext::getInstance()->getEventDispatcher()->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.count_fks', array(
      'criteria'          => \$criteria,
      'distinct'          => \$distinct,
      'connection'        => \$con,
      'related_class'     => '{$this->getRefFKPhpNameAffix($refFK, $plural = false)}',
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$event->isProcessed() && is_int(\$event->getReturnValue()))
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

    \$event = sfContext::getInstance()->getEventDispatcher()->notifyUntil(new sfEvent(\$this, 'Base{$this->getTable()->getPhpName()}.get_fks', array(
      'criteria'          => \$criteria,
      'connection'        => \$con,
      'related_class'     => '{$this->getRefFKPhpNameAffix($refFK, $plural = false)}',
      'in_object'         => \$this->{$this->getRefFKCollVarName($refFK)},
      'last_criteria'     => \$this->{$this->getRefFKLastCriteriaVarName($refFK)},
      'modified_columns'  => \$this->modifiedColumns,
    )));
    if (\$event->isProcessed() && is_array(\$event->getReturnValue()))
    {
      return \$event->getReturnValue();
    }

EOF;
    
    $pos = strpos($tmp, '{') + 1;
    $tmp = substr($tmp, 0, $pos).$getFks.substr($tmp, $pos);
    
    $script .= $tmp;
  }
}
