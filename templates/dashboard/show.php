<?php
	//Template dashboard
	
	$this->render('incs/head')
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'dashboard'])
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
						<a href="<?php echo \descartes\Router::url('Contact', 'list') ?>">
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
						<a href="<?php echo \descartes\Router::url('Group', 'list') ?>">
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
						<a href="<?php echo \descartes\Router::url('Scheduled', 'list') ?>">
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
									<i class="fa fa-eye-slash fa-5x"></i>
								</div>
								<div class="col-xs-9 text-right">
									<div class="huge"><?php echo $nb_unreads; ?></div>
									<div>SMS non lus</div>
								</div>
							</div>
						</div>
						<a href="<?php echo \descartes\Router::url('Received', 'list_unread') ?>">
							<div class="panel-footer">
								<span class="pull-left">Voir les SMS non lus</span>
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
					<div class="panel panel-default dashboard-panel-chart">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-area-chart fa-fw"></i> Activité de la semaine : </h3>
							<span style="color: #5CB85C;">SMS envoyés (moyenne = <?php echo $avg_sendeds; ?> par jour).</span><br/>
							<span style="color: #EDAB4D">SMS reçus (moyenne = <?php echo $avg_receiveds; ?> par jour).</span>
                            <?php if ($quota_unused) { ?>
                                <br/>
                                <span style="color: #d9534f">Crédits restants : <?= $quota_unused; ?>.</span>
                            <?php } ?>
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
							<h3 class="panel-title"><i class="fa fa-upload fa-fw"></i> SMS Envoyés</h3>
						</div>
                        <div class="panel-body">
                            <?php if (!$sendeds) { ?>
                                Vous n'avez envoyé aucun SMS pour l'instant.
                            <?php } else { ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped">
                                        <thead>
                                            <tr>
                                                <th>Numéro</th>
                                                <th>Date</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($sendeds as $sended) { ?>
                                                <tr>
                                                    <td><?php echo \controllers\internals\Tool::phone_link($sended['destination']); ?></td>
                                                    <td><?php $this->s($sended['at']); ?></td>
                                                </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-right">
                                    <a href="<?php echo \descartes\Router::url('Sended', 'list'); ?>">Voir tous les SMS envoyés <i class="fa fa-arrow-circle-right"></i></a>
                                </div>
                            <?php } ?>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-download fa-fw"></i> SMS Reçus</h3>
						</div>
                        <div class="panel-body">
                            <?php if (!$receiveds) { ?>
                                Vous n'avez reçu aucun SMS pour l'instant.
                            <?php } else { ?>
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
                                        <?php foreach ($receiveds as $received) { ?>
                                            <tr>
                                                <td><?php echo \controllers\internals\Tool::phone_link($received['origin']); ?></td>
                                                <td><?php $this->s($received['at']); ?></td>
                                                <td><?php echo ($received['command']) ? 'Oui' : 'Non'; ?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="text-right">
                                    <a href="<?php echo \descartes\Router::url('Received', 'list'); ?>">Voir tous les SMS reçus <i class="fa fa-arrow-circle-right"></i></a>
                                </div>
                            <?php } ?>
						</div>
					</div>
				</div>
				<div class="col-lg-4">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-clock-o fa-fw"></i> Évènements survenus</h3>
						</div>
                        <div class="panel-body">
                            <?php if (!$events) { ?>
                                Aucun évènement n'est encore survenu.
                            <?php } else { ?>
                                <div class="list-group">
                                    <?php foreach ($events as $event) { ?>
                                            <a href="#" class="list-group-item">
                                                <span class="badge"><?php $this->s($event['at']); ?></span>
                                                <i class="fa fa-fw <?php echo \controllers\internals\Tool::event_type_to_icon($event['type']); ?>"></i> <?php $this->s($event['text']); ?>
                                            </a>
                                    <?php } ?>
                                </div>
                                <div class="text-right">
                                    <a href="<?php echo \descartes\Router::url('Event', 'list'); ?>">Voirs tous les évènements survenus <i class="fa fa-arrow-circle-right"></i></a>
                                </div>
                            <?php } ?>
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
			data: <?php echo $data_area_chart;?>,
            xkey: 'period',
            parseTime: false,
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
	$this->render('incs/footer');
