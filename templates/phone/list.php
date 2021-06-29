<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Phones - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'phones'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Téléphones</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-phone"></i> Téléphones
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-phone fa-fw"></i> Liste des téléphones</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped datatable" id="table-phones">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Nom</th>
                                                <th>Type de téléphone</th>
                                                <th>Callbacks</th>
                                                <th class="checkcolumn"><input type="checkbox" id="check-all"/></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <div class="col-xs-6 no-padding">
                                        <a class="btn btn-success" href="<?php echo \descartes\Router::url('Phone', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un téléphone</a>
                                    </div>
                                    <div class="text-right col-xs-6 no-padding">
                                        <strong>Action pour la séléction :</strong>
                                        <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Phone', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
            'url': '<?php echo \descartes\Router::url('Phone', 'list_json'); ?>',
            'dataSrc': 'data',
        },
        "columns" : [
            {data: 'id', render: jQuery.fn.dataTable.render.text()},
            {data: 'name', render: jQuery.fn.dataTable.render.text()},
            {data: 'adapter', render: jQuery.fn.dataTable.render.text()},
            {
                data: '_',
                render: function (data, type, row, meta) {
                    var html = '<div class="bold">Réception d\'un SMS : </div>';

                    if (row.callback_reception) {
                        html += '<div><code>' + row.callback_reception + '</code></div>';
                    } else {
                        html += '<div>Non disponible.</div>';
                    }

                    html += '<br/>';
                    html += '<div class="bold">Changement de statut d\'un SMS : </div>';

                    if (row.callback_status) {
                        html += '<div><code>' + row.callback_status + '</code></div>';
                    } else {
                        html += '<div>Non disponible.</div>';
                    }
                    
                    html += '<br/>';
                    html += '<div class="bold">Notification d\'appel entrant : </div>';

                    if (row.callback_inbound_call) {
                        html += '<div><code>' + row.callback_inbound_call + '</code></div>';
                    } else {
                        html += '<div>Non disponible.</div>';
                    }
                    
                    html += '<br/>';
                    html += '<div class="bold">Notification de fin d\'appel : </div>';
                    console.log(row);
                    if (row.callback_end_call) {
                        html += '<div><code>' + row.callback_end_call + '</code></div>';
                    } else {
                        html += '<div>Non disponible.</div>';
                    }

                    return html;
                },
            },
            {
                data: 'id',
                render: function (data, type, row, meta) {
                    return '<input name="ids[]" type="checkbox" value="' + data + '">';
                },
            },
        ],
        "deferRender": true
    });

});
</script>
<?php
	$this->render('incs/footer');
