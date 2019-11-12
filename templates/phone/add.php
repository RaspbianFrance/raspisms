<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Phones - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'phones'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Nouveau téléphone
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-phone"></i> <a href="<?php echo \descartes\Router::url('Phone', 'list'); ?>">Téléphones</a>
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
							<h3 class="panel-title"><i class="fa fa-phone fa-fw"></i> Ajout d'un téléphone</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Phone', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
                                    <label>Numéro de téléphone</label>
                                    <p class="italic small">
                                        Le numéro de téléphone qui enverra et recevra les messages.
                                    </p>
									<div class="form-group">
										<input name="" class="form-control" type="tel" id="phone-international-input" placeholder="Numéro de téléphone à utiliser.">
									</div>
								</div>
                                <div class="form-group">
                                    <label>Adaptateur logiciel du téléphone : </label>
                                    <p class="italic small" id="description-adapter">
                                        L'adaptateur logiciel utilisé par RaspiSMS pour communiquer avec le téléphone. Pour plus d'information, consultez <a href="https://raspisms.raspberry-pi.fr/documentation" target="_blank">la documentation de RaspiSMS</a> concernant les adaptateurs logiciels.
                                    </p>
                                    <select name="adapter" class="form-control" id="adapter-select">
                                        <?php foreach ($adapters as $adapter) { ?>
                                            <option 
                                                value="<?= $adapter['meta_classname'] ?>"
                                                title="<?php $this->s($adapter['meta_description']); ?>"
                                                data-description="<?php $this->s($adapter['meta_description']); ?>"
                                                data-datas-help="<?php $this->s($adapter['meta_datas_help']); ?>"
                                            >
                                                <?php $this->s($adapter['meta_name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
								<div class="form-group" id="adapter-datas-container">
                                    <label>Configuration de l'adaptateur</label>
                                    <p class="italic small" id="description-adapter-datas">
                                        Les données à fournir à l'adaptateur pour lui permettre de faire la liaison avec le téléphone. Par exemple des identifiants d'API.<br/>
                                    </p>
                                    <textarea id="adapter-datas" name="adapter_datas" class="form-control has-error"></textarea>
                                    <p class="hidden help-block" id="adapter-datas-error-message">La configuration doit être un JSON valide.</p>
								</div>
								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('Phone', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le phone" /> 	
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery('document').ready(function($)
    {
        var option = jQuery('#adapter-select').find('option:selected');
        jQuery('#description-adapter').text(option.attr('data-description'));
        jQuery('#description-adapter-datas').text(option.attr('data-datas-help'));
        
        jQuery('#adapter-select').on('change', function (e)
        {
            var option = jQuery(this).find('option:selected');
            jQuery('#description-adapter').text(option.attr('data-description'));
            jQuery('#description-adapter-datas').text(option.attr('data-datas-help'));
        });

        jQuery('#adapter-datas').on('input', function (e)
        {
            try
            {
                if (jQuery(this).val() !== '')
                {
                    JSON.parse(jQuery(this).val());
                }

                jQuery('#adapter-datas-container').removeClass('has-error');
                jQuery('#adapter-datas-error-message').addClass('hidden');
            }
            catch (err) 
            {
                jQuery('#adapter-datas-container').addClass('has-error');
                jQuery('#adapter-datas-error-message').removeClass('hidden');
            }
        });

        var number_input = jQuery('#phone-international-input')[0];
        var iti_number_input = window.intlTelInput(number_input, {
            hiddenInput: 'number',
			defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
			preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
			nationalMode: true,
			utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js'
        });
	});
</script>
<?php
	$this->render('incs/footer');
