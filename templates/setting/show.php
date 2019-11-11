<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Réglages'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'settings'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Réglages</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-cogs"></i> Réglages
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-cogs fa-fw"></i> Les réglages de RaspiSMS</h3>
						</div>
						<div class="panel-body">
							<div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-share fa-fw"></i> Transfert des SMS par e-mail</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'transfer', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Transfert activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['transfer'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-link fa-fw"></i> Détection des URL dans les discussions</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'detect_url', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Détection activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['detect_url'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-phone fa-fw"></i> Pays préférés numéros internationaux</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'preferred_phone_country', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Code des pays (norme ISO 3166-1 alpha-2) séparés par des virgules : </label>
												<input name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['preferred_phone_country']); ?>" />
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-music fa-fw"></i> Son sur reception d'un SMS</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'sms_reception_sound', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Jouer un son quand vous recevez un SMS : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['sms_reception_sound'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
							</div>
							<div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-ban fa-fw"></i> Activation de SMS-STOP</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'smsstop', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>SMS STOP activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['smsstop'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-phone fa-fw"></i> Pays par défaut numéros internationaux</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'default_phone_country', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Code du pays (norme ISO 3166-1 alpha-2) : </label>
												<input name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>" />
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-flash fa-fw"></i> Support des SMS Flash</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'sms_flash', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>SMS Flash activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['sms_flash'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function ()
	{
		jQuery('.action-dropdown a').on('click', function (e)
		{
			e.preventDefault();
			var destination = jQuery(this).parents('.action-dropdown').attr('destination');
			var url = jQuery(this).attr('href');
			jQuery(destination).find('input:checked').each(function ()
			{
				url += '/' + jQuery(this).val();
			});
			window.location = url;
		});
	});
</script>
<?php
	$this->render('incs/footer');
