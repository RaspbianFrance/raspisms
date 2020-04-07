<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Receiveds - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'receiveds'])
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
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
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
                            <form method="GET">
                                <?php if (!$receiveds) { ?>
                                    <p>Aucun SMS n'a été reçu pour le moment.</p>
                                <?php } else { ?>
                                    <div class="table-receiveds">
                                        <table class="table table-bordered table-hover table-striped datatable" id="table-receiveds">
                                            <thead>
                                                <tr>
                                                    <th>De</th>
                                                    <th>À</th>
                                                    <th>Message</th>
                                                    <th>Date</th>
                                                    <th>Status</th>
                                                    <th>Commande</th>
                                                    <?php if ($_SESSION['user']['admin']) { ?><th class="checkcolumn">&#10003;</th><?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($receiveds as $received) { ?>
                                                    <tr>
                                                        <td class="no-wrap">
                                                            <?php if ($received['contact'] ?? false) { ?>
                                                                <?php echo \controllers\internals\Tool::phone_link($received['origin']) . ' (' . $received['contact'] . ')'; ?>
                                                            <?php } else { ?>
                                                                <?php echo \controllers\internals\Tool::phone_link($received['origin']); ?>
                                                            <?php } ?>
                                                        </td>
                                                        <td class="no-wrap"><?php $this->s($received['phone_name'] ?? 'Inconnu'); ?></td>
                                                        <td><?php $this->s($received['text']); ?></td>
                                                        <td><?php $this->s($received['at']); ?></td>
                                                        <td><?php echo ($received['status'] == 'read' ? 'Lu' : 'Non lu'); ?></td>
                                                        <td><?php echo $received['command'] ? 'Oui' : 'Non'; ?></td>
                                                        <?php if ($_SESSION['user']['admin']) { ?><td><input name="ids[]" type="checkbox" value="<?php $this->s($received['id']); ?>"></td><?php } ?>
                                                    </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div>
                                        <?php if ($_SESSION['user']['admin']) { ?>
                                            <div class="text-right col-xs-12 no-padding">
                                                <strong>Action pour la séléction :</strong>
                                                <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Received', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
