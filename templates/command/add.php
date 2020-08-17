<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Command - Add'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'commands'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Nouvelle commande
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-terminal"></i> <a href="<?php echo \descartes\Router::url('Command', 'list'); ?>">Commandes</a>
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
							<h3 class="panel-title"><i class="fa fa-terminal fa-fw"></i> Ajout d'une nouvelle commande</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Command', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Nom commande</label>
									<div class="form-group">
                                    <input name="name" class="form-control" type="text" placeholder="Nom commande" autofocus required value="<?php $this->s($_SESSION['previous_http_post']['name'] ?? '') ?>">
									</div>
								</div>	
								<div class="form-group">
									<label>Commande à appeler (la commande sera appelée depuis le dossier "<?php echo PWD_SCRIPTS; ?>")</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-link"></span></span>
										<input name="script" class="form-control" type="text" placeholder="Ex : chauffage/monter.sh" autofocus required value="<?php $this->s($_SESSION['previous_http_post']['script'] ?? ''); ?>">
									</div>
								</div>	
								<div class="form-group">
									<label>Niveau administrateur obligatoire</label>
									<div class="form-group">
                                        <input name="admin" type="radio" value="1" required <?= (isset($_SESSION['previous_http_post']['admin']) && (bool) $_SESSION['previous_http_post']['admin']) ? 'checked' : ''; ?>/> Oui 
										<input name="admin" type="radio" value="0" required <?= (isset($_SESSION['previous_http_post']['admin']) && !(bool) $_SESSION['previous_http_post']['admin']) ? '' : 'checked'; ?>/> Non
									</div>
								</div>
								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('Command', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer la commande" /> 	
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
