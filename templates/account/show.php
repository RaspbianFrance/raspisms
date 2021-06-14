<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Profile - Show'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'Account - Show']);
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Profil</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-user"></i> Profil
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-user fa-fw"></i> Mon profil</h3>
						</div>
						<div class="panel-body">
							<div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-child fa-fw"></i> Mes données</h4>
									</div>
									<div class="panel-body">
										<strong>Adresse e-mail :</strong> <?php $this->s($_SESSION['user']['email']); ?><br/>
										<strong>Niveau administrateur :</strong> <?php echo $_SESSION['user']['admin'] ? 'Oui' : 'Non'; ?><br/>
										<strong>Clef API :</strong> <?php echo $_SESSION['user']['api_key']; ?><br/>
									</div>
								</div>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-lock fa-fw"></i> Modifier mot de passe</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Account', 'update_password', ['csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Mot de passe :</label>
												<input name="password" type="password" class="form-control" placeholder="Nouveau mot de passe" autocomplete="new-password" />
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
                                </div>
                                <?php if (ENABLE_ACCOUNT_DELETION) { ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title"><i class="fa fa-trash-o fa-fw"></i> Supprimer ce compte</h4>
                                        </div>
                                        <div class="panel-body">
                                            <form action="<?php echo \descartes\Router::url('Account', 'delete', ['csrf' => $_SESSION['csrf']]); ?>" method="POST">
                                                <div class="checkbox">
                                                    <label>
                                                        <input name="delete_account" type="checkbox" value="1" /> Je suis totalement sûr de vouloir supprimer ce compte 
                                                    </label>
                                                </div>	
                                                <div class="text-center">
                                                    <button class="btn btn-danger">Supprimer ce compte</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                <?php } ?>
							</div>
                            <div class="col-xs-12 col-md-6">
                                <?php if ($quota) { ?>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">
                                            <h4 class="panel-title"><i class="fa fa-euro fa-fw"></i> Quota de SMS</h4>
                                        </div>
                                        <div class="panel-body">
                                            <strong>Crédit de base :</strong> <?php $this->s($quota['credit']); ?><br/>
                                            <strong>Crédit additionel :</strong> <?php $this->s($quota['additional']); ?><br/>
                                            <strong>Crédit consommés :</strong> <?php $this->s($quota['consumed']); ?> (<?= $quota_percent * 100; ?>%)<br/>
                                            <strong>Renouvellement automatique :</strong> <?php $this->s(($quota['auto_renew'] ? 'Oui, renouvellement le ' : 'Non, fin le ') . $quota['expiration_date']); ?><br/>
                                            <strong>Report des crédits non utilisés :</strong> <?= $quota['report_unused'] ? 'Oui' : 'Non'; ?><br/>
                                            <strong>Report des crédits additionels non utilisés :</strong> <?= $quota['report_unused_additional'] ? 'Oui' : 'Non'; ?><br/>
                                        </div>
                                    </div>
                                <?php } ?>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-at fa-fw"></i> Modifier e-mail</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Account', 'update_email', ['csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Adresse e-mail :</label>
												<input name="email" type="email" class="form-control" placeholder="Nouvelle adresse e-mail" />
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-key fa-fw"></i> Modifier clef API</h4>
									</div>
									<div class="panel-body">
                                        <div class="text-center">
                                            <div class="alert alert-warning text-left">Si vous générez une nouvelle clef API la clef actuelle sera supprimée. Pensez à mettre à jour toutes les callbacks chez les services externes.</div>
                                            <a class="btn btn-success btn-confirm" href="<?= \descartes\Router::url('Account', 'update_api_key', ['csrf' => $_SESSION['csrf']]); ?>" data-confirm-text="<i class='fa fa-refresh'></i> Confirmer la mise à jour"><i class="fa fa-refresh"></i> Générer une nouvelle clef API</a>	
                                        </div>
									</div>
								</div>
							</div>
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
