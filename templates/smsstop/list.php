<?php
	//Template dashboard
	
	$this->render('incs/head', ['title' => 'SMS STOP - Show All'])
?>
<div id="wrapper">
<?php
	$this->render('incs/nav', ['page' => 'smsstop'])
?>
	<div id="page-wrapper">
		<div class="container-fluid">
			<!-- Page Heading -->
			<div class="row">
				<div class="col-lg-12">
					<h1 class="page-header">
						Dashboard <small>SMS STOP</small>
					</h1>
					<ol class="breadcrumb">
						<li>
							<i class="fa fa-dashboard"></i> <a href="<?php echo \Router::url('Dashboard', 'show'); ?>">Dashboard</a>
						</li>
						<li class="active">
							<i class="fa fa-ban"></i> SMS STOP
						</li>
					</ol>
				</div>
			</div>
			<!-- /.row -->

			<div class="row">
				<div class="col-lg-12">
					<div class="panel panel-default">
						<div class="panel-heading">
							<h3 class="panel-title"><i class="fa fa-ban fa-fw"></i> Liste SMS STOP</h3>
						</div>
                        <div class="panel-body">
                            <form method="GET">
                                <?php if (!$smsstops) { ?>
                                    <p>Aucun SMS STOP n'a été reçu pour le moment.</p>
                                <?php } else { ?>
                                    <div class="table-events">
                                        <table class="table table-bordered table-hover table-striped" id="table-smsstop">
                                            <thead>
                                                <tr>
                                                    <th>#</th>
                                                    <th>Numéro</th>
                                                    <?php if ($_SESSION['user']['admin']) { ?><th>Sélectionner</th><?php } ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                                foreach ($smsstops as $smsstop)
                                                {
                                                    ?>
                                                    <tr>
                                                        <td><?php $this->s($smsstop['id']); ?></td>
                                                        <td><?php $this->s($smsstop['number']); ?></td>
                                                        <?php if ($_SESSION['user']['admin']) { ?><td><input name="ids[]" type="checkbox" value="<?php $this->s($smsstop['id']); ?>"></td><?php } ?>
                                                    </tr>
                                                    <?php
                                                }
                                            ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <div>
                                        <?php if ($_SESSION['user']['admin']) { ?>
                                            <div class="text-right col-xs-12 no-padding">
                                                <strong>Action pour la séléction :</strong>
                                                <button class="btn btn-default" type="submit" formaction="<?php echo \Router::url('SmsStop', 'delete', ['csrf' => $_SESSION['csrf']]); ?>"><span class="fa fa-trash-o"></span> Supprimer</button>
                                            </div>
                                        <?php } ?>
                                        <ul class="pager">
                                            <?php
                                                if ($page)
                                                {
                                                ?>
                                                    <li><a href="<?php echo \Router::url('smsstop', 'showAll', array('page' => $page - 1)); ?>"><span aria-hidden="true">&larr;</span> Précèdents</a></li>
                                                <?php
                                                }

                                                $numero_page = 'Page : ' . ($page + 1);
                                                $this->s($numero_page);

                                                if ($limit == $nb_results)
                                                {
                                                ?>
                                                    <li><a href="<?php echo \Router::url('smsstop', 'showAll', array('page' => $page + 1)); ?>">Suivants <span aria-hidden="true">&rarr;</span></a></li>
                                                <?php
                                                }
                                            ?>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
	jQuery(document).ready(function ()
	{
		jQuery('.action-dropdown a').on('click', function (e)
		{
			e.preventDefault();
			var target = jQuery(this).parents('.action-dropdown').attr('target');
			var url = jQuery(this).attr('href');
			jQuery(target).find('input:checked').each(function ()
			{
				url += '/' + jQuery(this).val();
			});
			window.location = url;
		});
	});
</script>
<?php
	$this->render('incs/footer');
