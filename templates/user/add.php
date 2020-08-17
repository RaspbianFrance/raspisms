<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Users - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'users'])
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
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-user"></i> <a href="<?php echo \descartes\Router::url('User', 'list'); ?>">Utilisateurs</a>
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
							<form action="<?php echo \descartes\Router::url('User', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Adresse e-mail</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-at"></span></span>
										<input name="email" class="form-control" type="email" placeholder="Adresse e-mail de l'utilisateur" autofocus required value="<?php $this->s($_SESSION['previous_http_post']['email'] ?? '') ?>">
									</div>
								</div>	
								<div class="form-group">
									<label>Mot de passe (laissez vide pour générer le mot de passe automatiquement)</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-lock"></span></span>
										<input name="password" class="form-control" type="password" placeholder="Mot de passe de l'utilisateur">
									</div>
								</div>
								<?php if (isset($_SESSION['user']['admin']) && $_SESSION['user']['admin']) { ?>
									<div class="form-group">
										<label>Niveau administrateur : </label>
										<div class="form-group">
											<input name="admin" type="radio" value="1" required <?= (isset($_SESSION['previous_http_post']['admin']) && (bool) $_SESSION['previous_http_post']['admin']) ? 'checked' : ''; ?>/> Oui 
											<input name="admin" type="radio" value="0" required <?= (isset($_SESSION['previous_http_post']['admin']) && !(bool) $_SESSION['previous_http_post']['admin']) ? 'checked' : ''; ?>/> Non
										</div>
									</div>
								<?php } ?>	
								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('User', 'list'); ?>">Annuler</a>
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
	$this->render('incs/footer');
