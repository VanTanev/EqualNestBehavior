
/**
 * Checks whether an equal nest relation between <?php echo $className ?> objects
 *
 * @param      <?php echo $refClassName ?>integer $object1
 * @param      <?php echo $refClassName ?>|integer $object2
 * @param      PropelPDO $con
 * @return     <?php echo $className ?>|false
 */
public static function checkForExistingEqualNest<?php echo $className ?>Relation($object1, $object2, PropelPDO $con = null)
{
    if ($object1 instanceof <?php echo $refClassName ?> && $object1->isNew()) {
        return false;
    }

    if ($object2 instanceof <?php echo $refClassName ?> && $object2->isNew()) {
         return false;
    }

    return ($relation = <?php echo $queryClassName ?>::create()
        ->filterBy<?php echo $pluralRefClassName ?>($object1, $object2)
        ->findOne($con)) ? $relation : false;
}
