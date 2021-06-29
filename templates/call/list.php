<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Appels - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'calls'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Appels</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-clock-o"></i> Appels
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-clock-o fa-fw"></i> Liste des appels</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <div class="table-events">
                                    <table class="table table-bordered table-hover table-striped datatable" id="table-calls">
                                        <thead>
                                            <tr>
                                                <th>Origine</th>
                                                <th>Destinataire</th>
                                                <th>Début de l'appel</th>
                                                <th>Fin de l'appel</th>
                                                <th>Direction</th>
                                                <th class="checkcolumn"><input type="checkbox" id="check-all"/></th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div>
                                    <div class="text-right col-xs-12 no-padding">
                                        <strong>Action pour la séléction :</strong>
                                        <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Call', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
            'url': '<?php echo \descartes\Router::url('Call', 'list_json'); ?>',
            'dataSrc': 'data',
        },
        "columns" : [
            {
                data: 'origin',
                render: function (data, type, row, meta) {
                    if (row.direction === 'outbound') {
                        return row.phone_name;
                    }

                    if (row.contact_name) {
                        return row.origin_formatted + ' (' + jQuery.fn.dataTable.render.text().display(row.contact_name) + ')';
                    }

                    return row.origin_formatted;
                },
            },
            {
                data: 'destination',
                render: function (data, type, row, meta) {
                    if (row.direction === 'inbound') {
                        return row.phone_name;
                    }

                    if (row.contact_name) {
                        return row.destination_formatted + ' (' + jQuery.fn.dataTable.render.text().display(row.contact_name) + ')';
                    }

                    return row.destination_formatted;
                },
            },
            {data: 'start', render: jQuery.fn.dataTable.render.text()},
            {data: 'end', render: jQuery.fn.dataTable.render.text()},
            {
                data: 'direction',
                render: function (data, type, row, meta) {
                    switch (data) {
                        case 'inbound':
                            return 'Appel entrant';
                            break;

                        case 'outbound':
                            return 'Appel sortant';
                        
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
