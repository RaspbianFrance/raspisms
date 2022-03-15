<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Scheduleds - Add'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'scheduleds'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Nouveau SMS programmé
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-send"></i> <a href="<?php echo \descartes\Router::url('Scheduled', 'list'); ?>">Scheduleds</a>
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
                    <?php if (!count($phones)) { ?>
                        <div class="alert alert-danger">Pour pouvoir envoyez un SMS vous devez d'abord <a href="<?= \descartes\Router::url('Phone', 'add'); ?>">créer au moins un téléphone.</a></div>
                    <?php } ?>
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-send fa-fw"></i> Création d'un nouveau SMS</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Scheduled', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST" enctype="multipart/form-data">
								<div class="form-group">
                                <label>Texte du SMS</label>
                                <?php if ($_SESSION['user']['settings']['templating']) { ?>
                                    <p class="italic small help description-scheduled-text">
                                        Vous pouvez utilisez des fonctionnalités de templating pour indiquer des valeures génériques qui seront remplacées par les données du contact au moment de l'envoie. Pour plus d'information, consultez la documentation sur <a href="https://documentation.raspisms.fr/users/templating/overview.html" target="_blank">l'utilisation des templates.</a><br/>
                                        Vous pouvez obtenir une prévisualisation du résultat pour un contact, ainsi qu'une estimation du nombre de crédits qui seront utilisés par SMS, en cliquant sur le boutton <b>"Prévisualiser"</b>.
                                    </p>
                                <?php } ?>
                                <textarea name="text" class="form-control" required><?php $this->s($_SESSION['previous_http_post']['text'] ?? '') ?></textarea>
                                <?php if ($_SESSION['user']['settings']['templating']) { ?>
                                    <div class="scheduled-preview-container">
                                        <label>Prévisualiser pour : </label>
                                        <select name="" class="form-control">
                                            <?php foreach ($contacts as $contact) { ?>
                                                <option value="<?php $this->s($contact['id']); ?>"><?php $this->s($contact['name']); ?></option>
                                            <?php } ?>
                                        </select>
                                        <a class="btn btn-info preview-button" href="#">Prévisualiser</a>
                                    </div>
                                <?php } ?>
                            </div>
                            
                            <?php if ($_SESSION['user']['settings']['mms'] ?? false) { ?>
                                <div class="form-group scheduled-media-group">
                                    <label>Ajouter un média au SMS</label>
                                    <p class="italic small help description-scheduled-media">
                                        L'ajout d'un média nécessite un téléphone supportant l'envoi de MMS. Pour plus d'information, consultez la documentation sur <a href="https://documentation.raspisms.fr/users/mms/overview.html" target="_blank">l'utilisation des MMS.</a>
                                    </p>
                                    <div class="form-group">
                                        <input class="" name="medias[]" value="" type="file" multiple />
                                    </div>
                                </div>
                            <?php } ?>
                            <div class="form-group">
                                <label>Date d'envoi du SMS</label>
                                <input name="at" class="form-control form-datetime auto-width" type="text" readonly value="<?php $this->s($_SESSION['previous_http_post']['at'] ?? $now) ?>">
                            </div>	
                            <div class="form-group">
                                <label>Numéros cibles</label>
                                <div class="form-group scheduleds-number-groupe-container container-fluid">
                                    <div class="row scheduleds-number-groupe">
                                        <?php $random_id = uniqid(); ?>
                                        <div class="col-xs-4">
                                            <label>Numéro cible : </label><br/>
                                            <input name="" data-uid="<?= $random_id ?>" class="form-control phone-international-input" type="tel" >
                                        </div>
                                        <div class="scheduleds-number-data-container col-xs-8">
                                            <label>Données associées : </label>
                                            <div class="form-group" data-uid="<?= $random_id ?>">
                                                <input name="" class="form-control scheduled-number-data-name" type="text" placeholder="Nom de la donnée" pattern="[a-zA-Z0-9_]*">
                                                : 
                                                <input name="" class="form-control scheduled-number-data-value" type="text" placeholder="Valeur de la donnée">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-center"><div class="add-number-button fa fa-plus-circle"></div></div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Contacts cibles</label>
                                <input class="add-contacts form-control" name="contacts[]" value="<?php $this->s(json_encode($_SESSION['previous_http_post']['contacts'] ?? $prefilled_contacts)) ?>" />
                            </div>
                            <div class="form-group">
                                <label>Groupes cibles</label>
                                <input class="add-groupes form-control" name="groups[]" value="<?php $this->s(json_encode($_SESSION['previous_http_post']['groups'] ?? $prefilled_groups)) ?>" />
                            </div>
                            <?php if ($_SESSION['user']['settings']['conditional_group'] ?? false) { ?>
                                <div class="form-group">
                                    <label>Groupes conditionnels cibles</label>
                                    <input class="add-conditional-groups form-control" name="conditional_groups[]" value="<?php $this->s(json_encode($_SESSION['previous_http_post']['conditional_groups'] ?? $prefilled_conditional_groups)) ?>" />
                                </div>
                            <?php } ?>
                            <div class="form-group scheduled-media-group">
                                <label>Ajouter un fichier CSV de destinataires</label>
                                <p class="italic small help description-scheduled-csv">
                                    Le SMS sera envoyé à tous les numéros inclus dans le fichier CSV. Assurez-vous que le fichier CSV respecte le format indiqué dans la documentation sur <a href="https://documentation.raspisms.fr/users/sms/csv.html" target="_blank">l'envoi de SMS à un fichier CSV.</a>
                                </p>
                                <div class="form-group">
                                    <input class="" name="csv" value="" type="file" multiple />
                                </div>
                            </div>
                            <?php if ($_SESSION['user']['settings']['sms_flash']) { ?>
                                <div class="form-group">
                                    <label>Envoyer comme un SMS Flash : </label>
                                    <div class="form-group">
                                        <input name="flash" type="radio" value="1" required <?= (isset($_SESSION['previous_http_post']['flash']) && (bool) $_SESSION['previous_http_post']['flash']) ? 'checked' : ''; ?>/> Oui 
                                        <input name="flash" type="radio" value="0" required <?= (!isset($_SESSION['previous_http_post']['flash']) || (isset($_SESSION['previous_http_post']['flash']) && !(bool) $_SESSION['previous_http_post']['flash'])) ? 'checked' : ''; ?>/> Non
                                    </div>
                                </div>
                            <?php } ?>
                            <?php if (count($phones)) { ?>
                                <div class="form-group">
                                    <label>Numéro à employer : </label>
                                    <select name="id_phone" class="form-control">
                                        <option value="">N'importe lequel</option>
                                        <?php foreach ($phones as $phone) { ?>
                                            <option value="<?php $this->s($phone['id']); ?>" <?= ($_SESSION['previous_http_post']['id_phone'] ?? '') == $phone['id'] ? 'selected' : ''  ?>><?php $this->s($phone['name']); ?></option>
                                        <?php } ?>
                                    </select>
                                </div>
                            <?php } ?>
                            <a class="btn btn-danger" href="<?php echo \descartes\Router::url('Scheduled', 'list'); ?>">Annuler</a>
                            <input type="submit" class="btn btn-success" value="Enregistrer le SMS" /> 	
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
<div class="modal fade" tabindex="-1" id="scheduled-preview-text-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Prévisualisation du message</h4>
            </div>
            <div class="modal-body">
                <pre></pre>
                <p class="credit-estimation-container bold">
                    Ce message devrait coûter <span class="credit-estimation-value"></span> crédits par destinataire.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<script>
	jQuery(document).ready(function()
    {
        var number_inputs = [];
        toto = number_inputs;

		jQuery('.add-contacts').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo \descartes\Router::url('Contact', 'json_list'); ?>',
				valueField: 'id',
				displayField: 'name',
                maxSelection: null,
			});
		});

		jQuery('.add-groupes').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo \descartes\Router::url('Group', 'json_list'); ?>',
				valueField: 'id',
				displayField: 'name',
                maxSelection: null,
			});
		});
        
        jQuery('.add-conditional-groups').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo \descartes\Router::url('ConditionalGroup', 'json_list'); ?>',
				valueField: 'id',
				displayField: 'name',
                maxSelection: null,
			});
		});
        
        jQuery('body').on('click', '.remove-scheduleds-number', function(e)
		{
			jQuery(this).parents('.scheduleds-number-groupe').remove();
		});

		jQuery('.form-datetime').datetimepicker(
		{
			format: 'yyyy-mm-dd hh:ii:ss',
			autoclose: true,
			minuteStep: 1,
			language: 'fr'
		});


        //intlTelInput
		jQuery('body').on('click', '.add-number-button', function(e)
        {
            var random_id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
			var newScheduledsNumberGroupe = '' +
			'<div class="row scheduleds-number-groupe">' +
                '<div class="col-xs-4">' +
                    '<label>Numéro cible : </label><br/>' +
                    '<input id="' + random_id + '" name="" class="form-control phone-international-input" type="tel" >' +
                '</div>' +
                '<div class="scheduleds-number-data-container col-xs-8">' +
                    '<label>Données associées : </label>' +
                    '<div class="form-group" data-uid="' + random_id + '">' +
                        '<input name="" class="form-control scheduled-number-data-name" type="text" placeholder="Nom de la donnée" pattern="[a-zA-Z0-9_]*">' +
                        ' : ' +
                        '<input name="" class="form-control scheduled-number-data-value" type="text" placeholder="Valeur de la donnée">' +
                    '</div>' +
                '</div>' +
                '<a href="#" class="scheduleds-number-groupe-remove"><span class="fa fa-times"></span></a>' +
            '</div>';

            jQuery(this).parent('div').before(newScheduledsNumberGroupe);
            
            var number_input = jQuery('#' + random_id)[0];
			var iti_number_input = window.intlTelInput(number_input, {
                hiddenInput: 'numbers[' + random_id + '][number]',
				defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
				preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
                <?php if ($_SESSION['user']['settings']['authorized_phone_country'] ?? false) { ?>
                    onlyCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['authorized_phone_country'])), false, false); ?>,
                <?php } ?>
				nationalMode: true,
				utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js'
            });

            number_inputs.push({
                'number_input': number_input,
                'iti_number_input': iti_number_input,
            });
        });

        jQuery('body').on('click', '.scheduleds-number-groupe-remove', function (e)
        {
            e.preventDefault();
            jQuery(this).parent('.scheduleds-number-groupe').remove();
            return false;
        });

        var number_input = jQuery('.phone-international-input')[0];
        var iti_number_input = window.intlTelInput(number_input, {
            hiddenInput: 'numbers[' + jQuery(number_input).attr('data-uid') + '][number]',
			defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
			preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
            <?php if ($_SESSION['user']['settings']['authorized_phone_country'] ?? false) { ?>
                onlyCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['authorized_phone_country'])), false, false); ?>,
            <?php } ?>
			nationalMode: true,
			utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js'
		});

        number_inputs.push({
            'number_input': number_input,
            'iti_number_input': iti_number_input,
        });

        jQuery('body').on('click', '.preview-button', function (e)
        {
            e.preventDefault();
            var id_contact = jQuery(this).parents('.scheduled-preview-container').find('select').val();
            var template = jQuery(this).parents('.form-group').find('textarea').val();

            var data = {
                'id_contact' : id_contact,
                'template' : template,
            };

            jQuery.ajax({
                type: "POST",
                url: HTTP_PWD + '/template/preview',
                data: data,
                success: function (data) {
                    jQuery('#scheduled-preview-text-modal').find('.modal-body pre').text(data.result);
                    
                    if (data.estimation_credit !== 'undefined') {
                        jQuery('#scheduled-preview-text-modal').find('.modal-body .credit-estimation-value').text(data.estimation_credit);
                    } else {
                        jQuery('#scheduled-preview-text-modal').find('.modal-body .credit-estimation-value').text('0');
                    }
                    
                    jQuery('#scheduled-preview-text-modal').modal({'keyboard': true});
                },
                dataType: 'json'
            });
        });

        jQuery('.scheduleds-number-groupe-container').on('input', '.scheduled-number-data-value, .scheduled-number-data-name', function (e) 
        {
            var scheduled_number = jQuery(this).parents('.scheduleds-number-groupe');
            var focus_group = jQuery(this).parent('.form-group');
            var focus_input = this;
            var input_name = focus_group.find('.scheduled-number-data-name');
            var input_value = focus_group.find('.scheduled-number-data-value');
            var uid = focus_group.attr('data-uid')

            scheduled_number.find('.form-group').each(function (e) 
            {
                var current_input_name = jQuery(this).find('.scheduled-number-data-name');
                var current_input_value = jQuery(this).find('.scheduled-number-data-value');

                if (current_input_value.is(focus_input) || current_input_name.is(focus_input))
                {
                    return true;
                }

                if (jQuery(current_input_name).val() === '' && jQuery(current_input_value).val() === '')
                {
                    jQuery(this).remove();
                }

                return true;
            });

            if (input_name.val() === '' || input_value.val() === '')
            {
                return true;
            }

            var template = '' +
                    '<div class="form-group" data-uid="' + uid + '">' +
                        '<input name="" class="form-control scheduled-number-data-name" type="text" placeholder="Nom de la donnée" pattern="[a-zA-Z0-9_]*">' +
                        ' : ' +
                        '<input name="" class="form-control scheduled-number-data-value" type="text" placeholder="Valeur de la donnée">' +
                        ' <a href="#" class="scheduled-number-data-remove"><span class="fa fa-times"></span></a>' +
                    '</div>';
            scheduled_number.find('.scheduleds-number-data-container').append(template);
        });

        jQuery('.scheduleds-number-groupe-container').on('click', '.scheduled-number-data-remove', function (e)
        {
            e.preventDefault();
            if (jQuery('.scheduleds-number-data-container .form-group').length > 1)
            {
                jQuery(this).parent('.form-group').remove();
            }

            return false;
        });

        
        jQuery('form').on('submit', function (e)
        {
            jQuery('.scheduleds-number-data-container .form-group').each(function ()
            {
                var name = jQuery(this).find('.scheduled-number-data-name').val();
                name = name.replace(/\W/g, '');
                var uid = jQuery(this).attr('data-uid');
                name = 'numbers[' + uid + '][data][' + name + ']';
                jQuery(this).find('.scheduled-number-data-value').attr('name', name);
            });

            return true;    
        });
    
	});
</script>
<?php
	$this->render('incs/footer');
