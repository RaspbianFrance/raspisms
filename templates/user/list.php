<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Users - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'users'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Utilisateurs</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-user"></i> Utilisateurs
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-user fa-fw"></i> Liste des utilisateurs</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped datatable" id="table-users" style="width:100%">
                                        <thead>
                                            <tr>
                                                <th>Email</th>
                                                <th>Admin</th>
                                                <th>Statut</th>
                                                <th>Crédit utilisé</th>
                                                <th class="checkcolumn"><input type="checkbox" id="check-all"/></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                                <div>
                                    <div class="col-xs-6 no-padding">
                                        <a class="btn btn-success" href="<?php echo \descartes\Router::url('User', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un utilisateur</a>
                                    </div>
                                    <div class="text-right col-xs-6 no-padding">
                                        <strong>Action pour la séléction :</strong>
                                        <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('User', 'update_status', ['csrf' => $_SESSION['csrf'], 'status' => 0]); ?>"><span class="fa fa-pause"></span> Suspendre</button>
                                        <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('User', 'update_status', ['csrf' => $_SESSION['csrf'], 'status' => 1]); ?>"><span class="fa fa-play"></span> Activer</button>
                                        <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('User', 'impersonate', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-child"></span> Incarner</button>
                                        <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('User', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</button>
                                        <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('User', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
            'url': '<?php echo \descartes\Router::url('User', 'list_json'); ?>',
            'dataSrc': 'data',
        },
        "columns" : [
            {data: 'email', render: jQuery.fn.dataTable.render.text()},
            {data: 'admin', render: jQuery.fn.dataTable.render.text()},
            {data: 'status', render: jQuery.fn.dataTable.render.text()},
            {
                data: 'quota_percentage',
                render: function (data, type, row, meta) {
                    var html = jQuery.fn.dataTable.render.text().display(data) + "%";
                    if (row['quota_expired_at'] !== undefined) {
                        html += ' - <span class="danger">Quota expiré le ' + jQuery.fn.dataTable.render.text().display(row['quota_expired_at']) + '</span>';
                    }
                    return html;
                },
            },
            {
                data: 'id',
                render: function (data, type, row, meta) {
                    return '<input name="user_ids[]" type="checkbox" value="' + data + '">';
                },
            },
        ],
        "deferRender": true
    });

});
</script>
<?php
	$this->render('incs/footer');
