
/**
 * Method called to associate another <?php echo $objectClassname ?> object as a <?php echo $refTableName ?> of this one
 * through the Equal Nest <?php echo $pluralRefTableName ?> relation.
 *
 * @param  <?php echo $objectClassname ?> $a<?php echo $refTableName ?> The <?php echo $objectClassname ?> object to set as Equal Nest <?php echo $pluralRefTableName ?> relation of the current object
 * @throws PropelException
 */
public function add<?php echo $refTableName ?>(<?php echo $objectClassname ?> $a<?php echo $refTableName ?>)
{
    if (!$this->has<?php echo $refTableName ?>($a<?php echo $refTableName ?>)) {
        $this-><?php echo $varRelatedObjectsColl ?>[] = $a<?php echo $refTableName ?>;
        $a<?php echo $refTableName ?>->add<?php echo $refTableName ?>($this);
    }
}
