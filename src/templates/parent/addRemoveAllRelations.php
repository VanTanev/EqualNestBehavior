
/**
 * Removes all Equal Nest <?php echo $pluralRefTableName ?> relations
 *
 * @see add<?php echo $refTableName ?>()
 * @see set<?php echo $pluralRefTableName ?>()
 */
public function remove<?php echo $pluralRefTableName ?>()
{
    foreach ($this->get<?php echo $pluralRefTableName ?>() as $obj) {
        $obj->remove<?php echo $refTableName ?>($this);
    }

    // this sets the collection to an empty Propel object collection; upon save, all relations will be removed
    $this->set<?php echo $pluralRefTableName ?>(array());
}
