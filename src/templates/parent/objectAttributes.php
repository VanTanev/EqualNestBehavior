
/**
 * @var array List of PKs of <?php echo $refClassName ?> for this <?php echo $objectClassname ?>
 */
protected $<?php echo $listName ?>;

/**
 * @var PropelObjectCollection <?php echo $objectClassname ?>[] Collection to store Equal Nest <?php echo $refClassName ?> of this <?php echo $objectClassname ?>
 */
protected $<?php echo $collName ?>;

/**
 * @var boolean Flag to prevent endless processing loop which occurs when 2 new objects are set as twins
 */
protected $alreadyInEqualNestProcessing = false;
