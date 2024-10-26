<?php
	//Template dashboard
	$this->render('incs/head', ['title' => 'Webhooks - Edit'])
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
						Modification webhooks
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li>
							<i class="fa fa-plug"></i> <a href="<?php echo \descartes\Router::url('Webhook', 'list'); ?>">Webhooks</a>
						</li>
						<li class="active">
							<i class="fa fa-edit"></i> Modifier
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-edit fa-fw"></i>Modification de webhooks</h3>
						</div>
						<div class="panel-body">
							<form action="<?php echo \descartes\Router::url('Webhook', 'update', ['csrf' => $_SESSION['csrf']]);?>" method="POST">
								<?php foreach ($webhooks as $webhook) { ?>
                                        <input type="hidden" value="<?php $this->s($webhook['id']); ?>" name="webhooks[<?php $this->s($webhook['id']); ?>][id]" />
										<div class="form-group">
											<label>URL cible</label>
											<div class="form-group">
											<input value="<?php $this->s($webhook['url']); ?>" name="webhooks[<?php $this->s($webhook['id']); ?>][url]" class="form-control" type="text" placeholder="http://example.fr/webhook/" autofocus required>
											</div>
										</div>	
										<div class="form-group">
											<label>Type de Webhook</label>
											<select name="webhooks[<?php $this->s($webhook['id']); ?>][type]" class="form-control" required>
                                                <option <?php echo $webhook['type'] == 'receive_sms' ? 'selected="selected"' : '' ?> value="receive_sms">Réception d'un SMS</option>
                                                <option <?php echo $webhook['type'] == 'send_sms' ? 'selected="selected"' : '' ?> value="send_sms">Envoi d'un SMS</option>
												<option <?php echo $webhook['type'] == 'send_sms_status_change' ? 'selected="selected"' : '' ?> value="send_sms_status_change">Mise à jour du statut d'un SMS envoyé</option>
												<option <?php echo $webhook['type'] == 'inbound_call' ? 'selected="selected"' : '' ?> value="inbound_call">Réception d'un appel téléphonique</option>
												<option <?php echo $webhook['type'] == 'phone_reliability' ? 'selected="selected"' : '' ?> value="phone_reliability">Détection d'un problème de fiabilité sur un téléphone</option>
											</select>
										</div>	
										<hr/>
                                <?php } ?>
								<a class="btn btn-danger" href="<?php echo \descartes\Router::url('Webhook', 'list'); ?>">Annuler</a>
								<input type="submit" class="btn btn-success" value="Enregistrer la webhook" /> 	
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
