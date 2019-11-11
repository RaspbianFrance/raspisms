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
							<form action="<?php echo \descartes\Router::url('Scheduled', 'update', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
							<?php foreach ($scheduleds as $scheduled) { ?>
                                    <div class="form-group">
										<label>Texte du SMS</label>
										<textarea name="scheduleds[<?php $this->s($scheduled['id']); ?>][text]" class="form-control" required><?php $this->s($scheduled['text'], true); ?></textarea>
									</div>
									<div class="form-group">
										<label>Date d'envoi du SMS</label>
										<input name="scheduleds[<?php $this->s($scheduled['id']); ?>][at]" class="form-control form-datetime" type="text" value="<?php $this->s($scheduled['at']); ?>" readonly>
									</div>	
									<div class="form-group">
										<label>Numéros cibles</label>
										<div class="form-group scheduleds-number-groupe-container" scheduled-id="<?php $this->s($scheduled['id']); ?>" >
											<?php foreach ($scheduled['numbers'] as $number) { ?>
												<div class="form-group scheduleds-number-groupe">
													<input name="" class="form-control phone-international-input" type="tel" value="<?php $this->s($number); ?>" scheduled-id="<?php $this->s($scheduled['id']); ?>">
													<span class="remove-scheduleds-number fa fa-times"></span>
												</div>
											<?php } ?>
											<div class="add-number-button fa fa-plus-circle"></div>
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
									<?php if ($_SESSION['user']['settings']['sms_flash']) { ?>
										<div class="form-group">
											<label>Envoyer comme un SMS Flash : </label>
											<div class="form-group">
												<input name="admin" type="radio" value="1" required <?php echo ($scheduled['flash'] ? 'checked' : ''); ?> /> Oui
												<input name="admin" type="radio" value="0" required <?php echo ($scheduled['flash'] ? '' : 'checked'); ?> /> Non
											</div>
										</div>
									<?php } ?>
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

        jQuery('.phone-international-input').each(function ()
        {
            var hidden_input_name = 'scheduleds[' + jQuery(this).attr('scheduled-id') + '][numbers][]';
            window.intlTelInput(this, {
                hiddenInput: hidden_input_name,
                defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
                preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
                nationalMode: true,
                utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js'
            });
        });

		jQuery('body').on('click', '.add-number-button', function(e)
        {
            var random_id = Math.random().toString(36).substring(2, 15) + Math.random().toString(36).substring(2, 15);
			var scheduledId = jQuery(this).parents('.scheduleds-number-groupe-container').attr('scheduled-id');
			var newScheduledsNumberGroupe = '' +
			'<div class="form-group scheduleds-number-groupe">' +
				'<input name="" class="form-control phone-international-input" type="tel" scheduled-id="' + scheduledId + '" id="' + random_id + '">' +
				'<span class="remove-scheduleds-number fa fa-times"></span>' +
			'</div>';

			jQuery(this).before(newScheduledsNumberGroupe);

            var hidden_input_name = 'scheduleds[' + scheduledId + '][numbers][]';
            var phone_input = jQuery('#' + random_id)[0];
			window.intlTelInput(phone_input, {
                hiddenInput: hidden_input_name,
				defaultCountry: '<?php $this->s($_SESSION['user']['settings']['default_phone_country']); ?>',
				preferredCountries: <?php $this->s(json_encode(explode(',', $_SESSION['user']['settings']['preferred_phone_country'])), false, false); ?>,
				nationalMode: true,
				utilsScript: '<?php echo HTTP_PWD_JS; ?>/intlTelInput/utils.js'
			});

		});


	});
</script>
<?php
	$this->render('incs/footer');
