<?php
include 'includes/dbConfig.php';

$query = $con->prepare("DELETE FROM events WHERE id = :id");
$query->bindParam(':id', $_POST['id']);
$query->execute();
?>