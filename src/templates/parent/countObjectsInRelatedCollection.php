
/**
 * Returns the number of Equal Nest <?php echo $pluralRefTableName ?> of this object.
 *
 * @param      Criteria   $criteria
 * @param      boolean    $distinct
 * @param      PropelPDO  $con
 * @return     integer    Count of <?php echo $pluralRefTableName . "\n" ?>
 * @throws     PropelException
 */
public function count<?php echo $pluralRefTableName ?>(Criteria $criteria = null, $distinct = false, PropelPDO $con = null)
{
    if (null === $this-><?php echo $varListRelatedPKs ?>) {
        $this->initList<?php echo $pluralRefTableName ?>PKs($con);
    }

    if (null === $this-><?php echo $varRelatedObjectsColl ?> || null !== $criteria) {
        if ($this->isNew() && null === $this-><?php echo $varRelatedObjectsColl ?>) {
            return 0;
        } else {
            $query = <?php echo $queryClassname ?>::create(null, $criteria);
            if ($distinct) {
                $query->distinct();
            }

            return $query
                ->addUsingAlias(<?php echo $pk->getConstantName() ?>, $this-><?php echo $varListRelatedPKs ?>, Criteria::IN)
                ->count($con);
        }
    } else {
        return count($this-><?php echo $varRelatedObjectsColl ?>);
    }
}
