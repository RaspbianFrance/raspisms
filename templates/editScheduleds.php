<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Scheduleds - Edit');
?>
<div id="wrapper">
<?php
	$incs->nav('scheduleds');
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
							<i class="fa fa-dashboard"></i> <a href="<?php echo $this->generateUrl('dashboard'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-calendar"></i> <a href="<?php echo $this->generateUrl('scheduleds'); ?>">Scheduleds</a>
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
							<form action="<?php echo $this->generateUrl('scheduleds', 'update', array('csrf' => $_SESSION['csrf']));?>" method="POST">
							<?php
								foreach ($scheduleds as $scheduled)
								{
									$numbers = array();
									foreach ($scheduled['numbers'] as $number)
									{
										$numbers[] = $number['number'];
									}
						
									$contacts = array();
									foreach ($scheduled['contacts'] as $contact)
									{
										$contacts[] = (int)$contact['id'];
									}
	
									$groups = array();
									foreach ($scheduled['groups'] as $group)
									{
										$groups[] = (int)$group['id'];
									}
									
									$numbers = json_encode($numbers);
									$contacts = json_encode($contacts);
									$groups = json_encode($groups);
								?>
									<div class="form-group">
										<label>Texte du SMS</label>
										<textarea name="scheduleds[<?php secho($scheduled['id']); ?>][content]" class="form-control" required><?php secho($scheduled['content'], true); ?></textarea>
									</div>
									<div class="form-group">
										<label>Date d'envoi du SMS</label>
										<input name="scheduleds[<?php secho($scheduled['id']); ?>][date]" class="form-control form-datetime" type="text" value="<?php secho($scheduled['at']); ?>" readonly>
									</div>	
									<div class="form-group">
										<label>Numéros cibles</label>
										<input class="add-numbers form-control" name="scheduleds[<?php secho($scheduled['id']); ?>][numbers][]" value="<?php secho($numbers); ?>"/>
									</div>
									<div class="form-group">
										<label>Contacts cibles</label>
										<input class="add-contacts form-control" name="scheduleds[<?php secho($scheduled['id']); ?>][contacts][]" value="<?php secho($contacts); ?>" />
									</div>
									<div class="form-group">
										<label>Groupes cibles</label>
										<input class="add-groups form-control" name="scheduleds[<?php secho($scheduled['id']); ?>][groups][]" value="<?php secho($groups); ?>" />
									</div>
									<hr/>
								<?php
								}
							?>
								<a class="btn btn-danger" href="<?php echo $this->generateUrl('scheduleds'); ?>">Annuler</a>
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
			format: 'yyyy-mm-dd hh:ii',
			autoclose: true,
			minuteStep: 1,
			language: 'fr'
		});

		jQuery('.add-numbers').each(function()
		{
			jQuery(this).magicSuggest();
		});

		jQuery('.add-contacts').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo $this->generateUrl('contacts', 'jsonGetContacts'); ?>',
				valueField: 'id',
				displayField: 'name',
			});
		});

		jQuery('.add-groups').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo $this->generateUrl('groups', 'jsonGetGroups'); ?>',
				valueField: 'id',
				displayField: 'name',
			});
		});
	});
</script>
<?php
	$incs->footer();
