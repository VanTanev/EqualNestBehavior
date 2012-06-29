
/**
 * @var array List of PKs of <?php echo $pluralForm ?> for this <?php echo $objectClassname ?>
 */
protected $listEqualNest<?php echo $pluralForm ?>PKs;

/**
 * @var PropelObjectCollection <?php echo $objectClassname ?>[] Collection to store Equal Nest <?php echo $pluralForm ?> of this <?php echo $objectClassname ?>
 */
protected $collEqualNest<?php echo $pluralForm ?>;

/**
 * @var boolean Flag to prevent endless processing loop which occurs when 2 new objects are set as twins
 */
protected $alreadyInEqualNestProcessing = false;
