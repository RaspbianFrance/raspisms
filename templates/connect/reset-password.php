<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Réinitilisation du mot de passer'])
?>
<div class="container-fluid">
	<div class="row">
		<form class="col-xs-10 col-xs-offset-1 col-md-4 col-md-offset-4 connexion-form" action="" method="POST">
			<h2>Ré-initialiez votre mot de passe</h2>
			<div class="form-group">
				<label>Nouveau mot de passe</label>
				<div class="form-group input-group">
					<span class="input-group-addon"><span class="fa fa-lock"></span></span>
					<input name="password" class="form-control" type="password" placeholder="Votre nouveau mot de passe" autofocus required>
				</div>
			</div>	

            <button class="btn btn-primary btn-lg btn-block">Mettre à jour le mot de passe</button>
		</form>
	</div>
</div>
</script>
<?php
	$this->render('incs/footer');
