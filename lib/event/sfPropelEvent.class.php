<?php

/**
 * Event class for sfPropelEventsPlugin.
 * 
 * @package     sfPropelEventsPlugin
 * @subpackage  event
 * @author      Kris Wallsmith <kris [dot] wallsmith [at] gmail [dot] com>
 * @version     SVN: $Id$
 */
class sfPropelEvent extends sfEvent
{
  protected
    $modifications = array();
  
  /**
   * Test whether this event modifies its issuing object.
   * 
   * @return  boolean
   */
  public function modifiesObject()
  {
    return (bool) count($this->modifications);
  }
  
  /**
   * Get any object modifications.
   * 
   * @return  array
   */
  public function getModifications()
  {
    return $this->modifications;
  }
  
  /**
   * Set this event to modify its issuing object.
   * 
   * @param   string $property  The name of a property of the issuing object
   * @param   mixed $value      A new value for this property
   */
  public function modifyObject($property, $value)
  {
    // some basic validations
    if (!is_null($value))
    {
      if (0 === strpos($property, 'coll'))
      {
        // i.e. collItems
        if (!is_array($value))
        {
          throw new sfException(sprintf('The property "%s" must be NULL or an array.', $property));
        }
      }
      elseif ($property{0} == 'a')
      {
        // i.e. asfGuardUser
        if (false === ($value instanceof BaseObject))
        {
          throw new sfException(sprintf('The property "%s" must be NULL or an instance of BaseObject.', $property));
        }
      }
      elseif (0 === strpos($property, 'last'))
      {
        // i.e. lastItemCriteria
        if (false === ($value instanceof Criteria))
        {
          throw new sfException(sprintf('The property "%s" must be NULL or an instance of Criteria.', $property));
        }
      }
    }
    
    $this->modifications[$property] = $value;
  }
}
