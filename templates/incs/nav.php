<!-- Navigation -->
		<nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<!-- Brand and toggle get grouped for better mobile display -->
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>
                <a class="navbar-brand" href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>"><img class="logo" src="<?= HTTP_PWD_IMG; ?>/logo.png" alt="RaspiSMS" /></a>
			</div>

            <!-- Top Menu Items -->
			<ul class="nav navbar-right top-nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-fw fa-user"></i> <?php $this->s($_SESSION['user']['email'] ?? 'Mon compte'); ?> <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li>
							<a href="<?php echo \descartes\Router::url('Account', 'show'); ?>"><i class="fa fa-fw fa-user"></i> Profil</a>
						</li>
						<li class="divider"></li>
						<li>
							<a href="<?php echo \descartes\Router::url('Account', 'logout'); ?>"><i class="fa fa-fw fa-power-off"></i> Déconnexion</a>
						</li>
					</ul>
				</li>
			</ul>

            <!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
			<div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
					<li <?php echo $page == 'dashboard' ? 'class="active"' : ''; ?>>
						<a href="<?php echo \descartes\Router::url('Dashboard', 'show'); ?>"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
					</li>
					<li <?php echo $page == 'discussions' ? 'class="active"' : ''; ?>>
						<a href="<?php echo \descartes\Router::url('Discussion', 'list'); ?>"><i class="fa fa-fw fa-comments"></i> Discussions</a>
					</li>
					<li>
						<a href="javascript:;" data-toggle="collapse" data-target="#smss"><i class="fa fa-fw fa-envelope"></i> SMS <i class="fa fa-fw fa-caret-down"></i></a>
						<ul id="smss" class="collapse <?php echo in_array($page, array('scheduleds', 'sendeds', 'receiveds', 'receiveds_unread')) ? 'in' : ''; ?>">
							<li <?php echo $page == 'scheduleds' ? 'class="active"' : ''; ?>>
                                <a href="<?php echo \descartes\Router::url('Scheduled', 'list'); ?>"><i class="fa fa-fw fa-send"></i> Nouveau SMS</a>
							</li>
							<li <?php echo $page == 'sendeds' ? 'class="active"' : ''; ?>>
								<a href="<?php echo \descartes\Router::url('Sended', 'list'); ?>"><i class="fa fa-fw fa-upload"></i> SMS envoyés</a>
							</li>
							<li <?php echo $page == 'receiveds' ? 'class="active"' : ''; ?>>
								<a href="<?php echo \descartes\Router::url('Received', 'list'); ?>"><i class="fa fa-fw fa-download"></i> SMS reçus</a>
							</li>
							<li <?php echo $page == 'receiveds_unread' ? 'class="active"' : ''; ?>>
								<a href="<?php echo \descartes\Router::url('Received', 'list_unread'); ?>"><i class="fa fa-fw fa-eye-slash"></i> SMS non lus</a>
							</li>
						</ul>
					</li>
					<li>
						<a href="javascript:;" data-toggle="collapse" data-target="#repertoire"><i class="fa fa-fw fa-book"></i> Répertoire <i class="fa fa-fw fa-caret-down"></i></a>
						<ul id="repertoire" class="collapse <?php echo in_array($page, array('contacts', 'groupes', 'conditional_groupes')) ? 'in' : ''; ?>">
							<li <?php echo $page == 'contacts' ? 'class="active"' : ''; ?>>
								<a href="<?php echo \descartes\Router::url('Contact', 'list'); ?>"><i class="fa fa-fw fa-user"></i> Contacts</a>
							</li>
							<li <?php echo $page == 'groupes' ? 'class="active"' : ''; ?>>
								<a href="<?php echo \descartes\Router::url('Group', 'list'); ?>"><i class="fa fa-fw fa-group"></i> Groupes</a>
                            </li>
                            <?php if ($_SESSION['user']['settings']['conditional_group'] ?? false) { ?>
                                <li <?php echo $page == 'conditional_groupes' ? 'class="active"' : ''; ?>>
                                    <a href="<?php echo \descartes\Router::url('ConditionalGroup', 'list'); ?>"><i class="fa fa-fw fa-bullseye"></i> Groupes Conditionnels</a>
                                </li>
                            <?php } ?>
						</ul>
                    </li>
                    <?php if (!in_array('log', json_decode($_SESSION['user']['settings']['hide_menus'], true) ?? [])) { ?>
                        <li>
                            <a href="javascript:;" data-toggle="collapse" data-target="#logs"><i class="fa fa-fw fa-file-text"></i> Logs <i class="fa fa-fw fa-caret-down"></i></a>
                            <ul id="logs" class="collapse <?php echo in_array($page, array('events', 'smsstop', 'calls')) ? 'in' : ''; ?>">
                                <?php if (!in_array('smsstop', json_decode($_SESSION['user']['settings']['hide_menus'], true) ?? [])) { ?>
                                    <li <?php echo $page == 'smsstop' ? 'class="active"' : ''; ?>>
                                        <a href="<?php echo \descartes\Router::url('SmsStop', 'list'); ?>"><i class="fa fa-fw fa-ban"></i> SMS STOP</a>
                                    </li>
                                <?php } ?>
                                <?php if (!in_array('calls', json_decode($_SESSION['user']['settings']['hide_menus'], true) ?? [])) { ?>
                                    <li <?php echo $page == 'calls' ? 'class="active"' : ''; ?>>
                                        <a href="<?php echo \descartes\Router::url('Call', 'list'); ?>"><i class="fa fa-fw fa-file-audio-o"></i> Appels</a>
                                    </li>
                                <?php } ?>
                                <?php if (!in_array('events', json_decode($_SESSION['user']['settings']['hide_menus'], true) ?? [])) { ?>
                                    <li <?php echo $page == 'events' ? 'class="active"' : ''; ?>>
                                        <a href="<?php echo \descartes\Router::url('Event', 'list'); ?>"><i class="fa fa-fw fa-clock-o"></i> Évènements</a>
                                    </li>
                                <?php } ?>
                            </ul>
                        </li>
                    <?php } ?>
                    <?php if (ENABLE_COMMAND) { ?>
                        <?php if (!in_array('commands', json_decode($_SESSION['user']['settings']['hide_menus'], true) ?? [])) { ?>
                            <li <?php echo $page == 'commands' ? 'class="active"' : ''; ?>>
                                <a href="<?php echo \descartes\Router::url('Command', 'list'); ?>"><i class="fa fa-fw fa-terminal"></i> Commandes</a>
                            </li>
                        <?php } ?>
					<?php } ?>
                    <?php if ($_SESSION['user']['settings']['webhook'] ?? false) { ?>
                        <li <?php echo $page == 'webhooks' ? 'class="active"' : ''; ?>>
                            <a href="<?php echo \descartes\Router::url('Webhook', 'list'); ?>"><i class="fa fa-fw fa-plug"></i> Webhooks</a>
                        </li>
					<?php } ?>
                    <?php if (!in_array('phones', json_decode($_SESSION['user']['settings']['hide_menus'], true) ?? [])) { ?>
                        <li <?php echo $page == 'phones' ? 'class="active"' : ''; ?>>
                            <a href="<?php echo \descartes\Router::url('Phone', 'list'); ?>"><i class="fa fa-fw fa-phone"></i> Téléphones</a>
                        </li>
					<?php } ?>
                    <?php if (!in_array('settings', json_decode($_SESSION['user']['settings']['hide_menus'], true) ?? [])) { ?>
                        <li <?php echo $page == 'settings' ? 'class="active"' : ''; ?>>
                            <a href="<?php echo \descartes\Router::url('Setting', 'show'); ?>"><i class="fa fa-fw fa-cogs"></i> Réglages</a>
                        </li>
					<?php } ?>
                    <?php if (\controllers\internals\Tool::is_admin()) { ?>
                        <li <?php echo $page == 'users' ? 'class="active"' : ''; ?>>
                            <a href="<?php echo \descartes\Router::url('User', 'list'); ?>"><i class="fa fa-fw fa-user"></i> Utilisateurs</a>
                        </li>
					<?php } ?>
				</ul>
			</div>
			<!-- /.navbar-collapse -->
		</nav>
