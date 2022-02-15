<?php
session_start();
session_destroy();
if(isset($_GET['sessionExpired'])) {
    if($_GET['sessionExpired'] === "true") {
        header('Location: signIn.php?endSession=success');
    }
}elseif(isset($_GET['signedOut'])) {
    if($_GET['signedOut'] === "true") {
        header('Location: signIn.php?userSignOut=success');
    }
}else{
    header('Location: signIn.php');
}
?>