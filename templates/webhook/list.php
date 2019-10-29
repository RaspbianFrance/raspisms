<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Webhooks - Show All');
?>
<div id="wrapper">
<?php
	$incs->nav('webhooks');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Webhooks</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-plug"></i> Webhooks
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-plug fa-fw"></i> Liste des webhooks</h3>
						</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped" id="table-webhooks">
									<thead>
										<tr>
											<th>#</th>
											<th>Url</th>
											<th>Type de Webhook</th>
											<th style="width:5%;">Sélectionner</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($webhooks as $webhook)
										{
											?>
											<tr>
												<td><?php $this->s($webhook['id']); ?></td>
												<td><?php $this->s($webhook['url']); ?></td>
												<td><?php echo array_search($webhook['type'], internalConstants::WEBHOOK_TYPE); ?></td>
												<td><input type="checkbox" value="<?php $this->s($webhook['id']); ?>"></td>
											</tr>
											<?php
										}
									?>
									</tbody>
								</table>
							</div>
							<div>
								<div class="col-xs-6 no-padding">
									<a class="btn btn-success" href="<?php echo \Router::url('webhooks', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un webhook</a>
								</div>
								<div class="text-right col-xs-6 no-padding">
									<strong>Action pour la séléction :</strong> 
									<div class="btn-groupe action-dropdown" target="#table-webhooks">
										<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">Action pour la sélection <span class="caret"></span></button>
										<ul class="dropdown-menu pull-right" role="menu">
											<li><a href="<?php echo \Router::url('webhooks', 'edit', [$_SESSION['csrf']]); ?>"><span class="fa fa-edit"></span> Modifier</a></li>
											<li><a href="<?php echo \Router::url('webhooks', 'delete', [$_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</a></li>
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
