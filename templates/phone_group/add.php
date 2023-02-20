<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Groupes de Téléphones - Add'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'phone_groups'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Nouveau groupe de téléphones
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-list-alt"></i> <a href="<?php echo \descartes\Router::url('PhoneGroup', 'list'); ?>">Groupes de téléphones</a>
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
							<h3 class="panel-title"><i class="fa fa-list-alt fa-fw"></i> Ajout d'un groupe de téléphones</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('PhoneGroup', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Nom du groupe de téléphone</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-phone"></span></span>
										<input name="name" class="form-control" type="text" placeholder="Nom du groupe" autofocus required value="<?php $this->s($_SESSION['previous_http_post']['name'] ?? '') ?>">
									</div>
								</div>	
								<div class="form-group">
									<label>Téléphones du groupe</label>
									<input class="add-phones form-control" name="phones[]" value="<?php $this->s(json_encode($_SESSION['previous_http_post']['phones'] ?? [])); ?>"/>
								</div>
								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('PhoneGroup', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le groupe de téléphones" /> 	
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
		jQuery('.add-phones').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo \descartes\Router::url('Phone', 'json_list'); ?>',
				valueField: 'id',
				displayField: 'name',
                name: 'phones[]',
                maxSelection: null,
			});
		});
	});
</script>
<?php
	$this->render('incs/footer');
