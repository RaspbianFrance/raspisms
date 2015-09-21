<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Scheduleds - Show All');
?>
<div id="wrapper">
<?php
	$incs->nav('scheduleds');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Scheduleds</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-calendar"></i> Scheduleds
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-calendar fa-fw"></i> Liste des SMS programmés</h3>
						</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped" id="table-scheduleds">
									<thead>
										<tr>
											<th>#</th>
											<th>Date</th>
											<th>Contenu</th>
											<th style="width:5%;">Sélectionner</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($scheduleds as $scheduled)
										{
											?>
											<tr>
												<td><?php secho($scheduled['id']); ?></td>
												<td><?php secho($scheduled['at']); ?></td>
												<td><?php secho($scheduled['content']); ?></td>
												<td><input type="checkbox" value="<?php secho($scheduled['id']); ?>"></td>
											</tr>
											<?php
										}
									?>
									</tbody>
								</table>
							</div>
								<div>
									<div class="col-xs-6 no-padding">
										<a class="btn btn-success" href="<?php echo $this->generateUrl('scheduleds', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un SMS programmé</a>
									</div>
									<div class="text-right col-xs-6 no-padding">
										<strong>Action groupée :</strong> 
										<div class="btn-group action-dropdown" target="#table-scheduleds">
											<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Action pour la sélection <span class="caret"></span></button>
											<ul class="dropdown-menu pull-right" role="menu">
												<li><a href="<?php echo $this->generateUrl('scheduleds', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</a></li>
												<li><a href="<?php echo $this->generateUrl('scheduleds', 'delete', [$_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</a></li>
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
