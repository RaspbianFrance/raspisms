<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Scheduleds - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'scheduleds'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Scheduleds</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-send"></i> Scheduleds
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-send fa-fw"></i> Liste des SMS à envoyer</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <div class="table-scheduleds">
                                    <table class="table table-bordered table-hover table-striped datatable" id="table-scheduleds">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Contenu</th>
                                                <th class="checkcolumn">&#10003;</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <div class="col-xs-6 no-padding">
                                        <a class="btn btn-success" href="<?php echo \descartes\Router::url('Scheduled', 'add'); ?>"><span class="fa fa-plus"></span> Créer un nouveau SMS</a>
                                    </div>
                                        <div class="text-right col-xs-6 no-padding">
                                            <strong>Action pour la séléction :</strong>
                                            <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('Scheduled', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</button>
                                            <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Scheduled', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
            'url': '<?php echo \descartes\Router::url('Scheduled', 'list_json'); ?>',
            'dataSrc': 'data',
        },
        "columns" : [
            {data: 'at', render: jQuery.fn.dataTable.render.text()},
            {data: 'text', render: jQuery.fn.dataTable.render.text()},
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
