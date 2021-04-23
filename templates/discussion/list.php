<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Discussions - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'discussions'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Discussions</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-comments-o"></i> Discussions
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-comments-o fa-fw"></i> Liste des discussions</h3>
						</div>
						<div class="panel-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-striped datatable" id="table-discussions">
                                    <thead>
                                        <tr>
                                            <th>Date du dernier message</th>
                                            <th>Numéro</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php /*foreach ($discussions as $discussion) { ?>
                                                    <tr class="goto" url="<?php $this->s(\descartes\Router::url('Discussion', 'show', ['number' => $discussion['number']])); ?>">
                                                    <td><?php $this->s($discussion['at']); ?></td>
                                                    <td><?php $this->s(isset($discussion['contact']) ? $discussion['contact'] . ' (' . \controllers\internals\Tool::phone_format($discussion['number']) . ')' : \controllers\internals\Tool::phone_format($discussion['number'])); ?></td>
                                                </tr>
                                        <?php } */?>
                                    </tbody>
                                </table>
                            </div>
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
            'url': '<?php echo \descartes\Router::url('Discussion', 'list_json'); ?>',
            'dataSrc': 'data',
        },
        "columns" : [
            {data: 'at', render: jQuery.fn.dataTable.render.text()},
            {
                data: 'number',
                render: function (data, type, row, meta) {
                    if (row.contact_name) {
                        return jQuery.fn.dataTable.render.text().display(row.contact_name) + ' (' + row.number_formatted + ')';
                    }

                    return row.number_formatted;
                },
            },
        ],
        "createdRow": function (row, data, dataIndex) {
            jQuery(row).addClass('goto').attr('url', data.link);
        },
        "deferRender": true
    });

});
</script>
<?php
	$this->render('incs/footer');
