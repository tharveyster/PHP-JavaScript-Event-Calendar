<?php
session_start();
require_once("includes/dbConfig.php");

if(isset($_SESSION['userId'])) {
	header("Location: index.php");
}

$username = "";
$firstName = "";
$lastName = "";
$email = "";
$firstNameError = "";
$lastNameError = "";
$emailError = "";
$usernameError = "";
$passwordError = "";

if(isset($_POST["submitButton"])) {

	$firstName = htmlspecialchars(strip_tags($_POST["firstName"]), ENT_QUOTES);
	if($firstName === "") {
		$firstNameError = '<span class="alert alert-danger">Your first name is required</span>';
	}
	if (strlen($firstName) > 25 && strlen($firstName) < 2) {
		$firstNameError = '<span class="alert alert-danger">Your first name must be between 2 and 25 characters</span>';
	}
	if(!ctype_alpha($firstName)) {
		$firstNameError = '<span class="alert alert-danger">Please use letters only</span>';
	}

	$lastName = htmlspecialchars(strip_tags($_POST["lastName"]), ENT_QUOTES);
	if($lastName === "") {
		$lastNameError = '<span class="alert alert-danger">Your last name is required</span>';
	}
	if (strlen($lastName) > 25 && strlen($lastName) < 2) {
		$lastNameError = '<span class="alert alert-danger">Your last name must be between 2 and 25 characters</span>';
	}
	if(!ctype_alpha($lastName)) {
		$lastNameError = '<span class="alert alert-danger">Please use letters only</span>';
	}

	$email = htmlspecialchars(strip_tags($_POST["email"]), ENT_QUOTES);
	if($email === "") {
		$emailError = '<span class="alert alert-danger">Your email is required</span>';
	}

	$query = $con->prepare("SELECT email FROM users WHERE email=:em");
	$query->bindParam(":em", $email);
	$query->execute();

	if($query->rowCount() != 0) {
		$emailError = '<span class="alert alert-danger">That email address is associated with another account</span>';
	}

	$username = htmlspecialchars(strip_tags($_POST["username"]), ENT_QUOTES);
	if($username === "") {
		$usernameError = '<span class="alert alert-danger">A username is required</span>';
	}
	if (strlen($username) > 25 && strlen($username) < 6) {
		$usernameError = '<span class="alert alert-danger">Your username must be between 6 and 25 characters</span>';
	}
	if (!preg_match('/^[a-zA-Z0-9]+$/', $username)) {
    $usernameError = '<span class="alert alert-danger">Please use letters and numbers only</span>';
	}

	$query = $con->prepare("SELECT username FROM users WHERE username=:un");
	$query->bindParam(":un", $username);
	$query->execute();

	if($query->rowCount() != 0) {
		$usernameError = '<span class="alert alert-danger">That username is not available</span>';
	}

	$password = htmlspecialchars(strip_tags($_POST["password"]), ENT_QUOTES);
	if($password === "") {
		$passwordError = '<span class="alert alert-danger">A password is required</span>';
	}
	if (strlen($password) < 8) {
		$passwordError = '<span class="alert alert-danger">Your password must be at least 8 characters</span>';
	}
	if (!preg_match('/^[a-zA-Z0-9.,?!@#$%^*~_]+$/', $password)) {
    $passwordError = '<span class="alert alert-danger">Please use letters, numbers, and special characters only (& \' \" < > not allowed)</span>';
	}

	$password = password_hash($password, PASSWORD_DEFAULT);

	if($firstNameError === "" && $lastNameError === "" && $emailError === "" && $usernameError === "" && $passwordError === "") {
		$query = $con->prepare("INSERT INTO users (firstName, lastName, email, username, password) VALUES(:fn, :ln, :em, :un, :pw)");
		$email = strtolower($email);
		$username = strtolower($username);
		$query->bindParam(":fn", $firstName);
		$query->bindParam(":ln", $lastName);
		$query->bindParam(":em", $email);
		$query->bindParam(":un", $username);
		$query->bindParam(":pw", $password);
		$query->execute();

		$newQuery = $con->prepare("SELECT * FROM users WHERE username=:un");
		$newQuery->bindParam(":un", $username);
		$newQuery->execute();
		if($newQuery->rowCount() === 1) {
			while($row = $newQuery->fetch(PDO::FETCH_ASSOC)) {
				$userId = $row['id'];
				$_SESSION["username"] = $username;
				$_SESSION["userId"] = $userId;
				header("Location: index.php");
			}
		}
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Sign Up</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="css/style.css">
  <link rel="shortcut icon" type="image/x-icon" href="images/icons/favicon.ico" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
</head>
<body>

	<div class="signInContainer">
		<div class="column">
			<div class="header">
				<h3>Sign Up</h3>
				<span>to create a calendar account</span>
			</div>
			<div class="loginForm">
				<form action="signUp.php" method="POST" name="signUp" id="signUp">
					<input class="form-control" type="text" name="firstName" placeholder="First name" value="<?php echo $firstName; ?>" required autofocus>
					<?php echo $firstNameError; ?>
					<input class="form-control" type="text" name="lastName" placeholder="Last name" value="<?php echo $lastName; ?>" required>
					<?php echo $lastNameError; ?>
					<input class="form-control" type="email" name="email" placeholder="Email address" value="<?php echo $email; ?>" required>
					<?php echo $emailError; ?>
					<input class="form-control" type="text" name="username" placeholder="Username" value="<?php echo $username; ?>" autocomplete="off" required>
					<?php echo $usernameError; ?>
					<input class="form-control" type="password" name="password" placeholder="Password" autocomplete="off" required>
					<?php echo $passwordError; ?>
					<input class="btn btn-primary" type="submit" name="submitButton" value="SUBMIT">
				</form>
			</div>
		</div>
	</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js"></script>
<script src="./js/validation.js"></script>

</body>
</html>