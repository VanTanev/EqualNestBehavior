
/**
 * Initializes the list of Equal Nest <?php echo $pluralRefTableName ?> PKs.
 *
 * This will query the database for Equal Nest <?php echo $pluralRefTableName ?> relations to this <?php echo $objectClassname ?> object.
 * It will set the list to an empty array if the object is newly created.
 *
 * @param PropelPDO $con
 */
protected function initList<?php echo $pluralRefTableName ?>PKs(PropelPDO $con = null)
{
    if ($con === null) {
        $con = Propel::getConnection(<?php echo $peerClassname ?>::DATABASE_NAME, Propel::CONNECTION_READ);
    }

    if (null === $this-><?php echo $varListRelatedPKs ?>) {
        if ($this->isNew()) {
            $this-><?php echo $varListRelatedPKs ?> = array();
        } else {
            $sql = "
SELECT DISTINCT <?php echo $tablePk . "\n" ?>
FROM <?php echo $tableName . "\n" ?>
INNER JOIN <?php echo $middleTableName ?> ON
<?php echo $tablePk ?> = <?php echo $refColumn1 . "\n" ?>
OR
<?php echo $tablePk ?> = <?php echo $refColumn2 . "\n" ?>
WHERE
<?php echo $tablePk ?> IN (
    SELECT <?php echo $refColumn1 . "\n" ?>
    FROM <?php echo $middleTableName . "\n" ?>
    WHERE <?php echo $refColumn2 ?> = :<?php echo $pkName . "\n" ?>
)
OR
<?php echo $tablePk ?> IN (
    SELECT <?php echo $refColumn2  . "\n" ?>
    FROM <?php echo $middleTableName . "\n" ?>
    WHERE <?php echo $refColumn1 ?> = :<?php echo $pkName . "\n" ?>
)";

            $stmt = $con->prepare($sql);
            $stmt->bindValue(':<?php echo $pkName ?>', $this->getPrimaryKey(), PDO::PARAM_INT);
            $stmt->execute();

            $this-><?php echo $varListRelatedPKs ?> = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }
    }
}
