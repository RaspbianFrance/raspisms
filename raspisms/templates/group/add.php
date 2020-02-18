<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Groupes - Add'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'groupes'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Nouveau groupe
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-group"></i> <a href="<?php echo \descartes\Router::url('Group', 'list'); ?>">Groupes</a>
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
							<h3 class="panel-title"><i class="fa fa-group fa-fw"></i> Ajout d'un groupe</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Group', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Nom du groupe</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-users"></span></span>
										<input name="name" class="form-control" type="text" placeholder="Nom groupe" autofocus required>
									</div>
								</div>	
								<div class="form-group">
									<label>Contacts du groupe</label>
									<input class="add-contacts form-control" name="contacts[]"/>
								</div>
								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('Group', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le groupe" /> 	
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function()
	{
		jQuery('.add-contacts').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo \descartes\Router::url('Contact', 'json_list'); ?>',
				valueField: 'id',
				displayField: 'name',
				name: 'contacts[]'
			});
		});
	});
</script>
<?php
	$this->render('incs/footer');
