<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Connexion'])
?>
<div class="container-fluid">
	<div class="row">
		<form class="col-xs-10 col-xs-offset-1 col-md-4 col-md-offset-4 connexion-form" action="<?php echo \descartes\Router::url('Connect', 'connection'); ?>" method="POST">
			<h2>Connexion - RaspiSMS</h2>
			<div class="form-group">
				<label>Adresse e-mail</label>
				<div class="form-group input-group">
					<span class="input-group-addon"><span class="fa fa-at"></span></span>
					<input name="mail" class="form-control" type="email" placeholder="Ex : john.doe@example.tld" autofocus required>
				</div>
			</div>	
			<div class="form-group">
				<label>Mot de passe</label>
				<div class="form-group input-group">
					<span class="input-group-addon"><span class="fa fa-lock"></span></span>
					<input name="password" class="form-control" type="password" placeholder="Your password" required>
				</div>
			</div>	

			<a class="forget-password-link" href="<?php echo \descartes\Router::url('Connect', 'forget_password'); ?>">Mot de passe oublié ?</a>
			<button class="btn btn-primary btn-lg btn-block">Connexion</button>
		</form>
	</div>
</div>
<?php
	$this->render('incs/footer');
