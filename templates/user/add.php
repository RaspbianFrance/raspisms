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
						Nouvel utilisateur
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-user"></i> <a href="<?php echo \descartes\Router::url('User', 'list'); ?>">Utilisateurs</a>
						</li>
						<li class="active">
							<i class="fa fa-plus"></i> Nouveau
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-user fa-fw"></i> Ajout d'un utilisateur</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('User', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Adresse e-mail</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-at"></span></span>
										<input name="email" class="form-control" type="email" placeholder="Adresse e-mail de l'utilisateur" autofocus required value="<?php $this->s($_SESSION['previous_http_post']['email'] ?? '') ?>">
									</div>
								</div>	
								<div class="form-group">
									<label>Mot de passe (laissez vide pour générer le mot de passe automatiquement)</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-lock"></span></span>
                                        <input name="password" class="form-control" type="password" placeholder="Mot de passe de l'utilisateur" autocomplete="new-password" value="<?php $this->s($_SESSION['previous_http_post']['password'] ?? ''); ?>">
									</div>
								</div>
                                <div class="form-group">
                                    <label>Niveau administrateur : </label>
                                    <div class="form-group">
                                        <input name="admin" type="radio" value="1" required <?= (isset($_SESSION['previous_http_post']['admin']) && (bool) $_SESSION['previous_http_post']['admin']) ? 'checked' : ''; ?>/> Oui 
                                        <input name="admin" type="radio" value="0" required <?= (isset($_SESSION['previous_http_post']['admin']) && !(bool) $_SESSION['previous_http_post']['admin']) ? 'checked' : ''; ?>/> Non
                                    </div>
                                </div>
                                <fieldset>
                                    <legend>Quota de SMS</legend>
                                    
                                    <div class="form-group">
                                        <label>Définir un quota pour cet utilisateur : </label>
                                        <p class="italic small help">
                                            Définir un quota pour un utilisateur vous permet de choisir combien de SMS cet utilisateur pourras envoyer sur une période donnée.
                                        </p>
                       
                                         <div class="form-group">
                                            <input name="quota_enable" type="radio" value="1" required <?= (isset($_SESSION['previous_http_post']['quota_enable']) && (bool) $_SESSION['previous_http_post']['quota_enable']) ? 'checked' : ''; ?>/> Oui 
                                            <input name="quota_enable" type="radio" value="0" required <?= (isset($_SESSION['previous_http_post']['quota_enable']) && !(bool) $_SESSION['previous_http_post']['quota_enable']) ? 'checked' : ''; ?>/> Non
                                        </div>
                                    </div>
                                    
                                    <div class="quota-settings hidden">
                                        <div class="form-group">
                                            <label>Nombre de SMS disponibles</label>
                                            <input name="quota_credit" class="form-control" type="number" required disabled placeholder="Crédit de base" value="<?php $this->s($_SESSION['previous_http_post']['quota_credit'] ?? '') ?>">
                                        </div>
                                        <div class="form-group">
                                            <label>SMS additionels</label>
                                            <p class="italic small help">
                                                SMS venants s'ajouter au crédit de base. Vous pouvez par exemple utiliser des SMS additionels pour augmenter temporairement la limite de SMS d'un utilisateur.
                                            </p>
                                            <input name="quota_additional" class="form-control" type="number" required disabled placeholder="Nombre de SMS additionel au crédit de base" value="<?php $this->s($_SESSION['previous_http_post']['quota_additional'] ?? '') ?>">
                                        </div>

                                        <div class="form-group">
                                            <label>Date de début du quota</label>
                                            <input name="quota_start_date" class="form-control form-datetime auto-width" type="text" required disabled readonly value="<?php $this->s($_SESSION['previous_http_post']['quota_start_date'] ?? $now) ?>">
                                        </div>
                                       
                                        <div class="form-group">
                                            <label>Durée du quota : </label>
                                            <p class="italic small help">
                                                Sur quelle durée le quota doit-il s'appliqué. Une fois cette durée passée, le quota sera soit désactivé soit renouvelé automatiquement.
                                            </p>
                                            <div class="form-group">
                                                <select name="quota_renew_interval" class="form-control" disabled required>
                                                    <option value="P1D" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P1D' ? 'selected' : ''  ?>>1 jour</option>
                                                    <option value="P15D" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P15D' ? 'selected' : ''  ?>>15 jours</option>
                                                    <option value="P28D" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P28D' ? 'selected' : ''  ?>>28 jours</option>
                                                    <option value="P30D" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P30D' ? 'selected' : ''  ?>>30 jours</option>
                                                    <option value="P31D" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P31D' ? 'selected' : ''  ?>>31 jours</option>
                                                    <option value="P1W" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P1W' ? 'selected' : ''  ?>>1 semaine</option>
                                                    <option value="P2W" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P2W' ? 'selected' : ''  ?>>2 semaines</option>
                                                    <option value="P3W" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P3W' ? 'selected' : ''  ?>>3 semaines</option>
                                                    <option value="P4W" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P4W' ? 'selected' : ''  ?>>4 semaines</option>
                                                    <option value="P1M" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P1M' ? 'selected' : ''  ?>>1 mois</option>
                                                    <option value="P2M" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P2M' ? 'selected' : ''  ?>>2 mois</option>
                                                    <option value="P3M" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P3M' ? 'selected' : ''  ?>>3 mois</option>
                                                    <option value="P6M" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P6M' ? 'selected' : ''  ?>>6 mois</option>
                                                    <option value="P9M" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P9M' ? 'selected' : ''  ?>>9 mois</option>
                                                    <option value="P12M" <?= ($_SESSION['previous_http_post']['quota_renew_interval'] ?? '') == 'P12M' ? 'selected' : ''  ?>>12 mois</option>
                                                </select>
                                            </div>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label>Renouveler automatiquement le quota : </label>
                                            <p class="italic small help">
                                                Si activé, le crédit consommé sera automatiquement remis à zéro et le quota renouvelé pour la même durée à chaque fois qu'il arrivera à sa fin.
                                            </p>
                                            <div class="form-group">
                                                <input name="quota_auto_renew" type="radio" value="1" disabled required <?= (isset($_SESSION['previous_http_post']['quota_auto_renew']) && (bool) $_SESSION['previous_http_post']['quota_auto_renew']) ? 'checked' : ''; ?>/> Oui 
                                                <input name="quota_auto_renew" type="radio" value="0" disabled required <?= (isset($_SESSION['previous_http_post']['quota_auto_renew']) && !(bool) $_SESSION['previous_http_post']['quota_auto_renew']) ? 'checked' : ''; ?>/> Non
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label>Reporter les SMS non consommés à la fin de la période : </label>
                                            <p class="italic small help">
                                                Si activé, les SMS non consommés serons reportés au mois suivant sous la forme de crédit additionel. Sinon, les SMS non utilisés seront simplement perdus.
                                            </p>
                                            <div class="form-group">
                                                <input name="quota_report_unused" type="radio" value="1" disabled required <?= (isset($_SESSION['previous_http_post']['quota_report_unused']) && (bool) $_SESSION['previous_http_post']['quota_report_unused']) ? 'checked' : ''; ?>/> Oui 
                                                <input name="quota_report_unused" type="radio" value="0" disabled required <?= (isset($_SESSION['previous_http_post']['quota_report_unused']) && !(bool) $_SESSION['previous_http_post']['quota_report_unused']) ? 'checked' : ''; ?>/> Non
                                            </div>
                                        </div>
                                            
                                        <div class="form-group">
                                            <label>Reporter les SMS additionels non consommés à la fin de la période : </label>
                                            <p class="italic small help">
                                                Si activé, les SMS additionels non consommés serons reportés au mois suivant sous la forme de crédit additionel. Sinon, les SMS additionels non utilisés seront simplement perdus.
                                            </p>
                                            <div class="form-group">
                                                <input name="quota_report_unused_additional" type="radio" value="1" disabled required <?= (isset($_SESSION['previous_http_post']['quota_report_unused_additional']) && (bool) $_SESSION['previous_http_post']['quota_report_unused_additional']) ? 'checked' : ''; ?>/> Oui 
                                                <input name="quota_report_unused_additional" type="radio" value="0" disabled required <?= (isset($_SESSION['previous_http_post']['quota_report_unused_additional']) && !(bool) $_SESSION['previous_http_post']['quota_report_unused_additional']) ? 'checked' : ''; ?>/> Non
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>

								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('User', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le user" /> 	
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
    jQuery(document).ready(function()
    {
        jQuery('.form-datetime').datetimepicker(
        {
            format: 'yyyy-mm-dd hh:ii:ss',
            autoclose: true,
            minuteStep: 1,
            language: 'fr'
        });

        jQuery('input[name="quota_enable"]').on('change', function(event)
        {
            if (event.target.value == 0)
            {
                console.log('disable');
                jQuery('.quota-settings').addClass('hidden');
                jQuery('.quota-settings input, .quota-settings select').prop('disabled', true);
            }
            else
            {
                console.log('enable');
                jQuery('.quota-settings').removeClass('hidden');
                jQuery('.quota-settings input, .quota-settings select').prop('disabled', false);
            }
        })
        
        jQuery('input[name="quota_enable"]:checked').trigger('change');
    });
</script>
<?php
	$this->render('incs/footer');
