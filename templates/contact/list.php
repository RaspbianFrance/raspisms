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
                        <a class="btn btn-info float-right" id="btn-export" href="#"><span class="fa fa-upload"></span> Exporter la liste des contacts</a>
                        <a class="btn btn-info float-right" id="btn-import" href="#" style="margin-right: 10px;"><span class="fa fa-download"></span> Importer une liste de contacts</a>
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
                                        <table class="table table-bordered table-hover table-striped datatable" id="table-contacts">
                                            <thead>
                                                <tr>
                                                    <th>Nom</th>
                                                    <th>Numéro</th>
                                                    <th class="checkcolumn">&#10003;</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($contacts as $contact) { ?>
                                                    <tr>
                                                        <td><?php $this->s($contact['name']); ?></td>
                                                        <td><?php echo(\controllers\internals\Tool::phone_link($contact['number'])); ?></td>
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
<div class="modal fade" tabindex="-1" id="import-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php $this->s(\descartes\Router::url('Contact', 'import', ['csrf' => $_SESSION['csrf']])); ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Importer une liste de contacts</h4>
                </div>
                <div class="modal-body text-center">
                    <p>Vous pouvez importer une liste aux formats suivants : CSV ou JSON.</p>
                    <input id="contacts_list_file" type="file" name="contacts_list_file" class="hidden" required="required">
                    <label class="btn btn-default" for="contacts_list_file"><span class="fa fa-file-text-o hidden invalid-icon"></span><span class="fa fa-check hidden valid-icon"></span> Choisir le fichier</label>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                    <input type="submit" class="btn btn-success" value="Valider" />
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" tabindex="-1" id="export-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php $this->s(\descartes\Router::url('Contact', 'import', ['csrf' => $_SESSION['csrf']])); ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Exporter la liste des contacts</h4>
                </div>
                <div class="modal-body text-center">
                    <p>Vous pouvez exporter la liste aux formats suivants.</p>
                    <a target="_blank" href="<?php $this->s(\descartes\Router::url('Contact', 'export', ['format' => 'csv'])); ?>" class="btn btn-default">CSV</a>
                    <a target="_blank" href="<?php $this->s(\descartes\Router::url('Contact', 'export', ['format' => 'json'])); ?>" class="btn btn-default">JSON</a>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
	jQuery(document).ready(function()
    {
        jQuery('body').on('click', '#btn-import', function ()
        {
            jQuery('#import-modal').modal({'keyboard': true});
        });
        
        jQuery('body').on('click', '#btn-export', function ()
        {
            jQuery('#export-modal').modal({'keyboard': true});
        });
    });
</script>
<?php
	$this->render('incs/footer');
