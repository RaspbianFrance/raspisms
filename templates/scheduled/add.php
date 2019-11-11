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
							<i class="fa fa-calendar"></i> <a href="<?php echo \descartes\Router::url('Scheduled', 'list'); ?>">Scheduleds</a>
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
							<h3 class="panel-title"><i class="fa fa-calendar fa-fw"></i> Ajout d'un SMS programmé</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Scheduled', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Texte du SMS</label>
									<textarea name="text" class="form-control" required></textarea>
								</div>
								<div class="form-group">
									<label>Date d'envoi du SMS</label>
									<input name="at" class="form-control form-datetime" type="text" value="<?php $this->s($now); ?>" readonly>
								</div>	
								<div class="form-group">
									<label>Numéros cibles</label>
									<div class="form-group scheduleds-number-groupe-container">
										<div class="form-group scheduleds-number-groupe">
											<input name="" class="form-control phone-international-input" type="tel" >
											<span class="remove-scheduleds-number fa fa-times"></span>
										</div>
										<div class="add-number-button fa fa-plus-circle"></div>
									</div>
								</div>
								<div class="form-group">
									<label>Contacts cibles</label>
									<input class="add-contacts form-control" name="contacts[]"/>
								</div>
								<div class="form-group">
									<label>Groupes cibles</label>
									<input class="add-groupes form-control" name="groups[]"/>
								</div>
								<?php if ($_SESSION['user']['settings']['sms_flash']) { ?>
									<div class="form-group">
										<label>Envoyer comme un SMS Flash : </label>
										<div class="form-group">
											<input name="flash" type="radio" value="1" required /> Oui 
											<input name="flash" type="radio" value="0" required checked/> Non
										</div>
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
<script>
	jQuery(document).ready(function()
    {
        var number_inputs = [];

		jQuery('.add-contacts').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo \descartes\Router::url('Contact', 'json_list'); ?>',
				valueField: 'id',
				displayField: 'name',
			});
		});

		jQuery('.add-groupes').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo \descartes\Router::url('Group', 'json_list'); ?>',
				valueField: 'id',
				displayField: 'name',
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
			'<div class="form-group scheduleds-number-groupe">' +
				'<input name="" class="form-control phone-international-input" type="tel" id="' + random_id + '">' +
				'<span class="remove-scheduleds-number fa fa-times"></span>' +
			'</div>';

            jQuery(this).before(newScheduledsNumberGroupe);
            
            var number_input = jQuery('#' + random_id)[0];
			var iti_number_input = window.intlTelInput(number_input, {
                hiddenInput: 'numbers[]',
				defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
				preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
				nationalMode: true,
				utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js'
            });

            number_inputs.push({
                'number_input': number_input,
                'iti_number_input': iti_number_input,
            });
        });

        var number_input = jQuery('.phone-international-input')[0];
        var iti_number_input = window.intlTelInput(number_input, {
            hiddenInput: 'numbers[]',
			defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
			preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
			nationalMode: true,
			utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js'
		});

        number_inputs.push({
            'number_input': number_input,
            'iti_number_input': iti_number_input,
        });
	});
</script>
<?php
	$this->render('incs/footer');
