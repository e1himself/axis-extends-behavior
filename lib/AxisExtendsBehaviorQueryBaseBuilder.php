<?php
/**
 * Date: 07.10.12
 * Time: 1:14
 * Author: Ivan Voskoboynyk
 */
class AxisExtendsBehaviorQueryBaseBuilder extends QueryInheritanceBuilder
{
  const HOST_BEHAVIOR_NAME = 'axis_extends';
  /**
   * Returns the qualified (prefixed) classname that is being built by the current class.
   * This method must be implemented by child classes.
   * @return string
   */
  public function getUnprefixedClassname()
  {
    return 'Base'.$this->getTable()->getBehavior(static::HOST_BEHAVIOR_NAME)->getParameter('class_name').'Query';
  }

  /**
   * Gets the package for the [base] object classes.
   * @return string
   */
  public function getPackage()
  {
    return OMBuilder::getPackage().'.om';
  }

  /**
   * Returns classpath to parent class.
   * @return string
   */
  protected function getParentClassName()
  {
    $baseTableName = $this->getTable()->getBehavior(static::HOST_BEHAVIOR_NAME)->getParameter('extends');
    return $this->getDatabase()->getTable($baseTableName)->getPhpName().'Query';
  }

  protected function getClassKeyCondition()
  {
    $child = $this->getChild();
    /** @var $col Column */
    $col = $child->getColumn();

    $extensionClassName = $this->getEntityClassname();

    return "\$this->addUsingAlias(" . $col->getConstantName() . ", '$extensionClassName');";
  }

  public function getChild()
  {
    $baseTableName = $this->getTable()->getBehavior(static::HOST_BEHAVIOR_NAME)->getParameter('extends');
    $baseTable = $this->getDatabase()->getTable($baseTableName);
    $childrenColumn = $baseTable->getChildrenColumn();

    $inherintance = new Inheritance();
    $inherintance->setColumn($childrenColumn);
    $inherintance->setClassName($this->getEntityClassname());

    return $inherintance;
  }


  protected function addClassBody(&$script)
  {
    try
    {
      parent::addClassBody($script);
    }
    catch (Exception $e)
    {
      $script .= '/*'.$e->getTraceAsString().'*/';
    }
  }

  protected  function getEntityClassname()
  {
    return $this->getTable()->getBehavior(static::HOST_BEHAVIOR_NAME)->getParameter('class_name');
  }
}
