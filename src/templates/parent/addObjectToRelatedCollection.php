
/**
 * Method called to associate multiple <?php echo $objectClassname ?> objects as Equal Nest <?php echo $pluralRefTableName ?> of this one
 *
 * @param   <?php echo $objectClassname ?>[] <?php echo $pluralRefTableName ?> The <?php echo $objectClassname ?> objects to set as
 *          Equal Nest <?php echo $pluralRefTableName ?> relation of the current object.
 * @throws  PropelException
 */
public function add<?php echo $pluralRefTableName ?>(<?php echo '$' . $pluralRefTableName ?>)
{
    foreach (<?php echo '$' . $pluralRefTableName ?> as $a<?php echo $pluralRefTableName ?>) {
        $this->add<?php echo $refTableName ?>($a<?php echo $pluralRefTableName ?>);
    }
}
