<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Discussions - Show All');
?>
<div id="wrapper">
<?php
	$incs->nav('discussions');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Discussions</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-comments-o"></i> Discussions
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-comments-o fa-fw"></i> Liste des discussions</h3>
						</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped" id="table-discussions">
									<thead>
										<tr>
											<th>Date du dernier message</th>
											<th>Numéro</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($discussions as $discussion)
										{
											?>
												<tr class="goto" url="<?php secho($this->generateUrl('discussions', 'show', [$discussion['number']])); ?>">
												<td><?php secho($discussion['at']); ?></td>
												<td><?php secho(isset($discussion['contact']) ? $discussion['contact'] . ' (' . $discussion['number'] . ')' : $discussion['number']); ?></td>
											</tr>
											<?php
										}
									?>
									</tbody>
								</table>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<?php
	$incs->footer();
