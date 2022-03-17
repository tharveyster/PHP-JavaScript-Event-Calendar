<?php
session_start();
date_default_timezone_set('America/New_York');

$userId = "";

$_SESSION['rdrurl'] = $_SERVER['REQUEST_URI'];

$timerResets = "";

if(isset($_SESSION['userId'])) {
	$userId = htmlspecialchars(strip_tags($_SESSION['userId']), ENT_QUOTES);

	$timerResets = ' onload="start();" onmousemove="start();" onclick="start();" onkeydown="start();"';
}else{
	header("Location:signIn.php");
	die();
}

include('includes/dbConfig.php');

$query = $con->prepare("SELECT * FROM users WHERE id = :id");
$query->bindParam(":id", $userId);
$query->execute();

$row_count = $query->rowCount();
if($row_count === 0) {
	$userId = "";
} else {
	$row = $query->fetch(PDO::FETCH_ASSOC);
}

$detailsMessage = "";
$passwordMessage = "";
$pictureMessage = "";

if(isset($_POST["firstName"])) {
  $firstName = $_POST["firstName"];
}
else {
  $firstName = $row['firstName'];
}
if(isset($_POST["lastName"])) {
  $lastName = $_POST["lastName"];
}
else {
  $lastName = $row['lastName'];
}
if(isset($_POST["email"])) {
  $email = $_POST["email"];
}
else {
  $email = $row['email'];
}

if(isset($_POST["saveDetailsButton"])) {
	$firstName = strip_tags($_POST["firstName"]);
	$firstName = str_replace(" ", "", $firstName);
	$firstName = strtolower($firstName);
	$firstName = ucfirst($firstName);
	$lastName = strip_tags($_POST["lastName"]);
	$lastName = str_replace(" ", "", $lastName);
	$lastName = strtolower($lastName);
	$lastName = ucfirst($lastName);
	$email = strip_tags($_POST["email"]);
	$email = str_replace(" ", "", $email);

	if (strlen($firstName) <= 25 && strlen($firstName) >= 2) {
		if (strlen($lastName) <= 25 && strlen($lastName) >= 2) {
			if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
				$query = $con->prepare("UPDATE users SET firstName=:fn, lastName=:ln, email=:em WHERE id=:id");
				$query->bindParam(":fn", $firstName);
				$query->bindParam(":ln", $lastName);
				$query->bindParam(":em", $email);
				$query->bindParam(":id", $userId);
	
				$query->execute();
				$detailsMessage = '        <div class="alert alert-success"><strong>SUCCESS!</strong> Details updated sucessfully!</div>';
			} else {
				$detailsMessage = '        <div class="alert alert-danger">Please enter a valid email address</div>';
			}
		} else {
			$detailsMessage = '        <div class="alert alert-danger">Your last name must be between 2 and 25 characters</div>';
		}
	} else {
		$detailsMessage = '        <div class="alert alert-danger">Your first name must be between 2 and 25 characters</div>';
	}
}

if(isset($_POST["savePasswordButton"])) {
	$oldPassword = htmlspecialchars(strip_tags($_POST["oldPassword"]), ENT_QUOTES);
	$newPassword = htmlspecialchars(strip_tags($_POST["newPassword"]), ENT_QUOTES);
	$newPassword2 = htmlspecialchars(strip_tags($_POST["newPassword2"]), ENT_QUOTES);

	if (preg_match("/^[A-Za-z0-9.,?!@#$%*~_]+$/", $oldPassword) && preg_match("/^[A-Za-z0-9.,?!@#$%*~_]+$/", $newPassword) && preg_match("/^[A-Za-z0-9.,?!@#$%*~_]+$/", $newPassword2)) {
		if (strlen($newPassword) <= 30 && strlen($newPassword) >= 8) {
			if ($newPassword === $newPassword2) {
				$query = $con->prepare("SELECT * FROM users WHERE id=:id");
				$query->bindParam(":id", $userId);

				$query->execute();

				if($query->rowCount() != 0) {
					while($row = $query->fetch(PDO::FETCH_ASSOC)) {
						if (password_verify($oldPassword, $row["password"])) {
							$newpassword = password_hash($newPassword, PASSWORD_DEFAULT);
							$query = $con->prepare("UPDATE users SET password=:pw WHERE id=:id");
							$query->bindParam(":pw", $newpassword);
							$query->bindParam(":id", $userId);
				
							$query->execute();
							$passwordMessage = '<div class="alert alert-success">Your password has been changed</div>';
						}
						else {
							$passwordMessage = '<div class="alert alert-danger">The old password you entered is not correct</div>';
						}
					}
				}
				else {
					$passwordMessage = '<div class="alert alert-danger">The old password you entered is not correct</div>';
				}
			} else {
				$passwordMessage = '<div class="alert alert-danger">Your new passwords do not match</div>';
			}
		} else {
			$passwordMessage = '<div class="alert alert-danger">Your new password must be 8 to 30 characters long</div>';
		}
	} else {
		$passwordMessage = '<div class="alert alert-danger">Your passwords must contain letters, numbers, and . , ? ! @ # $ % * ~ _ symbols only</div>';
	}
}

if(isset($_FILES["image"]["name"])) {
	$inputName = "image";
	$imageFile = $_FILES[$inputName]["name"];
	$imageTmp = $_FILES[$inputName]["tmp_name"];
	$imageSize = $_FILES[$inputName]["size"];
	$fileExt = strtolower(pathinfo($imageFile, PATHINFO_EXTENSION));
	$validext = array("jpeg","jpg","gif","png");
	$imageTypes = exif_imagetype($imageTmp);
	$maxFileSize = 1024*1024*4;
	$calMonthName = $_POST['calMonth'];
	$calMonth = "";
	if($calMonthName === "january") {
		$calMonth = "01";
	}elseif($calMonthName === "february") {
		$calMonth = "02";
	}elseif($calMonthName === "march") {
		$calMonth = "03";
	}elseif($calMonthName === "april") {
		$calMonth = "04";
	}elseif($calMonthName === "may") {
		$calMonth = "05";
	}elseif($calMonthName === "june") {
		$calMonth = "06";
	}elseif($calMonthName === "july") {
		$calMonth = "07";
	}elseif($calMonthName === "august") {
		$calMonth = "08";
	}elseif($calMonthName === "september") {
		$calMonth = "09";
	}elseif($calMonthName === "october") {
		$calMonth = "10";
	}elseif($calMonthName === "november") {
		$calMonth = "11";
	}elseif($calMonthName === "december") {
		$calMonth = "12";
	}
	$temporaryPath = "temp/" . uniqid() . ".png"; // This was changed for deployment to Heroku. It should be ../../images/ for a server.
    $finalPath = "images/$userId-$calMonth.png";
	$finalLink = "$userId-$calMonth.png";
	$imageErrors = array();

	if ($_FILES["image"]["error"]) {
		if (($_FILES["image"]["error"] === 1) || $_FILES["image"]["error"] == 2 ){
			$errorCode = "File size was too large! It must be under 4MB.";
		} elseif ($_FILES["image"]["error"] === 3) {
			$errorCode = "The uploaded file was only partially uploaded.";
		} elseif ($_FILES["image"]["error"] === 4) {
			$errorCode = "No file was uploaded.";
		}
		$imageErrors[] = "<strong>ERROR!</strong> $errorCode";
	} elseif ($imageSize > $maxFileSize) {
		$imageErrors[] = "<strong>ERROR!</strong> File size was too large! It must be under 4MB.";
	} elseif (!in_array($fileExt, $validext) || ($imageTypes != IMAGETYPE_GIF && $imageTypes != IMAGETYPE_JPEG && $imageTypes != IMAGETYPE_PNG)) {
		$imageErrors[] = "<strong>ERROR!</strong> Not an image file! Only jpg, gif, and png allowed.";
	} else {
		$imageType = exif_imagetype($imageTmp);
		$functions = [
			IMAGETYPE_GIF => 'imagecreatefromgif',
			IMAGETYPE_JPEG => 'imagecreatefromjpeg',
			IMAGETYPE_PNG => 'imagecreatefrompng'
		];
		imagepng($functions[$imageType]($imageTmp), $imageTmp);
		$maxDim = 1200;
		list($width, $height, $type, $attr) = getimagesize( $imageTmp );
		if ( $width > $maxDim || $height > $maxDim ) {
		    $target_filename = $imageTmp;
		    $ratio = $width/$height;
		    if( $ratio > 1) {
		        $new_width = $maxDim;
		        $new_height = $maxDim/$ratio;
		    } else {
		        $new_width = $maxDim*$ratio;
		        $new_height = $maxDim;
		    }
		    $src = imagecreatefromstring( file_get_contents( $imageTmp ) );
		    $dst = imagecreatetruecolor( $new_width, $new_height );
		    imagecopyresampled( $dst, $src, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
		    imagedestroy( $src );
			imagepng( $dst, $target_filename );
		}
	}

	if(empty($imageErrors)) {
		move_uploaded_file($imageTmp, $temporaryPath);
		$pictureMessage = '        <div class="alert alert-success">
          <strong>SUCCESS!</strong> '.ucfirst($calMonthName). ' image file uploaded!
        </div>' . chr(13) . chr(10);
        rename($temporaryPath, $finalPath);

        $query = $con->prepare("UPDATE users SET $calMonthName = :monthPic WHERE id=:id");
        $query->bindParam(":monthPic", $finalLink);
        $query->bindParam(":id", $userId);
        $query->execute();
	}else{
		foreach($imageErrors as $imageError) {
			$pictureMessage = '        <div class="alert alert-danger">'
          .$imageError.
        '</div>' . chr(13) . chr(10);
		}
	}
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
	<title>User Settings</title>
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css" integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
	<link rel="stylesheet" type="text/css" href="css/style.css" />
  <link rel="shortcut icon" type="image/x-icon" href="images/icons/favicon.ico" />
  <script src="./js/script.js"></script>
</head>
<body<?php echo $timerResets; ?>>

	<div class="signInContainer">
		<div class="column">
			<div class="header">
				<h3>User Settings</h3>
			</div>
			<div id="loginForm">
				<div class="settingsContainer">
			    	<div class="formSection">
				    	<div class="message">
<?php echo $detailsMessage; ?>
				    	</div>
						<form action="settings.php" method="POST" name="changeSettings" id="changeSettings">
        					<span class="title">User detals</span>
   	    					<input type="text" placeholder="First name" name="firstName" value="<?php echo $firstName ?>" required>
        					<input type="text" placeholder="Last name" name="lastName" value="<?php echo $lastName ?>" required>
       						<input type="email" placeholder="Email" name="email" value="<?php echo $email ?>" required>
        					<input type="submit" name="saveDetailsButton" value="Save">
	    				</form>
    				</div>
    				<div class="formSection">
						<div class="message">
<?php echo $passwordMessage; ?>
				    	</div>
    					<form action="settings.php" method="POST" name="changePassword" id="changePassword">
        					<span class="title">Update password</span>
       						<input type="password" placeholder="Old password" name="oldPassword" required>
   							<input type="password" placeholder="New password" name="newPassword" id="newPassword" required>
       						<input type="password" placeholder="Confirm new password" name="newPassword2" required>
        					<input type="submit" name="savePasswordButton" value="Save">
      					</form>
    				</div>
    				<div class="formSection">
    					<div class="message">
<?php echo $pictureMessage; ?>
    					</div>
    					<form action="settings.php" id="imageForm" method="POST" enctype="multipart/form-data">
    	    				<span class="title">Change background pictures</span>
					        <div class="form-group">
								<select class="form-control" name="calMonth" required>
									<option value="" selected disabled>Select a month</option>
									<option value="january">January</option>
									<option value="february">February</option>
									<option value="march">March</option>
									<option value="april">April</option>
									<option value="may">May</option>
									<option value="june">June</option>
									<option value="july">July</option>
									<option value="august">August</option>
									<option value="september">September</option>
									<option value="october">October</option>
									<option value="november">November</option>
									<option value="december">December</option>
								</select>
								<input type="file" name="image" accept="image/png, image/gif, image/jpeg" required>
        						(.jpg, .jpeg, .gif, or .png files only, 4MB maximum file size)
        					</div>
    	    				<input type="submit" name="savePictureButton" value="Save">
    					</form>
						<script>
							$("#imageForm").submit(function() {
								$("#loadingModal").modal("show");
							});
						</script>
						<div class="modal fade" id="loadingModal" tabindex="-1" role="dialog" aria-labelledby="loadingModal" aria-hidden="true" data-backdrop="static" data-keyboard="false">
							<div class="modal-dialog modal-dialog-centered" role="document">
								<div class="modal-content">
									<div class="modal-body">
										<div class="centered">
											<img src="/images/icons/loading-spinner.gif" alt="Please wait" />
											<br />
											Please wait.
										</div>
									</div>
								</div>
							</div>
						</div>
   					</div>
					<a href="index.php">Back to Calendar</a>
  				</div>
			</div>
		</div>
	</div>

	<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.1/dist/jquery.validate.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.min.js" integrity="sha384-w1Q4orYjBQndcko6MimVbzY0tgp4pWB4lZ7lr30WKz0vr/aWKhXdBNmNb5D92v7s" crossorigin="anonymous"></script>
	<script src="./js/validation.js"></script>
</body>
</html>

