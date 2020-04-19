<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Phones - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'phone'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Téléphones</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-phone"></i> Téléphones
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-phone fa-fw"></i> Liste des téléphones</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <?php if (!$phones) { ?>
                                    <p>Aucun téléphone utilisable n'a été ajouté pour le moment.</p>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped datatable" id="table-phones">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Nom</th>
                                                    <th>Adaptateur</th>
                                                    <th>Callbacks</th>
                                                    <th class="checkcolumn">&#10003;</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($phones as $phone) { ?>
                                                <tr>
                                                    <td><?php $this->s($phone['id']); ?></td>
                                                    <td><?php $this->s($phone['name']); ?></td>
                                                    <td><?php $this->s($phone['adapter']); ?></td>
                                                    <td>
                                                        <div class="bold">Reception d'un SMS : </div>
                                                        <?php if ($phone['callback_reception'] ?? false) { ?>
                                                            <div><code><?= $phone['callback_reception']; ?></code></div>
                                                        <?php } else { ?>
                                                            <div>Non disponible.</div>
                                                        <?php } ?>
                                                        <br/>
                                                        <div class="bold">Changement de status d'un SMS : </div>
                                                        <?php if ($phone['callback_status'] ?? false) { ?>
                                                            <div><code><?= $phone['callback_status']; ?></code></div>
                                                        <?php } else { ?>
                                                            <div>Non disponible.</div>
                                                        <?php } ?>
                                                    </td>
                                                    <td><input type="checkbox" value="<?php $this->s($phone['id']); ?>" name="ids[]"></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                    <div>
                                        <div class="col-xs-6 no-padding">
                                            <a class="btn btn-success" href="<?php echo \descartes\Router::url('Phone', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un téléphone</a>
                                        </div>
                                        <?php if ($phones) { ?>
                                            <div class="text-right col-xs-6 no-padding">
                                                <strong>Action pour la séléction :</strong>
                                                <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Phone', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </div>
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
