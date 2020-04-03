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
                                    <label>Nom du téléphone</label>
                                    <p class="italic small help">
                                        Le nom du téléphone qui enverra et recevra les messages.
                                    </p>
									<div class="form-group">
										<input required="required" name="name" class="form-control" placeholder="Nom du téléphone">
									</div>
								</div>
                                <div class="form-group">
                                    <label>Adaptateur logiciel du téléphone : </label>
                                    <p class="italic small help" id="description-adapter-general">
                                        L'adaptateur logiciel utilisé par RaspiSMS pour communiquer avec le téléphone. Pour plus d'information, consultez <a href="https://raspisms.raspberry-pi.fr/documentation" target="_blank">la documentation de RaspiSMS</a> concernant les adaptateurs logiciels.
                                    </p>
                                    <select name="adapter" class="form-control" id="adapter-select">
                                        <?php foreach ($adapters as $adapter) { ?>
                                            <option 
                                                value="<?= $adapter['meta_classname'] ?>"
                                                data-description="<?php $this->s($adapter['meta_description']); ?>"
                                                data-datas-fields="<?php $this->s(json_encode($adapter['meta_datas_fields'])); ?>"
                                            >
                                                <?php $this->s($adapter['meta_name']); ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div id="adapter-datas-container" class="form-group">
                                    <div id="adapter-datas-description-container">
                                        <h4>Description de l'adaptateur</h4>
                                        <div id="adapter-datas-description"></div>
                                    </div>
                                    
                                    <div id="adapter-data-fields-container">
                                        <h4>Réglages de l'adaptateur</h4>
                                        <div id="adapter-datas-fields"></div>
                                    </div>
                                </div>
								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('Phone', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le téléphone" /> 	
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
        jQuery('#adapter-datas-description').html(option.attr('data-description'));
        jQuery('#description-adapter-datas').text(option.attr('data-datas-help'));
    
        var datas_fields = option.attr('data-datas-fields');
        datas_fields = JSON.parse(datas_fields);


        var numbers = [];

        var html = '';
        jQuery.each(datas_fields, function (index, field)
        {
            if (!field.number)
            {
                html += '<div class="form-group">' +
                            '<label>' + field.title + '</label>' +
                            '<p class="italic small help">' + field.description + '</p>' +
                            '<div class="form-group">' + 
                                '<input name="adapter_datas[' + field.name + ']" class="form-control" ' + (field.required ? 'required' : '') + ' ' + (field.default_value ? 'value="' + field.default_value + '"' :  '') +  '>' +
                            '</div>' +
                        '</div>';
            }
            else
            {
                var random_id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                html += '' +
                '<div class="form-group">' +
                    '<label>' + field.title + '</label>' +
                    '<p class="italic small help">' + field.description + '</p>' +
                    '<div class="form-group">' + 
                        '<input name="" class="form-control phone-international-input" type="tel" id="' + random_id + '" ' + (field.required ? 'required' : '') + ' ' + (field.default_value ? 'value="' + field.default_value + '"' :  '') + '>' +
                    '</div>' +
                '</div>';

                var number = {
                    'id': random_id, 
                    'name': field.name,
                };

                numbers.push(number);
            }
        });

        if (html == '')
        {
            html = 'Pas de réglages.';
        }

        jQuery('#adapter-datas-fields').html(html);
        
        for (i = 0; i < numbers.length; i++)
        {
            var iti_number_input = window.intlTelInput(document.getElementById(numbers[i].id), {
                hiddenInput: 'adapter_datas[' + numbers[i].name + ']',
                defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
                preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
                nationalMode: true,
                utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js',
            });
        }
    }

	jQuery('document').ready(function($)
    {
        change_adapter();

        jQuery('#adapter-select').on('change', function (e)
        {
            change_adapter();
        });
	});
</script>
<?php
	$this->render('incs/footer');
