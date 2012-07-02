<?php

/**
 * This file is part of the Equal Nest Behavior package.
 * For the full copyright and license information, please view the README.md
 * file that was distributed with this source code.
 *
 * @license    MIT License
 */

/**
 * @author      Ivan Plamenov Tanev aka Crafty_Shadow @ WEBWORLD.BG <vankata.t@gmail.com>
 */
class EqualNestParentBehaviorObjectBuilderModifier
{
    protected $behavior;

    protected $table;

    protected $builder;

    protected $middleTable;

    protected $middleBehavior;

    public function __construct($behavior, $middleTable)
    {
        $this->behavior         = $behavior;
        $this->table            = $behavior->getTable();
        $this->middleTable      = $middleTable;
        $this->middleBehavior   = $this->middleTable->getBehavior('equal_nest');
    }

    public function objectAttributes($builder)
    {
        return $this->behavior->renderTemplate('objectAttributes', array(
            'objectClassname' => $builder->getStubObjectBuilder()->getClassname(),
            'refClassName'    => $this->middleTable->getPhpName(),
            'collName'        => $this->getEqualNestCollectionName($builder),
            'listName'        => $this->getEqualNestListPksName($builder),
        ), '/templates/parent/');
    }

    public function objectMethods($builder)
    {
        $builder->declareClassFromBuilder($builder->getNewStubQueryBuilder($this->middleTable));
        $builder->declareClassFromBuilder($builder->getNewStubPeerBuilder($this->middleTable));
        $builder->declareClassFromBuilder($builder->getNewStubObjectBuilder($this->table));

        $script = '';

        $script .= $this->addPorcessEqualNestQueries($builder);
        $script .= $this->addClearListRelatedPKs($builder);

        $script .= $this->addInitListRelatedPKs($builder);
        $script .= $this->addClearRelatedCollection($builder);

        $script .= $this->addInitRelatedCollection($builder);
        $script .= $this->addRemoveAllRelations($builder);

        $script .= $this->addGetRelatedCollection($builder);
        $script .= $this->addSetRelatedCollection($builder);

        $script .= $this->hasObjectInRelatedCollection($builder);
        $script .= $this->setObjectsOfRelatedCollection($builder);

        $script .= $this->addObjectToRelatedCollection($builder);
        $script .= $this->removeObjectFromRelatedCollection($builder);

        $script .= $this->countObjectsInRelatedCollection($builder);

        return $script;
    }

    public function postSave($builder)
    {
        return $this->behavior->renderTemplate('postSave', array(), '/templates/parent/');
    }

    public function objectClearReferences($builder)
    {
        return $this->behavior->renderTemplate('objectClearReferences', array(
            'collName' => $this->getEqualNestCollectionName($builder),
            'listName' => $this->getEqualNestListPksName($builder),
        ), '/templates/parent/');
    }

    protected function addPorcessEqualNestQueries($builder)
    {
        return $this->behavior->renderTemplate('processEqualNestQueries', array(
            'collName'            => $this->getEqualNestCollectionName($builder),
            'listName'            => $this->getEqualNestListPksName($builder),
            'peerClassname'       => $builder->getStubPeerBuilder()->getClassname(),
            'refPeerClassname'    => $builder->getNewStubPeerBuilder($this->middleTable)->getClassname(),
            'refTableName'        => $this->middleTable->getPhpName(),
            'pluralRefTableName'  => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
        ), '/templates/parent/');
    }

    protected function addClearListRelatedPKs($builder)
    {
        return $this->behavior->renderTemplate('clearListRelatedPks', array(
            'pluralRefTableName' => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'listName'           => $this->getEqualNestListPksName($builder),
        ), '/templates/parent/');
    }

    protected function addInitListRelatedPKs($builder)
    {
        return $this->behavior->renderTemplate('addInitListRelatedPKs', array(
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'objectClassname'       => $builder->getStubObjectBuilder()->getClassname(),
            'peerClassname'         => $builder->getStubPeerBuilder()->getClassname(),
            'varListRelatedPKs'     => $this->getEqualNestListPksName($builder),
            'pkName'                => $this->behavior->getFirstPrimaryKey()->getStudlyPhpName(),
            'tablePk'               => $this->behavior->getFirstPrimaryKey()->getFullyQualifiedName(),
            'tableName'             => $this->table->getName(),
            'middleTableName'       => $this->middleTable->getName(),
            'refColumn1'            => $this->middleBehavior->getReferenceColumn1()->getFullyQualifiedName(),
            'refColumn2'            => $this->middleBehavior->getReferenceColumn2()->getFullyQualifiedName(),
        ), '/templates/parent/');
    }

    protected function addClearRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('addClearRelatedCollection', array(
            'refTableName'          => $this->middleTable->getPhpName(),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'varRelatedObjectsColl' => $this->getEqualNestCollectionName($builder),
        ), '/templates/parent/');
    }

    protected function addInitRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('addInitRelatedCollection', array(
            'varRelatedObjectsColl' => $this->getEqualNestCollectionName($builder),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'objectClass'           => $builder->getStubObjectBuilder()->getFullyQualifiedClassname(),
        ), '/templates/parent/');
    }

    protected function addRemoveAllRelations($builder)
    {
        return $this->behavior->renderTemplate('addRemoveAllRelations', array(
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'refTableName'          => $this->middleTable->getPhpName(),
        ), '/templates/parent/');
    }

    protected function addGetRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('addGetRelatedCollection', array(
            'objectClassname'       => $builder->getStubObjectBuilder()->getClassname(),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'queryClassname'        => $builder->getStubQueryBuilder()->getClassname(),
            'varListRelatedPKs'     => $this->getEqualNestListPksName($builder),
            'varRelatedObjectsColl' => $this->getEqualNestCollectionName($builder),
            'pk'                    => $this->behavior->getFirstPrimaryKey(),
        ), '/templates/parent/');
    }

    protected function addSetRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('addSetRelatedCollection', array(
            'objectClassname'       => $builder->getStubObjectBuilder()->getClassname(),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'refTableName'          => $this->middleTable->getPhpName(),
        ), '/templates/parent/');
    }

    protected function hasObjectInRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('hasObjectInRelatedCollection', array(
            'objectClassname'       => $builder->getStubObjectBuilder()->getClassname(),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'refTableName'          => $this->middleTable->getPhpName(),
            'varRelatedObjectsColl' => $this->getEqualNestCollectionName($builder),
        ), '/templates/parent/');
    }

    protected function setObjectsOfRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('setObjectsOfRelatedCollection', array(
            'objectClassname'       => $builder->getStubObjectBuilder()->getClassname(),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'refTableName'          => $this->middleTable->getPhpName(),
            'varRelatedObjectsColl' => $this->getEqualNestCollectionName($builder),
        ), '/templates/parent/');
    }

    protected function addObjectToRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('addObjectToRelatedCollection', array(
            'objectClassname'       => $builder->getStubObjectBuilder()->getClassname(),
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'refTableName'          => $this->middleTable->getPhpName(),
        ), '/templates/parent/');
    }

    protected function removeObjectFromRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('removeObjectFromRelatedCollection', array(
            'refTableName'         => $this->middleTable->getPhpName(),
            'varRefTableName'      => '$' . lcfirst($this->middleTable->getPhpName()),
            'pluralRefTableName'   => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'objectClassname'      => $builder->getStubObjectBuilder()->getClassname(),
            'varRelObjectsColl'    => $this->getEqualNestCollectionName($builder),
        ), '/templates/parent/');
    }

    protected function countObjectsInRelatedCollection($builder)
    {
        return $this->behavior->renderTemplate('countObjectsInRelatedCollection', array(
            'pluralRefTableName'    => $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()),
            'varListRelatedPKs'     => $this->getEqualNestListPksName($builder),
            'varRelatedObjectsColl' => $this->getEqualNestCollectionName($builder),
            'queryClassname'        => $builder->getStubQueryBuilder()->getClassname(),
            'pk'                    => $this->behavior->getFirstPrimaryKey(),
        ), '/templates/parent/');
    }

    protected function getEqualNestCollectionName($builder)
    {
        return 'collEqualNest' .  $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName());
    }

    protected function getEqualNestListPksName($builder)
    {
        return sprintf('listEqualNest%sPKs', $builder->getPluralizer()->getPluralForm($this->middleTable->getPhpName()));
    }
}
