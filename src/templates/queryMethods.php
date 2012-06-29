
/**
 * Filter the query by 2 <?php echo $refClassName ?> objects for a Equal Nest <?php echo $className ?> relation
 *
 * @param      <?php echo $refClassName ?>|integer $object1
 * @param      <?php echo $refClassName ?>|integer $object2
 * @return     <?php echo $queryClassName ?> Fluent API
 */
public function filterBy<?php echo $pluralRefClassName ?>($object1, $object2)
{
    return $this
        ->condition('first-one', '<?php echo $fullNameRefColumn1 ?> = ?', is_object($object1) ? $object1->getPrimaryKey() : $object1)
        ->condition('first-two', '<?php echo $fullNameRefColumn2 ?> = ?', is_object($object2) ? $object2->getPrimaryKey() : $object2)
        ->condition('second-one', '<?php echo $fullNameRefColumn2 ?> = ?', is_object($object1) ? $object1->getPrimaryKey() : $object1)
        ->condition('second-two', '<?php echo $fullNameRefColumn1 ?> = ?', is_object($object2) ? $object2->getPrimaryKey() : $object2)
        ->combine(array('first-one',  'first-two'),  'AND', 'first')
        ->combine(array('second-one', 'second-two'), 'AND', 'second')
        ->where(array('first', 'second'), 'OR');
}
