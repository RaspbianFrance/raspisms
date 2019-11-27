<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Groupes Conditionnels - Edit'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'conditional_groupes'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Modification groupes conditionnels
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-random"></i> <a href="<?php echo \descartes\Router::url('ConditionalGroup', 'list'); ?>">Groupes Conditionnels</a>
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
							<h3 class="panel-title"><i class="fa fa-edit fa-fw"></i> Modification des groupes conditionnels</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('ConditionalGroup', 'update', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
                            <?php foreach ($groups as $group) { ?>
                                    <input name="groups[<?php $this->s($group['id']); ?>][group][id]" type="hidden" value="<?php $this->s($group['id']); ?>">
									<div class="form-group">
                                        <label>Nom du groupe conditionnel</label>
										<div class="form-group input-group">
											<span class="input-group-addon"><span class="fa fa-user"></span></span>
											<input name="groups[<?php $this->s($group['id']); ?>][name]" class="form-control" type="text" placeholder="Nom groupe" autofocus required value="<?php $this->s($group['name']); ?>">
										</div>
									</div>	
									<div class="form-group">
                                        <label>Condition</label>
                                        <p class="italic small help">
                                            Les conditions vous permettent de définir dynamiquement les contacts qui appartiennent au groupe en utilisant leurs données additionnelles. Pour plus d'informations consultez la documentation relative à <a href="#">l'utilisation des groupes conditionnels.</a>
                                        </p>
										<input class="form-control" name="groups[<?php $this->s($group['id']); ?>][condition]" value="<?php $this->s($group['condition']); ?>"/>
									</div>
									<hr/>
                            <?php } ?>
								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('ConditionalGroup', 'list'); ?>">Annuler</a>
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
				data: '<?php echo \descartes\Router::url('Contact', 'json_list'); ?>',
				valueField: 'id',
				displayField: 'name',
			});
		});
	});
</script>
<?php
	$this->render('incs/footer');
