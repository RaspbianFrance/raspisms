<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $title; ?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="author" content="<?php echo $author; ?>" />
		<link rel="icon" type="image/png" href="<?php echo HTTP_PWD_IMG; ?>favicon.png" />
		<!-- Bootstrap Core CSS -->
		<link href="<?php echo HTTP_PWD; ?>css/bootstrap.min.css" rel="stylesheet">
		<!-- Custom CSS Theme -->
		<link href="<?php echo HTTP_PWD; ?>css/sb-admin.css" rel="stylesheet">
		<!-- Morris Charts CSS -->
		<link href="<?php echo HTTP_PWD; ?>css/plugins/morris.css" rel="stylesheet">
		<!-- Custom Fonts -->
		<link href="<?php echo HTTP_PWD; ?>font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<!-- Custom CSS site -->
		<link href="<?php echo HTTP_PWD; ?>css/style.css" rel="stylesheet">
		<script src="<?php echo HTTP_PWD; ?>js/jquery.js"></script>
		<script src="<?php echo HTTP_PWD; ?>js/bootstrap.min.js"></script>
		<script src="<?php echo HTTP_PWD; ?>js/plugins/morris/raphael.min.js"></script>
		<script src="<?php echo HTTP_PWD; ?>js/plugins/morris/morris.min.js"></script>

		<!-- Magic Suggest JS and CSS -->
		<script src="<?php echo HTTP_PWD; ?>js/magicsuggest.min.js"></script>
		<link href="<?php echo HTTP_PWD; ?>css/magicsuggest.css" rel="stylesheet">

		<!-- Datetime Picked JS and CSS -->
		<script src="<?php echo HTTP_PWD; ?>js/bootstrap-datetimepicker.min.js"></script>
		<script src="<?php echo HTTP_PWD; ?>js/locales/bootstrap-datetimepicker.fr.js"></script>
		<link href="<?php echo HTTP_PWD; ?>css/bootstrap-datetimepicker.min.css" rel="stylesheet">

		<!-- Custom JS -->
		<script src="<?php echo HTTP_PWD; ?>js/custom.js"></script>

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->

		<script>
			jQuery(document).ready(function ()
			{
				<?php 
					if (!empty($error_message))
					{
						?>
						showMessage('<?php secho($error_message, false, true); ?>', 0);
						<?php
					}
					
					if (!empty($success_message))
					{
						?>
						showMessage('<?php secho($success_message, false, true); ?>', 1);
						<?php
					}
				?>
			});
		</script>
	</head>
	<body>
