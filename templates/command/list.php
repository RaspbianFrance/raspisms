<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Commands - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'commands'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Commandes</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-terminal"></i> Commandes
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-terminal fa-fw"></i> Liste des commandes</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <?php if (!$commands) { ?>
                                    <p>Aucune commande n'a été créée pour le moment.</p>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped" id="table-commands">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nom</th>
                                                    <th>Script</th>
                                                    <th>Admin obligatoire</th>
                                                    <th style="width:5%;">Sélectionner</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                foreach ($commands as $command)
                                                {
                                                    ?>
                                                    <tr>
                                                        <td><?php $this->s($command['id']); ?></td>
                                                        <td><?php $this->s($command['name']); ?></td>
                                                        <td><?php $this->s($command['script']); ?></td>
                                                        <td><?php echo $command['admin'] ? 'Oui' : 'Non' ; ?></td>
                                                        <td><input type="checkbox" name="ids[]" value="<?php $this->s($command['id']); ?>"></td>
                                                    </tr>
                                                    <?php
                                                }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                <div>
                                    <div class="col-xs-6 no-padding">
                                        <a class="btn btn-success" href="<?php echo \Router::url('Command', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter une commande</a>
                                    </div>
                                    <?php if ($commands) { ?>
                                        <div class="text-right col-xs-6 no-padding">
                                            <strong>Action pour la séléction :</strong>
                                            <button class="btn btn-default" type="submit" formaction="<?php echo \Router::url('Command', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</button>
                                            <button class="btn btn-default" type="submit" formaction="<?php echo \Router::url('Command', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
</div>
<script>
	jQuery(document).ready(function ()
	{
		jQuery('.action-dropdown a').on('click', function (e)
		{
			e.preventDefault();
			var target = jQuery(this).parents('.action-dropdown').attr('target');
			var url = jQuery(this).attr('href');
			jQuery(target).find('input:checked').each(function ()
			{
				url += '/' + jQuery(this).val();
			});
			window.location = url;
		});
	});
</script>
<?php
	$this->render('incs/footer');
