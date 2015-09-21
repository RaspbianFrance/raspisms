<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Command - Edit');
?>
<div id="wrapper">
<?php
	$incs->nav('commands');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Modification commandes
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-terminal"></i> <a href="<?php echo $this->generateUrl('commands'); ?>">Commandes</a>
						</li>
						<li class="active">
							<i class="fa fa-edit"></i> Modifier
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-edit fa-fw"></i>Modification de commandes</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo $this->generateUrl('commands', 'update', [$_SESSION['csrf']]);?>" method="POST">
								<?php
									foreach ($commands as $command)
									{
									?>
										<div class="form-group">
											<label>Nom commande</label>
											<div class="form-group">
												<input value="<?php secho($command['name']); ?>" name="commands[<?php secho($command['id']); ?>][name]" class="form-control" type="text" placeholder="Nom commande" autofocus required>
											</div>
										</div>	
										<div class="form-group">
											<label>Commande à appeler (la commande sera appelée depuis le dossier "<?php echo PWD_SCRIPTS; ?>")</label>
											<div class="form-group input-group">
												<span class="input-group-addon"><span class="fa fa-link"></span></span>
												<input value="<?php secho($command['script']); ?>" name="commands[<?php secho($command['id']); ?>][script]" class="form-control" type="text" placeholder="Ex : chauffage/monter.sh" autofocus required>
											</div>
										</div>	
										<div class="form-group">
											<label>Niveau administrateur obligatoire</label>
											<div class="form-group">
												<input <?php echo $command['admin'] ? 'checked' : ''; ?> name="commands[<?php secho($command['id']); ?>][admin]" type="radio" value="1" required /> Oui 
												<input <?php echo $command['admin'] ? '' : 'checked'; ?> name="commands[<?php secho($command['id']); ?>][admin]" type="radio" value="0" required /> Non
											</div>
										</div>
										<hr/>
									<?php
									}
								?>
								<a class="btn btn-danger" href="<?php echo $this->generateUrl('commands'); ?>">Annuler</a>
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
	$incs->footer();
