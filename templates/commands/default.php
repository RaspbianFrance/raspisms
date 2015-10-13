<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Commands - Show All');
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
						Dashboard <small>Commandes</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-terminal"></i> Commandes
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-terminal fa-fw"></i> Liste des commandes</h3>
						</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped" id="table-commands">
									<thead>
										<tr>
											<th>#</th>
											<th>Nom</th>
											<th>Script</th>
											<th>Admin obligatoire</th>
											<th style="width:5%;">Sélectionner</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($commands as $command)
										{
											?>
											<tr>
												<td><?php secho($command['id']); ?></td>
												<td><?php secho($command['name']); ?></td>
												<td><?php secho($command['script']); ?></td>
												<td><?php echo $command['admin'] ? 'Oui' : 'Non' ; ?></td>
												<td><input type="checkbox" value="<?php secho($command['id']); ?>"></td>
											</tr>
											<?php
										}
									?>
									</tbody>
								</table>
							</div>
							<div>
								<div class="col-xs-6 no-padding">
									<a class="btn btn-success" href="<?php echo $this->generateUrl('commands', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter une commande</a>
								</div>
								<div class="text-right col-xs-6 no-padding">
									<strong>Action groupée :</strong> 
									<div class="btn-group action-dropdown" target="#table-commands">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Action pour la sélection <span class="caret"></span></button>
										<ul class="dropdown-menu pull-right" role="menu">
											<li><a href="<?php echo $this->generateUrl('commands', 'edit', [$_SESSION['csrf']]); ?>"><span class="fa fa-edit"></span> Modifier</a></li>
											<li><a href="<?php echo $this->generateUrl('commands', 'delete', [$_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</a></li>
										</ul>
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
				url += '/' + jQuery(this).val();
			});
			window.location = url;
		});
	});
</script>
<?php
	$incs->footer();
