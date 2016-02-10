<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Groups - Add');
?>
<div id="wrapper">
<?php
	$incs->nav('groups');
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Nouveau groupe
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-group"></i> <a href="<?php echo $this->generateUrl('groups'); ?>">Groupes</a>
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
							<h3 class="panel-title"><i class="fa fa-group fa-fw"></i> Ajout d'un groupe</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo $this->generateUrl('groups', 'create', [$_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Nom du groupe</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-users"></span></span>
										<input name="name" class="form-control" type="text" placeholder="Nom groupe" autofocus required>
									</div>
								</div>
								<div class="form-group">
									<label>Contacts au groupe</label>
									<input class="add-contacts form-control" name="contacts[]"/>
								</div>
								<a class="btn btn-danger" href="<?php echo $this->generateUrl('groups'); ?>">Annuler</a>
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
	jQuery(document).ready(function()
	{
		<?php
			if (RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS) {
				$magicSuggestRenderer = "(data['contacts_infos.civility']!=null ? (data['contacts_infos.civility']==1 ? 'M. ' : 'Mme ') : '')";
				$magicSuggestRenderer .= " + data['contacts.name']";
				$magicSuggestRenderer .= " + (data['contacts_infos.birthday']!=null ? ' (' + age(data['contacts_infos.birthday']) + ' ans)' : '')";
			} else {
				$magicSuggestRenderer = "data['name']";
			}
		?>

		// Affiche plus d'infos que le nom du contact si on est en mode infos contacts
		jQuery('.add-contacts').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo $this->generateUrl('contacts', 'jsonGetContacts'); ?>',
				valueField: '<?php echo RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS ? 'contacts.' : ''; ?>id',
				displayField: '<?php echo RASPISMS_SETTINGS_EXTENDED_CONTACTS_INFOS ? 'contacts.' : ''; ?>name',
				name: 'contacts[]',
				allowFreeEntries: false, // évite que l'utilisateur ne saisisse autre chose qu'un contact de la liste
				renderer: function(data) {
		            return <?php echo $magicSuggestRenderer; ?>;
		        }
			});
		});

		function age(birthday)
		{
		  	birthday = new Date(birthday);
		  	return new Number((new Date().getTime() - birthday.getTime()) / 31536000000).toFixed(0);
		}
	});
</script>
<?php
	$incs->footer();
