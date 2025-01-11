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
require_once 'session.start.php';
$_TOKEN=$_GET['token'];
require_once 'users.crc32.php';
?>
<div>

	<form id='login-form' action='index.php' method='post' accept-charset='UTF-8'>

		<fieldset >
		<legend>Login</legend>
			<input type='hidden' name='submitted' id='submitted' value='1'/>
 
			<label for='username' >UserName:</label>
			<input type='text' name='username' id='username'  maxlength="50" />
 
			<label for='password' >Password:</label>
			<input type='password' name='password' id='password' maxlength="50" />
 
			<input type='submit' name='Submit' value='Submit' />
 
		</fieldset>

	</form>

</div>

<div>