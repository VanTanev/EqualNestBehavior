
/**
 * Set an array of <?php echo $objectClassname ?> objects as <?php echo $pluralRefTableName ?> of the this object
 *
 * @param  <?php echo $objectClassname ?>[] $objects The <?php echo $objectClassname ?> objects to set as <?php echo $pluralRefTableName ?> of the current object
 * @throws PropelException
 * @see    add<?php echo $refTableName ?>()
 */
public function set<?php echo $pluralRefTableName ?>($objects)
{
    $this->clear<?php echo $pluralRefTableName ?>();
    foreach ($objects as $a<?php echo $refTableName ?>) {
        if (!$a<?php echo $refTableName ?> instanceof <?php echo $objectClassname ?>) {
            throw new PropelException(sprintf(
                '[Equal Nest] Cannot set object of type %s as <?php echo $refTableName ?>, expected <?php echo $objectClassname ?>',
                is_object($a<?php echo $refTableName ?>) ? get_class($a<?php echo $refTableName ?>) : gettype($a<?php echo $refTableName ?>)
            ));
        }

        $this->add<?php echo $refTableName ?>($a<?php echo $refTableName ?>);
    }
}
