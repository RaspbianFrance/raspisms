<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'Demande de réinitialisation du mot de passe'])
?>
<div class="container-fluid">
    <div class="row">
		<div class="col-xs-10 col-xs-offset-1 col-md-8 col-md-offset-2 text-center email-reset-container">
            <h2>Email envoyé</h2>
            <div class="col-xs-8 col-xs-offset-2 text-center">
                <img src="<?= HTTP_PWD_IMG; ?>/send-email.png" class="full-width"/>
            </div>
            <div class="col-xs-8 col-xs-offset-2 text-center">
                Un email avec un lien vous permettant de ré-initialiser votre mot de passe viens de vous être envoyé.
            </div>
        </div>
	</div>
</div>
<?php
	$this->render('incs/footer');
