<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Contacts - Edit'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'contacts'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Modification contacts
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-user"></i> <a href="<?php echo \descartes\Router::url('Contact', 'list'); ?>">Contacts</a>
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
							<h3 class="panel-title"><i class="fa fa-edit fa-fw"></i> Modification de contacts</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Contact', 'update', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
                            <?php foreach ($contacts as $contact) { ?>
									<div class="form-group">
                                        <input name="contacts[<?php $this->s($contact['id']); ?>][id]" type="hidden" value="<?php $this->s($contact['id']); ?>">
										<label>Nom contact</label>
										<div class="form-group input-group">
											<span class="input-group-addon"><span class="fa fa-user"></span></span>
											<input name="contacts[<?php $this->s($contact['id']); ?>][name]" class="form-control" type="text" placeholder="Nom contact" autofocus required value="<?php $this->s($contact['name']); ?>">
										</div>
									</div>	
									<div class="form-group">
										<label>Numéro de téléphone du contact</label>
										<div class="form-group">
											<input name="" class="form-control phone-international-input" type="tel" contact-id="<?php $this->s($contact['id']); ?>" value="<?php $this->s($contact['number']); ?>">
										</div>
									</div>
                                    <div class="form-group">
                                        <label>Données du contact</label>
                                        <p class="italic small help" id="description-data">
                                            Les données d'un contact vous permettent de l'enrichir afin de pouvoir accéder à ces données au sein d'un message via <a href="#">l'utilisation de templates.</a><br/>
                                            Laissez vide si vous ne souhaitez pas renseigner d'informations supplémentaires pour le contact. Utilisez uniquement des lettres, des chiffres et des underscore pour les noms de données, ni espace ni caractères spéciaux.
                                        </p>
                                        <div class="contact-data-container" data-id-contact="<?php $this->s($contact['id']); ?>">
                                            <?php if (!$contact['data']) { ?>
                                                <div class="form-group">
                                                    <input name="" class="form-control contact-data-name" type="text" placeholder="Nom de la donnée" pattern="[a-zA-Z0-9_]*">
                                                     : 
                                                    <input name="" class="form-control contact-data-value" type="text" placeholder="Valeur de la donnée">
                                                </div>
                                            <?php } else {?>
                                                <?php foreach ($contact['data'] as $name => $data) { ?>
                                                    <div class="form-group">
                                                        <input name="" class="form-control contact-data-name" type="text" placeholder="Nom de la donnée" pattern="[a-zA-Z0-9_]*" value="<?php $this->s($name); ?>">
                                                         : 
                                                        <input name="" class="form-control contact-data-value" type="text" placeholder="Valeur de la donnée" value="<?php $this->s($data); ?>">
                                                        <a href="#" class="contact-data-remove"><span class="fa fa-times"></span></a>
                                                    </div>
                                                <?php } ?>
                                                <div class="form-group">
                                                    <input name="" class="form-control contact-data-name" type="text" placeholder="Nom de la donnée" pattern="[a-zA-Z0-9_]*">
                                                     : 
                                                    <input name="" class="form-control contact-data-value" type="text" placeholder="Valeur de la donnée">
                                                    <a href="#" class="contact-data-remove"><span class="fa fa-times"></span></a>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>	
									<hr/>
                                <?php } ?>
                                <a class="btn btn-danger" href="<?php echo \descartes\Router::url('Contact', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le contact" /> 	
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
        jQuery('.phone-international-input').each(function()
        {
            var number_input = this;
            var hidden_input_name = 'contacts[' + jQuery(number_input).attr('contact-id') + '][number]';
            var iti_number_input = window.intlTelInput(number_input, {
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
        
        
        jQuery('.contact-data-container').on('input', '.contact-data-value, .contact-data-name', function (e) 
        {
            var focus_group = jQuery(this).parent('.form-group');
            var focus_input = this;
            var input_name = focus_group.find('.contact-data-name');
            var input_value = focus_group.find('.contact-data-value');

            var data_container = jQuery(this).parents('.contact-data-container');

            data_container.find('.form-group').each(function (e) 
            {
                var current_input_name = jQuery(this).find('.contact-data-name');
                var current_input_value = jQuery(this).find('.contact-data-value');

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
                '<div class="form-group">' +
                    '<input name="" class="form-control contact-data-name" type="text" placeholder="Nom de la donnée" pattern="[a-zA-Z0-9_]*">' +
                    ' : ' +
                    '<input name="" class="form-control contact-data-value" type="text" placeholder="Valeur de la donnée">' +
                    ' <a href="#" class="contact-data-remove"><span class="fa fa-times"></span></a>' +
                '</div>';
            data_container.append(template);
        });

        jQuery('.contact-data-container').on('click', '.contact-data-remove', function (e)
        {
            e.preventDefault();
            if (jQuery('.contact-data-container .form-group').length > 1)
            {
                jQuery(this).parent('.form-group').remove();
            }

            return false;
        });

        jQuery('form').on('submit', function (e)
        {
            e.preventDefault();

            jQuery('.contact-data-container .form-group').each(function ()
            {
                var contact_id = jQuery(this).parents('.contact-data-container').attr('data-id-contact');
                var name = jQuery(this).find('.contact-data-name').val();
                name = name.replace(/\W/g, '');

                name = 'contacts[' + contact_id + '][data][' + name + ']';
                jQuery(this).find('.contact-data-value').attr('name', name);
            });

            e.currentTarget.submit();    
        });
	});
</script>
<?php
	$this->render('incs/footer');
