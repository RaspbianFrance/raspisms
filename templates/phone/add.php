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
                                    <input required="required" name="name" class="form-control" placeholder="Nom du téléphone" value="<?php $this->s($_SESSION['previous_http_post']['name'] ?? '') ?>">
									</div>
								</div>
                                <div class="form-group">
                                    <label>Type de téléphone : </label>
                                    <p class="italic small help" id="description-adapter-general">
                                        Le type de téléphone utilisé par RaspiSMS pour envoyer ou recevoir les SMS. Pour plus d'information, consultez <a href="https://documentation.raspisms.fr/users/adapters/overview.html" target="_blank">la documentation de RaspiSMS</a> concernant les différents types de téléphones.
                                    </p>
                                    <select name="adapter" class="form-control" id="adapter-select">
                                        <?php foreach ($adapters as $adapter) { ?>
                                            <?php if ($adapter['meta_hidden'] === false) { ?>
                                                <option 
                                                    value="<?= $adapter['meta_classname'] ?>"
                                                    data-description="<?php $this->s($adapter['meta_description']); ?>"
                                                    data-data-fields="<?php $this->s(json_encode($adapter['meta_data_fields'])); ?>"
                                                    <?= ($_SESSION['previous_http_post']['adapter'] ?? '') == $adapter['meta_classname'] ? 'selected' : ''  ?>
                                                >
                                                    <?php $this->s($adapter['meta_name']); ?>
                                                </option>
                                            <?php } ?>
                                        <?php } ?>
                                    </select>
                                </div>
                                <div id="adapter-data-container" class="form-group">
                                    <div id="adapter-data-description-container">
                                        <h4>Description du téléphone</h4>
                                        <div id="adapter-data-description"></div>
                                    </div>
                                    
                                    <div id="adapter-data-fields-container">
                                        <h4>Réglages du téléphone</h4>
                                        <div id="adapter-data-fields"></div>
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
        jQuery('#adapter-data-description').html(option.attr('data-description'));
        jQuery('#description-adapter-data').text(option.attr('data-data-help'));
    
        var data_fields = option.attr('data-data-fields');
        data_fields = JSON.parse(data_fields);


        var numbers = [];

        var html = '';
        jQuery.each(data_fields, function (index, field)
        {
            if (field.type == 'phone_number')
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
            else if (field.type == 'boolean')
            {
                html += '<div class="form-group">' +
                            '<label>' + field.title + '</label>' +
                            '<p class="italic small help">' + field.description + '</p>' +
                            '<div class="form-group">' + 
                                '<input type="checkbox" id="adapter_data[' + field.name + ']" name="adapter_data[' + field.name + ']" class="form-control" ' + (field.required ? 'required' : '') + ' ' + (field.default_value ? 'value="' + field.default_value + '" checked' :  'value="1"') +  '><label class="switch" for="adapter_data[' + field.name + ']"></label>' +
                            '</div>' +
                        '</div>';
            }
            else
            {
                html += '<div class="form-group">' +
                            '<label>' + field.title + '</label>' +
                            '<p class="italic small help">' + field.description + '</p>' +
                            '<div class="form-group">' + 
                                '<input name="adapter_data[' + field.name + ']" class="form-control" ' + (field.required ? 'required' : '') + ' ' + (field.default_value ? 'value="' + field.default_value + '"' :  '') +  '>' +
                            '</div>' +
                        '</div>';
            }
        });

        if (html == '')
        {
            html = 'Pas de réglages.';
        }

        jQuery('#adapter-data-fields').html(html);
        
        for (i = 0; i < numbers.length; i++)
        {
            var iti_number_input = window.intlTelInput(document.getElementById(numbers[i].id), {
                hiddenInput: 'adapter_data[' + numbers[i].name + ']',
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
