<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Réglages'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'settings'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Réglages</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-cogs"></i> Réglages
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-cogs fa-fw"></i> Les réglages de RaspiSMS</h3>
						</div>
						<div class="panel-body">
                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-phone fa-fw"></i> Pays par défaut numéros internationaux</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'default_phone_country', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Code du pays (norme ISO 3166-1 alpha-2) : </label>
												<input name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>" />
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-phone fa-fw"></i> Pays préférés numéros internationaux</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'preferred_phone_country', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Code des pays (norme ISO 3166-1 alpha-2) séparés par des virgules : </label>
												<input name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['preferred_phone_country']); ?>" />
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-flag fa-fw"></i> Pays autorisés pour l'envoi</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'authorized_phone_country', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Code des pays (norme ISO 3166-1 alpha-2) séparés par des virgules (laissez vide pour tout autoriser) : </label>
												<input name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['authorized_phone_country']); ?>" />
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
                                <div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-font fa-fw"></i> Alphabet SMS optimisé</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'force_gsm_alphabet', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Optimiser la taille des SMS en remplaçant les caractères spéciaux par leur équivalent GSM 7-bit : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['force_gsm_alphabet'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>
                           
                            <div class="col-xs-12 col-md-6">
                                <div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-picture-o fa-fw"></i> Support des MMS</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'mms', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Activer les MMS : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['mms'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-flash fa-fw"></i> Support des SMS Flash</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'sms_flash', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>SMS Flash activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['sms_flash'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>
                            
							<div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-ban fa-fw"></i> Activation de SMS-STOP</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'smsstop', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>SMS STOP activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['smsstop'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-code fa-fw"></i> Support du templating</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'templating', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Templating activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['templating'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-bullseye fa-fw"></i> Support des groupes conditionnels</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'conditional_group', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Groupes conditionnels activés : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['conditional_group'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-thermometer-3 fa-fw"></i> Support des limites d'envoi par téléphones</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_limit', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Limites d'envoi par téléphones activées : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['phone_limit'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-sort-numeric-desc fa-fw"></i> Support des téléphones prioritaires</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_priority', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Téléphones prioritaires activés : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['phone_priority'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-plug fa-fw"></i> Activation de Webhooks</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'webhook', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Webhooks activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['webhook'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-share fa-fw"></i> Transfert des SMS par e-mail</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'transfer', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Transfert activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['transfer'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
                                </div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-exclamation-triangle fa-fw"></i> Alerte limite de SMS atteinte</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'alert_quota_limit_reached', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Recevoir un e-mail quand la limite de SMS est atteinte :</label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['alert_quota_limit_reached'] == 1 ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
                                </div>
                            </div>
                            
                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-exclamation fa-fw"></i> Alerte limite de SMS proche</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'alert_quota_limit_close', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Recevoir un e-mail quand le nombre de SMS envoyés dépasse un pourcentage de la limite : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Désactivé</option>
													<option value="0.7" <?php echo $_SESSION['user']['settings']['alert_quota_limit_close'] == 0.7 ? 'selected' : ''; ?>>70%</option>
													<option value="0.75" <?php echo $_SESSION['user']['settings']['alert_quota_limit_close'] == 0.75 ? 'selected' : ''; ?>>75%</option>
													<option value="0.8" <?php echo $_SESSION['user']['settings']['alert_quota_limit_close'] == 0.8 ? 'selected' : ''; ?>>80%</option>
													<option value="0.85" <?php echo $_SESSION['user']['settings']['alert_quota_limit_close'] == 0.85 ? 'selected' : ''; ?>>85%</option>
													<option value="0.9" <?php echo $_SESSION['user']['settings']['alert_quota_limit_close'] == 0.9 ? 'selected' : ''; ?>>90%</option>
													<option value="0.95" <?php echo $_SESSION['user']['settings']['alert_quota_limit_close'] == 0.95 ? 'selected' : ''; ?>>95%</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>
                            
                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-music fa-fw"></i> Son sur reception d'un SMS</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'sms_reception_sound', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Jouer un son quand vous recevez un SMS : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['sms_reception_sound'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-link fa-fw"></i> Détection des URL dans les discussions</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'detect_url', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Détection activé : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['detect_url'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-question-circle fa-fw"></i> Affichage de l'aide</h4>
									</div>
									<div class="panel-body">
										<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'display_help', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
											<div class="form-group">
												<label>Afficher l'aide : </label>
												<select name="setting_value" class="form-control">
													<option value="0">Non</option>
													<option value="1" <?php echo $_SESSION['user']['settings']['display_help'] ? 'selected' : ''; ?>>Oui</option>
												</select>
											</div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
									</div>
								</div>
                            </div>

                            <div class="col-xs-12 col-md-6">
								<div class="panel panel-default">
									<div class="panel-heading">
										<h4 class="panel-title"><i class="fa fa-eye-slash fa-fw"></i> Cacher des menus</h4>
									</div>
									<div class="panel-body">
                                        <form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'hide_menus', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
                                            <input type="hidden" name="allow_no_value" value="1" />
											<div class="form-group">
                                                <label>Cacher certains menus à  l'utilisateur (ces menus restent accessibles par l'URL) : </label>
                                                <input name="setting_value[]" class="add-hide-menus form-control" type="text" placeholder="Menus à cacher" value="<?php $this->s($_SESSION['user']['settings']['hide_menus']); ?>">
                                            </div>	
											<div class="text-center">
												<button class="btn btn-success">Mettre à jour les données</button>	
											</div>
										</form>
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

        jQuery('.add-hide-menus').each(function()
        {
            jQuery(this).magicSuggest({
                data: [
                    {'id': 'logs', 'name': 'Logs'},
                    {"id": "smsstop", "name": "SMS Stop"},
                    {"id": "calls", "name": "Appels"},
                    {"id": "events", "name": "Évènements"},
                    {"id": "commands", "name": "Commandes"},
                    {"id": "phones", "name": "Téléphones"},
                    {"id": "settings", "name": "Réglages"}, 
                ],
                valueField: 'id',
                displayField: 'name',
                name: 'hide_menus[]',
                maxSelection: null,
            });
        });

	});
</script>
<?php
	$this->render('incs/footer');
