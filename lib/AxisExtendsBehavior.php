<?php
/**
 * Date: 07.10.12
 * Time: 1:07
 * Author: Ivan Voskoboynyk
 */
class AxisExtendsBehavior extends Behavior
{
  protected $additionalBuilders = array(
    'AxisExtendsBehaviorSubclassBaseBuilder',
    'AxisExtendsBehaviorSubclassStubBuilder',
    'AxisExtendsBehaviorQueryStubBuilder',
    'AxisExtendsBehaviorQueryBaseBuilder'
  );

  public function modifyTable()
  {
    if (!$this->getParameter('class_name'))
    {
      throw new InvalidArgumentException("You should specify 'class_name' parameter for '{$this->getName()}' behavior.");
    }
    if (!$this->getParameter('extends'))
    {
      throw new InvalidArgumentException("You should specify 'extends' parameter for '{$this->getName()}' behavior.");
    }
  }

}
