<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => ''])
?>
<div class="container-fluid">
	<div class="row">
        <div class="col-xs-10 col-xs-offset-1 col-md-4 col-md-offset-4 text-center" style="color: #fff">
            <h1>Une erreur inconnue est survenue, vous devriez peut-être ré-essayer dans quelques minutes.</h1>
            <br/>
            <img src="<?= HTTP_PWD_IMG; ?>/error_unknown.svg" width="100%"/>
            <br/><br/>
            <br/><br/>
            <a href="<?= HTTP_PWD; ?>" class="btn btn-default btn-lg">Retour à l'accueil</a>
            <br/><br/>
        </div>
	</div>
</div>
<?php
	$this->render('incs/footer');
