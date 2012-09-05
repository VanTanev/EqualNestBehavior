<?php

/**
 * This file is part of the Equal Nest Behavior package.
 * For the full copyright and license information, please view the README.md
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * Gives a model class the ability to support Equal Nest relations
 *
 * @author      Ivan Plamenov Tanev aka Crafty_Shadow @ WEBWORLD.BG <vankata.t@gmail.com>
 */
class EqualNestBehavior extends Behavior
{
    // default parameters value
    protected $parameters = array(
        'parent_table'          => null,
        'reference_column_1'    => null,
        'reference_column_2'    => null,
    );

    /**
     * @var EqualNestParentBehavior
     */
    protected $parentBehavior = null;

    public function modifyTable()
    {
        $table = $this->getTable();
        $parentTable = $this->getParentTable();

        if (count($parentTable->getPrimaryKey()) > 1) {
            throw new RuntimeException('Equal nest works only with a single primary key for the parent table');
        }

        $parentTablePrimaryKey = $parentTable->getPrimaryKey();

        if (!$this->getTable()->containsColumn($this->getReferenceColumn1Name())) {
            $this->getTable()->addColumn(array(
                'name'          => $this->getReferenceColumn1Name(),
                'primaryKey'    => 'true',
                'type'          => $parentTablePrimaryKey[0]->getType(),
            ));

            $fk = new ForeignKey();
            $fk->setName($this->getReferenceColumn1Name());
            $fk->setForeignTableCommonName($this->getParentTable()->getCommonName());
            $fk->setOnDelete(ForeignKey::CASCADE);
            $fk->setOnUpdate(null);
            $fk->addReference($this->getReferenceColumn1Name(), $parentTablePrimaryKey[0]->getName());
            $this->getTable()->addForeignKey($fk);
        }

        if (!$this->getTable()->containsColumn($this->getReferenceColumn2Name())) {
            $this->getTable()->addColumn(array(
                'name'          => $this->getReferenceColumn2Name(),
                'primaryKey'    => 'true',
                'type'          => $parentTablePrimaryKey[0]->getType(),
            ));

            $fk = new ForeignKey();
            $fk->setName($this->getReferenceColumn2Name());
            $fk->setForeignTableCommonName($this->getParentTable()->getCommonName());
            $fk->setOnDelete(ForeignKey::CASCADE);
            $fk->setOnUpdate(null);
            $fk->addReference($this->getReferenceColumn2Name(), $parentTablePrimaryKey[0]->getName());
            $this->getTable()->addForeignKey($fk);
        }

        if (!$parentTable->hasBehavior('equal_nest_parent')) {
            $parentBehavior = new EqualNestParentBehavior();
            $parentBehavior->setName('equal_nest_parent');
            $parentBehavior->addParameter(array('name' => 'middle_table', 'value' => $this->getTable()->getName()));
            $parentTable->addBehavior($parentBehavior);
        }

        $this->parentBehavior = $parentTable->getBehavior('equal_nest_parent');
    }

    public function staticMethods($builder)
    {
        $builder->declareClassFromBuilder($builder->getNewStubQueryBuilder($this->getTable()));

        $script = '';
        $script .= $this->addPeerBuildEqualNestRelation($builder);
        $script .= $this->addPeerRemoveEqualNestRelation($builder);
        $script .= $this->addPeerCheckForEqualNestRelation($builder);

        return $script;
    }

    public function addPeerBuildEqualNestRelation($builder)
    {
        return $this->renderTemplate('addPeerBuildEqualNestRelation', array(
            'refClassName'    => $this->parentBehavior->getTable()->getPhpName(),
            'className'       => $this->getTable()->getPhpName(),
            'setterRefCol1'   => $this->getSetterForReferenceColumn1(),
            'setterRefCol2'   => $this->getSetterForReferenceColumn2(),
        ));
    }

    public function addPeerRemoveEqualNestRelation($builder)
    {
        return $this->renderTemplate('addPeerRemoveEqualNestRelation', array(
            'refClassName'    => $this->parentBehavior->getTable()->getPhpName(),
            'className'       => $this->getTable()->getPhpName(),
        ));
    }

    public function addPeerCheckForEqualNestRelation($builder)
    {
        $fullNameRefColumn1 = $this->table->getPhpName(). '.' .$this->getReferenceColumn1()->getPhpName();
        $fullNameRefColumn2 = $this->table->getPhpName(). '.' .$this->getReferenceColumn2()->getPhpName();

        return $this->renderTemplate('addPeerCheckForEqualNestRelation', array(
            'refClassName'        => $this->parentBehavior->getTable()->getPhpName(),
            'className'           => $this->getTable()->getPhpName(),
            'queryClassName'      => $builder->getStubQueryBuilder()->getClassname(),
            'pluralRefClassName'  => $builder->getPluralizer()->getPluralForm($this->parentBehavior->getTable()->getPhpName()),
        ));
    }

    public function queryMethods($builder)
    {
        $tableName = $this->table->getPhpName();
        if (null !== $namespace = $this->table->getNamespace()) {
            $tableName = $namespace . '\\' . $tableName;
        }

        return $this->renderTemplate('queryMethods', array(
            'fullNameRefColumn1'  => $tableName . '.' . $this->getReferenceColumn1()->getPhpName(),
            'fullNameRefColumn2'  => $tableName . '.' . $this->getReferenceColumn2()->getPhpName(),
            'className'           => $this->getTable()->getPhpName(),
            'pluralRefClassName'  => $builder->getPluralizer()->getPluralForm($this->parentBehavior->getTable()->getPhpName()),
            'refClassName'        => $this->parentBehavior->getTable()->getPhpName(),
            'queryClassName'      => $builder->getStubQueryBuilder()->getClassname(),
        ));
    }

    protected function getParentTable()
    {
        if (null === $this->getParameter('parent_table')) {
            throw new InvalidArgumentException('You must set a parent table for the Equal Nest behavior');
        }

        return $this->getTable()->getDatabase()->getTable($this->getParameter('parent_table'));
    }

    public function getReferenceColumn1Name()
    {
        if (null === $this->getParameter('reference_column_1')) {
            return strtolower($this->getParentTable()->getPhpName()) . '_1';
        } else {
            return $this->getParameter('reference_column_1');
        }
    }

    public function getReferenceColumn2Name()
    {
        if (null === $this->getParameter('reference_column_2')) {
            return strtolower($this->getParentTable()->getPhpName()) . '_2';
        } else {
            return $this->getParameter('reference_column_2');
        }
    }

    public function getReferenceColumn1()
    {
        return $this->table->getColumn($this->getReferenceColumn1Name());
    }

    public function getReferenceColumn2()
    {
        return $this->table->getColumn($this->getReferenceColumn2Name());
    }

    protected function getSetterForReferenceColumn1()
    {
        return sprintf('set%s', $this->getTable()->getColumn($this->getReferenceColumn1Name())->getPhpName());
    }

    protected function getSetterForReferenceColumn2()
    {
        return sprintf('set%s', $this->getTable()->getColumn($this->getReferenceColumn2Name())->getPhpName());
    }
}
