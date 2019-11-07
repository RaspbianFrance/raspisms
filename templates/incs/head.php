<!DOCTYPE html>
<html>
	<head>
		<title><?php echo !empty($title) ? $title . ' - ' . WEBSITE_TITLE : WEBSITE_TITLE; ?></title>
        
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<meta name="author" content="<?php echo WEBSITE_AUTHOR; ?>" />
        
        <link rel="icon" type="image/png" href="<?php echo HTTP_PWD_IMG; ?>/favicon.png" />
    
        <!-- Bootstrap Core CSS -->
		<link href="<?php echo HTTP_PWD_CSS; ?>/css/bootstrap.min.css" rel="stylesheet">
		<!-- Custom CSS Theme -->
		<link href="<?php echo HTTP_PWD_CSS; ?>/css/sb-admin.css" rel="stylesheet">
		<!-- Morris Charts CSS -->
		<link href="<?php echo HTTP_PWD_CSS; ?>/css/plugins/morris.css" rel="stylesheet">
		<!-- Custom Fonts -->
		<link href="<?php echo HTTP_PWD_FONT; ?>/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<!-- Custom CSS site -->
		<link href="<?php echo HTTP_PWD_CSS; ?>/css/style.css" rel="stylesheet">
        
        <?php $this->render('incs/phptojs'); ?>
        
        <script src="<?php echo HTTP_PWD_JS; ?>/js/jquery.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/js/bootstrap.min.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/js/plugins/morris/raphael.min.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/js/plugins/morris/morris.min.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/js/Autolinker.min.js"></script>
		<!-- Magic Suggest JS and CSS -->
		<script src="<?php echo HTTP_PWD_JS; ?>/js/magicsuggest.min.js"></script>
		<link href="<?php echo HTTP_PWD_CC; ?>/css/magicsuggest.css" rel="stylesheet">
		<!-- \Datetime Picked JS and CSS -->
		<script src="<?php echo HTTP_PWD_JS; ?>/js/bootstrap-datetimepicker.min.js"></script>
		<script src="<?php echo HTTP_PWD_JS; ?>/js/locales/bootstrap-datetimepicker.fr.js"></script>
		<link href="<?php echo HTTP_PWD_CSS; ?>/css/bootstrap-datetimepicker.min.css" rel="stylesheet">
		<!-- International Phone Number, JS and CSS -->
		<script src="<?php echo HTTP_PWD_JS; ?>/js/intlTelInput/intlTelInput.min.js"></script>
		<link href="<?php echo HTTP_PWD_CSS; ?>/css/intlTelInput.css" rel="stylesheet">
		
		<!-- Custom JS -->
		<script src="<?php echo HTTP_PWD_JS; ?>/js/custom.js"></script>

		<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
		<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
		<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
		<![endif]-->

		<script>
			jQuery(document).ready(function ()
			{
                <?php if (!empty($_SESSION['errormessage'])) { ?>
                    showMessage('<?php $this->s($_SESSION['errormessage'], false, true); ?>', 0);
                    <?php unset($_SESSION['errormessage']); ?> 
                <?php } ?>
                
                <?php if (!empty($_SESSION['successmessage'])) { ?>
                    showMessage('<?php $this->s($_SESSION['successmessage'], false, true); ?>', 1);
                    <?php unset($_SESSION['successmessage']); ?> 
                <?php } ?>
			});
		</script>
	</head>
	<body>
	<div class="popup-alerts-container"></div>
