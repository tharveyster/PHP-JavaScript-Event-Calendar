<?php
include_once 'includes/functions.php';
$username = "";
$displayName = "";
$timerResets = "";
if(isset($_SESSION['username'])) {
	$username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);
  if (substr($username, -1) == 's') {
    $displayName = $username.'\'';
  } else {
    $displayName = $username.'\'s';
  }

	$timerResets = ' onload="start();" onmousemove="start();" onclick="start();" onkeydown="start();"';
}else{
  header("Location: signIn.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Event Calendar</title>
  <link rel="stylesheet" href="./css/style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <link rel="shortcut icon" type="image/x-icon" href="images/icons/favicon.ico" />
  <script src="./js/script.js"></script>
</head>
<body<?php echo $timerResets; ?>>
  <div class="content-wrap">
  <div id="container">
  <div class="loginMessage">
      <a href="signOut.php?signedOut=true" class="loginMessageLink">SIGN OUT</a>
    </div>
    <div class="settingsEl">
      <a href="settings.php" class="settingsLink">SETTINGS</a>
    </div>
    <div class="title">
    <p class="title-text"><span><?php echo $displayName; ?> Event Calendar</span></p>
    </div>
    <div id="calendar_div">
<?php echo getCalender(); ?>
    </div>
  </div>
  </div>
  <div style="clear:both;line-height:2px;">&nbsp;</div>
  <div id="createdModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-body">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
          <p id="modalText"></p>
        </div>
      </div>
    </div>
  </div>
  <script>
  if ( window.history.replaceState ) {
    window.history.replaceState( null, null, window.location.href );
  }
  </script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>