<?php
    //Template dashboard
    
    $this->render('incs/head')
?>
<div id="wrapper">
<?php
    $this->render('incs/nav', ['page' => 'stats'])
?>
    <div id="page-wrapper">
        <div class="container-fluid">
            <!-- Page Heading -->
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">
                        Statistiques <small>Statistiques avancées</small>
                    </h1>   
                    <ol class="breadcrumb">
                        <li class="active">
                            <i class="fa fa-dashboard"></i> Statistiques avancées
                        </li>
                    </ol>
                </div>
            </div>
            <!-- /.row -->


            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default dashboard-panel-chart">
                        <div class="panel-heading">
                            <h3 class="panel-title"><i class="fa fa-area-chart fa-fw"></i> Status des SMS envoyés par téléphone : </h3>
                        </div>
                        <div class="panel-body">
                            <form id="sms-status-form" class="form-inline text-right mb-3" action="" method="POST">
                                <div class="form-group">
                                    <label for="id_phone">Téléphone : </label>
                                    <div class="form-group">
                                        <select id="id_phone" name="id_phone" class="form-control">
                                            <option value="">Tous les téléphones</option>
                                            <?php foreach ($phones as $phone) { ?>
                                                <option value="<?php $this->s($phone['id']); ?>"><?php $this->s($phone['name']); ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>	
                                <div class="form-group ml-4">
                                    <label for="start">Période : </label>
                                    <input id="start" name="start" class="form-control form-date auto-width" type="date" value="<?php $this->s($seven_days_ago->format('Y-m-d')) ?>">
                                     - <input id="end" name="end" class="form-control form-date auto-width" type="date" value="<?php $this->s($now->format('Y-m-d')) ?>">
                                </div>
                                
                                <input type="submit" class="btn btn-success ml-4" value="Valider" /> 	
                            </form>
                            <canvas id="bar-chart-sms-status"></canvas>
                            <div id="bar-chart-sms-status-loader" class="text-center mb-5"><div class="loader"></div></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->

    </div>
    <!-- /#page-wrapper -->

</div>
<script>
    smsStatusChart = null;
    const phones = {};
    for (const phone of <?= json_encode($phones); ?>) {
        phones[phone.id] = phone;
    };


    async function drawChart(e = null) {
        const startDate = new Date(document.getElementById('start').value);
        const formatedStartDate = startDate.toISOString().split('T')[0]
        const endDate = new Date(document.getElementById('end').value);
        const formatedEndDate = endDate.toISOString().split('T')[0]
        const id_phone = document.getElementById('id_phone').value;

        let url = <?= json_encode(\descartes\Router::url('Api', 'get_sms_status_stats'))?>;
        url += `?start=${formatedStartDate}&end=${formatedEndDate}`;
        url += id_phone ? `&id_phone=${id_phone}` : '';
        const response = await fetch(url);
        const data = (await response.json()).response;
        
        // Get all dates to avoid holes in data
        const dates = [];
        let currentDate = new Date(startDate);
        while (currentDate <= endDate) {
            const formated_date = (new Date(currentDate)).toISOString().split('T')[0]
            dates.push(formated_date);
            currentDate.setDate(currentDate.getDate() + 1);
        }
        const empty_dataset = Array(dates.length + 1).fill(0)

        const colors = {'failed': '#d9534f', 'unknown': '#337ab7', 'delivered': '#5cb85c'};



        let datasets = {};
        for (const entry of data) { 
            if (!datasets[entry.id_phone]) {
                datasets[entry.id_phone] = {
                    'failed': {
                        'data': [...empty_dataset], 
                        'label': `Phone ${phones[entry.id_phone]['name']} - Failed`,
                        'backgroundColor': colors['failed'],
                        'stack': entry.id_phone,
                    }, 
                    'unknown': {
                        'data': [...empty_dataset], 
                        'label': `Phone ${phones[entry.id_phone]['name']} - Unknown`,
                        'backgroundColor': colors['unknown'],
                        'stack': entry.id_phone,
                    }, 
                    'delivered': {
                        'data': [...empty_dataset], 
                        'label': `Phone ${phones[entry.id_phone]['name']} - Delivered`,
                        'backgroundColor': colors['delivered'],
                        'stack': entry.id_phone,
                    }, 
                };
            }

            const date_index = dates.indexOf(entry.at_ymd);

            // This should never happen, but better be sure
            if (date_index == -1) {
                throw Error('Data for a date not in dates array');
            }

            datasets[entry.id_phone][entry.status]['data'][date_index] = entry.nb;
        }
        // Pass all from dict to array
        const formated_datasets = [];
        for (const key in datasets) {
            formated_datasets.push(datasets[key]['failed']);
            formated_datasets.push(datasets[key]['unknown']);
            formated_datasets.push(datasets[key]['delivered']);
        }

        // Custom plugin to display "Pas de données sur cette période"
        const noDataPlugin = {
            id: 'noDataPlugin',
            afterDraw: (chart) => {
                const datasets = chart.data.datasets;
                const hasData = datasets.some(dataset => dataset.data.some(value => value !== null && value !== undefined && value !== 0));

                if (!hasData) {
                    const ctx = chart.ctx;
                    const { width, height } = chart;
                    ctx.save();
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.font = '3em Helvetica';
                    ctx.fillText('Pas de données sur cette période', width / 2, height / 2);
                    ctx.restore();
                }
            }
        };
        
        // Create the chart
        const ctx = document.getElementById('bar-chart-sms-status');
        const config = {
            type: 'bar',
            data: {
                labels: dates,
                datasets: formated_datasets,
            },
            options: {
                responsive: true,
                scales: {
                    x: {
                        stacked: true,
                    },
                    y: {
                        stacked: true,
                        beginAtZero: true
                    }
                }
            },
            plugins: [noDataPlugin],
        };

        document.getElementById('bar-chart-sms-status-loader').classList.add('hidden');

        // On first run create chart, after update
        if (!smsStatusChart) {
            smsStatusChart = new Chart(ctx, config); 
        } else {
            for (const key in config) {
                smsStatusChart[key] = config[key];
            }
            smsStatusChart.update();
        }
        
    }

    jQuery(document).ready(function()
    {
        drawChart();
    });

    jQuery('#sms-status-form').on('submit', (e) => {
        e.preventDefault();
        drawChart();
        return false;
    });
</script>
<!-- /#wrapper -->
<?php
    $this->render('incs/footer');
