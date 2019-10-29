<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Groupes - Edit'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'groupes'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Modification groupes
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-group"></i> <a href="<?php echo \Router::url('Groupe', 'list'); ?>">Groupes</a>
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
							<h3 class="panel-title"><i class="fa fa-edit fa-fw"></i> Modification de groupes</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \Router::url('Groupe', 'update', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
							<?php
								foreach ($groupes as $groupe)
								{
									$contacts = array();
									foreach ($groupe['contacts'] as $contact)
									{
										$contacts[] = (int)$contact['id'];
									}
									$contacts = json_encode($contacts);

									?>
                                    <input name="groupes[<?php $this->s($groupe['id']); ?>][groupe][id]" type="hidden" value="<?php $this->s($groupe['id']); ?>">
									<div class="form-group">
										<label>Nom groupe</label>
										<div class="form-group input-group">
											<span class="input-group-addon"><span class="fa fa-user"></span></span>
											<input name="groupes[<?php $this->s($groupe['id']); ?>][name]" class="form-control" type="text" placeholder="Nom groupe" autofocus required value="<?php $this->s($groupe['name']); ?>">
										</div>
									</div>	
									<div class="form-group">
										<label>Contacts du groupe</label>
										<input class="add-contacts form-control" name="groupes[<?php $this->s($groupe['id']); ?>][contacts_ids][]" value="<?php $this->s($contacts); ?>"/>
									</div>
									<hr/>
									<?php
								}
							?>
								<a class="btn btn-danger" href="<?php echo \Router::url('Groupe', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer le groupe" /> 	
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
		jQuery('.add-contacts').each(function()
		{
			jQuery(this).magicSuggest({
				data: '<?php echo \Router::url('Contact', 'json_list'); ?>',
				valueField: 'id',
				displayField: 'name',
			});
		});
	});
</script>
<?php
	$this->render('incs/footer');
