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
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped" id="table-phones">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Numéro</th>
                                                <th>Adaptateur</th>
                                                <th style="width:5%;">Sélectionner</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($phones as $phone) { ?>
                                            <tr>
                                                <td><?php $this->s($phone['id']); ?></td>
                                                <td><?php $this->s($phone['number']); ?></td>
                                                <td><?php $this->s($phone['adapter']); ?></td>
                                                <td><input type="checkbox" value="<?php $this->s($phone['id']); ?>" name="ids[]"></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                    <div>
                                        <div class="col-xs-6 no-padding">
                                            <a class="btn btn-success" href="<?php echo \descartes\Router::url('Phone', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un téléphone</a>
                                        </div>
                                        <div class="text-right col-xs-6 no-padding">
                                            <strong>Action pour la séléction :</strong>
                                            <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Phone', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
                                        </div>
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
