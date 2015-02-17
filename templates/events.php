<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Events - Show All');
?>
<div id="wrapper">
<?php
	$incs->nav('events');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Évènements</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-clock"></i> Évènements
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-clock fa-fw"></i> Liste des évènements</h3>
						</div>
						<div class="panel-body">
							<div class="table-events">
								<table class="table table-bordered table-hover table-striped" id="table-events">
									<thead>
										<tr>
											<th>#</th>
											<th>Type</th>
											<th>Date</th>
											<th>Texte</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($events as $event)
										{
											?>
											<tr>
												<td><?php secho($event['id']); ?></td>
												<td><span class="fa fa-fw <?php echo internalTools::eventTypeToIcon($event['type']); ?>"></span></td>
												<td><?php secho($event['at']); ?></td>
												<td><?php secho($event['text']); ?></td>
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
											<li><a href="<?php echo $this->generateUrl('events', 'showAll', array('page' => $page - 1)); ?>"><span aria-hidden="true">&larr;</span> Précèdents</a></li>
										<?php
										}

										$numero_page = 'Page : ' . ($page + 1);
										secho($numero_page);

										if ($limit == $nbResults)
										{
										?>
											<li><a href="<?php echo $this->generateUrl('events', 'showAll', array('page' => $page + 1)); ?>">Suivants <span aria-hidden="true">&rarr;</span></a></li>
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
