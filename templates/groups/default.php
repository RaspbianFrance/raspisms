<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Groups - Show All');
?>
<div id="wrapper">
<?php
	$incs->nav('groups');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Groupes</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-group"></i> Groupes
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-group fa-fw"></i> Liste des groupes</h3>
						</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped" id="table-groups">
									<thead>
										<tr>
											<th>#</th>
											<th>Nom</th>
											<th>Nombre de contacts</th>
											<th style="width:5%;">Sélectionner</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($groups as $group)
										{
											?>
											<tr>
												<td><?php secho($group['id']); ?></td>
												<td><?php secho($group['name']); ?></td>
												<td><?php secho($group['nb_contacts']); ?></td>
												<td><input type="checkbox" value="<?php secho($group['id']); ?>"></td>
											</tr>
											<?php
										}
									?>
									</tbody>
								</table>
							</div>
							<div>
								<div class="col-xs-6 no-padding">
									<a class="btn btn-success" href="<?php echo $this->generateUrl('groups', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un groupe</a>
								</div>
								<div class="text-right col-xs-6 no-padding">
									<strong>Action groupée :</strong> 
									<div class="btn-group action-dropdown" target="#table-groups">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Action pour la sélection <span class="caret"></span></button>
										<ul class="dropdown-menu pull-right" role="menu">
											<li><a href="<?php echo $this->generateUrl('groups', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</a></li>
											<li><a href="<?php echo $this->generateUrl('groups', 'delete', [$_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</a></li>
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
