<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Sendeds - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'sendeds'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>SMS envoyés</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-upload"></i> SMS envoyés
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-upload fa-fw"></i> Liste des SMS envoyés</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <div class="table-sendeds">
                                    <table class="table table-bordered table-hover table-striped datatable" id="table-sendeds">
                                        <thead>
                                            <tr>
                                                <th>De</th>
                                                <th>À</th>
                                                <th>Message</th>
                                                <th>Date</th>
                                                <th>Statut</th>
                                                <th class="checkcolumn">&#10003;</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div>
                                    <div class="text-right col-xs-12 no-padding">
                                        <strong>Action pour la séléction :</strong>
                                        <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Sended', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
                                    </div>
                                </div>
                            </div>
                        </form>
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
        "bLengthChange": false,
        "language": {
            "url": HTTP_PWD + "/assets/js/datatables/french.json",
        },
        "columnDefs": [{
            'targets': 'checkcolumn',
            'orderable': false,
        }],

        "ajax": {
            'url': '<?php echo \descartes\Router::url('Sended', 'list_json'); ?>',
            'dataSrc': 'data',
        },
        "columns" : [
            {data: 'phone_name', render: jQuery.fn.dataTable.render.text()},
            {
                data: 'destination',
                render: function (data, type, row, meta) {
                    if (row.contact_name) {
                        return row.destination_formatted + ' (' + jQuery.fn.dataTable.render.text().display(row.contact_name) + ')';
                    }

                    return row.destination_formatted;
                },
            },
            {data: 'text', render: jQuery.fn.dataTable.render.text()},
            {data: 'at', render: jQuery.fn.dataTable.render.text()},
            {
                data: 'status',
                render: function (data, type, row, meta) {
                    switch (data) {
                        case 'failed':
                            return 'Échec';
                            break;

                        case 'delivered':
                            return 'Délivré';
                        
                        default:
                            return 'Inconnu';
                    }
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
