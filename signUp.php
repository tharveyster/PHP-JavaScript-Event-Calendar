<?php
require_once("includes/dbConfig.php");
$username = "";
$firstName = "";
$lastName = "";
$email = "";
$emailError = "";
$usernameError = "";
if(isset($_POST["submitButton"])) {
	$firstName = htmlspecialchars(strip_tags($_POST["firstName"]), ENT_QUOTES);
	$lastName = htmlspecialchars(strip_tags($_POST["lastName"]), ENT_QUOTES);
	$email = htmlspecialchars(strip_tags($_POST["email"]), ENT_QUOTES);
	$query = $con->prepare("SELECT email FROM users WHERE email=:em");
	$query->bindParam(":em", $email);
	$query->execute();

	if($query->rowCount() != 0) {
		$emailError = '<span class="alert alert-danger">That email address is associated with another account</span>';
	}
	$username = htmlspecialchars(strip_tags($_POST["username"]), ENT_QUOTES);
	$query = $con->prepare("SELECT username FROM users WHERE username=:un");
	$query->bindParam(":un", $username);
	$query->execute();

	if($query->rowCount() != 0) {
		$usernameError = '<span class="alert alert-danger">That username is not available</span>';
	}
	$password = htmlspecialchars(strip_tags($_POST["password"]), ENT_QUOTES);
	$password = password_hash($password, PASSWORD_DEFAULT);

	if($emailError === "" && $usernameError === "") {
		$query = $con->prepare("INSERT INTO users (firstName, lastName, email, username, password) VALUES(:fn, :ln, :em, :un, :pw)");

		$query->bindParam(":fn", $firstName);
		$query->bindParam(":ln", $lastName);
		$query->bindParam(":em", $email);
		$query->bindParam(":un", $username);
		$query->bindParam(":pw", $password);
		$query->execute();
		header("Location:index.php");
	}
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Sign Up</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="css/style.css">
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
					<input class="form-control" type="text" name="lastName" placeholder="Last name" value="<?php echo $lastName; ?>" required>
					<input class="form-control" type="email" name="email" placeholder="Email address" value="<?php echo $email; ?>" required>
					<?php echo $emailError; ?>
					<input class="form-control" type="text" name="username" placeholder="Username" value="<?php echo $username; ?>" autocomplete="off" required>
					<?php echo $usernameError; ?>
					<input class="form-control" type="password" name="password" placeholder="Password" autocomplete="off" required>
					<input class="btn btn-primary" type="submit" name="submitButton" value="SUBMIT">
				</form>
			</div>
		</div>
	</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js"></script>
<script>
jQuery.validator.addMethod("lettersonly", function(value, element) {
	return this.optional(element) || /^[a-zA-Z]+$/i.test(value);
	}, 'Letters only please');
jQuery.validator.addMethod("alphanumeric", function(value, element) {
	return this.optional(element) || /^[a-zA-Z0-9_]+$/i.test(value);
	}, 'Letters, numbers, and underscores only please');
jQuery.validator.addMethod("alphanumchar", function(value, element) {
	return this.optional(element) || /^[a-zA-Z0-9.,?!@#$%^*~_]+$/i.test(value);
	}, 'Letters, numbers, and special characters only please');
$(function() {
	$("#signUp").validate({
		errorClass: "alert alert-danger",
		validClass: "alert alert-success",
		rules: {
			firstName: {
				required: true,
				lettersonly: true
			},
			lastName: {
				required: true,
				lettersonly: true
			},
			email: {
				required: true,
			},
			username: {
				required: true,
				minlength: 6,
				alphanumeric: true
			},
			password: {
				required: true,
				minlength: 8,
				alphanumchar: true
			}
		},
		messages: {
			firstName: {
				required: "You must enter your first name",
				lettersonly: "Please use letters only"
			},
			lastName: {
				required: "You must enter your last name",
				lettersonly: "Please use letters only"
			},
			email: {
				required: "You must enter your email address"
			},
			username: {
				required: "You must enter a username",
				minlength: "Your username must be at least 6 characters",
				alphanumeric: "Please use letters and numbers only"
			},
			password: {
				required: "You must enter a password",
				minlength: "Your password must be at least 8 characters",
				alphanumchar: "Please use letters, numbers, and special characters only (& \' \" < > not allowed)"
			}
		}
	});
});
</script>

</body>
</html>