
/**
 * This function checks the local equal nest collection against the database
 * and creates new relations or deletes ones that have been removed
 *
 * @param PropelPDO $con
 */
public function processEqualNestQueries(PropelPDO $con = null)
{
    if (false === $this->alreadyInEqualNestProcessing && null !== $this-><?php echo $collName ?>) {

        if (null === $con) {
            $con = Propel::getConnection(<?php echo $peerClassname ?>::DATABASE_NAME, Propel::CONNECTION_WRITE);
        }

        $this->alreadyInEqualNestProcessing = true;

        $this->clearList<?php echo $pluralRefTableName ?>PKs();
        $this->initList<?php echo $pluralRefTableName ?>PKs($con);

        $this-><?php echo $collName ?>->save();

        $con->beginTransaction();

        try {
            foreach ($this->get<?php echo $pluralRefTableName ?>()->getPrimaryKeys($usePrefix = false) as $columnKey => $pk) {
                if (!in_array($pk, $this-><?php echo $listName ?>)) {
                    // save new equal nest relation
                    <?php echo $refPeerClassname ?>::buildEqualNest<?php echo $refTableName ?>Relation($this, $pk, $con);
                    // add this object to the sibling's collection
                    $this->get<?php echo $pluralRefTableName ?>()->get($columnKey)->add<?php echo $refTableName ?>($this);
                } else {
                    // remove the pk from the list of db keys
                    unset($this-><?php echo $listName ?>[array_search($pk, $this-><?php echo $listName ?>)]);
                }
            }

            // if we have keys still left, this means they are relations that have to be removed
            foreach ($this-><?php echo $listName ?> as $oldPk) {
                <?php echo $refPeerClassname ?>::removeEqualNest<?php echo $refTableName ?>Relation($this, $oldPk, $con);
            }

            $con->commit();
            $this->alreadyInEqualNestProcessing = false;
        } catch (PropelException $e) {
            $con->rollBack();
            throw $e;
        }
    }
}
