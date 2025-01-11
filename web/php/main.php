<?php
/*
───────────────────────────────────────────────────────────────────────────
SnipeZilla Srcds Manager
───────────────────────────────────────────────────────────────────────────
Copyright (C) 2015 SnipeZilla.com

SnipeZilla Srcds Manager is free software:
you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

SnipeZilla Srcds Manager is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with SnipeZilla Srcds Manager.
If not, see <http://www.gnu.org/licenses/>.

───────────────────────────────────────────────────────────────────────────
- contact:                   https://steamcommunity.com/profiles/76561197960637077
- Forum:                     https://www.snipezilla.com
- Steam Group:               https://steamcommunity.com/groups/snipezilla
- Installation Guide & Help: https://www.snipezilla.com/snipezilla-srcds-manager
───────────────────────────────────────────────────────────────────────────
*/
require_once 'users.crc32.php';
?>
<!DOCTYPE html>
 <html>
<!--version: 2.0.0 | SnipeZilla.com-->
	<head>
		<title>SnipeZilla Srcds Manager</title>
		<link rel="shortcut icon" type="image/png" href="css/images/SZ.png">
		<link rel="stylesheet" type="text/css" href="css/default.css?9">
		<link rel="stylesheet" type="text/css" href="css/jqueryFileTree.css">
		<link rel="stylesheet" type="text/css" href="css/font-awesome-4.3.0/css/font-awesome.css">
		<script type="text/javascript" src="js/jquery-3.7.1.min.js"></script>
		<script type="text/javascript" src="js/jquery-ui.min.js"></script>
		<script type="text/javascript" src="js/jquery-scrollto.js"></script>
		<script type="text/javascript" src="js/jqueryFileTree.js" ></script>
		<script type="text/javascript" src="js/snipezilla.js?11"></script>
        <meta content="text/html;charset=utf-8" http-equiv="Content-Type">
	</head>
	<body>
        <div id="container">
			<div id="header">
				<div id="navigation">
					<ul id="nav-1">
						<li class="logo">
							<a href="https://snipezilla.com" target="_blank" title="Snipin' a good job, Mate!"><img src="css/images/logo.png" alt="logo"/></a>
						</li>
                        <?php if ($_SESSION['login']) { ?>
						<?php if ( preg_match('/a|b/',$_SESSION['level'])) { ?>
							<li class="nav-2">
								<a  id="configuration" title="Set-up config.xml" href="#configuration">Configuration</a>
							</li>
                        <?php } ?>
						<?php if ( preg_match('/a|c/',$_SESSION['level']) ) { ?>
							<li class="nav-2">
								<a id="installation" title="SnipeZilla Srcds Manager" href="#installation">Installation</a>
							</li>
                        <?php } ?>

							<li class="nav-2">
								<a id="status" title="Servers Status" href="#status">Status</a>
							</li>
						<?php if ( preg_match('/a|j/',$_SESSION['level']) ) { ?>
							<li class="nav-2">
								<a id="filemanager" title="File Manager" href="#filemanager">File Manager</a>
							</li>
                        <?php } ?>
						    <li class="nav-2">
							    <a id="users" title="Users" href="#users">Users</a>
						    </li>

                        <?php } else { ?>
						    <li class="nav-2">
							    <a id="login" title="Login" href="#login">Login</a>
						    </li>
                        <?php } ?>
						<li class="nav-2">
							<a id="about" title="Legal & Links" href="#about">About</a>
						</li>
						<li id="version"><span class="version" title="(c)snipezilla"></span><?php if ($_SESSION['login'] && $users) {echo '<a href="index.php?logout=true" class="sign-out"><span class="fa fa-sign-out" title="log-out ('.$_SESSION['login'].')"></span></a>';}?></li>
					</ul>
				</div>
			</div>
			<div id="content"></div>
			<div class="footer"></div>
		</div>
		<script>
			Sz.token='<?php echo $_TOKEN;?>';
			Sz.ini();
		</script>
	</body>
</html>
