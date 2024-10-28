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
                        <a class="btn btn-warning float-right" id="btn-invalid-numbers" href="#"><span class="fa fa-eraser"></span> Télécharger les numéros invalides</a>
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
                                                <th>Expéditeur</th>
                                                <th>Destinataire</th>
                                                <th>Message</th>
                                                <th>Tag</th>
                                                <th>Date</th>
                                                <th>Statut</th>
                                                <th class="checkcolumn"><input type="checkbox" id="check-all"/></th>
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
<div class="modal fade" tabindex="-1" id="invalid-numbers-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="invalid-numbers-form" action="<?php $this->s(\descartes\Router::url('Api', 'get_invalid_numbers')); ?>" method="GET">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">Télécharger les numéros invalides</h4>
                </div>
                <div class="modal-body">
                    <p class="help">Vous pouvez téléchager une liste de destinataires qui affichent un taux d'erreur anormal selon les critères de votre choix (liste limitée à 25 000 numéros).</p>
                    <div class="form-group">
                        <label>Volume minimum de SMS envoyés au numéros</label>
                        <div class="form-group input-group">
                            <span class="input-group-addon"><span class="fa fa-arrow-circle-up"></span></span>
                            <input name="volume" class="form-control" type="number" min="1" step="1" placeholder="" autofocus required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pourcentage d'échecs minimum</label>
                        <div class="form-group input-group">
                            <span class="input-group-addon"><span class="fa fa-percent"></span></span>
                            <input name="percent_failed" class="form-control" type="number" min="0" step="1" placeholder="" autofocus required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pourcentage d'inconnus minimum</label>
                        <div class="form-group input-group">
                            <span class="input-group-addon"><span class="fa fa-percent"></span></span>
                            <input name="percent_unknown" class="form-control" type="number" min="0" step="1" placeholder="" autofocus required>
                        </div>
                    </div>
                    <div id="invalid-numbers-loader" class="text-center hidden"><div class="loader"></div></div>
                </div>
                <div class="modal-footer">
                    <a type="button" class="btn btn-danger" data-dismiss="modal">Annuler</a>
                    <input type="submit" class="btn btn-success" value="Valider" />
                </div>
            </form>
        </div>
    </div>
</div>
<script>
jQuery(document).ready(function ()
{
    jQuery('body').on('click', '#btn-invalid-numbers', function ()
    {
        jQuery('#invalid-numbers-modal').modal({'keyboard': true});
    });

    jQuery('body').on('submit', '#invalid-numbers-form', function (e)
    {
        e.preventDefault();
        
        jQuery('#invalid-numbers-loader').removeClass('hidden');

        const form = this;
        const formData = jQuery(form).serialize();

        let invalidNumbers = []; // Array to store cumulative results

        // Function to fetch data and handle pagination
        const fetchData = (url, limit = -1, params = null) => {
            if (params) {
                url += '?' + params;
            }
            
            fetch(url)
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(jsonResponse => {
                invalidNumbers = invalidNumbers.concat(jsonResponse.response);

                // Check if there is a "next" URL to fetch more data
                if (jsonResponse.next && limit != 0) {
                    fetchData(jsonResponse.next, limit - 1); // Recursive call for next page
                } else {
                    exportToCSV(invalidNumbers);
                    jQuery('#invalid-numbers-loader').addClass('hidden');
                }
            })
            .catch(error => {
                console.error('There was a problem with the fetch operation:', error);
            });
        };

        // Function to export data to CSV
        const exportToCSV = (results) => {
            // Define the CSV headers
            let csvContent = "Destination,Total SMS Sent,Failed Percentage,Unknown Percentage\n";
            
            // Append each row of data to the CSV content
            results.forEach(item => {
                csvContent += `${item.destination},${item.total_sms_sent},${item.failed_percentage},${item.unknown_percentage}\n`;
            });

            // Create a downloadable link for the CSV file
            const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const downloadLink = document.createElement('a');
            downloadLink.href = url;
            downloadLink.download = 'invalid_numbers.csv';
            
            // Trigger download
            document.body.appendChild(downloadLink);
            downloadLink.click();
            document.body.removeChild(downloadLink); // Clean up
        };

        // Initial call to fetch data
        fetchData(form.action, 1000, formData);
    });

    jQuery('.datatable').DataTable({
        "pageLength": 25,
        "lengthMenu": [[25, 50, 100, 1000, 10000, Math.pow(10, 10)], [25, 50, 100, 1000, 10000, "All"]],
        "language": {
            "url": HTTP_PWD + "/assets/js/datatables/french.json",
        },
        "orderMulti": false,
        "order": [[4, "desc"]],
        "columnDefs": [{
            'targets': 'checkcolumn',
            'orderable': false,
        }],
        "serverSide": true,
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
            {
                data: 'text',
                render: function (data, type, row, meta) {
                    if (row.mms == 1) {
                        var medias = [];
                        for (i = 0; i < row.medias.length; i++) {
                            medias.push('<a href="' + HTTP_PWD + '/data/public/' + jQuery.fn.dataTable.render.text().display(row.medias[i].path) + '" target="_blank">Fichier ' + (i + 1) + '</a>');
                        }
                        html = jQuery.fn.dataTable.render.text().display(data) + '<br/>' + medias.join(' - ');
                        return html;
                    }

                    return jQuery.fn.dataTable.render.text().display(data);
                },
            },
            {data: 'tag', render: jQuery.fn.dataTable.render.text()},
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
