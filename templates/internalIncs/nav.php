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
				<a class="navbar-brand" href="<?php echo $this->generateUrl('dashboard'); ?>">RaspiSMS</a>
			</div>
			<!-- Top Menu Items -->
			<ul class="nav navbar-right top-nav">
				<li class="dropdown">
					<a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-fw fa-user"></i> <?php secho($email); ?> <b class="caret"></b></a>
					<ul class="dropdown-menu">
						<li>
							<a href="<?php echo $this->generateUrl('profile'); ?>"><i class="fa fa-fw fa-user"></i> Profil</a>
						</li>
						<li class="divider"></li>
						<li>
							<a href="<?php echo $this->generateUrl('connect', 'logout'); ?>"><i class="fa fa-fw fa-power-off"></i> Déconnexion</a>
						</li>
					</ul>
				</li>
			</ul>
			<!-- Sidebar Menu Items - These collapse to the responsive navigation menu on small screens -->
			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<ul class="nav navbar-nav side-nav">
					<li <?php echo $page == 'dashboard' ? 'class="active"' : ''; ?>>
						<a href="<?php echo $this->generateUrl('dashboard'); ?>"><i class="fa fa-fw fa-dashboard"></i> Dashboard</a>
					</li>
					<li <?php echo $page == 'scheduleds' ? 'class="active"' : ''; ?>>
						<a href="<?php echo $this->generateUrl('scheduleds'); ?>"><i class="fa fa-fw fa-envelope"></i> SMS</a>
					</li>
					<li <?php echo $page == 'discussions' ? 'class="active"' : ''; ?>>
						<a href="<?php echo $this->generateUrl('discussions'); ?>"><i class="fa fa-fw fa-comments"></i> Discussions</a>
					</li>
					<li <?php echo $page == 'commands' ? 'class="active"' : ''; ?>>
						<a href="<?php echo $this->generateUrl('commands'); ?>"><i class="fa fa-fw fa-terminal"></i> Commandes</a>
					</li>
					<li>
						<a href="javascript:;" data-toggle="collapse" data-target="#repertoire"><i class="fa fa-fw fa-book"></i> Répertoire <i class="fa fa-fw fa-caret-down"></i></a>
						<ul id="repertoire" class="collapse <?php echo in_array($page, array('contacts', 'groups')) ? 'in' : ''; ?>">
							<li <?php echo $page == 'contacts' ? 'class="active"' : ''; ?>>
								<a href="<?php echo $this->generateUrl('contacts'); ?>"><i class="fa fa-fw fa-user"></i> Contacts</a>
							</li>
							<li <?php echo $page == 'groups' ? 'class="active"' : ''; ?>>
								<a href="<?php echo $this->generateUrl('groups'); ?>"><i class="fa fa-fw fa-group"></i> Groupes</a>
							</li>
						</ul>
					</li>
					<li>
						<a href="javascript:;" data-toggle="collapse" data-target="#logs"><i class="fa fa-fw fa-file-text"></i> Logs <i class="fa fa-fw fa-caret-down"></i></a>
						<ul id="logs" class="collapse <?php echo in_array($page, array('sendeds', 'receiveds', 'events', 'smsstop')) ? 'in' : ''; ?>">
							<li <?php echo $page == 'sendeds' ? 'class="active"' : ''; ?>>
								<a href="<?php echo $this->generateUrl('sendeds'); ?>"><i class="fa fa-fw fa-send"></i> SMS envoyés</a>
							</li>
							<li <?php echo $page == 'receiveds' ? 'class="active"' : ''; ?>>
								<a href="<?php echo $this->generateUrl('receiveds'); ?>"><i class="fa fa-fw fa-download"></i> SMS reçus</a>
							</li>
							<li <?php echo $page == 'smsstop' ? 'class="active"' : ''; ?>>
								<a href="<?php echo $this->generateUrl('smsstop'); ?>"><i class="fa fa-fw fa-ban"></i> SMS STOP</a>
							</li>
							<li <?php echo $page == 'events' ? 'class="active"' : ''; ?>>
								<a href="<?php echo $this->generateUrl('events'); ?>"><i class="fa fa-fw fa-clock-o"></i> Évènements</a>
							</li>
						</ul>
					</li>
					<li <?php echo $page == 'users' ? 'class="active"' : ''; ?>>
						<a href="<?php echo $this->generateUrl('users'); ?>"><i class="fa fa-fw fa-user"></i> Utilisateurs</a>
					</li>
					<?php if ($admin) { ?>
						<li <?php echo $page == 'settings' ? 'class="active"' : ''; ?>>
							<a href="<?php echo $this->generateUrl('settings'); ?>"><i class="fa fa-fw fa-cogs"></i> Réglages</a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<!-- /.navbar-collapse -->
		</nav>
