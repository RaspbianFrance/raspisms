<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Events - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'events'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Évènements</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-clock-o"></i> Évènements
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-clock-o fa-fw"></i> Liste des évènements</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <div class="table-events">
                                    <table class="table table-bordered table-hover table-striped datatable" id="table-events">
                                        <thead>
                                            <tr>
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Texte</th>
                                                <?php if ($_SESSION['user']['admin']) { ?>
                                                    <th class="checkcolumn">&#10003;</th>
                                                <?php } ?>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                                <div>
                                    <?php if ($_SESSION['user']['admin']) { ?>
                                        <div class="text-right col-xs-12 no-padding">
                                            <strong>Action pour la séléction :</strong>
                                            <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Event', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
            'url': '<?php echo \descartes\Router::url('Event', 'list_json'); ?>',
            'dataSrc': 'data',
        },
        "columns" : [
            {
                data: 'type',
                    render: function (data, type, row, meta) {
                    return '<span class="fa fa-fw ' + row.icon + '"></span>';
                },
            },
            {data: 'at', render: jQuery.fn.dataTable.render.text()},
            {data: 'text', render: jQuery.fn.dataTable.render.text()},
            
            <?php if ($_SESSION['user']['admin']) { ?>
            {
                data: 'id',
                render: function (data, type, row, meta) {
                    return '<input name="ids[]" type="checkbox" value="' + data + '">';
                },
            },
            <?php } ?>
        ],
        "deferRender": true
    });

});
</script>
<?php
	$this->render('incs/footer');
