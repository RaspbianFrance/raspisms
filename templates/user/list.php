<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Users - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'users'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Utilisateurs</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-user"></i> Utilisateurs
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-user fa-fw"></i> Liste des utilisateurs</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped datatable" id="table-users">
                                        <thead>
                                            <tr>
                                                <th>Email</th>
                                                <th>Admin</th>
                                                <th>Status</th>
                                                <th class="checkcolumn">&#10003;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($users as $user) { ?>
                                            <tr>
                                                <td><?php $this->s($user['email']); ?></td>
                                                <td><?php $this->s($user['admin']); ?></td>
                                                <td><?php $this->s($user['status']); ?></td>
                                                <td><input type="checkbox" value="<?php $this->s($user['id']); ?>" name="ids[]"></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                                    <div>
                                        <div class="col-xs-6 no-padding">
                                            <a class="btn btn-success" href="<?php echo \descartes\Router::url('User', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un utilisateur</a>
                                        </div>
                                        <div class="text-right col-xs-6 no-padding">
                                            <strong>Action pour la séléction :</strong>
                                            <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('User', 'update_status', ['csrf' => $_SESSION['csrf'], 'status' => 0]); ?>"><span class="fa fa-pause"></span> Suspendre</button>
                                            <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('User', 'update_status', ['csrf' => $_SESSION['csrf'], 'status' => 1]); ?>"><span class="fa fa-play"></span> Activer</button>
                                            <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('User', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
