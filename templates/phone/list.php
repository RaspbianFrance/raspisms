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
                                                <?php if ($_SESSION['user']['settings']['phone_priority']) { ?>
                                                    <th>Priorité</th>
                                                <?php } ?>
                                                <th>Type de téléphone</th>
                                                <th>Callbacks</th>
                                                <?php if ($_SESSION['user']['settings']['phone_limit']) { ?>
                                                    <th>Limites</th>
                                                <?php } ?>
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
                                        <button class="btn btn-default mb-2" type="submit" formaction="<?php echo \descartes\Router::url('Phone', 'update_status', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-refresh"></span> Rafraichir le status</button>
                                        <button class="btn btn-default mb-2" type="submit" formaction="<?php echo \descartes\Router::url('Phone', 'change_status', ['csrf' => $_SESSION['csrf'], 'new_status' => 'available']); ?>"><span class="fa fa-toggle-on"></span> Activer</button>
                                        <button class="btn btn-default mb-2" type="submit" formaction="<?php echo \descartes\Router::url('Phone', 'change_status', ['csrf' => $_SESSION['csrf'], 'new_status' => 'disabled']); ?>"><span class="fa fa-toggle-off"></span> Désactiver</button>
                                        <button class="btn btn-default mb-2" type="submit" formaction="<?php echo \descartes\Router::url('Phone', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</button>
                                        <button class="btn btn-default btn-confirm mb-2" type="submit" formaction="<?php echo \descartes\Router::url('Phone', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
            {
                data: 'name', 
                render: function (data, type, row, meta) {
                    html = jQuery.fn.dataTable.render.text().display(data)
                    switch (row.status)
                    {
                        case 'available':
                            html += ' - <span class="text-success">Disponible</span>'
                            break;

                        case 'disabled':
                            html += ' - <span class="text-warning">Désactivé</span>'
                            break;

                        case 'unavailable':
                            html += ' - <span class="text-danger">Indisponible</span>'
                            break;

                        case 'no_credit':
                            html += ' - <span class="text-warning">Plus de crédit</span>'
                            break;

                        case 'limit_reached':
                            html += ' - <span class="text-warning">Limite RaspiSMS atteinte</span>'
                            break;
                    }

                    return html
                },
            },
            <?php if ($_SESSION['user']['settings']['phone_priority']) { ?>
                {data: 'priority', render: jQuery.fn.dataTable.render.text()},
            <?php } ?>
            {data: 'adapter', render: jQuery.fn.dataTable.render.text()},
            {
                data: '_',
                render: function (data, type, row, meta) {

                    var html = '';

                    if (row.callback_reception) {
                        html += '<div class="bold">Réception d\'un SMS : </div>';
                        html += '<div><code>' + jQuery.fn.dataTable.render.text().display(row.callback_reception) + '</code></div>';
                        html += '<br/>';
                    }


                    if (row.callback_status) {
                        html += '<div class="bold">Changement de statut d\'un SMS : </div>';
                        html += '<div><code>' + jQuery.fn.dataTable.render.text().display(row.callback_status) + '</code></div>';
                        html += '<br/>';
                    }
                    

                    if (row.callback_inbound_call) {
                        html += '<div class="bold">Notification d\'appel entrant : </div>';
                        html += '<div><code>' + jQuery.fn.dataTable.render.text().display(row.callback_inbound_call) + '</code></div>';
                        html += '<br/>';
                    }
                    
                    if (row.callback_end_call) {
                        html += '<div class="bold">Notification de fin d\'appel : </div>';
                        html += '<div><code>' + jQuery.fn.dataTable.render.text().display(row.callback_end_call) + '</code></div>';
                    }

                    return html;
                },
            },
            <?php if ($_SESSION['user']['settings']['phone_limit']) { ?>
                {
                    data: 'limits',
                    render: function (limits) {
                        if (!limits.length)
                        {
                            return 'Pas de limites.';
                        }

                        var html = '';
                        for (limit of limits)
                        {
                            switch (limit.startpoint)
                            {
                                case "today" : 
                                    var startpoint = 'Par jour';
                                    break;
                                case "-24 hours" : 
                                    var startpoint = '24 heures glissantes';
                                    break;
                                case "this week midnight" : 
                                    var startpoint = 'Cette semaine';
                                    break;
                                case "-7 days" : 
                                    var startpoint = '7 jours glissants';
                                    break;
                                case "this week midnight -1 week" : 
                                    var startpoint = 'Ces deux dernières semaines';
                                    break;
                                case "-14 days" : 
                                    var startpoint = '14 jours glissants';
                                    break;
                                case "this month midnight" : 
                                    var startpoint = 'Ce mois';
                                    break;
                                case "-1 month" : 
                                    var startpoint = '1 mois glissant';
                                    break;
                                case "-28 days" : 
                                    var startpoint = '28 jours glissants';
                                    break;
                                case "-30 days" : 
                                    var startpoint = '30 jours glissants';
                                    break;
                                case "-31 days" : 
                                    var startpoint = '31 jours glissants';
                                    break;
                                default : 
                                    var startpoint = 'Inconnu'
                            }
                            html += '<div><span class="bold">' + jQuery.fn.dataTable.render.text().display(startpoint) + ' : </span>' + jQuery.fn.dataTable.render.text().display(limit.volume) + '</div>';
                        }

                        return html;
                    },
                },
            <?php } ?>
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
