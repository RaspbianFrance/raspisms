<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Scheduleds - Edit'])
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
						Modifier SMS programmés
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-calendar"></i> <a href="<?php echo \descartes\Router::url('Scheduled', 'list'); ?>">Scheduleds</a>
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
							<h3 class="panel-title"><i class="fa fa-edit fa-fw"></i> Modification des SMS programmés</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Scheduled', 'update', ['csrf' => $_SESSION['csrf']]);?>" method="POST" enctype="multipart/form-data">
							<?php foreach ($scheduleds as $scheduled) { ?>
                                    <div class="form-group">
                                        <label>Texte du SMS</label>
                                        <?php if ($_SESSION['user']['settings']['templating']) { ?>
                                            <p class="italic small help description-scheduled-text">
                                                Vous pouvez utilisez des fonctionnalités de templating pour indiquer des valeures génériques qui seront remplacées par les données du contact au moment de l'envoie. Pour plus d'information, consultez la documentation sur <a href="#">l'utilisation des templates.</a><br/>
                                                Vous pouvez obtenir une prévisualisation du résultat pour un contact en cliquant sur le boutton <b>"Prévisualiser"</b>.
                                            </p>
                                        <?php } ?>
										<textarea name="scheduleds[<?php $this->s($scheduled['id']); ?>][text]" class="form-control" required><?php $this->s($scheduled['text'], true); ?></textarea>
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
                                        <div class="form-group">
                                            <label>Ajouter un média</label>
                                            <p class="italic small help description-scheduled-media">
                                                L'ajout d'un média nécessite un téléphone supportant l'envoi de MMS. Pour plus d'information, consultez la documentation sur <a href="#">l'utilisation des MMS.</a>.
                                            </p>
                                            <div class="form-group">
                                                <input class="" name="scheduleds_<?php $this->s($scheduled['id']); ?>_medias[]" value="" type="file" multiple />
                                            </div>
                                            <?php if ($scheduled['medias']) { ?>
                                                <div class="current-medias-container">
                                                    <label>Médias déjà attachés au SMS</label>
                                                    <?php foreach ($scheduled['medias'] as $key => $media) { ?>
                                                        <p class="current-media">
                                                            <input type="hidden" name="scheduleds[<?php $this->s($scheduled['id']); ?>][media_ids][]" value="<?php $this->s($media['id']); ?>">
                                                            <label>Fichier <?= $key + 1 ?> :</label><br/> <a href="<?php $this->s(HTTP_PWD_DATA_PUBLIC . '/' . $media['path']); ?>" class="btn btn-info btn-sm" target="_blank">Voir le média</a> <a href="#" class="btn btn-warning btn-delete-media btn-sm">Supprimer le média</a>
                                                        </p>
                                                    <?php } ?>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    <?php } ?>
									<div class="form-group">
										<label>Date d'envoi du SMS</label>
										<input name="scheduleds[<?php $this->s($scheduled['id']); ?>][at]" class="form-control form-datetime auto-width" type="text" value="<?php $this->s($scheduled['at']); ?>" readonly>
									</div>
                                    <div class="form-group">
                                        <label>Numéros cibles</label>
                                        <div class="form-group scheduleds-number-groupe-container container-fluid" data-scheduled-id="<?php $this->s($scheduled['id']); ?>">
                                            <?php foreach ($scheduled['numbers'] as $number_key => $number) { ?>
                                                <div class="row scheduleds-number-groupe">
                                                    <div class="col-xs-4">
                                                        <label>Numéro cible : </label><br/>
                                                        <input name="" data-uid="<?= $number_key ?>" class="form-control phone-international-input" type="tel" value="<?php $this->s($number['number']); ?>" >
                                                    </div>
                                                    <div class="scheduleds-number-data-container col-xs-8">
                                                        <label>Données associées : </label>
                                                        <?php foreach ($number['data'] as $data_key => $data_value) { ?>
                                                            <div class="form-group" data-uid="<?= $number_key ?>">
                                                                <input value="<?php $this->s($data_key); ?>" name="" class="form-control scheduled-number-data-name" type="text" placeholder="Nom de la donnée" pattern="[a-zA-Z0-9_]*">
                                                                : 
                                                                <input value="<?php $this->s($data_value); ?>" name="" class="form-control scheduled-number-data-value" type="text" placeholder="Valeur de la donnée">
                                                                <a href="#" class="scheduled-number-data-remove"><span class="fa fa-times"></span></a>
                                                            </div>
                                                        <?php } ?>
                                                        <div class="form-group" data-uid="<?= $number_key ?>">
                                                                <input name="" class="form-control scheduled-number-data-name" type="text" placeholder="Nom de la donnée" pattern="[a-zA-Z0-9_]*">
                                                                : 
                                                                <input name="" class="form-control scheduled-number-data-value" type="text" placeholder="Valeur de la donnée">
                                                        </div>
                                                    </div>
                                                    <?php if (!($first ?? true)) { ?>
                                                        <a href="#" class="scheduleds-number-groupe-remove"><span class="fa fa-times"></span></a>
                                                    <?php } ?>
                                                    <?php $first = false; ?>
                                                </div>
                                            <?php } ?>
                                            <div class="text-center"><div class="add-number-button fa fa-plus-circle"></div></div>
                                        </div>
                                    </div>
									<div class="form-group">
										<label>Contacts cibles</label>
										<input class="add-contacts form-control" name="scheduleds[<?php $this->s($scheduled['id']); ?>][contacts][]" value="<?php $this->s(json_encode($scheduled['contacts'])); ?>" />
									</div>
									<div class="form-group">
										<label>Groupes cibles</label>
										<input class="add-groupes form-control" name="scheduleds[<?php $this->s($scheduled['id']); ?>][groups][]" value="<?php $this->s(json_encode($scheduled['groups'])); ?>" />
									</div>
                                    <?php if ($_SESSION['user']['settings']['conditional_group'] ?? false) { ?>
                                        <div class="form-group">
                                            <label>Groupes conditionnels cibles</label>
                                            <input class="add-conditional-groups form-control" name="scheduleds[<?php $this->s($scheduled['id']); ?>][conditional_groups][]" value="<?php $this->s(json_encode($scheduled['conditional_groups'])); ?>" />
                                        </div>
                                    <?php } ?>
                                    <div class="form-group scheduled-media-group">
                                        <label>Ajouter un fichier CSV de destinataires</label>
                                        <p class="italic small help description-scheduled-csv">
                                            Le SMS sera envoyé à tous les numéros inclus dans le fichier CSV. Assurez-vous que le fichier CSV respecte le format indiqué dans la documentation sur <a href="https://documentation.raspisms.fr/users/sms/csv.html" target="_blank">l'envoi de SMS à un fichier CSV.</a>
                                        </p>
                                        <div class="form-group">
                                            <input class="" name="scheduleds_<?php $this->s($scheduled['id']); ?>_csv" value="" type="file" multiple />
                                        </div>
                                    </div>
									<?php if ($_SESSION['user']['settings']['sms_flash']) { ?>
										<div class="form-group">
											<label>Envoyer comme un SMS Flash : </label>
											<div class="form-group">
												<input name="admin" type="radio" value="1" required <?php echo ($scheduled['flash'] ? 'checked' : ''); ?> /> Oui
												<input name="admin" type="radio" value="0" required <?php echo ($scheduled['flash'] ? '' : 'checked'); ?> /> Non
											</div>
										</div>
									<?php } ?>
                                    <div class="form-group">
                                        <label>Numéro à employer : </label>
                                        <select name="scheduleds[<?php $this->s($scheduled['id']); ?>][id_phone]" class="form-control">
                                            <option <?php echo ($scheduled['id_phone'] ? '' : 'selected="selected"'); ?> value="">N'importe lequel</option>
                                            <?php foreach ($phones as $phone) { ?>
                                                <option <?php echo ($scheduled['id_phone'] == $phone['id'] ? 'selected="selected"' : '' ); ?> value="<?php $this->s($phone['id']); ?>"><?php $this->s($phone['name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
									<hr/>
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
		jQuery('.form-datetime').datetimepicker(
		{
			format: 'yyyy-mm-dd hh:ii:ss',
			autoclose: true,
			minuteStep: 1,
			language: 'fr'
		});

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

        jQuery('.phone-international-input').each(function ()
        {
            var scheduledId = jQuery(this).parents('.scheduleds-number-groupe-container').attr('data-scheduled-id');
            var uid = jQuery(this).attr('data-uid');
            var hidden_input_name = 'scheduleds[' + scheduledId + '][numbers][' + uid + '][number]';
            window.intlTelInput(this, {
                hiddenInput: hidden_input_name,
                defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
                preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
                <?php if ($_SESSION['user']['settings']['authorized_phone_country'] ?? false) { ?>
                    onlyCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['authorized_phone_country'])), false, false); ?>,
                <?php } ?>
                nationalMode: true,
                utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js'
            });
        });

        jQuery('body').on('click', '.add-number-button', function(e)
        {
            var random_id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
            var scheduledId = jQuery(this).parents('.scheduleds-number-groupe-container').attr('data-scheduled-id');
			var newScheduledsNumberGroupe = '' +
			'<div class="row scheduleds-number-groupe" data-scheduled-id="' + scheduledId + '">' +
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
                hiddenInput: 'scheduleds[' + scheduledId + '][numbers][' + random_id + '][number]',
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

        jQuery('body').on('click', '.btn-delete-media', function (e)
        {
            e.preventDefault();
            jQuery(this).parents('.current-media').remove();
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
                var scheduled_id = jQuery(this).parents('.scheduleds-number-groupe-container').attr('data-scheduled-id')
                var uid = jQuery(this).attr('data-uid');
                name = 'scheduleds[' + scheduled_id + '][numbers][' + uid + '][data][' + name + ']';
                jQuery(this).find('.scheduled-number-data-value').attr('name', name);
            });

            return true;    
        });
	});
</script>
<?php
	$this->render('incs/footer');
