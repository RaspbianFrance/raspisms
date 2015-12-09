<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Users - Show All');
?>
<div id="wrapper">
<?php
	$incs->nav('users');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Nouvel utilisateur
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-user"></i> <a href="<?php echo $this->generateUrl('users'); ?>">Utilisateurs</a>
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
							<h3 class="panel-title"><i class="fa fa-user fa-fw"></i> Ajout d'un utilisateur</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo $this->generateUrl('users', 'create', [$_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Adresse e-mail</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-at"></span></span>
										<input name="email" class="form-control" type="email" placeholder="Adresse e-mail de l'utilisateur" autofocus required>
									</div>
								</div>	
								<div class="form-group">
									<label>Confirmer adresse e-mail</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-at"></span></span>
										<input name="email_confirm" class="form-control" type="email" placeholder="Confirmer l'adresse e-mail de l'utilisateur" required>
									</div>
								</div>
								<div class="form-group">
									<label>Mot de passe (laissez vide pour générer le mot de passe automatiquement)</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-lock"></span></span>
										<input name="password" class="form-control" type="password" placeholder="Mot de passe de l'utilisateur" >
									</div>
								</div>
								<div class="form-group">
									<label>Confirmer le mot de passe</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-lock"></span></span>
										<input name="password_confirm" class="form-control" type="password" placeholder="Confirmer le mot de passe de l'utilisateur" >
									</div>
								</div>
								<?php if (isset($_SESSION['admin']) && $_SESSION['admin']) { ?>
									<div class="form-group">
										<label>Niveau administrateur : </label>
										<div class="form-group">
											<input name="admin" type="radio" value="1" required /> Oui 
											<input name="admin" type="radio" value="0" required /> Non
										</div>
									</div>
								<?php } ?>	
								<a class="btn btn-danger" href="<?php echo $this->generateUrl('users'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le user" /> 	
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
	$incs->footer();
