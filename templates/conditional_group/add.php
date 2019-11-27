<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Groupes Conditionnels - Add'])
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
						Nouveau groupe conditionnel
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-random"></i> <a href="<?php echo \descartes\Router::url('ConditionalGroup', 'list'); ?>">Groupes Conditionnels</a>
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
							<h3 class="panel-title"><i class="fa fa-random fa-fw"></i> Ajout d'un groupe conditionnel</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('ConditionalGroup', 'create', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<div class="form-group">
									<label>Nom du groupe conditionnel</label>
									<div class="form-group input-group">
										<span class="input-group-addon"><span class="fa fa-users"></span></span>
										<input name="name" class="form-control" type="text" placeholder="Nom groupe" autofocus required>
									</div>
								</div>	
								<div class="form-group">
									<label>Condition</label>
                                    <p class="italic small help">
                                        Les conditions vous permettent de définir dynamiquement les contacts qui appartiennent au groupe en utilisant leurs données additionnelles. Pour plus d'informations consultez la documentation relative à <a href="#">l'utilisation des groupes conditionnels.</a>
                                    </p>
									<input class="form-control" name="condition" placeholder="Ex : contact.datas.gender == 'male'"/>
								</div>
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
<?php
	$this->render('incs/footer');
