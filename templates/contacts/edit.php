<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Contacts - Edit');
?>
<div id="wrapper">
<?php
	$incs->nav('contacts');
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
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-user"></i> <a href="<?php echo $this->generateUrl('contacts'); ?>">Contacts</a>
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
							<form action="<?php echo $this->generateUrl('contacts', 'update', [$_SESSION['csrf']]);?>" method="POST">
							<?php
								foreach ($contacts as $contact)
								{
									if (RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS) { ?>
	                                    <div class="form-group">
	                                        <label>Civilité du contact</label>
	                                        <div class="form-group">
	                                            <input name="contacts[<?php secho($contact['contacts.id']); ?>][civility]" type="radio" value="1" required <?php echo ($contact['contacts_infos.civility']==='1' ? 'checked' : ''); ?>/> Monsieur
	                                            <input name="contacts[<?php secho($contact['contacts.id']); ?>][civility]" type="radio" value="0" required <?php echo ($contact['contacts_infos.civility']==='0' ? 'checked' : ''); ?>/> Madame
	                                        </div>
	                                    </div>
	                                    <div class="form-group">
	                                        <label>Prénom du contact (facultatif)</label>
	                                        <div class="form-group input-group">
	                                            <span class="input-group-addon"><span class="fa fa-user"></span></span>
	                                            <input name="contacts[<?php secho($contact['contacts.id']); ?>][first_name]" class="form-control" type="text" placeholder="Prénom du contact (facultatif)" value="<?php secho($contact['contacts_infos.first_name']); ?>">
	                                        </div>
	                                    </div>
	                                <?php } ?>
	                                <?php
	                                	if (RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS) {
	                                		$name = ($contact['contacts_infos.last_name'] != '') ? $contact['contacts_infos.last_name'] : $contact['contacts.name'];
	                                		$tableAlias = "contacts.";
	                                	} else {
	                                		$name = $contact['name'];
	                                		$tableAlias = '';
	                                	}
	                                ?>
	                                <div class="form-group">
	                                    <label>Nom du contact</label>
	                                    <div class="form-group input-group">
	                                        <span class="input-group-addon"><span class="fa fa-user"></span></span>
	                                        <input name="contacts[<?php secho($contact[$tableAlias.'id']); ?>][name]" class="form-control" type="text" placeholder="Nom du contact" required value="<?php secho($name); ?>">
	                                    </div>
	                                </div>
	                                <div class="form-group">
										<label>Numéro de téléphone du contact</label>
										<div class="form-group">
											<input name="" class="form-control phone-international-input" type="tel" contact-id="<?php secho($contact[$tableAlias.'id']); ?>" value="<?php secho($contact[$tableAlias.'number']); ?>">
											<input name="contacts[<?php secho($contact[$tableAlias.'id']); ?>][phone]" type="hidden" id="phone-hidden-input-<?php secho($contact[$tableAlias.'id']); ?>" required>
										</div>
									</div>
	                                <?php if (RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS) { ?>
                                        <input name="contacts[<?php secho($contact['contacts.id']); ?>][contacts_infos_id]" type="hidden" value="<?php secho($contact['contacts_infos.id']); ?>">
	                                    <div class="form-group">
	                                        <label>Date de naissance du contact (facultatif)</label>
	                                        <input name="contacts[<?php secho($contact['contacts.id']); ?>][birthday]" class="form-control form-date" type="text" readonly value="<?php secho($contact['contacts_infos.birthday']); ?>">
	                                    </div>
	                                    <div class="form-group">
	                                        <label>Situation amoureuse du contact (facultatif)</label>
	                                        <div class="form-group">
	                                            <input name="contacts[<?php secho($contact['contacts.id']); ?>][love_situation]" type="radio" value="0" <?php echo ($contact['contacts_infos.love_situation']==='0' ? 'checked' : ''); ?>/> Célibataire
	                                            <input name="contacts[<?php secho($contact['contacts.id']); ?>][love_situation]" type="radio" value="1" <?php echo ($contact['contacts_infos.love_situation']==='1' ? 'checked' : ''); ?>/> En couple
	                                        </div>
	                                    </div>
	                                <?php } ?>
									<hr/>
									<?php
								}
							?>
								<a class="btn btn-danger" href="<?php echo $this->generateUrl('contacts'); ?>">Annuler</a>
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
		jQuery('.phone-international-input').intlTelInput({
			defaultCountry: '<?php secho(RASPISMS_SETTINGS_DEFAULT_PHONE_COUNTRY); ?>',
			preferredCountries: <?php secho(json_encode(explode(',', RASPISMS_SETTINGS_PREFERRED_PHONE_COUNTRY)), false, false); ?>,
			nationalMode: true,
			utilsScript: '<?php echo HTTP_PWD; ?>/js/intlTelInput/lib/libphonenumber/utils.js'
		});

        jQuery('.form-date').datepicker(
        {
            format: 'yyyy-mm-dd',
            autoclose: true,
            minuteStep: 1,
            startView: 3,
            language: 'fr'
        });

		jQuery('form').on('submit', function(e)
		{
			e.preventDefault();
			jQuery('.phone-international-input').each(function(key, value)
			{
				jQuery('#phone-hidden-input-' +  jQuery(this).attr('contact-id')).val(jQuery(this).intlTelInput("getNumber"));
			});

			this.submit();
		});
	});
</script>
<?php
	$incs->footer();
