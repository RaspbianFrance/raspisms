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
				<h3 class="col-xs-12 mb-5"><i class="fa fa-cogs fa-fw"></i> Réglages de RaspiSMS</h3>
				<div class="col-xs-12 mb-5">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-language fa-fw"></i> Localisation</h4>
						</div>
						<div class="panel-body">
							<p class="italic help mb-5">
								Réglages dédiés à adapter l'expérience logicielle aux pays d'utilisation.
							</p>

							<div class="row">
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
							</div>
						</div>
					</div>
				</div>


				<div class="col-xs-12 mb-5">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-heart fa-fw"></i> Ergonomie et expérience utilisateur</h4>
						</div>
						<div class="panel-body">
							<p class="italic help mb-5">
								Fonctionnalités permettant d'adapter l'interface et le confort d'utilisation.
							</p>

							<div class="row">
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

				<div class="col-xs-12 mb-5">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-commenting-o fa-fw"></i> Optimisation des SMS</h4>
						</div>
						<div class="panel-body">
							<p class="italic help mb-5">
								Optimisation de la taille des SMS pour limiter le crédit consommé.
							</p>

							<div class="row">
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

								<?php if (ENABLE_URL_SHORTENER) { ?>
									<div class="col-xs-12 col-md-6">
										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title"><i class="fa fa-link fa-fw"></i> Support du raccourcisseur d'URL</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'shorten_url', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Raccourcir automatiquement les liens HTTP(S) dans les SMS : </label>
														<select name="setting_value" class="form-control">
															<option value="0">Non</option>
															<option value="1" <?php echo $_SESSION['user']['settings']['shorten_url'] ? 'selected' : ''; ?>>Oui</option>
														</select>
													</div>	
													<div class="text-center">
														<button class="btn btn-success">Mettre à jour les données</button>	
													</div>
												</form>
											</div>
										</div>
									</div>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 mb-5">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-commenting fa-fw"></i> Fonctions SMS avancées</h4>
						</div>
						<div class="panel-body">
							<p class="italic help mb-5">
								Fonctionnalités permettant d'envoyer des SMS particuliers ou de débloquer des fonctions avancées comme le templating.
							</p>

							<div class="row">
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
							</div>
						</div>
					</div>
				</div>


				<div class="col-xs-12 mb-5">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-hand-stop-o fa-fw"></i> Support des SMS "STOP"</h4>
						</div>
						<div class="panel-body">
							<p class="italic help mb-5">
								Fonctionnalités relative au support des SMS "STOP" permettant à un utilisateur de ne plus recevoir de messages.
							</p>

							<div class="row">
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
											<h4 class="panel-title"><i class="fa fa-reply fa-fw"></i> Activation des réponses automatiques aux SMS-STOP</h4>
										</div>
										<div class="panel-body">
											<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'smsstop_respond', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
												<div class="form-group">
													<label>Réponses automatiques aux SMS STOP activées : </label>
													<select name="setting_value" class="form-control">
														<option value="0">Non</option>
														<option value="1" <?php echo $_SESSION['user']['settings']['smsstop_respond'] ? 'selected' : ''; ?>>Oui</option>
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
											<h4 class="panel-title"><i class="fa fa-align-left fa-fw"></i> Texte de réponse aux SMS-STOP</h4>
										</div>
										<div class="panel-body">
											<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'smsstop_response', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
												<div class="form-group">
													<label>Texte des réponses automatiques aux SMS-STOP : </label>
													<input name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['smsstop_response'] ?? ''); ?>" />
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

				<div class="col-xs-12 mb-5">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-mobile fa-fw"></i> Gestion avancée des téléphones</h4>
						</div>
						<div class="panel-body">
							<p class="italic help mb-5">
								Fonctionnalités liées aux téléphones et à leur gestion.
							</p>

							<div class="row">
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
							</div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 mb-5">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-calendar-times-o fa-fw"></i> Limite périodique de SMS</h4>
						</div>
						<div class="panel-body">
							<p class="italic help mb-5">
								Permet de définir le mode de remonté d'information quand vous approchez/atteignez votre limite de SMS disponibles pour la période.
							</p>

							<div class="row">
								<div class="col-xs-12 col-md-6">
									<div class="panel panel-default">
										<div class="panel-heading">
											<h4 class="panel-title"><i class="fa fa-exclamation-triangle fa-fw"></i> Alerte limite de SMS atteinte</h4>
										</div>
										<div class="panel-body">
											<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'alert_quota_limit_reached', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
												<div class="form-group">
													<label>Recevoir un e-mail quand la limite de SMS disponibles pour la période est atteinte :</label>
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
							</div>
						</div>
					</div>
				</div>


				<div class="col-xs-12 mb-5">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-cogs fa-fw"></i> Fonctions diverses</h4>
						</div>
						<div class="panel-body">
							<p class="italic help mb-5">
								Fonctionnalités avancées diverses.
							</p>

							<div class="row">
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
							</div>
						</div>
					</div>
				</div>

				<div class="col-xs-12 mb-5">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title"><i class="fa fa-thermometer-full fa-fw"></i> Supervision des téléphones</h4>
						</div>
						<div class="panel-body">
							<p class="italic help mb-5">
								Les fonctions de supervision des téléphones permettent de détecter les téléphones qui semblent présenter des problèmes de fiabilité, notamment des taux anormaux de SMS échoués ou inconnus.<br/>
								Le système vérifie la fiabilité des téléphones toutes les 15 minutes et permet de déclencher des webhooks, envoyer des emails ou même désactiver automatiquement les téléphones.<br/>
							</p>

							<div class="row">
								<div class="col-xs-12 col-md-6">
									<div class="panel panel-default">
										<div class="panel-heading">
											<h4 class="panel-title"><i class="fa fa-exclamation-circle fa-fw"></i> Surveillance des SMS échoués</h4>
										</div>
										<div class="panel-body">
											<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_failed', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
												<div class="form-group">
													<label>Activer la surveillance des SMS échoués : </label>
													<select name="setting_value" class="form-control">
														<option value="0">Non</option>
														<option value="1" <?php echo $_SESSION['user']['settings']['phone_reliability_failed'] ? 'selected' : ''; ?>>Oui</option>
													</select>
												</div>	
												<div class="text-center">
													<button class="btn btn-success">Mettre à jour les données</button>	
												</div>
											</form>
										</div>
									</div>
								</div>

								<?php if ($_SESSION['user']['settings']['phone_reliability_failed']) { ?>
									<div class="col-xs-12 col-md-6">
										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title"><i class="fa fa-percent fa-fw"></i> Taux limite de SMS échoués</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_failed_rate_limit', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Déclencher une alerte si le taux de SMS échoués atteint cette limite : </label>
														<div class="input-group">
															<input type="number" min="1" max="100" step="1" name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['phone_reliability_failed_rate_limit']); ?>" />
															<span class="input-group-addon bold">%</span>
														</div>
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
												<h4 class="panel-title"><i class="fa fa-flag-checkered fa-fw"></i> Volume minimum de données</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_failed_volume', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Ignorer les alertes si le nombre de SMS utilisé pour calculer le pourcentage d'échecs est inférieur à : </label>
														<input type="number" min="1" step="1" name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['phone_reliability_failed_volume']); ?>" />
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
												<h4 class="panel-title"><i class="fa fa-hourglass-start fa-fw"></i> Période de surveillance</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_failed_period', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Limiter le calcul aux SMS envoyés pendant les dernières : </label>
														<div class="input-group">
															<input type="number" min="15" max="10080" step="1" name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['phone_reliability_failed_period']); ?>" />
															<span class="input-group-addon bold">minutes</span>
														</div>
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
												<h4 class="panel-title"><i class="fa fa-hourglass-end fa-fw"></i> Ignorer les SMS récents</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_failed_grace_period', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Exclure du calcul les SMS envoyés depuis moins de : </label>
														<div class="input-group">
															<input type="number" min="1" max="10080" step="1" name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['phone_reliability_failed_grace_period']); ?>" />
															<span class="input-group-addon bold">minutes</span>
														</div>
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
												<h4 class="panel-title"><i class="fa fa-envelope-o fa-fw"></i> Alerte par e-mail</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_failed_email', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Envoyer un e-mail quand un problème de fiabilité est détecté : </label>
														<select name="setting_value" class="form-control">
															<option value="0">Non</option>
															<option value="1" <?php echo $_SESSION['user']['settings']['phone_reliability_failed_email'] ? 'selected' : ''; ?>>Oui</option>
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
												<h4 class="panel-title"><i class="fa fa-plug fa-fw"></i> Déclencher un webhook</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_failed_webhook', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Déclencher un webhook quand un problème de fiabilité est détecté : </label>
														<select name="setting_value" class="form-control">
															<option value="0">Non</option>
															<option value="1" <?php echo $_SESSION['user']['settings']['phone_reliability_failed_webhook'] ? 'selected' : ''; ?>>Oui</option>
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
												<h4 class="panel-title"><i class="fa fa-ban fa-fw"></i> Désactiver automatiquement le téléphone</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_failed_auto_disable', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Désactiver automatiquement le téléphone quand un problème de fiabilité est détecté : </label>
														<select name="setting_value" class="form-control">
															<option value="0">Non</option>
															<option value="1" <?php echo $_SESSION['user']['settings']['phone_reliability_failed_auto_disable'] ? 'selected' : ''; ?>>Oui</option>
														</select>
													</div>	
													<div class="text-center">
														<button class="btn btn-success">Mettre à jour les données</button>	
													</div>
												</form>
											</div>
										</div>
									</div>
								<?php } ?>

								<div class="col-xs-6 col-xs-offset-3 mb-4"><hr/></div>

								<div class="col-xs-12 col-md-6">
									<div class="panel panel-default">
										<div class="panel-heading">
											<h4 class="panel-title"><i class="fa fa-question-circle fa-fw"></i> Surveillance des SMS inconnus</h4>
										</div>
										<div class="panel-body">
											<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_unknown', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
												<div class="form-group">
													<label>Activer la surveillance des SMS inconnus : </label>
													<select name="setting_value" class="form-control">
														<option value="0">Non</option>
														<option value="1" <?php echo $_SESSION['user']['settings']['phone_reliability_unknown'] ? 'selected' : ''; ?>>Oui</option>
													</select>
												</div>	
												<div class="text-center">
													<button class="btn btn-success">Mettre à jour les données</button>	
												</div>
											</form>
										</div>
									</div>
								</div>

								<?php if ($_SESSION['user']['settings']['phone_reliability_unknown']) { ?>
									<div class="col-xs-12 col-md-6">
										<div class="panel panel-default">
											<div class="panel-heading">
												<h4 class="panel-title"><i class="fa fa-percent fa-fw"></i> Taux limite de SMS inconnus</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_unknown_rate_limit', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Déclencher une alerte si le taux de SMS inconnus atteint cette limite : </label>
														<div class="input-group">
															<input type="number" min="1" max="100" step="1" name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['phone_reliability_unknown_rate_limit']); ?>" />
															<span class="input-group-addon bold">%</span>
														</div>
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
												<h4 class="panel-title"><i class="fa fa-flag-checkered fa-fw"></i> Volume minimum de données</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_unknown_volume', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Ignorer les alertes si le nombre de SMS utilisé pour calculer le pourcentage d'inconnus est inférieur à : </label>
														<input type="number" min="1" step="1" name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['phone_reliability_unknown_volume']); ?>" />
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
												<h4 class="panel-title"><i class="fa fa-hourglass-start fa-fw"></i> Période de surveillance</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_unknown_period', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Limiter le calcul aux SMS envoyés pendant les dernières : </label>
														<div class="input-group">
															<input type="number" min="15" max="10080" step="1" name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['phone_reliability_unknown_period']); ?>" />
															<span class="input-group-addon bold">minutes</span>
														</div>
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
												<h4 class="panel-title"><i class="fa fa-hourglass-end fa-fw"></i> Ignorer les SMS récents</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_unknown_grace_period', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Exclure du calcul les SMS envoyés depuis moins de : </label>
														<div class="input-group">
															<input type="number" min="1" max="10080" step="1" name="setting_value" class="form-control" value="<?php $this->s($_SESSION['user']['settings']['phone_reliability_unknown_grace_period']); ?>" />
															<span class="input-group-addon bold">minutes</span>
														</div>
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
												<h4 class="panel-title"><i class="fa fa-envelope-o fa-fw"></i> Alerte par e-mail</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_unknown_email', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Envoyer un e-mail quand un problème de fiabilité est détecté : </label>
														<select name="setting_value" class="form-control">
															<option value="0">Non</option>
															<option value="1" <?php echo $_SESSION['user']['settings']['phone_reliability_unknown_email'] ? 'selected' : ''; ?>>Oui</option>
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
												<h4 class="panel-title"><i class="fa fa-plug fa-fw"></i> Déclencher un webhook</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_unknown_webhook', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Déclencher un webhook quand un problème de fiabilité est détecté : </label>
														<select name="setting_value" class="form-control">
															<option value="0">Non</option>
															<option value="1" <?php echo $_SESSION['user']['settings']['phone_reliability_unknown_webhook'] ? 'selected' : ''; ?>>Oui</option>
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
												<h4 class="panel-title"><i class="fa fa-ban fa-fw"></i> Désactiver automatiquement le téléphone</h4>
											</div>
											<div class="panel-body">
												<form action="<?php echo \descartes\Router::url('Setting', 'update', ['setting_name' => 'phone_reliability_unknown_auto_disable', 'csrf' => $_SESSION['csrf']]); ?>" method="POST">
													<div class="form-group">
														<label>Désactiver automatiquement le téléphone quand un problème de fiabilité est détecté : </label>
														<select name="setting_value" class="form-control">
															<option value="0">Non</option>
															<option value="1" <?php echo $_SESSION['user']['settings']['phone_reliability_unknown_auto_disable'] ? 'selected' : ''; ?>>Oui</option>
														</select>
													</div>	
													<div class="text-center">
														<button class="btn btn-success">Mettre à jour les données</button>	
													</div>
												</form>
											</div>
										</div>
									</div>
								<?php } ?>
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
                    {"id": "stats", "name": "Statistiques"}, 
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
