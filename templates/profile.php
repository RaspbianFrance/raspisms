<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Profile - Show');
?>
<div id="wrapper">
<?php
	$incs->nav();
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Profil</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-user"></i> Profil
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-user fa-fw"></i> Mon profil</h3>
						</div>
						<div class="panel-body">
							<div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-child fa-fw"></i> Mes données</h4>
									</div>
									<div class="panel-body">
										<strong>Adresse e-mail :</strong> <?php secho($_SESSION['email']); ?><br/>
										<strong>Niveau administrateur :</strong> <?php echo $_SESSION['admin'] ? 'Oui' : 'Non'; ?><br/>
									</div>
								</div>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-key fa-fw"></i> Modifier mot de passe</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo $this->generateUrl('profile', 'changePassword', array('csrf' => $_SESSION['csrf'])); ?>" method="POST">
											<div class="form-group">
												<label>Mot de passe :</label>
												<input name="password" type="password" class="form-control" placeholder="Nouveau mot de passe" />
											</div>	
											<div class="form-group">
												<label>Vérification mot de passe :</label>
												<input name="verif_password" type="password" class="form-control" placeholder="Retapez le mot de passe" />
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
										<h4 class="panel-title"><i class="fa fa-at fa-fw"></i> Modifier e-mail</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo $this->generateUrl('profile', 'changeEmail', array('csrf' => $_SESSION['csrf'])); ?>" method="POST">
											<div class="form-group">
												<label>Adresse e-mail :</label>
												<input name="mail" type="email" class="form-control" placeholder="Nouvelle adresse e-mail" />
											</div>	
											<div class="form-group">
												<label>Vérification e-mail :</label>
												<input name="verif_mail" type="email" class="form-control" placeholder="Retapez l'adresse e-mail" />
											</div>
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-trash-o fa-fw"></i> Supprimer ce compte</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo $this->generateUrl('profile', 'delete', array('csrf' => $_SESSION['csrf'])); ?>" method="POST">
											<div class="checkbox">
												<label>
													<input name="delete_account" type="checkbox" value="1" /> Je suis totalement sûr de vouloir supprimer ce compte 
												</label>
											</div>	
											<div class="text-center">
												<button class="btn btn-danger">Supprimer ce compte</button>
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
			var target = jQuery(this).parents('.action-dropdown').attr('target');
			var url = jQuery(this).attr('href');
			jQuery(target).find('input:checked').each(function ()
			{
				url += '/users' + jQuery(this).val() + '_' + jQuery(this).val();
			});
			window.location = url;
		});
	});
</script>
<?php
	$incs->footer();
