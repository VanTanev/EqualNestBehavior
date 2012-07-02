
/**
 * Find equal nest <?php echo $pluralRefTableName; ?> of the supplied <?php echo $objectClassname ?> object
 *
 * @param  <?php echo $objectClassname ?> <?php echo $objectName ?>
 * @param  PropelPDO $con
 * @return <?php echo $objectClassname ?>[]|PropelObjectCollection
 */
public function find<?php echo $pluralRefTableName ?>Of(<?php echo $objectClassname ?> <?php echo $objectName ?>, $con = null)
{
    $obj = clone <?php echo $objectName ?>;
    $obj->clearList<?php echo $pluralRefTableName ?>PKs();
    $obj->clear<?php echo $pluralRefTableName ?>();

    return $obj->get<?php echo $pluralRefTableName ?>($this, $con);
}
