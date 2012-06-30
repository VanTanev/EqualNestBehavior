
/**
 * Initializes the <?php echo $varRelatedObjectsColl ?> collection.
 *
 * By default this just sets the <?php echo $varRelatedObjectsColl ?> collection to an empty PropelObjectCollection;
 * however, you may wish to override this method in your stub class to provide setting appropriate
 * to your application -- for example, setting the initial array to the values stored in database (ie, calling get<?php echo $pluralRefTableName ?>).
 */
protected function init<?php echo $pluralRefTableName ?>()
{
    $this-><?php echo $varRelatedObjectsColl ?> = new PropelObjectCollection();
    $this-><?php echo $varRelatedObjectsColl ?>->setModel('<?php echo $objectClass ?>');
}
