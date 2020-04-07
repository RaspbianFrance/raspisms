<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Events - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'events'])
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
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-clock-o"></i> Évènements
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-clock-o fa-fw"></i> Liste des évènements</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <?php if (!$events) { ?>
                                    <p>Aucun évènement n'a été enregistré pour le moment.</p>
                                <?php } else { ?>
                                    <div class="table-events">
                                        <table class="table table-bordered table-hover table-striped datatable" id="table-events">
                                            <thead>
                                                <tr>
                                                    <th>Type</th>
                                                    <th>Date</th>
                                                    <th>Texte</th>
                                                    <th class="checkcolumn">&#10003;</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($events as $event) { ?>
                                                    <tr>
                                                        <td><span class="fa fa-fw <?php echo \controllers\internals\Tool::event_type_to_icon($event['type']); ?>"></span></td>
                                                        <td><?php $this->s($event['at']); ?></td>
                                                        <td><?php $this->s($event['text']); ?></td>
                                                        <?php if ($_SESSION['user']['admin']) { ?>
                                                            <td><input name="ids[]" type="checkbox" value="<?php $this->s($event['id']); ?>"></td>
                                                        <?php } ?>
                                                    </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div>
                                        <?php if ($_SESSION['user']['admin']) { ?>
                                            <div class="text-right col-xs-12 no-padding">
                                                <strong>Action pour la séléction :</strong>
                                                <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Event', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </form>
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
			var destination = jQuery(this).parents('.action-dropdown').attr('destination');
			var url = jQuery(this).attr('href');
			jQuery(destination).find('input:checked').each(function ()
			{
				url += '/' + jQuery(this).val();
			});
			window.location = url;
		});
	});
</script>
<?php
	$this->render('incs/footer');
