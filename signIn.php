<?php
session_start();

require_once("includes/dbConfig.php");

if(isset($_SESSION['userId'])) {
	header("Location: index");
}

$errorMessage = "";
$username = "";
$userId = "";
if(isset($_GET['userSignOut'])) {
  if($_GET['userSignOut'] === "success") {
    $errorMessage = '<span class="alert alert-success">You have logged off</span>';
  }
}
if(isset($_GET['endSession'])) {
  if($_GET['endSession'] === "success") {
    $errorMessage = '<span class="alert alert-danger">Your session timed out</span>';
  }
}

if(isset($_POST["submitButton"])) {
	$username = htmlspecialchars(strip_tags($_POST["username"]), ENT_QUOTES);
	$password = htmlspecialchars(strip_tags($_POST["password"]), ENT_QUOTES);

	$query = $con->prepare("SELECT * FROM users WHERE username=:un");
	$query->bindParam(":un", $username);

	$query->execute();

	if($query->rowCount() === 1) {
		while($row = $query->fetch(PDO::FETCH_ASSOC)) {
			$userId = $row['id'];
			if (password_verify($password, $row["password"])) {
				$_SESSION["username"] = $username;
				$_SESSION["userId"] = $userId;
				header("Location: index.php");
				exit();
			}
			else {
				$errorMessage = '<span class="alert alert-danger">Login failed</span>';
			}
		}
	}
	else {
		$errorMessage = '<span class="alert alert-danger">Login failed</span>';
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Sign In</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="css/style.css">
  <link rel="shortcut icon" type="image/x-icon" href="images/icons/favicon.ico" />
</head>
<body>

	<div class="signInContainer">
		<div class="column">
			<div class="header">
				<h3>Sign In</h3>
				<span>to continue to the calendar</span>
			</div>
			<div class="loginForm">
				<?php echo $errorMessage; ?>
				<form action="signIn" method="POST" name="signIn" id="signIn">
					<input type="text" name="username" placeholder="Username" value="<?php echo $username; ?>" required autocomplete="off" autofocus>
					<input type="password" name="password" placeholder="Password" required>
					<input type="submit" name="submitButton" value="SUBMIT">
				</form>
			</div>
			<!--<a class="signInMessage" href="signUp.php">Need an account? Sign up here!</a>-->
		</div>
	</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
	<script src="./js/validation.js"></script>
</body>
</html>