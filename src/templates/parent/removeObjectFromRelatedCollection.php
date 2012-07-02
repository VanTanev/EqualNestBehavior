
/**
 * Method called to remove a <?php echo $objectClassname ?> object from the Equal Nest <?php echo $pluralRefTableName ?> relation
 *
 * @param  <?php echo $objectClassname ?> <?php echo $varRefTableName ?> The <?php echo $objectClassname ?> object
 *         to remove as a <?php echo $refTableName ?> of the current object
 * @throws PropelException
 */
public function remove<?php echo $refTableName ?>(<?php echo $objectClassname ?> <?php echo $varRefTableName ?>)
{
    if (null === $this-><?php echo $varRelObjectsColl ?>) {
        $this->get<?php echo $pluralRefTableName ?>();
    }

    if ($this-><?php echo $varRelObjectsColl ?>->contains(<?php echo $varRefTableName ?>)) {
        $this-><?php echo $varRelObjectsColl ?>->remove($this-><?php echo $varRelObjectsColl ?>->search(<?php echo $varRefTableName ?>));

        $coll = <?php echo $varRefTableName ?>->get<?php echo $pluralRefTableName ?>();
        if ($coll->contains($this)) {
            $coll->remove($coll->search($this));
        }
    } else {
        throw new PropelException(sprintf('[Equal Nest] Cannot remove <?php echo $refTableName ?> from Equal Nest relation because it is not set as one!'));
    }
}
