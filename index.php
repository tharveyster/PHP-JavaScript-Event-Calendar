<!DOCTYPE html>
<?php
include_once 'includes/functions.php';
$logoutMessage = "";
$timerResets = "";
$settingsLink = "";
if(isset($_GET['userSignOut'])) {
  if($_GET['userSignOut'] === "success") {
    $logoutMessage = '<div class="logoutMessage">
    <span class="signoutMessage">You have logged off</span>
    </div>';
  }
}
if(isset($_GET['endSession'])) {
  if($_GET['endSession'] === "success") {
    $logoutMessage = '      <div class="logoutMessage">'."\r\n".
      '        <span class="endsessionMessage">Your session timed out</span>'."\r\n".
    '      </div>'."\r\n";
  }
}
if(isset($_SESSION['username'])) {
	$loginMessage = '<div class="loginMessage">'."\r\n".
	  '        <a href="signOut.php?signedOut=true" class="loginMessageLink">SIGN OUT</a>'."\r\n".
	'      </div>';
  $settingsLink = '<div class="settingsEl">'."\r\n".
    '        <a href="settings.php" class="settingsLink">SETTINGS</a>'."\r\n".
  '      </div>';
	$username = htmlspecialchars(strip_tags($_SESSION['username']), ENT_QUOTES);

	$timerResets = ' onload="start();" onmousemove="start();" onclick="start();" onkeydown="start();"';
	echo '
		<script language="javascript" type="text/javascript">
		  var session_timeout = 1000 * 60 * 20;
		  // 1000 milliseconds in a second *
		  // 60 seconds in a minute *
		  // 40 minutes
		  var reloadpage = "signOut.php?sessionExpired=true";
		  var timeout = null;

		  function start() {
			if (timeout)
			  clearTimeout(timeout);
			timeout = setTimeout("alert(\'Your session has timed out!\');location.replace(\'" + reloadpage + "\');", session_timeout);
		  }
		</script>
	';
}else{
	$loginMessage = '<div class="loginMessage">'."\r\n".
	  '        <a href="signIn.php" class="loginMessageLink">SIGN IN</a>'."\r\n".
	'      </div>';
}
?>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Event Calendar</title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">  <link rel="stylesheet" href="css/style.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
  <!--<link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />-->
</head>
<body<?php echo $timerResets; ?>>
  <div id="container">
    <?php
      echo $loginMessage. "\r\n";
      echo $logoutMessage;
      echo $settingsLink;
    ?>
    <div class="title">
      <p class="title-text"><?php echo $username; ?> Event Calendar</p>
    </div>
    <div id="calendar_div">
<?php echo getCalender(); ?>
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
  <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
</body>
</html>