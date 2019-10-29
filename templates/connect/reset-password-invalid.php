<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Réinitialisation du mot de passe'])
?>
<div class="container-fluid">
	<div class="row">
            <h2>Erreur lors de la réinitialisation</h2>
            <div class="col-xs-8 col-xs-offset-2 text-center">
                <img src="<?php HTTP_PWD_IMG; ?>/reinitialize-password-error.png" />
            </div>
            <div class="col-xs-8 col-xs-offset-2 text-center">
                Le lien de ré-initialisation que vous avez utilisé est incorrect ou expiré.<br/>
                Merci de re-demander un modification de votre mot de passe.
            </div>
	</div>
</div>
</script>
<?php
	$this->render('incs/footer');
