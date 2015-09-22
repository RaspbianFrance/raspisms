<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('SMS STOP - Show All');
?>
<div id="wrapper">
<?php
	$incs->nav('smsstop');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>SMS STOP</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-ban"></i> SMS STOP
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-ban fa-fw"></i> Liste SMS STOP</h3>
						</div>
						<div class="panel-body">
							<div class="table-events">
								<table class="table table-bordered table-hover table-striped" id="table-sms-stop">
									<thead>
										<tr>
											<th>#</th>
											<th>Numéro</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($smsStops as $smsStop)
										{
											?>
											<tr>
												<td><?php secho($smsStop['id']); ?></td>
												<td><?php secho($smsStop['number']); ?></td>
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
											<li><a href="<?php echo $this->generateUrl('smsstop', 'showAll', array('page' => $page - 1)); ?>"><span aria-hidden="true">&larr;</span> Précèdents</a></li>
										<?php
										}

										$numero_page = 'Page : ' . ($page + 1);
										secho($numero_page);

										if ($limit == $nbResults)
										{
										?>
											<li><a href="<?php echo $this->generateUrl('smsstop', 'showAll', array('page' => $page + 1)); ?>">Suivants <span aria-hidden="true">&rarr;</span></a></li>
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
