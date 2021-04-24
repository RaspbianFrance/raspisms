<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Groupes Conditionnels - Show All'])
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
						Dashboard <small>Groupes Conditionnels</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-bullseye"></i> Groupes Conditionnels
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-bullseye fa-fw"></i> Liste des groupes conditionnels</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped datatable" id="table-groupes">
                                        <thead>
                                            <tr>
                                                <th>Nom</th>
                                                <th>Condition</th>
                                                <th>Date de création</th>
                                                <th>Dernière modification</th>
                                                <th class="checkcolumn">&#10003;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <div class="col-xs-6 no-padding">
                                        <a class="btn btn-success" href="<?php echo \descartes\Router::url('ConditionalGroup', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un groupe conditionnel</a>
                                    </div>
                                    <div class="text-right col-xs-6 no-padding">
                                        <strong>Action pour la séléction :</strong>
                                        <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('Scheduled', 'add'); ?>"><span class="fa fa-send"></span> Envoyer un message</button>
                                        <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('ConditionalGroup', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</button>
                                        <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('ConditionalGroup', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
<script>
jQuery(document).ready(function ()
{
    jQuery('.datatable').DataTable({
        "pageLength": 25,
        "lengthMenu": [[25, 50, 100, 1000, 10000, -1], [25, 50, 100, 1000, 10000, "All"]],
        "language": {
            "url": HTTP_PWD + "/assets/js/datatables/french.json",
        },
        "columnDefs": [{
            'targets': 'checkcolumn',
            'orderable': false,
        }],

        "ajax": {
            'url': '<?php echo \descartes\Router::url('ConditionalGroup', 'list_json'); ?>',
            'dataSrc': 'data',
        },
        "columns" : [
            {data: 'name', render: jQuery.fn.dataTable.render.text()},
            {
                data: 'condition', 
                render: function (data, type, row, meta) { 
                    return '<code>' + jQuery.fn.dataTable.render.text().display(data) + '</code>';
                },
            },
            {data: 'created_at'},
            {data: 'updated_at'},
            {
                data: 'id',
                render: function (data, type, row, meta) {
                    return '<input name="conditional_group_ids[]" type="checkbox" value="' + data + '">';
                },
            },
        ],
        "deferRender": true
    });

});
</script>
<?php
	$this->render('incs/footer');
