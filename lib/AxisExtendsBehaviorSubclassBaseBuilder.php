<?php
/**
 * Date: 07.10.12
 * Time: 1:14
 * Author: Ivan Voskoboynyk
 */
class AxisExtendsBehaviorSubclassBaseBuilder extends OMBuilder
{
  const HOST_BEHAVIOR_NAME = 'axis_extends';
  /**
   * Returns the qualified (prefixed) classname that is being built by the current class.
   * This method must be implemented by child classes.
   * @return string
   */
  public function getUnprefixedClassname()
  {
    return 'Base'.$this->getEntityClassname();
  }

  /**
   * @return string
   */
  public function getEntityClassname()
  {
    return $this->getTable()->getBehavior(static::HOST_BEHAVIOR_NAME)->getParameter('class_name');
  }

  /**
   * Gets the package for the [base] object classes.
   * @return string
   */
  public function getPackage()
  {
    $package = $this->getTable()->getBehavior(static::HOST_BEHAVIOR_NAME)->getParameter('package') ?: parent::getPackage();
    return $package.'.om';
  }

  protected function addClassOpen(&$script)
  {
    $baseTableName = $this->getTable()->getBehavior(static::HOST_BEHAVIOR_NAME)->getParameter('extends');
    $baseTable = $this->getDatabase()->getTable($baseTableName);
    $baseClassName = $baseTable->getPhpName();

    $absctract = $baseTable->isAbstract() ? 'abstract ' : '';

    $script .= "
/**
 * Base class that represents an extended object {$this->getClassName()} of a row from the '$baseTableName' table.
 *
 * Virtual methods:
{$this->generateVirtualMethods()}
 */
{$absctract}class " . $this->getClassname() . " extends " . $baseClassName . "
{
";
  }

  protected function addClassBody(& $script)
  {
    $this->addConstructor($script);
    $this->addObjectCall($script);
  }

  protected function addClassClose(&$script)
  {
    $script .= "
} // " . $this->getClassname() . "
";
  }

  /**
   * @return string
   */
  protected function generateVirtualMethods()
  {
    $script = '';

    foreach ($this->getTable()->getColumns() as $column)
    {
      /** @var $column Column */
      $phpName = $column->getPhpName();
      $script .= " * @method get{$phpName}()\n";
      $script .= " * @method set{$phpName}(\$v)\n";
    }

    foreach ($this->getTable()->getForeignKeys() as $fk)
    {
      /** @var $fk ForeignKey */
      $relation = $this->getFKPhpNameAffix($fk);
      $script .= " * @method get{$relation}()\n";
      $script .= " * @method set{$relation}(\${$relation})\n";
    }

    $script .= ' *';

    return $script;
  }

  protected function addConstructor(&$script)
  {
    $this->declareClassFromBuilder($this->getStubPeerBuilder());

    $baseTableName = $this->getTable()->getBehavior(static::HOST_BEHAVIOR_NAME)->getParameter('extends');
    $baseTable = $this->getDatabase()->getTable($baseTableName);

    $col = $baseTable->getChildrenColumn();
    $cfc = $col->getPhpName();

    $script .= "
    /**
     * Constructs a new {$this->getEntityClassname()} class, setting the ".$col->getName()." column to this object's class name.
     */
    public function __construct()
    {";
    $script .= "
        parent::__construct();
        \$this->set$cfc(get_class(\$this));
    }
";
  }

  protected function addObjectCall(&$script)
  {
    $baseTableName = $this->getTable()->getBehavior(static::HOST_BEHAVIOR_NAME)->getParameter('extends');

    $fks = $this->getTable()->getForeignKeysReferencingTable($baseTableName);
    $fk = $fks[0];
    $ARFQCN = $this->getNewStubObjectBuilder($this->getTable())->getFullyQualifiedClassname();
    $ARClassName = $this->getNewStubObjectBuilder($this->getTable())->getClassname();
    $relationName = $this->getRefFKPhpNameAffix($fk);

    $script .= "

  /**
   * Catches calls to extension object methods
   */
  public function __call(\$name, \$params)
  {

    if (method_exists('$ARFQCN', \$name)) {
        if (!\$extension = \$this->get$relationName()) {
            \$extension = new $ARClassName();
            \$this->set$relationName(\$extension);
        }

        return call_user_func_array(array(\$extension, \$name), \$params);
    }

    return parent::__call(\$name, \$params);
  }
";
  }
}
