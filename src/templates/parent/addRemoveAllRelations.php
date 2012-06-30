
/**
 * Removes all Equal Nest <?php echo $pluralRefTableName ?> relations
 *
 * @see add<?php echo $refTableName ?>()
 * @see set<?php echo $pluralRefTableName ?>()
 */
public function remove<?php echo $pluralRefTableName ?>()
{
    // this sets the collection to an empty Propel object collection; upon save, all relations will be removed
    self::init<?php echo $pluralRefTableName ?>();
}
