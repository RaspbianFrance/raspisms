<!DOCTYPE html>
<html>
	<head>
		<title><?php echo !empty($title) ? $title . ' - ' . WEBSITE_TITLE : WEBSITE_TITLE; ?></title>
        
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="author" content="<?php echo WEBSITE_AUTHOR; ?>" />
        
        <link rel="icon" type="image/png" href="<?php echo HTTP_PWD_IMG; ?>/favicon.png" />
    
        <!-- Bootstrap Core CSS -->
		<link href="<?php echo HTTP_PWD_CSS; ?>/bootstrap.min.css" rel="stylesheet">
		<!-- Custom CSS Theme -->
		<link href="<?php echo HTTP_PWD_CSS; ?>/sb-admin.css" rel="stylesheet">
		<!-- Morris Charts CSS -->
		<link href="<?php echo HTTP_PWD_CSS; ?>/plugins/morris.css" rel="stylesheet">
		<!-- Custom Fonts -->
		<link href="<?php echo HTTP_PWD_FONT; ?>/fonts-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<!-- Custom CSS site -->
		<link href="<?php echo HTTP_PWD_CSS; ?>/style.css" rel="stylesheet">
        
        <?php $this->render('incs/phptojs'); ?>
        
        <script src="<?php echo HTTP_PWD_JS; ?>/jquery.js"></script>
        <script src="<?php echo HTTP_PWD_JS; ?>/jquery.shiftcheckbox.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/bootstrap.min.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/plugins/morris/raphael-min.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/plugins/morris/morris.min.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/Autolinker.min.js"></script>
		<!-- Magic Suggest JS and CSS -->
		<script src="<?php echo HTTP_PWD_JS; ?>/magicsuggest.min.js"></script>
		<link href="<?php echo HTTP_PWD_CSS; ?>/magicsuggest.css" rel="stylesheet">
		<!-- \Datetime Picked JS and CSS -->
		<script src="<?php echo HTTP_PWD_JS; ?>/bootstrap-datetimepicker.min.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/locales/bootstrap-datetimepicker.fr.js"></script>
		<link href="<?php echo HTTP_PWD_CSS; ?>/bootstrap-datetimepicker.min.css" rel="stylesheet">
		<!-- International Phone Number, JS and CSS -->
		<script src="<?php echo HTTP_PWD_JS; ?>/intlTelInput/intlTelInput.min.js"></script>
		<link href="<?php echo HTTP_PWD_CSS; ?>/intlTelInput.min.css" rel="stylesheet">
		<!-- DataTables -->
		<link href="<?php echo HTTP_PWD_CSS; ?>/datatables/datatables.min.css" rel="stylesheet" type="text/css">
		<script src="<?php echo HTTP_PWD_JS; ?>/datatables/datatables.min.js"></script>
		<!-- Qrcode lib -->
		<script src="<?php echo HTTP_PWD_JS; ?>/qrcode.min.js"></script>

		<!-- Custom JS -->
		<script src="<?php echo HTTP_PWD_JS; ?>/custom.js"></script>

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->

	</head>
    <body>
