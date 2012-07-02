
/**
 * Gets an array of <?php echo $objectClassname ?> objects which are Equal Nest <?php echo $pluralRefTableName ?> of this object.
 *
 * If the $criteria is not null, it is used to always fetch the results from the database.
 * Otherwise the results are fetched from the database the first time, then cached.
 * Next time the same method is called without $criteria, the cached collection is returned.
 * If this <?php echo $objectClassname ?> object is new, it will return an empty collection; the criteria is ignored on a new object.
 *
 * @param      Criteria $criteria
 * @param      PropelPDO $con
 * @return     PropelObjectCollection <?php echo $objectClassname ?>[] List of Equal Nest <?php echo $pluralRefTableName ?> of this <?php echo $objectClassname ?>.
 * @throws     PropelException
 */
public function get<?php echo $pluralRefTableName ?>(Criteria $criteria = null, PropelPDO $con = null)
{
    if (null === $this-><?php echo $varListRelatedPKs ?>) {
        $this->initList<?php echo $pluralRefTableName ?>PKs($con);
    }

    if (null === $this-><?php echo $varRelatedObjectsColl ?> || null !== $criteria) {
        if (array() === $this-><?php echo $varListRelatedPKs ?> && null === $this-><?php echo $varRelatedObjectsColl ?>) {
            // return empty collection
            $this->init<?php echo $pluralRefTableName ?>();
        } else {
            $newCollection = <?php echo $queryClassname ?>::create(null, $criteria)
                ->addUsingAlias(<?php echo $pk->getConstantName() ?>, $this-><?php echo $varListRelatedPKs ?>, Criteria::IN)
                ->find($con);

            if (null !== $criteria) {
                return $newCollection;
            }

            $this-><?php echo $varRelatedObjectsColl ?> = $newCollection;
        }
    }

    return $this-><?php echo $varRelatedObjectsColl ?>;
}
