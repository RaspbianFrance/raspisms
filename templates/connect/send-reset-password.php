<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Demande de réinitialisation du mot de passe'])
?>
<div class="container-fluid">
	<div class="row">
            <h2>Email envoyé</h2>
            <div class="col-xs-8 col-xs-offset-2 text-center">
                <img src="<?php HTTP_PWD_IMG; ?>/send-email.png" />
            </div>
            <div class="col-xs-8 col-xs-offset-2 text-center">
                Un email avec un lien vous permettant de ré-initialiser votre mot de passe viens de vous être envoyé.
            </div>
	</div>
</div>
</script>
<?php
	$this->render('incs/footer');
