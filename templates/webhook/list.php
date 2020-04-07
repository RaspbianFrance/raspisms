<?php
	$this->render('incs/head', ['title' => 'Webhooks - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'webhooks'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>Webhooks</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-plug"></i> Webhooks
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-plug fa-fw"></i> Liste des webhooks</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <?php if (!$webhooks) { ?>
                                    <p>Aucun webhook n'a été créé pour le moment.</p>
                                <?php } else { ?>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-striped datatable" id="table-webhooks">
                                            <thead>
                                                <tr>
                                                    <th>Url</th>
                                                    <th>Type de webhook</th>
                                                    <th class="checkcolumn">&#10003;</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($webhooks as $webhook) { ?>
                                                    <tr>
                                                        <td><?php $this->s($webhook['url']); ?></td>
                                                        <td><?php $this->s($webhook['type'] == 'send_sms' ? 'Envoi de SMS' : 'Reception de SMS'); ?></td>
                                                        <td><input type="checkbox" name="ids[]" value="<?php $this->s($webhook['id']); ?>"></td>
                                                    </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php } ?>
                                <div>
                                    <div class="col-xs-6 no-padding">
                                        <a class="btn btn-success" href="<?php echo \descartes\Router::url('Webhook', 'add'); ?>"><span class="fa fa-plus"></span> Ajouter un webhook</a>
                                    </div>
                                    <?php if ($webhooks) { ?>
                                        <div class="text-right col-xs-6 no-padding">
                                            <strong>Action pour la séléction :</strong>
                                            <button class="btn btn-default" type="submit" formaction="<?php echo \descartes\Router::url('Webhook', 'edit'); ?>"><span class="fa fa-edit"></span> Modifier</button>
                                            <button class="btn btn-default btn-confirm" type="submit" formaction="<?php echo \descartes\Router::url('Webhook', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
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
<?php
	$this->render('incs/footer');
