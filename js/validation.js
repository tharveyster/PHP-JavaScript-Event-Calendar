jQuery.validator.addMethod("lettersonly", function(value, element) {
	return this.optional(element) || /^[a-zA-Z]+$/i.test(value);
}, 'Letters only please');
jQuery.validator.addMethod("alphanumeric", function(value, element) {
	return this.optional(element) || /^[a-zA-Z0-9_]+$/i.test(value);
}, 'Letters, numbers, and underscores only please');
jQuery.validator.addMethod("alphanumchar", function(value, element) {
	return this.optional(element) || /^[a-zA-Z0-9.,?!@#$%^*~_]+$/i.test(value);
}, 'Letters, numbers, and special characters only please');
jQuery.validator.addMethod("emailaddress", function(value, element) {
	return this.optional(element) || /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,6})+$/i.test(value);
}, 'Enter a valid email address please');
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
$(function() {
	$("#changeSettings").validate({
		errorClass: "alert alert-danger",
		validClass: "alert alert-success",
		rules: {
			firstName: {
				minlength: 2,
				maxlength: 25,
				lettersonly: true
			},
			lastName: {
				minlength: 2,
				maxlength: 25,
				lettersonly: true
			},
			email: {
				emailaddress: true
			}
		},
		messages: {
			firstName: {
				minlength: "Your first name must be at least 2 characters",
				maxlength: "Your first name must be 25 characters or less",
				lettersonly: "Your first name can contain letters only"
			},
			lastName: {
				minlength: "Your last name must be at least 2 characters",
				maxlength: "Your last name must be 25 characters or less",
				lettersonly: "Your last name can contain letters only"
			},
			email: {
				email: "Please enter a valid email address"
			}
		}
	});
});
$(function() {
	$("#changePassword").validate({
		errorClass: "alert alert-danger",
		validClass: "alert alert-success",
		rules: {
			oldPassword: {
				required: true,
				minlength: 8,
				alphanumchar: true
			},
			newPassword: {
				required: true,
				minlength: 8,
				maxlength: 30,
				alphanumchar: true
			},
			newPassword2: {
				required: true,
				minlength: 8,
				maxlength: 30,
				alphanumchar: true,
				equalTo : "#newPassword"
			}
		},
		messages: {
			oldPassword: {
				required: "You must enter your current password",
				minlength: "Your old password must be at least 8 characters",
				alphanumchar: "Please use letters, numbers, and special characters only (& \' \" < > not allowed)"
			},
			newPassword: {
				required: "You must enter a new password",
				minlength: "Your new password must be at least 8 characters",
				maxlength: "Your new password must be 30 characters or less",
				alphanumchar: "Please use letters, numbers, and special characters only (& \' \" < > not allowed)"
			},
			newPassword2: {
				required: "You must re-enter a new password",
				minlength: "Your new password must be at least 8 characters",
				maxlength: "Your new password must be 30 characters or less",
				alphanumchar: "Please use letters, numbers, and special characters only (& \' \" < > not allowed)",
				equalTo: "Your new passwords do not match"
			}
		}
	});
});
