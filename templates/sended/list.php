<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Sendeds - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'sendeds'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>SMS envoyés</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-upload"></i> SMS envoyés
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-upload fa-fw"></i> Liste des SMS envoyés</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <?php if (!$sendeds) { ?>
                                    <p>Aucun SMS n'a été envoyé pour le moment.</p>
                                <?php } else { ?>
                                    <div class="table-sendeds">
                                        <table class="table table-bordered table-hover table-striped datatable" id="table-sendeds">
                                            <thead>
                                                <tr>
                                                    <th>De</th>
                                                    <th>À</th>
                                                    <th>Message</th>
                                                    <th>Date</th>
                                                    <th>Statut</th>
                                                    <?php if ($_SESSION['user']['admin']) { ?>
                                                        <th class="checkcolumn">&#10003;</th>
                                                    <?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($sendeds as $sended) { ?>
                                                    <tr>
                                                        <td class="no-wrap"><?php $this->s($sended['phone_name'] ?? 'Inconnu'); ?></td>
                                                        <td class="no-wrap">
                                                            <?php if ($sended['contact'] ?? false) { ?>
                                                                <?php echo \controllers\internals\Tool::phone_link($sended['destination']) . ' (' . $sended['contact'] . ')'; ?>
                                                            <?php } else { ?>
                                                                <?php echo \controllers\internals\Tool::phone_link($sended['destination']); ?>
                                                            <?php } ?>
                                                        </td>
                                                        <td><?php $this->s($sended['text']); ?></td>
                                                        <td><?php $this->s($sended['at']); ?></td>

                                                        <?php if ($sended['status'] == 'unknown') { ?>
                                                            <td>Inconnu</td>
                                                        <?php } elseif ($sended['status'] == 'delivered') { ?>
                                                            <td>Délivré</td>
                                                        <?php } elseif ($sended['status'] == 'failed') { ?>
                                                            <td>Échoué</td>
                                                        <?php } ?>

                                                        <?php if ($_SESSION['user']['admin']) { ?>
                                                            <td><input name="ids[]" type="checkbox" value="<?php $this->s($sended['id']); ?>"></td>
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
                                                <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Sended', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </form>
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
