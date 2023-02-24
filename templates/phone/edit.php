<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Phones - Edit'])
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
						Modification téléphones
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-phone"></i> <a href="<?php echo \descartes\Router::url('Phone', 'list'); ?>">Téléphones</a>
						</li>
						<li class="active">
							<i class="fa fa-edit"></i> Modifier
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-phone fa-fw"></i> Modification de téléphones</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Phone', 'update', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
                                <?php foreach ($phones as $phone) { ?>
                                    <div class="entry-container" data-entry-id="<?php $this->s($phone['id']); ?>">

                                        <div class="form-group">
                                            <label>Nom du téléphone</label>
                                            <p class="italic small help">
                                                Le nom du téléphone qui enverra et recevra les messages.
                                            </p>
                                            <div class="form-group">
                                            <input required="required" name="phones[<?php $this->s($phone['id']); ?>][name]" class="form-control" placeholder="Nom du téléphone" value="<?php $this->s($phone['name']); ?>">
                                            </div>
                                        </div>

                                        <?php if ($_SESSION['user']['settings']['phone_priority']) { ?>
                                            <div class="form-group">
                                                <label>Priorité d'utilisation du téléphone</label>
                                                <p class="italic small help">
                                                    Lors de l'envoi de SMS sans téléphone spécifié, les téléphones avec la plus haute priorité seront utilisés en premier.
                                                </p>
                                                <div class="form-group">
                                                    <input required="required" name="phones[<?php $this->s($phone['id']); ?>][priority]" class="form-control" type="number" min="0" placeholder="Priorité d'utilisation" value="<?php $this->s($phone['priority']) ?>">
                                                </div>
                                            </div>
                                        <?php } ?>

                                        <div class="form-group">
                                            <label>Type de téléphone</label>
                                            <p class="italic small help description-adapter-general">
                                                Le type de téléphone utilisé par RaspiSMS pour envoyer ou recevoir les SMS. Pour plus d'information, consultez <a href="https://documentation.raspisms.fr/users/adapters/overview.html" target="_blank">la documentation de RaspiSMS</a> concernant les différents types de téléphones.
                                            </p>
                                            <select name="phones[<?php $this->s($phone['id']); ?>][adapter]" class="form-control adapter-select">
                                                <?php foreach ($adapters as $adapter) { ?>
                                                    <?php if ($adapter['meta_hidden'] === false || $phone['adapter'] == $adapter['meta_classname']) { ?>
                                                        <option 
                                                            value="<?= $adapter['meta_classname'] ?>"
                                                            data-description="<?php $this->s($adapter['meta_description']); ?>"
                                                            data-data-fields="<?php $this->s(json_encode($adapter['meta_data_fields'])); ?>"
                                                            <?php if ($phone['adapter'] == $adapter['meta_classname']) { ?>
                                                                <?php if (!$adapter['meta_hide_data']) { ?>
                                                                    data-phone-adapter-data="<?php $this->s($phone['adapter_data']); ?>"
                                                                <?php } ?>
                                                                selected
                                                            <?php } ?>
                                                        >
                                                            <?php $this->s($adapter['meta_name']); ?>
                                                        </option>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group well adapter-data-container">
                                            <div class="adapter-data-description-container">
                                                <h4>Description du téléphone</h4>
                                                <div class="adapter-data-description"></div>
                                            </div>
                                            
                                            <div class="adapter-data-fields-container">
                                                <h4>Réglages du téléphone</h4>
                                                <div class="adapter-data-fields"></div>
                                            </div>
                                        </div>

                                        <?php if ($_SESSION['user']['settings']['phone_limit']) { ?>
                                            <div class="form-group">
                                                <label>Limites des volumes d'envoi du téléphone</label>
                                                <p class="italic small help">
                                                    Défini le nombre maximum de SMS qui pourront être envoyés avec ce téléphone sur des périodes de temps données.
                                                </p>
                                                <div class="form-group phone-limits-container container-fluid">
                                                    <?php foreach ($phone['limits'] as $limit) { ?>
                                                        <div class="row phone-limits-group">
                                                            <div class="col-xs-4">
                                                                <label>Période</label><br/>
                                                                <?php $random_id = uniqid(); ?>
                                                                <select name="phones[<?= $phone['id']; ?>][limits][<?= $random_id; ?>][startpoint]" class="form-control" required>
                                                                    <option value="" disabled selected>Période sur laquelle appliquer la limite</option>
                                                                    <option <?= $limit['startpoint'] == 'today' ? 'selected' : ''; ?> value="today">Par jour</option>
                                                                    <option <?= $limit['startpoint'] == '-24 hours' ? 'selected' : ''; ?> value="-24 hours">24 heures glissantes</option>
                                                                    <option <?= $limit['startpoint'] == 'this week midnight' ? 'selected' : ''; ?> value="this week midnight">Cette semaine</option>
                                                                    <option <?= $limit['startpoint'] == '-7 days' ? 'selected' : ''; ?> value="-7 days">7 jours glissants</option>
                                                                    <option <?= $limit['startpoint'] == 'this week midnight -1 week' ? 'selected' : ''; ?> value="this week midnight -1 week">Ces deux dernières semaines</option>
                                                                    <option <?= $limit['startpoint'] == '-14 days' ? 'selected' : ''; ?> value="-14 days">14 jours glissants</option>
                                                                    <option <?= $limit['startpoint'] == 'this month midnight' ? 'selected' : ''; ?> value="this month midnight">Ce mois</option>
                                                                    <option <?= $limit['startpoint'] == '-1 month' ? 'selected' : ''; ?> value="-1 month">1 mois glissant</option>
                                                                    <option <?= $limit['startpoint'] == '-28 days' ? 'selected' : ''; ?> value="-28 days">28 jours glissants</option>
                                                                    <option <?= $limit['startpoint'] == '-30 days' ? 'selected' : ''; ?> value="-30 days">30 jours glissants</option>
                                                                    <option <?= $limit['startpoint'] == '-31 days' ? 'selected' : ''; ?> value="-31 days">31 jours glissants</option>
                                                                </select>
                                                            </div>
                                                            <div class="scheduleds-number-data-container col-xs-8">
                                                                <label>Volume</label>
                                                                <div class="form-group">
                                                                    <input name="phones[<?= $phone['id']; ?>][limits][<?= $random_id; ?>][volume]" class="form-control" type="number" min="1" value="<?php $this->s($limit['volume']); ?>" placeholder="Nombre de SMS maximum sur la période.">
                                                                </div>
                                                            </div>
                                                            <a href="#" class="phone-limits-group-remove"><span class="fa fa-times"></span></a>
                                                        </div>
                                                    <?php } ?>
                                                    <div class="text-center"><div class="add-phone-limit-button fa fa-plus-circle"></div></div>
                                                </div>
                                            </div>
                                        <?php } ?>
                                        
                                    </div>
                                    <hr/>
                                <?php } ?>
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

    function change_adapter (target)
    {
        var phone_id = target.parents('.entry-container').attr('data-entry-id');

        var option = target.find('option:selected');
        target.parents('.entry-container').find('.adapter-data-description').html(option.attr('data-description'));
        target.parents('.entry-container').find('.description-adapter-data').text(option.attr('data-data-help'));
    
        var data_fields = option.attr('data-data-fields');
        data_fields = JSON.parse(data_fields);

        if (option.attr('data-phone-adapter-data'))
        {
            var phone_adapter_data = option.attr('data-phone-adapter-data');
            phone_adapter_data = JSON.parse(phone_adapter_data);
        }

        var numbers = [];

        var html = '';
        jQuery.each(data_fields, function (index, field)
        {
            if (phone_adapter_data)
            {
                if (field.name in phone_adapter_data)
                {
                    value = phone_adapter_data[field.name];
                }
                else
                {
                    value = field.default_value ? field.default_value : null;
                }
            }
            else
            {
                value = field.default_value ? field.default_value : null;
            }
            

            if (field.type == 'phone_number')
            {
                var random_id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
                html += '' +
                '<div class="form-group">' +
                    '<label>' + field.title + '</label>' +
                    '<p class="italic small help">' + field.description + '</p>' +
                    '<div class="form-group">' + 
                        '<input name="" class="form-control phone-international-input" type="tel" id="' + random_id + '" ' + (field.required ? 'required' : '') + ' ' + (value ? 'value="' + value + '"' :  '') + '>' +
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
                html += '' + 
                '<div class="form-group">' +
                    '<label>' + field.title + '</label>' +
                    '<p class="italic small help">' + field.description + '</p>' +
                    '<div class="form-group">' + 
                        '<input type="checkbox" name="phones[' + phone_id + '][adapter_data][' + field.name + ']" class="form-control" ' + (field.required ? 'required' : '') + ' ' + (value ? 'value="' + value + '" checked' :  'value="1"') +  '><label class="switch" for="adapter_data[' + field.name + ']"></label>' +
                    '</div>' +
                '</div>';
            }
            else
            {
                html += '' + 
                '<div class="form-group">' +
                    '<label>' + field.title + '</label>' +
                    '<p class="italic small help">' + field.description + '</p>' +
                    '<div class="form-group">' + 
                        '<input name="phones[' + phone_id + '][adapter_data][' + field.name + ']" class="form-control" ' + (field.required ? 'required' : '') + ' ' + (value ? 'value="' + value + '"' :  '') +  '>' +
                    '</div>' +
                '</div>';
            }
        });

        if (html == '')
        {
            html = 'Pas de réglages.';
        }

        target.parents('.entry-container').find('.adapter-data-fields').html(html);
        
        for (i = 0; i < numbers.length; i++)
        {
            var iti_number_input = window.intlTelInput(document.getElementById(numbers[i].id), {
                hiddenInput: 'phones[' + phone_id + '][adapter_data][' + numbers[i].name + ']',
                defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
                preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
                nationalMode: true,
                utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js',
            });
        }
    }

	jQuery('document').ready(function($)
    {
        jQuery('.adapter-select').each(function () {
            change_adapter(jQuery(this));
        });

        jQuery('.adapter-select').on('change', function (e)
        {
            change_adapter(jQuery(this));
        });

        jQuery('body').on('click', '.phone-limits-group-remove', function (e)
        {
            e.preventDefault();
            jQuery(this).parent('.phone-limits-group').remove();
            return false;
        });

        jQuery('body').on('click', '.add-phone-limit-button', function(e)
        {
            var phone_id = jQuery(this).parents('.entry-container').attr('data-entry-id');
            var random_id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
			var newLimit = '' +
			'<div class="row phone-limits-group">'+
                '<div class="col-xs-4">'+
                    '<label>Période</label><br/>'+
                    '<select name="phones[' + phone_id + '][limits][' + random_id + '][startpoint]" class="form-control adapter-select" required>'+
                        '<option value="" disabled selected>Période sur laquelle appliquer la limite</option>'+
                        '<option value="today">Par jour</option>'+
                        '<option value="-24 hours">24 heures glissantes</option>'+
                        '<option value="this week midnight">Cette semaine</option>'+
                        '<option value="-7 days">7 jours glissants</option>'+
                        '<option value="this week midnight -1 week">Ces deux dernières semaines</option>'+
                        '<option value="-14 days">14 jours glissants</option>'+
                        '<option value="this month midnight">Ce mois</option>'+
                        '<option value="-1 month">1 mois glissant</option>'+
                        '<option value="-28 days">28 jours glissants</option>'+
                        '<option value="-30 days">30 jours glissants</option>'+
                        '<option value="-31 days">31 jours glissants</option>'+
                    '</select>'+
                '</div>'+
                '<div class="scheduleds-number-data-container col-xs-8">'+
                    '<label>Volume</label>'+
                    '<div class="form-group">'+
                        '<input name="phones[' + phone_id + '][limits][' + random_id + '][volume]" class="form-control" type="number" min="1" placeholder="Nombre de SMS maximum sur la période.">'+
                    '</div>'+
                '</div>'+
                '<a href="#" class="phone-limits-group-remove"><span class="fa fa-times"></span></a>'+
            '</div>';

            jQuery(this).parent('div').before(newLimit);
        });
	});
</script>
<?php
	$this->render('incs/footer');
