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
                                    <p class="italic small help">
                                        Le numéro de téléphone qui enverra et recevra les messages.
                                    </p>
									<div class="form-group">
										<input name="" class="form-control" type="tel" id="phone-international-input" placeholder="Numéro de téléphone à utiliser.">
									</div>
								</div>
                                <div class="form-group">
                                    <label>Adaptateur logiciel du téléphone : </label>
                                    <p class="italic small help" id="description-adapter">
                                        L'adaptateur logiciel utilisé par RaspiSMS pour communiquer avec le téléphone. Pour plus d'information, consultez <a href="https://raspisms.raspberry-pi.fr/documentation" target="_blank">la documentation de RaspiSMS</a> concernant les adaptateurs logiciels.
                                    </p>
                                    <select name="adapter" class="form-control" id="adapter-select">
                                        <?php foreach ($adapters as $adapter) { ?>
                                            <option 
                                                value="<?= $adapter['meta_classname'] ?>"
                                                title="<?php $this->s($adapter['meta_description']); ?>"
                                                data-description="<?php $this->s($adapter['meta_description']); ?>"
                                                data-datas-help="<?php $this->s($adapter['meta_datas_help']); ?>"
                                                data-datas-fields="<?php $this->s(json_encode($adapter['meta_datas_fields'])); ?>"
                                            >
                                                <?php $this->s($adapter['meta_name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div id="adapter-datas-fields-container">
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

    function change_adapter ()
    {
        var option = jQuery('#adapter-select').find('option:selected');
        jQuery('#description-adapter').text(option.attr('data-description'));
        jQuery('#description-adapter-datas').text(option.attr('data-datas-help'));
    
        var datas_fields = option.attr('data-datas-fields');
        datas_fields = JSON.parse(datas_fields);


        var html = '';
        jQuery.each(datas_fields, function (index, field)
        {
            html += '<div class="form-group">' +
                        '<label>' + field.title + '</label>' +
                        '<p class="italic small help">' + field.description + '</p>' +
                        '<div class="form-group">' + 
                            '<input name="adapter_datas[' + field.name + ']" class="form-control" ' + (field.required ? 'required' : '') + ' >' +
                        '</div>' +
                    '</div>';
        });

        jQuery('#adapter-datas-fields-container').html(html);
    }

	jQuery('document').ready(function($)
    {
        change_adapter();

        jQuery('#adapter-select').on('change', function (e)
        {
            change_adapter();
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
