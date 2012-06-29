
/**
 * Builds a new equal nest relation between <?php echo $refClassName ?> objects
 *
 * @param      <?php echo $refClassName ?>|integer $object1
 * @param      <?php echo $refClassName ?>|integer $object2
 * @param      PropelPDO $con
 */
public static function buildEqualNest<?php echo $className ?>Relation($object1, $object2, PropelPDO $con = null)
{
    if (self::checkForExistingEqualNest<?php echo $className ?>Relation($object1, $object2, $con)) {
        return;
    }

    $a<?php echo $className ?> = new <?php echo $className ?>();
    $a<?php echo $className ?>-><?php echo $setterRefCol1 ?>(is_object($object1) ? $object1->getPrimaryKey() : $object1);
    $a<?php echo $className ?>-><?php echo $setterRefCol2 ?>(is_object($object2) ? $object2->getPrimaryKey() : $object2);
    $a<?php echo $className ?>->save();
}
