<?php

if (!class_exists('SfPeerBuilder'))
{
  require_once sfConfig::get('sf_symfony_lib_dir').'/addon/propel/builder/SfPeerBuilder.php';
}

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
    
    if (file_exists($absolute_behavior_file_path))
    {
      unlink($absolute_behavior_file_path);
    }
    
    if ($behaviors = $this->getTable()->getAttribute('behaviors'))
    {
      $behaviors = var_export(unserialize($behaviors), true);
      $addBehaviors = <<<EOF
<?php

sfPropelEvents::addBehaviors('{$this->getTable()->getPhpName()}', $behaviors);

EOF;
      file_put_contents($absolute_behavior_file_path, $addBehaviors);
      
      if (false === strpos($script, $behavior_file_path))
      {
        $script .= sprintf("\n\ninclude_once '%s';\n", $behavior_file_path);
      }
    }
  }
}
