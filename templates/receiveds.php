<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Receiveds - Show All');
?>
<div id="wrapper">
<?php
	$incs->nav('receiveds');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>SMS reçus</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-download "></i> SMS reçus
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-download  fa-fw"></i> Liste des SMS reçus</h3>
						</div>
						<div class="panel-body">
							<div class="table-receiveds">
								<table class="table table-bordered table-hover table-striped" id="table-receiveds">
									<thead>
										<tr>
											<th>#</th>
											<th>Numéro</th>
											<th>Message</th>
											<th>Date</th>
											<th>Commande</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($receiveds as $received)
										{
											?>
											<tr>
												<td><?php secho($received['id']); ?></td>
												<td><?php secho($received['send_by']); ?></td>
												<td><?php secho($received['content']); ?></td>
												<td><?php secho($received['at']); ?></td>
												<td><?php echo $received['is_command'] ? 'Oui' : 'Non'; ?></td>
											</tr>
											<?php
										}
									?>
									</tbody>
								</table>
							</div>
							<nav>
								<ul class="pager">
									<?php
										if ($page)
										{
										?>
											<li><a href="<?php echo $this->generateUrl('receiveds', 'showAll', array('page' => $page - 1)); ?>"><span aria-hidden="true">&larr;</span> Précèdents</a></li>
										<?php
										}

										$numero_page = 'Page : ' . ($page + 1);
										secho($numero_page);

										if ($limit == $nbResults)
										{
										?>
											<li><a href="<?php echo $this->generateUrl('receiveds', 'showAll', array('page' => $page + 1)); ?>">Suivants <span aria-hidden="true">&rarr;</span></a></li>
										<?php
										}
									?>
								</ul>
							</nav>
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
