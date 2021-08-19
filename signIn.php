<?php
session_start();

require_once("includes/dbConfig.php");

$errorMessage = "";
$username = "";
$username = "";

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
<script>
jQuery.validator.addMethod("alphanumeric", function(value, element) {
	return this.optional(element) || /^[a-zA-Z0-9_]+$/i.test(value);
	}, 'Letters, numbers, and underscores only please');
jQuery.validator.addMethod("alphanumchar", function(value, element) {
	return this.optional(element) || /^[a-zA-Z0-9.,?!@#$%^*~_]+$/i.test(value);
	}, 'Letters, numbers, and special characters only please');
$(function() {
	$("#signIn").validate({
		errorClass: "alert alert-danger",
		validClass: "alert alert-success",
		rules: {
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