
/**
 * Removes a new equal nest relation between <?php echo $refClassName ?> objects
 *
 * @param      <?php echo $refClassName ?>|integer $object1
 * @param      <?php echo $refClassName ?>|integer $object2
 * @param      PropelPDO $con
 */
public static function removeEqualNest<?php echo $className ?>Relation($object1, $object2, PropelPDO $con = null)
{
    if (!$relation = self::checkForExistingEqualNest<?php echo $className ?>Relation($object1, $object2, $con)) {
        throw new PropelException('[Equal Nest] Cannot remove a relation that does not exist.');
    }

    $relation->delete();
}
