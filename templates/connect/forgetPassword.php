<?php
	//Template dashboard
	$incs = new internalIncs();
	$incs->head('Forget Password');
?>
<div class="container-fluid">
	<div class="row">
		<form class="col-xs-10 col-xs-offset-1 col-md-4 col-md-offset-4 connexion-form" action="<?php echo $this->generateUrl('connect', 'changePassword'); ?>" method="POST">
			<h2>Mot de passe oublié</h2>
			<div class="form-group">
				<label>Adresse e-mail</label>
				<div class="form-group input-group">
					<span class="input-group-addon"><span class="fa fa-at"></span></span>
					<input name="mail" class="form-control" type="email" placeholder="Ex : john.doe@example.tld" autofocus required>
				</div>
			</div>	

			<a class="forget-password-link" href="<?php echo $this->generateUrl('connect'); ?>">Se connecter ?</a>
			<button class="btn btn-primary btn-lg btn-block">Renvoyer nouveau mot de passe</button>
		</form>
	</div>
</div>
</script>
<?php
	$incs->footer();
