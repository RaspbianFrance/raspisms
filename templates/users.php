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
						Dashboard <small>Utilisateurs</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-user"></i> Utilisateurs
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-user fa-fw"></i> Liste des utilisateurs</h3>
						</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped" id="table-users">
									<thead>
										<tr>
											<th>#</th>
											<th>Email</th>
											<th>Admin</th>
											<th style="width:5%;">Sélectionner</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($users as $user)
										{
											?>
											<tr>
												<td><?php secho($user['id']); ?></td>
												<td><?php secho($user['email']); ?></td>
												<td><?php secho($user['admin']); ?></td>
												<td><input type="checkbox" value="<?php secho($user['id']); ?>"></td>
											</tr>
											<?php
										}
									?>
									</tbody>
								</table>
							</div>
								<div>
									<div class="col-xs-6 no-padding">
										<a class="btn btn-success" href="<?php echo $this->generateUrl('users', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un utilisateur</a>
									</div>
									<div class="text-right col-xs-6 no-padding">
										<strong>Action groupée :</strong> 
										<div class="btn-group action-dropdown" target="#table-users">
											<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Action pour la sélection <span class="caret"></span></button>
											<ul class="dropdown-menu pull-right" role="menu">
												<li><a href="<?php echo $this->generateUrl('users', 'delete', [$_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</a></li>
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
