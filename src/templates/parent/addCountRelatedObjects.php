
/**
 * Count equal nest <?php echo $pluralRefTableName ?> of the supplied <?php echo $objectClassname ?> object
 *
 * @param  <?php echo $objectClassname ?> <?php echo $objectName ?>
 * @param  PropelPDO $con
 * @return integer
 */
public function count<?php echo $pluralRefTableName ?>Of(<?php echo $objectClassname ?> <?php echo $objectName ?>, PropelPDO $con = null)
{
    return <?php echo $objectName ?>->get<?php echo $pluralRefTableName ?>()->count();
}
