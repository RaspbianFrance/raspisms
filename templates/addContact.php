<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Contacts - Show All');
?>
<div id="wrapper">
<?php
	$incs->nav('contacts');
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
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-user"></i> <a href="<?php echo $this->generateUrl('contacts'); ?>">Contacts</a>
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
							<form action="<?php echo $this->generateUrl('contacts', 'create', array('csrf' => $_SESSION['csrf']));?>" method="POST">
								<div class="form-group">
									<label>Nom contact</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-user"></span></span>
										<input name="name" class="form-control" type="text" placeholder="Nom contact" autofocus required>
									</div>
								</div>	
								<div class="form-group">
									<label>Numéro de téléphone du contact</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-phone"></span></span>
										<input name="phone" class="form-control" type="text" placeholder="Numéro du contact" pattern="0[1-9]([0-9] ?){8}" required>
									</div>
								</div>
								<a class="btn btn-danger" href="<?php echo $this->generateUrl('contacts'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le contact" /> 	
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
