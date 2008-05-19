<?php

/**
 * Add events to Propel peer classes.
 * 
 * @package     sfPropelEventsPlugin
 * @subpackage  builder
 * @author      Kris Wallsmith <kris [dot] wallsmith [at] gmail [dot] com>
 * @version     SVN: $Id$
 */
class SfPropelEventsPeerBuilder extends SfPeerBuilder
{
  /**
   * Add behaviors to sfPropelEvents.
   * 
   * @see SfPeerBuilder
   */
  protected function addClassClose(& $script)
  {
    parent::addClassClose($script);
    
    // add to behavior file
    $behavior_file_name = 'Base'.$this->getTable()->getPhpName().'Behaviors';
    $behavior_file_path = $this->getFilePath($this->getStubObjectBuilder()->getPackage().'.om.'.$behavior_file_name);
    $absolute_behavior_file_path = sfConfig::get('sf_root_dir').'/'.$behavior_file_path;
    
    $behaviors = $this->getTable()->getAttribute('behaviors');
    if ($behaviors)
    {
      $behaviors = var_export(unserialize($behaviors), true);
      
      $addBehaviors = <<<EOF

sfPropelEvents::addBehaviors('{$this->getTable()->getPhpName()}', $behaviors);

EOF;
      
      file_put_contents($absolute_behavior_file_path, file_get_contents($absolute_behavior_file_path).$addBehaviors);
    }
  }
}
