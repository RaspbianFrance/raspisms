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
                        <a class="btn btn-warning float-right" id="btn-conditional-deletion" href="#"><span class="fa fa-trash-o"></span> Supprimer une liste dynamique de contacts</a>
                        <a class="btn btn-info float-right" id="btn-export" href="#" style="margin-right: 10px;"><span class="fa fa-upload"></span> Exporter la liste des contacts</a>
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
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped datatable" id="table-contacts">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>Numéro</th>
                                                <th>Date de création</th>
                                                <th>Dernière modification</th>
                                                <th class="checkcolumn"><input type="checkbox" id="check-all"/></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <div class="col-xs-6 no-padding">
                                        <a class="btn btn-success" href="<?php echo \descartes\Router::url('Contact', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un contact</a>
                                    </div>
                                    <div class="text-right col-xs-6 no-padding">
                                            <strong>Action pour la séléction :</strong>
                                            <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('Scheduled', 'add'); ?>"><span class="fa fa-send"></span> Envoyer un message</button>
                                            <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('Contact', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</button>
                                            <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Contact', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
                                    </div>
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
<div class="modal fade" tabindex="-1" id="conditional-deletion-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="<?php $this->s(\descartes\Router::url('Contact', 'conditional_delete', ['csrf' => $_SESSION['csrf']])); ?>" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Supprimer des contacts de façon conditionnelle</h4>
                </div>
                <div class="modal-body text-center">
                    <div class="form-group">
                        <p>Vous pouvez supprimer une liste dynamique de contacts, construite selon des règles basées sur les données de ces contacts. Pour plus d'informations consultez la documentation relative à <a href="https://documentation.raspisms.fr/users/groups_and_contacts/conditionnals_groups.html" target="_blank">l'utilisation des groupes conditionnels.</a><br/></p>
                        <input class="form-control" name="condition" placeholder="Ex : contact.gender == 'male'" required/>
                        <div class="conditional-deletion-preview-container">
                            <div class="conditional-deletion-preview-text"></div>
                            <a class="btn btn-info conditional-deletion-preview-button" href="#">Prévisualiser les contacts qui seront supprimés</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                    <button class="btn btn-default btn-confirm" type="submit"><span class="fa fa-trash-o"></span> Supprimer les contacts</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
jQuery(document).ready(function()
{
    //Import/export contacts
    jQuery('body').on('click', '#btn-import', function ()
    {
        jQuery('#import-modal').modal({'keyboard': true});
    });
    
    jQuery('body').on('click', '#btn-export', function ()
    {
        jQuery('#export-modal').modal({'keyboard': true});
    });
    
    jQuery('body').on('click', '#btn-conditional-deletion', function ()
    {
        jQuery('#conditional-deletion-modal').modal({'keyboard': true});
    });

    jQuery('body').on('click', '.conditional-deletion-preview-button', function (e)
    {
        e.preventDefault();
        var condition = jQuery(this).parents('.form-group').find('input').val();

        var data = {
            'condition' : condition,
        };

        jQuery.ajax({
            type: "POST",
            url: HTTP_PWD + '/conditional_group/preview/',
            data: data,
            success: function (data) {
                jQuery('.conditional-deletion-preview-text').text(data.result);
            },
            dataType: 'json'
        });
    });
    
    
    //Datatable
    jQuery('.datatable').DataTable({
        "pageLength": 25,
        "lengthMenu": [[25, 50, 100, 1000, 10000, -1], [25, 50, 100, 1000, 10000, "All"]],
        "language": {
            "url": HTTP_PWD + "/assets/js/datatables/french.json",
        },
        "orderMulti": false,
        "columnDefs": [{
            'targets': 'checkcolumn',
            'orderable': false,
        }],
        "serverSide": true,
        "ajax": {
            'url': '<?php echo \descartes\Router::url('Contact', 'list_json'); ?>',
            'dataSrc': 'data',
        },
        "columns" : [
            {data: 'name', render: jQuery.fn.dataTable.render.text()},
            {data: 'number_formatted'},
            {data: 'created_at'},
            {data: 'updated_at'},
            {
                data: 'id',
                render: function (data, type, row, meta) {
                    return '<input name="contact_ids[]" type="checkbox" value="' + data + '">';
                },
            },
        ],
        "deferRender": true
    });

});
</script>
<?php
	$this->render('incs/footer');
