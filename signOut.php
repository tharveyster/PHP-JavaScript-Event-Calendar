<?php
session_start();
session_destroy();
if(isset($_GET['sessionExpired'])) {
    if($_GET['sessionExpired'] === "true") {
        header('Location: index.php?endSession=success');
    }
}elseif(isset($_GET['signedOut'])) {
    if($_GET['signedOut'] === "true") {
        header('Location: index.php?userSignOut=success');
    }
}else{
    header('Location: index.php');
}
?>