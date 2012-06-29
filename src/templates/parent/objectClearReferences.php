
if ($deep) {
    if ($this-><?php echo $collName ?>) {
        foreach ($this-><?php echo $collName ?> as $obj) {
            $obj->clearAllReferences($deep);
        }
    }
}

$this-><?php echo $listName ?> = null;
$this-><?php echo $collName ?> = null;
