<?php
	//Template dashboard
    $this->render('incs/head', ['title' => 'Mot de passe ré-initialisé'])
?>
<div class="container-fluid">
	<div class="row">
            <h2>Mot de passe ré-initialisé</h2>
            <div class="col-xs-8 col-xs-offset-2 text-center">
                <img src="<?php HTTP_PWD_IMG; ?>/reinitialize-password-validation.png" />
            </div>
            <div class="col-xs-8 col-xs-offset-2 text-center">
                Votre mot de passe a bien été mis à jour, vous allez êtres redirigé vers la page de connexion dans quelques secondes.
            </div>
	</div>
</div>
<script>
    window.setTimeout(function () {
        location.href = "<?php echo \descartes\Router::url('Connect', 'login'); ?>";
    }, 5000);
</script>
<?php
	$this->render('incs/footer');
