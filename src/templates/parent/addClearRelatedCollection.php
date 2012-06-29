
/**
 * Clears out the collection of Equal Nest <?php echo $pluralRefTableName ?>
 *
 * This does not modify the database; however, it will remove any associated objects, causing
 * them to be refetched by subsequent calls to the accessor method.
 *
 * @see add<?php echo $refTableName ?>()
 * @see set<?php echo $pluralRefTableName ?>()
 * @see remove<?php echo $pluralRefTableName ?>()
 */
public function clear<?php echo $pluralRefTableName ?>()
{
    $this-><?php echo $varRelatedObjectsColl ?> = null;
}
