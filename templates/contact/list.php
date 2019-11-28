<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Contacts - Show All'])
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
						Dashboard <small>Contacts</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-user"></i> Contacts
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-user fa-fw"></i> Liste des contacts</h3>
						</div>
						<div class="panel-body">
                            <form method="GET">
                                <?php if (!$contacts) { ?>
                                    <p>Aucun contact n'est enregistré pour le moment.</p>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped" id="table-contacts">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nom</th>
                                                    <th>Numéro</th>
                                                    <th style="width:5%;">Sélectionner</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($contacts as $contact) { ?>
                                                    <tr>
                                                        <td><?php $this->s($contact['id']); ?></td>
                                                        <td><?php $this->s($contact['name']); ?></td>
                                                        <td><?php $this->s($contact['number']); ?></td>
                                                        <td><input type="checkbox" name="ids[]" value="<?php $this->s($contact['id']); ?>"></td>
                                                    </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                <div>
                                    <div class="col-xs-6 no-padding">
                                        <a class="btn btn-success" href="<?php echo \descartes\Router::url('Contact', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un contact</a>
                                    </div>
                                    <?php if ($contacts) { ?>
                                        <div class="text-right col-xs-6 no-padding">
                                                <strong>Action pour la séléction :</strong>
                                                <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('Scheduled', 'add', ['prefilled' => 'contacts']); ?>"><span class="fa fa-send"></span> Envoyer un message</button>
                                                <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('Contact', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</button>
                                                <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Contact', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
                                        </div>
                                    <?php } ?>
                                </div>
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
