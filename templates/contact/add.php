<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Contacts - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'contacts'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Nouveau contact
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-user"></i> <a href="<?php echo \descartes\Router::url('Contact', 'list'); ?>">Contacts</a>
						</li>
						<li class="active">
							<i class="fa fa-plus"></i> Nouveau
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-user fa-fw"></i> Ajout d'un contact</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Contact', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Nom contact</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-user"></span></span>
										<input name="name" class="form-control" type="text" placeholder="Nom contact" autofocus required>
									</div>
								</div>	
								<div class="form-group">
									<label>Numéro de téléphone du contact</label>
									<div class="form-group">
										<input name="number" class="form-control" type="tel" id="phone-international-input">
										<input name="number" type="hidden" id="phone-hidden-input" required>
									</div>
								</div>
								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('Contact', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le contact" /> 	
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery('document').ready(function($)
    {
        var number_input = jQuery('#phone-international-input')[0];
        var iti_number_input = window.intlTelInput(number_input, {
			defaultCountry: '<?php $this->s(RASPISMS_SETTINGS_DEFAULT_PHONE_COUNTRY); ?>',
			preferredCountries: <?php $this->s(json_encode(explode(',', RASPISMS_SETTINGS_PREFERRED_PHONE_COUNTRY)), false, false); ?>,
			nationalMode: true,
			utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js'
        });

		jQuery('form').on('submit', function(e)
		{
            e.preventDefault();
            jQuery('#phone-hidden-input').val(iti_number_input.getNumber())
			this.submit();
		});
	});
</script>
<?php
	$this->render('incs/footer');
