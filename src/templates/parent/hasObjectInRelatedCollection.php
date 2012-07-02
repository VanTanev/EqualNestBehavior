
/**
 * Checks for Equal Nest relation
 *
 * @param  <?php echo $objectClassname ?> $a<?php echo $refTableName ?> The object to check for Equal Nest <?php echo $refTableName ?> relation to the current object
 * @return boolean
 */
public function has<?php echo $refTableName ?>(<?php echo $objectClassname ?> $a<?php echo $refTableName ?>)
{
    if (null === $this-><?php echo $varRelatedObjectsColl ?>) {
        $this->get<?php echo $pluralRefTableName ?>();
    }

    return $a<?php echo $refTableName ?>->isNew() || $this->isNew()
        ? in_array($a<?php echo $refTableName ?>, $this-><?php echo $varRelatedObjectsColl ?>->getArrayCopy())
        : in_array($a<?php echo $refTableName ?>->getPrimaryKey(), $this-><?php echo $varRelatedObjectsColl ?>->getPrimaryKeys());
}
