<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Forget Password'])
?>
<div class="container-fluid">
	<div class="row">
		<form class="col-xs-10 col-xs-offset-1 col-md-4 col-md-offset-4 connexion-form" action="<?php echo \descartes\Router::url('Connect', 'send_reset_password', ['csrf' => $_SESSION['csrf']]); ?>" method="POST">
			<h2>Mot de passe oublié</h2>
			<div class="form-group">
				<label>Adresse e-mail</label>
				<div class="form-group input-group">
					<span class="input-group-addon"><span class="fa fa-at"></span></span>
					<input name="email" class="form-control" type="email" placeholder="Ex : john.doe@example.tld" autofocus required>
				</div>
			</div>	

			<a class="forget-password-link" href="<?php echo \descartes\Router::url('Connect', 'login'); ?>">Se connecter ?</a>
			<button class="btn btn-primary btn-lg btn-block">Renvoyer nouveau mot de passe</button>
		</form>
	</div>
</div>
</script>
<?php
	$this->render('incs/footer');
