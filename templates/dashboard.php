<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Dashboard');
?>
<div id="wrapper">
<?php
	$incs->nav('dashboard');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Statistiques d'utilisation</small>
					</h1>
					<ol class="breadcrumb">
						<li class="active">
							<i class="fa fa-dashboard"></i> Dashboard
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-3 col-md-6">
					<div class="panel panel-primary">
						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-3">
									<i class="fa fa-user fa-5x"></i>
								</div>
								<div class="col-xs-9 text-right">
									<div class="huge"><?php echo $nb_contacts; ?></div>
									<div>Contacts</div>
								</div>
							</div>
						</div>
						<a href="<?php echo $this->generateUrl('contacts') ?>">
							<div class="panel-footer">
								<span class="pull-left">Voir vos contacts</span>
								<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
								<div class="clearfix"></div>
							</div>
						</a>
					</div>
				</div>
				<div class="col-lg-3 col-md-6">
					<div class="panel panel-green">
						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-3">
									<i class="fa fa-group fa-5x"></i>
								</div>
								<div class="col-xs-9 text-right">
									<div class="huge"><?php echo $nb_groups; ?></div>
									<div>Groupes</div>
								</div>
							</div>
						</div>
						<a href="<?php echo $this->generateUrl('groups') ?>">
							<div class="panel-footer">
								<span class="pull-left">Voir les groupes</span>
								<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
								<div class="clearfix"></div>
							</div>
						</a>
					</div>
				</div>
				<div class="col-lg-3 col-md-6">
					<div class="panel panel-yellow">
						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-3">
									<i class="fa fa-calendar fa-5x"></i>
								</div>
								<div class="col-xs-9 text-right">
									<div class="huge"><?php echo $nb_scheduleds; ?></div>
									<div>SMS programmés</div>
								</div>
							</div>
						</div>
						<a href="<?php echo $this->generateUrl('scheduleds') ?>">
							<div class="panel-footer">
								<span class="pull-left">Voir les SMS programmés</span>
								<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
								<div class="clearfix"></div>
							</div>
						</a>
					</div>
				</div>
				<div class="col-lg-3 col-md-6">
					<div class="panel panel-red">
						<div class="panel-heading">
							<div class="row">
								<div class="col-xs-3">
									<i class="fa fa-terminal fa-5x"></i>
								</div>
								<div class="col-xs-9 text-right">
									<div class="huge"><?php echo $nb_commands; ?></div>
									<div>Commandes</div>
								</div>
							</div>
						</div>
						<a href="<?php echo $this->generateUrl('commands') ?>">
							<div class="panel-footer">
								<span class="pull-left">Voir les commandes</span>
								<span class="pull-right"><i class="fa fa-arrow-circle-right"></i></span>
								<div class="clearfix"></div>
							</div>
						</a>
					</div>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-area-chart fa-fw"></i> Activité de la semaine : </h3>
							<span style="color: #5CB85C;">SMS envoyés (moyenne = <?php echo $avg_sendeds; ?> par jour).</span><br/>
							<span style="color: #EDAB4D">SMS reçus (moyenne = <?php echo $avg_receiveds; ?> par jour).</span>
						</div>
						<div class="panel-body">
							<div id="morris-area-chart"></div>
						</div>
					</div>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-4">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-send fa-fw"></i> SMS Envoyés</h3>
						</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped">
									<thead>
										<tr>
											<th>Numéro</th>
											<th>Date</th>
										</tr>
									</thead>
									<tbody>
									<?php
										foreach ($sendeds as $sended)
										{
											?>
											<tr>
												<td><?php secho($sended['target']); ?></td>
												<td><?php secho($sended['at']); ?></td>
											</tr>
											<?php
										}
									?>
									</tbody>
								</table>
							</div>
							<div class="text-right">
								<a href="<?php echo $this->generateUrl('sendeds'); ?>">Voir tous les SMS envoyés <i class="fa fa-arrow-circle-right"></i></a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-download fa-fw"></i> SMS Reçus</h3>
						</div>
						<div class="panel-body">
							<div class="table-responsive">
								<table class="table table-bordered table-hover table-striped">
									<thead>
										<tr>
											<th>Numéro</th>
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
												<td><?php secho($received['send_by']); ?></td>
												<td><?php secho($received['at']); ?></td>
												<td><?php echo ($received['is_command']) ? 'Oui' : 'Non'; ?></td>
											</tr>
											<?php
										}
									?>
									</tbody>
								</table>
							</div>
							<div class="text-right">
								<a href="<?php echo $this->generateUrl('receiveds'); ?>">Voir tous les SMS reçus <i class="fa fa-arrow-circle-right"></i></a>
							</div>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-clock-o fa-fw"></i> Évènement survenus</h3>
						</div>
						<div class="panel-body">
							<div class="list-group">
								<?php
									foreach ($events as $event)
									{
										$logo = internalTools::eventTypeToIcon($event['type']);
										?>
										<a href="#" class="list-group-item">
											<span class="badge"><?php secho($event['at']); ?></span>
											<i class="fa fa-fw <?php echo $logo; ?>"></i> <?php secho($event['text']); ?>
										</a>
										<?php
									}
								?>
							</div>
							<div class="text-right">
								<a href="<?php echo $this->generateUrl('events'); ?>">Voirs tous les évènements survenus <i class="fa fa-arrow-circle-right"></i></a>
							</div>
						</div>
					</div>
				</div>
			</div>
			<!-- /.row -->

		</div>
		<!-- /.container-fluid -->

	</div>
	<!-- /#page-wrapper -->

</div>
<script>
	jQuery(document).ready(function()
	{
		Morris.Area({
			element: 'morris-area-chart',
			behaveLikeLine: true,
			fillOpacity: 0.4,
			data: <?php echo $datas_area_chart;?>,
			xkey: 'period',
			ykeys: ['sendeds', 'receiveds'],
			labels: ['SMS envoyés', 'SMS reçus'],
			lineColors: ['#5CB85C', '#EDAB4D'],
			goals: [<?php echo $avg_sendeds; ?>, <?php echo $avg_receiveds; ?>],
			goalLineColors: ['#5CB85C', '#EDAB4D'],
			goalStrokeWidth: 2,
			pointSize: 4,
			hideHover: 'auto',
			resize: true
		});
	});
</script>
<!-- /#wrapper -->
<?php
	$incs->footer();
