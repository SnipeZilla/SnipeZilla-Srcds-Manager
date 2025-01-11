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
if ( !preg_match('/a|b/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

require 'config.php';

$total = sizeof($server);
$IPAddress = preg_split('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', shell_exec('wmic NICCONFIG WHERE IPEnabled=true GET IPAddress'),-1, PREG_SPLIT_DELIM_CAPTURE);
$ip =array();
 foreach ($IPAddress as $v) {

    if (preg_match('/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/', $v)) $ip[sizeof($ip)] = $v;

}
//Check if service is installed
$root = exec('chdir',$o,$r);
$root = preg_replace('/\\\\web.*+$/','\\',$root);
$appdir = preg_replace('/\\\\bin.*+$/','\\',$root);
$ExecFile = 'php.exe';
$ExecPath = $root.'\\'.$ExecFile.' -f '.$root.'\SzMgr.php';
//
$SzMgr = "SnipeZilla Srcds Manager";
if ( preg_match('/'.$SzMgr.'/i', shell_exec('schtasks /Query')) ) {

    $task_status = TaskMgr($ExecFile) ? 'Running' : 'Ready'; 

} else {

    $task_status = 'Create';

}
?>
<div id="server">
<div class="control">
<span id="saved" title="Save Config"></span>
<table>
	<tr>
		<td>
			<input class="default" type="checkbox" name="default" value="1" <?php if ($default) echo ' checked';?> title="Use Default values for steamcmd" >Default<br/>
			<input class="global" type="checkbox" name="global" value="1" <?php if ($common) echo ' checked';?> title="Use same values for every server (from steamcmd)" >Common
		</td>
		<td>
			<span id="add-server" title="Add a new dedicated server" class="fa fa-plus fa-3"></span>
		</td>
		<td>
			<span id="log-server" class="fa fa-file-text-o fa-3" title="View Log Files"></span>
		</td>
		<td>
			<span id="save-server" class="fa fa-floppy-o fa-3" title="Save Config"></span>
		</td>
        <?php if ( preg_match('/a|c/',$_SESSION['level']) ) { ?>
		<td>
			<span id="reload-config" class="fa fa-refresh fa-3<?php if ($task_status=='Create') {echo ' disable" title="Task is not installed">';}else{ echo '" title="Reload Config">';}?></span>
		</td>
        <?php } ?>
    </tr>
</table>
</div>
	<ul id="svrlist">
		<li><a href="#server-0">SteamCMD</a></li>
    <?php for ($i=1; $i<$total; $i++) {
		    echo '<li><a href="#server-'.$i.'">'.( $server[$i]['pid']? '<span class="fa fa-steam-square server-online" title="ONLINE"></span>' : '<span class="fa fa-close server-delete" title="Delete Server"></span>' ).'<span>'.($server[$i]['fname']?$server[$i]['fname']:'Server '.$i).'</span></a></li>';
        }
    ?>
	</ul>

	<div id="server-0" class="setup">
			<div title="SteamCMD.exe location">
				<span class="title">SteamCMD Location</span><span name="steamcmddir" class="dir path"><?php echo $server[0]['steamcmddir'];?></span><span class="fa fa-search browse" title="Select steamcmd.exe"></span>
			</div>
			<div title="The server will be stopped with a plugin on update available">
				<span class="title">Plugin</span><input type="checkbox" name="plugin" value="1" <?php if ($server[0]['plugin']) echo ' checked';?>>
			</div>
			<div title="Flag file, an update is available. Use for plugin and on server start.">
				<span class="title">Update File name</span><input type="text" name="updatefile" value="<?php echo $server[0]['updatefile'];?>">
			</div>
			<div title="Srcds Process Priority.">
				<span class="title">Srcds Priority</span>
					<select name="priority">
					<?php
						echo "<option value=\"256\"".($server[0]['priority']==256?' selected="selected"':'').">Real time</option>";
						echo "<option value=\"128\"".($server[0]['priority']==128?' selected="selected"':'').">High priority</option>";
						echo "<option value=\"32768\"".($server[0]['priority']==32768?' selected="selected"':'').">Above normal</option>";
						echo "<option value=\"32\"".($server[0]['priority']==32?' selected="selected"':'').">Normal</option>";
						echo "<option value=\"16384\"".($server[0]['priority']==16384?' selected="selected"':'').">Below Normal</option>";
						echo "<option value=\"64\"".($server[0]['priority']==64?' selected="selected"':'').">Idle</option>";
					?>
					</select>
			</div>
			<div title="Update all servers from a cache folder with xcopy.">
				<span class="title">XCOPY</span><input type="checkbox" name="xcopy" value="1" <?php if ($server[0]['xcopy']) echo ' checked';?>>
			</div>
			<div title="Frequency to check for an update.">
				<span class="separator">Delay/Frequency</span>
				<span class="title">Update</span>
					<select name="steam">
					<?php
						echo "<option value=\"30\"".($server[0]['delay']['steam']<31?' selected="selected"':'').">30 sec.</option>";
						echo "<option value=\"60\"".($server[0]['delay']['steam']>30 && $server[0]['delay']['steam']<=60?' selected="selected"':'').">60 sec.</option>";
						echo "<option value=\"120\"".($server[0]['delay']['steam']>60 && $server[0]['delay']['steam']<=120?' selected="selected"':'').">2 minutes</option>";
						echo "<option value=\"300\"".($server[0]['delay']['steam']>180 && $server[0]['delay']['steam']<=300?' selected="selected"':'').">5 minutes</option>";
						echo "<option value=\"600\"".($server[0]['delay']['steam']>300 && $server[0]['delay']['steam']<=600?' selected="selected"':'').">10 minutes</option>";
						echo "<option value=\"900\"".($server[0]['delay']['steam']>600 && $server[0]['delay']['steam']<=900?' selected="selected"':'').">15 minutes</option>";
						echo "<option value=\"1800\"".($server[0]['delay']['steam']>900 && $server[0]['delay']['steam']<=1800?' selected="selected"':'').">30 minutes</option>";
						echo "<option value=\"3600\"".($server[0]['delay']['steam']>1800?' selected="selected"':'').">1 hour</option>";
					?>
					</select>
			</div>
			<div title="Ping server frequency.">
				<span class="title">Ping</span>
					<select name="ping">
					<?php
						echo "<option value=\"30\"".($server[0]['delay']['ping']<31?' selected="selected"':'').">30 sec.</option>";
						echo "<option value=\"60\"".($server[0]['delay']['ping']>30 && $server[0]['delay']['ping']<=60?' selected="selected"':'').">60 sec.</option>";
						echo "<option value=\"120\"".($server[0]['delay']['ping']>60 && $server[0]['delay']['ping']<=120?' selected="selected"':'').">2 minutes</option>";
						echo "<option value=\"180\"".($server[0]['delay']['ping']>120 && $server[0]['delay']['ping']<=180?' selected="selected"':'').">3 minutes</option>";
						echo "<option value=\"240\"".($server[0]['delay']['ping']>180 && $server[0]['delay']['ping']<=240?' selected="selected"':'').">4 minutes</option>";
						echo "<option value=\"300\"".($server[0]['delay']['ping']>180 && $server[0]['delay']['ping']<=300?' selected="selected"':'').">5 minutes</option>";
						echo "<option value=\"600\"".($server[0]['delay']['ping']>300 && $server[0]['delay']['ping']<=600?' selected="selected"':'').">10 minutes</option>";
						echo "<option value=\"900\"".($server[0]['delay']['ping']>600 && $server[0]['delay']['ping']<=900?' selected="selected"':'').">15 minutes</option>";
						echo "<option value=\"1800\"".($server[0]['delay']['ping']>900?' selected="selected"':'').">30 minutes</option>";
					?>
					</select>
					<span title="Enable server to be ping at regular interval to monitor unresponsiveness and current state." >
						<input type="checkbox" name="pingable" value="1" <?php if ($server[0]['pingable'] == 'true') echo ' checked';?>>&nbsp;Pingable
					</span>
			</div>
			<div title="Delay monitor at start-up. Takes usually 2mn to be pingable.">
				<span class="title">Start</span>
					<select name="start">
					<?php
						echo "<option value=\"30\"".($server[0]['delay']['start']<31?' selected="selected"':'').">30 sec.</option>";
						echo "<option value=\"60\"".($server[0]['delay']['start']>30 && $server[0]['delay']['start']<=60?' selected="selected"':'').">60 sec.</option>";
						echo "<option value=\"120\"".($server[0]['delay']['start']>60 && $server[0]['delay']['start']<=120?' selected="selected"':'').">2 minutes</option>";
						echo "<option value=\"300\"".($server[0]['delay']['start']>180 && $server[0]['delay']['start']<=300?' selected="selected"':'').">5 minutes</option>";
						echo "<option value=\"600\"".($server[0]['delay']['start']>300 && $server[0]['delay']['start']<=600?' selected="selected"':'').">10 minutes</option>";
						echo "<option value=\"900\"".($server[0]['delay']['start']>600 && $server[0]['delay']['start']<=900?' selected="selected"':'').">15 minutes</option>";
						echo "<option value=\"1800\"".($server[0]['delay']['start']>900?' selected="selected"':'').">30 minutes</option>";
					?>
					</select>

			</div>
			<div title="Delay server restart after 3 crashes to preserve ressources">
				<span class="title">Failure</span>
					<select name="failure">
					<?php
						echo "<option value=\"60\"".($server[0]['delay']['failure']<=60 && $server[0]['delay']['failure'] > $server[0]['delay']['start']?' selected="selected"':'').">60 sec.</option>";
						echo "<option value=\"120\"".($server[0]['delay']['failure']>60 && $server[0]['delay']['failure']<=120 && $server[0]['delay']['failure'] > $server[0]['delay']['start']?' selected="selected"':'').">2 minutes</option>";
						echo "<option value=\"180\"".($server[0]['delay']['failure']>120 && $server[0]['delay']['failure']<=180 && $server[0]['delay']['failure'] > $server[0]['delay']['start']?' selected="selected"':'').">3 minutes</option>";
						echo "<option value=\"240\"".($server[0]['delay']['failure']>180 && $server[0]['delay']['failure']<=240 && $server[0]['delay']['failure'] > $server[0]['delay']['start']?' selected="selected"':'').">4 minutes</option>";
						echo "<option value=\"300\"".($server[0]['delay']['failure']>240 && $server[0]['delay']['failure']<=300 && $server[0]['delay']['failure'] > $server[0]['delay']['start']?' selected="selected"':'').">5 minutes</option>";
						echo "<option value=\"600\"".($server[0]['delay']['failure']>300 && $server[0]['delay']['failure']<=600 && $server[0]['delay']['failure'] > $server[0]['delay']['start']?' selected="selected"':'').">10 minutes</option>";
						echo "<option value=\"900\"".($server[0]['delay']['failure']>600 && $server[0]['delay']['failure']<=900 && $server[0]['delay']['failure'] > $server[0]['delay']['start']?' selected="selected"':'').">15 minutes</option>";
						echo "<option value=\"1800\"".($server[0]['delay']['failure']>900 && $server[0]['delay']['failure']<=1800 && $server[0]['delay']['failure'] > $server[0]['delay']['start']?' selected="selected"':'').">30 minutes</option>";
						echo "<option value=\"3600\"".($server[0]['delay']['failure']>1800?' selected="selected"':'').">60 minutes</option>";
					?>
					</select>
			</div>
			<div title="On update available, notify players the server will be updated shortly.">
				<span class="title">Quit</span>
					<select name="quit">
					<?php
						echo "<option value=\"30\"".($server[0]['delay']['quit']<=30?' selected="selected"':'').">30 sec.</option>";
						echo "<option value=\"60\"".($server[0]['delay']['quit']>30 && $server[0]['delay']['quit']<=60?' selected="selected"':'').">60 sec.</option>";
						echo "<option value=\"120\"".($server[0]['delay']['quit']>60 && $server[0]['delay']['quit']<=120?' selected="selected"':'').">2 minutes</option>";
						echo "<option value=\"180\"".($server[0]['delay']['quit']>120 && $server[0]['delay']['quit']<=180?' selected="selected"':'').">3 minutes</option>";
						echo "<option value=\"240\"".($server[0]['delay']['quit']>180 && $server[0]['delay']['quit']<=240?' selected="selected"':'').">4 minutes</option>";
						echo "<option value=\"300\"".($server[0]['delay']['quit']>180 && $server[0]['delay']['quit']<=300?' selected="selected"':'').">5 minutes</option>";
						echo "<option value=\"600\"".($server[0]['delay']['quit']>300 && $server[0]['delay']['quit']<=600?' selected="selected"':'').">10 minutes</option>";
						echo "<option value=\"900\"".($server[0]['delay']['quit']>600 && $server[0]['delay']['quit']<=900?' selected="selected"':'').">15 minutes</option>";
						echo "<option value=\"1800\"".($server[0]['delay']['quit']>900?' selected="selected"':'').">30 minutes</option>";
					?>
					</select>
			</div>
			<div title="Send RCON say frequency during update available.">
				<span class="title">RCON 'SAY'</span>
					<select name="say">
					<?php
						echo "<option value=\"15\"".($server[0]['delay']['say']<=15?' selected="selected"':'').">15 sec.</option>";
						echo "<option value=\"20\"".($server[0]['delay']['say']>15 && $server[0]['delay']['say']<=20?' selected="selected"':'').">20 sec.</option>";
						echo "<option value=\"25\"".($server[0]['delay']['say']>20 && $server[0]['delay']['say']<=25?' selected="selected"':'').">25 sec.</option>";
						echo "<option value=\"30\"".($server[0]['delay']['say']>25 && $server[0]['delay']['say']<=30?' selected="selected"':'').">30 sec.</option>";
						echo "<option value=\"45\"".($server[0]['delay']['say']>30 && $server[0]['delay']['say']<=45?' selected="selected"':'').">45 sec.</option>";
						echo "<option value=\"60\"".($server[0]['delay']['say']>45 && $server[0]['delay']['say']<=60?' selected="selected"':'').">60 sec.</option>";
						echo "<option value=\"120\"".($server[0]['delay']['say']>60 && $server[0]['delay']['say']<=120?' selected="selected"':'').">2 minutes</option>";
						echo "<option value=\"300\"".($server[0]['delay']['say']>120?' selected="selected"':'').">5 minutes</option>";
					?>
					</select>
			</div>
            <span class="separator">Tasks</span>
			<div title="Sendmail to Email(s) address(es) (separate with , or ;). Require SMTP server; otherwise leave empty.">
				<span class="title">Email to</span><input type="text" name="sendmail_to" value="<?php echo $server[0]['email']['sendmail_to'];?>">
			</div>
			<div title="Sendmail from">
				<span class="title">From</span><input type="text" name="sendmail_from" value="<?php echo $server[0]['email']['sendmail_from'];?>">
			</div>
			<div title="SMTP server address. Default is localhost.">
				<span class="title">SMTP Host</span><input type="text" name="smtp" placeholder="localhost" value="<?php echo $server[0]['email']['smtp'];?>">
			</div>
			<div title="SMTP port. Default is 25, no ssl">
				<span class="title">SMTP port</span><input class="small" type="text" name="smtp_port" placeholder="25" value="<?php echo $server[0]['email']['smtp_port'];?>">
				<input type="checkbox" name="smtp_ssl" value="1" <?php if ($server[0]['email']['smtp_ssl']=='true') echo ' checked';?>> SSL
			</div>
			<div title="Optional Username for SMTP Authentication">
				<span class="title">SMTP username</span><input type="text" name="auth_username" value="<?php echo trim($server[0]['email']['auth_username']);?> " autocomplete="off">
			</div>
			<div title="Optional Password for SMTP Authentication">
				<span class="title">SMTP password</span><input type="password" name="auth_password" value="<?php echo $server[0]['email']['auth_password'];?>" autocomplete="off">
			</div>
			<div title="Send a test message">
				<span class="title">Send a test message</span><span id="sendmail" class="title btn email"><i class="fa fa-envelope fa-lg"></i> Email Server Test</span>
			</div>

			<div title="Send email on defined events">
				 <span class="title">Email Alert</span><span class="dir" name="alert" ><?php echo $server[0]['email']['alert'];?></span><span class="fa fa-check-square-o msg_alert" title="Select Alerts"></span>
			</div>

			<div title="Automatically delete log files after x days">
				<span class="title">Clean Log</span>
					<select name="cleanlog">
					<?php
						echo "<option value=\"0\"".($server[0]['cleanlog']<=0?' selected="selected"':'').">Never</option>";
						echo "<option value=\"1\"".($server[0]['cleanlog']==1 && $server[0]['cleanlog']<7?' selected="selected"':'').">Daily</option>";
						echo "<option value=\"7\"".($server[0]['cleanlog']==7 && $server[0]['cleanlog']<28?' selected="selected"':'').">Weekly</option>";
						echo "<option value=\"28\"".($server[0]['cleanlog']==28 && $server[0]['cleanlog']<365?' selected="selected"':'').">Monthly</option>";
						echo "<option value=\"365\"".($server[0]['cleanlog']>=365?' selected="selected"':'').">Yearly</option>";
					?>
					</select>
			</div>
			<div title="Schedule a daily restart. Auto will reboot anytime after 24h when empty.">
				<span class="title" >Daily Restart</span>
					<select name="restart">
					<?php
						echo '<option value="never"'.($server[0]['restart']=="never"?' selected="selected"':'').'">NEVER</option>';
						echo '<option value="auto"'.($server[0]['restart']=='auto'?' selected="selected"':'').'">AUTO</option>';
						echo '<option value="00"'.($server[0]['restart']=='00'?' selected="selected"':'').'">12:00 AM</option>';
						for ($ii = 1 ;$ii<12; $ii++) {
							echo '<option value="'.sprintf("%02d", $ii).'"'.($server[0]['restart']==$ii?' selected="selected"':'').'>'.sprintf("%02d", $ii).':00 AM</option>';
						}
						echo '<option value="12"'.($server[0]['restart']==12?' selected="selected"':'').'">12:00 PM</option>';
						for ($ii = 1 ;$ii<12; $ii++) {
							echo '<option value="'.($ii+12).'"'.($server[0]['restart']==($ii+12)?' selected="selected"':'').'>'.sprintf("%02d", $ii).':00 PM</option>';
						}
					?>
					</select>
					<span title="Postpone (up to 24h) until the server is empty" >
						<input type="checkbox" name="empty" value="1" <?php if ($server[0]['empty']) echo ' checked';?>>&nbsp;Postpone if not empty
					</span>
			</div>
	<br clear="all"/>
	</div>

	<?php for ($i=1; $i<$total; $i++) {
			echo '<div id="server-'.$i.'" class="server-sz setup">';
	?>
			<div title="Srcds Game">
				<span class="title">Game</span>
					<select name="appid">
						<option value="none" <?php if ($server[$i]['pid']) echo ' disabled="disabled"';?>>--Select a game--</option>
						<?php
						foreach ($games as $game=>$k) {
							echo "<option ".($games[$game]['login']?'class="login" ':'')."value=\"".$game."\"".($server[$i]['appid']==$game?' selected="selected"':($server[$i]['pid']?' disabled="disabled"':'')).">".$games[$game]['name']." [".$game."]</option>";
						}
					?>
					</select>
                <?php if ($server[0]['steamcmddir'] && $server[$i]['installdir']) {
                          echo '<span class="fa fa-folder-open-o opendir" title="Open directory"></span>'.
                               '<span class="fa fa-wrench editcfg" title="Edit server config"></span>';
                                if (!$server[$i]['pid']) echo '<span class="update fa fa-steam" title="Update" ></span>';
                      } ?>
			</div>
            <div title="Friendly Name to replace Server id">
                <span class="title">Name</span><input type="text" name="fname" class="fname" value="<?php echo $server[$i]['fname'];?>">
            </div>
			<div title="Path to the root server directory, where srcds.exe resides.">
				<span class="title">Install Dir</span><span class="dir path" name="installdir"><?php echo $server[$i]['installdir'];?></span>
				<?php if (!$server[$i]['pid']) echo '<span class="fa fa-search browse" title="Select Root Directory"></span>';?>
			</div>
			<div title="Server Executable(e.g. srcds.exe)">
				<span class="title">SRCDS.exe</span><div style="position:relative;display:inline-block;"><input type="text" name="srcds" value="<?php echo $server[$i]['srcds'];?>" <?php if ($server[$i]['pid']) echo 'readonly'?>>
				<div class="suggest-server suggestions" style="position:absolute;z-index:999">
					<span>srcds.exe</span>
					<span>srcds_win64.exe</span>
				</div></div>
			</div>
			<div title="Server IP">
				<span class="title">IP</span><div style="position:relative;display:inline-block;"><input type="text" name="ip" value="<?php echo $server[$i]['ip'];?>"<?php if ($server[$i]['pid']) echo " readonly";?>>
					<?php
							if ( sizeof($ip) ) {
								echo '<div class="suggest-ip suggestions" style="position:absolute;z-index:999;">';
								for ($ii=0; $ii<sizeof($ip); $ii++) {
									echo '<span>'.$ip[$ii].'</span>';
								}
								echo '</div>';
							}
					?>
					</div>
			</div>
			<div title="Server Port">
				<span class="title">Port</span><div style="position:relative;display:inline-block;"><input type="text" name="port" value="<?php echo $server[$i]['port'];?>"<?php if ($server[$i]['pid']) echo " readonly";?>>
					<div class="suggest-port suggestions" style="position:absolute;z-index:999">
					<?php
					for ($ii=$i; $ii<37; $ii++) {
						echo '<span>'.(27014+$ii).'</span>';
					}
					?>
					</div></div>
			</div>
			<div title="Optionnal <username> <password>, leave empty for 'anonymous'." class="steamlogin">
				<span class="title">Login</span><input type="text" name="login" value="<?php echo $server[$i]['login'];?>">
			</div>
			<div title="Srcds Process Priority.">
				<span class="title">Srcds Priority</span>
					<select name="priority">
					<?php
						echo "<option value=\"256\"".($server[$i]['priority']==256?' selected="selected"':'').">Real time</option>";
						echo "<option value=\"128\"".($server[$i]['priority']==128?' selected="selected"':'').">High priority</option>";
						echo "<option value=\"32768\"".($server[$i]['priority']==32768?' selected="selected"':'').">Above normal</option>";
						echo "<option value=\"32\"".($server[$i]['priority']==32?' selected="selected"':'').">Normal</option>";
						echo "<option value=\"16384\"".($server[$i]['priority']==16384?' selected="selected"':'').">Below Normal</option>";
						echo "<option value=\"64\"".($server[$i]['priority']==64?' selected="selected"':'').">Idle</option>";
					?>
					</select>
			</div>
			<div title="Initial map">
				<span class="title">Map</span><div style="position:relative;display:inline-block;"><input type="text" name="map" value="<?php echo $server[$i]['map'];?>"><div class="suggest-map suggestions" style="position:absolute;z-index:999"></div></div>
			</div>
			<div title="Max players">
				<span class="title">Max Players</span><div style="position:relative;display:inline-block;"><input type="text" name="maxplayers" value="<?php echo $server[$i]['maxplayers'];?>">
					<div class="suggest-players suggestions" style="position:absolute;z-index:999">
					<?php
					for ($ii=2; $ii<65; $ii+=2) {
						echo '<span>'.$ii.'</span>';
					}
					?>
					</div></div>
			</div>
			<div title="Start parameters for srcds.exe (e.g. -nohltv -maxplayers_override 32 +sv_lan 1 +map de_dust2)">
				<span class="title">Additionnal Parameters:</span><br/><textarea rows="2" cols="50" name="cmd"><?php echo $server[$i]['cmd'];?></textarea>
			</div>
			<div title="The server will be stopped only with a plugin on update available">
				<span class="title">Plugin</span><input type="checkbox" name="plugin" value="1" <?php if ($server[$i]['plugin']) echo ' checked';?>>
			</div>
			<div title="Ping server at regular interval to monitor unresponsiveness.">
				<span class="title">Pingable</span><input type="checkbox" name="pingable" value="1" <?php if ($server[$i]['pingable']=='true') echo ' checked';?>>
			</div>
			<div title="RCON say on update available. Sourcemod: sm_hsay, sm_tsay, sm_csay... Default is 'say'(no plugins), best is 'sm_hsay' ">
				<span class="title">RCON SAY</span><input type="text" name="rcon_say" value="<?php echo $server[$i]['rcon_say'];?>">
			</div>
			<div title="Update this server from the cache folder for a quicker update.">
				<span class="title">XCOPY</span><input type="checkbox" name="xcopy" value="1" <?php if ($server[$i]['xcopy']) {echo ' checked';}?>>
			</div>
			<div title="Flag file, an update is available. Use for plugin and at server start-up.">
				<span class="title">Update file name</span><input type="text" name="updatefile" value="<?php echo $server[$i]['updatefile']; ?>">
			</div>
	<?php
			echo '<div style="clear:both"></div></div>';
        }
    ?>
</div>
<div style="clear:both"></div>
<div id="Dialog_Drive" title="Explorer">
    <div id="hdd"></div>
    <div class="wrapper"><div class="validateTips"></div></div>
    <form>
<!-- Allow form submission with keyboard without duplicating the dialog button -->
             <input type="submit" tabindex="-1" style="position:absolute; top:-1000px">

    </form>
</div>

 <script>
//----------------
//----------------
//----------------

//--------------------------
//         TABS
//--------------------------
    if (!$('#server-1 [name="installdir"]').html()) {
        $('div#server ul li:eq(1)').hide();
        Sz.tab.id=0;
    }
    $("#add-server").click(function() {

        Sz.tab.id = $("div#server ul li").length;
        if ( Sz.tab.id ==2 && !$('div#server ul li:eq(1)').is(':visible') ) {
            $('#server-1 select[name="appid"] option:selected').prop("selected", false);
            $('div#server ul li:eq(1)').show();
            $("#server").tabs({ active: 1 });
            return false;
        }
        $("div#server ul").append(
            '<li><a href="#server-' + Sz.tab.id + '"><span class="fa fa-close server-delete" title="Delete Server"></span><span>Server ' + Sz.tab.id + '</span></a></li>'
        );
        $("div#server").append(
            '<div id="server-' + (Sz.tab.id) + '" class="server-sz setup">' + $('#server-1').html() + '</div>'
        );
        if ( !($( '#server-'+(Sz.tab.id)+' .browse' ).length > 0) ) {
            $( '#server-'+(Sz.tab.id)+' [name="installdir"]' ).after( '<span class="fa fa-search browse" title="Select Root Directory"></span>' );
        }
        $("div#server").tabs("refresh");
        $('#server-' + Sz.tab.id).find("input[type=text], textarea").val("");
        $('#server-' + Sz.tab.id).find("input").attr("readonly", false);
        $('#server-' + Sz.tab.id +' .update').remove();
        $('#server-' + Sz.tab.id +' .editcfg').remove();
        $('#server-' + Sz.tab.id +' .opendir').remove();
        $('.path:eq('+Sz.tab.id+')').text('');
        $('#server-' + Sz.tab.id +' .suggest-map').text('').removeClass('hovered').hide();
        $('#server-' + Sz.tab.id +' .suggest-ip').removeClass('hovered').hide();
        $('#server-' + Sz.tab.id +' .suggest-port').removeClass('hovered').hide();
        $('#server-' + Sz.tab.id +' .suggest-players').removeClass('hovered').hide();
        $('#server-' + Sz.tab.id +' .suggest-server').removeClass('hovered').hide();
        $('#server-' + Sz.tab.id +' option:disabled').removeAttr('disabled');
        $("#server").tabs({ active: Sz.tab.id });
        //Refresh Tabs:
        reTabs();

    }); 

    function Delete(id_tabs) {

        var num_tabs = $("div#server ul li").length;
        $('#server-' + id_tabs).remove();

        var hrefStr = "a[href='#server-" + id_tabs + "']";
        $( hrefStr ).closest("li").remove();

        for (var i=1; i<num_tabs; i++) {

            var fname = $('#svrlist a:eq(' + i +') span:eq(1)').html();
            if (/^Server\s\d+$/.test(fname)) {
                $('#svrlist a:eq(' + i +') span:eq(1)').html('Server ' + i);
            }
            $('#svrlist a:eq(' + i +')').attr('href','#server-'+i);
            $('.setup:eq('+i+')').attr('id','server-'+i);

        }

        $("#server").tabs("destroy");
        $( "#server" ).tabs({ activate: function( event, ui ) { Sz.tab.id = ui.newTab.index(); } }).addClass( "ui-tabs-vertical ui-helper-clearfix" );
        $("#server").tabs({active: 0});
        Sz.tab.id = 0;
         
        //Refresh Tabs:
        reTabs();
        //Aussie effect:
        $('#server li:gt(0)').stop(true,true).effect('highlight', {color:'#fc3d32'},500);

    } 

    function reTabs() {

        //delete server:
        $(".server-delete").unbind('click');
        $(".server-delete").click(function() {
            var num_tabs = $("div#server ul li").length-1;
            var id_tabs  = $(this).closest('li').index();
            if (num_tabs<2) {
                $('div#server ul li:eq(1)').hide();
                $('#server-1').hide();
                $("#server").tabs({ active: 0 });
                $('#server-1').find("input[type=text], textarea").val("");
                $('#server-1 .update').remove();
                $('#server-1 .editcfg').remove();
                $('#server-1 .opendir').remove();
                $('.path:eq(1)').text('');
                $('#server-1 .suggest-map').text('').removeClass('hovered').hide();
                return false;
            }
            Delete(id_tabs);
        });

        //browse folder:
        $( ".browse" ).unbind('click');
        $( ".browse" ).click(function() {
            drive.dialog( "open" );
        });

        //Update srcds:
        $(".update").unbind('click');
        $( ".update" ).click( function() {
        
            Sz.Ajax('php/setup.php',{fct:'update', par: Sz.tab.id, token: Sz.token},'#saved');
        
        })

        //IP available:
        $('[name="ip"]').each(function(i,v){

            if ( ($( '#server-'+(i+1)+' .browse' ).length > 0) ) {

                $(this).focus(function() {
                    $('.suggest-ip:eq('+i+')').slideDown(250);
                });
                
                $(this).focusout(function(){
                    if(!$('.suggest-ip:eq('+i+')').hasClass('hovered'))
                    $('.suggest-ip:eq('+i+')').slideUp(250,'swing');
                });
                
                $('.suggest-ip:eq('+i+')').hover(function(){
                    $(this).addClass('hovered');
                },function(){ $(this).removeClass('hovered');});
                
                $('.suggest-ip:eq('+i+') span').click(function(){
                    $('[name="ip"]:eq('+i+')').val($(this).text());
                    $('.suggest-ip:eq('+i+')').slideUp(250,'swing');
                });

            }

        });

        //server suggestion:
        $('[name="srcds"]').each(function(i,v){

            if ( ($( '#server-'+(i+1)+' .browse' ).length > 0) ) {
        
                $(this).focus(function() {
                    $('.suggest-server:eq('+i+')').slideDown(250);
                });
                
                $(this).focusout(function(){
                    if(!$('.suggest-server:eq('+i+')').hasClass('hovered'))
                    $('.suggest-server:eq('+i+')').slideUp(250,'swing');
                });
                
                $('.suggest-server:eq('+i+')').hover(function(){
                    $(this).addClass('hovered');
                },function(){ $(this).removeClass('hovered');});
                
                $('.suggest-server:eq('+i+') span').click(function(){
                    $('[name="srcds"]:eq('+i+')').val($(this).text());
                    $('.suggest-server:eq('+i+')').slideUp(250,'swing');
                });

            }

        });

        //Port available:
        $('[name="port"]').each(function(i,v){

            if ( ($( '#server-'+(i+1)+' .browse' ).length > 0) ) {
        
                $(this).focus(function() {
                    $('.suggest-port:eq('+i+')').slideDown(250);
                });
                
                $(this).focusout(function(){
                    if(!$('.suggest-port:eq('+i+')').hasClass('hovered'))
                    $('.suggest-port:eq('+i+')').slideUp(250,'swing');
                });
                
                $('.suggest-port:eq('+i+')').hover(function(){
                    $(this).addClass('hovered');
                },function(){ $(this).removeClass('hovered');});
                
                $('.suggest-port:eq('+i+') span').click(function(){
                    $('[name="port"]:eq('+i+')').val($(this).text());
                    $('.suggest-port:eq('+i+')').slideUp(250,'swing');
                });

            }

        });

        //MAP available:
        $('[name="map"]').each(function(i,v){

            //if ( ($( '#server-'+(i+1)+' .browse' ).length > 0) ) {

                $(this).focus(function() {
                    if (!$('.suggest-map:eq('+(Sz.tab.id-1)+')').html()) {
                        $.ajax({
                            type: "POST",
                            url: 'php/mapcycle.php?'+(new Date()).getTime(),
                            data: {token: Sz.token, dir: $('#server-'+Sz.tab.id+' [name="installdir"]').html() ,app: $('#server-'+Sz.tab.id+' [name="appid"] option:selected').val() },
                            cache: false,
                            async: true
                        }).done(function(data) {
                            $('.suggest-map:eq('+(Sz.tab.id-1)+')').html(data);
                            if (data.trim()) {
                                var id = Sz.tab.id-1;
                                $('.suggest-map:eq('+(id)+') span').click(function(){
                                    $('[name="map"]:eq('+id+')').val($(this).text());
                                    $('.suggest-map:eq('+id+')').slideUp(250,'swing');
                                });
                                $('.suggest-map:eq('+id+')').slideDown(250);
                            }
                        });
                    } else {
                        $('.suggest-map:eq('+(Sz.tab.id-1)+')').slideDown(250);
                    }
                });
                
                $(this).focusout(function(){
                    if(!$('.suggest-map:eq('+(Sz.tab.id-1)+')').hasClass('hovered'))
                    $('.suggest-map:eq('+(Sz.tab.id-1)+')').slideUp(250,'swing');
                });
                
                $('.suggest-map:eq('+(Sz.tab.id-1)+')').hover(function(){
                    $(this).addClass('hovered');
                },function(){ $(this).removeClass('hovered');});

            //}

        });

        //Players available:
        $('[name="maxplayers"]').each(function(i,v){

            if ( ($( '#server-'+(i+1)+' .browse' ).length > 0) ) {
        
                $(this).focus(function() {
                    $('.suggest-players:eq('+i+')').slideDown(250);
                });
                
                $(this).focusout(function(){
                    if(!$('.suggest-players:eq('+i+')').hasClass('hovered'))
                    $('.suggest-players:eq('+i+')').slideUp(250,'swing');
                });
                
                $('.suggest-players:eq('+i+')').hover(function(){
                    $(this).addClass('hovered');
                },function(){ $(this).removeClass('hovered');});
                
                $('.suggest-players:eq('+i+') span').click(function(){
                    $('[name="maxplayers"]:eq('+i+')').val($(this).text());
                    $('.suggest-players:eq('+i+')').slideUp(250,'swing');
                });

            }

        });

        //Login
        $('[name="appid"]').change(function() {
            if ($(this).find('option:selected').hasClass('login')) {
                $('.steamlogin:eq('+(Sz.tab.id-1)+')').show();
            } else {
                $('.steamlogin:eq('+(Sz.tab.id-1)+')').hide();
            }
            $('#server-'+(Sz.tab.id)+' .suggest-map').text('').removeClass('hovered').hide();
        });
        var num_appid = $('[name="appid"]').length;
        for (var i=0;i<num_appid;i++){
            if ($('[name="appid"]:eq('+i+')').find('option:selected').hasClass('login')) {
                $('.steamlogin:eq('+(i)+')').show();
            } else {
                $('.steamlogin:eq('+(i)+')').hide();
            }
        }

        //Resize panel:
        if ($('#server ul').outerHeight() > parseInt($('#server.ui-tabs.ui-tabs-vertical .ui-tabs-panel').css('min-height')) ) {
            $('#server.ui-tabs.ui-tabs-vertical .ui-tabs-panel').outerHeight($('#server ul').outerHeight());
        } else {
            $('#server.ui-tabs.ui-tabs-vertical .ui-tabs-panel').outerHeight('auto');
        }

    }

    //Create Tabs
    $( "#server" ).tabs({ activate: function( event, ui ) { Sz.tab.id = ui.newTab.index(); } }).addClass( "ui-tabs-vertical ui-helper-clearfix" );
    $( "#server li" ).removeClass( "ui-corner-top" ).addClass( "ui-corner-left" );
    $('#server').tabs({active: Sz.tab.id});
    Sz.Response(Sz.tab.txt,1);Sz.tab.txt='';
    $('.suggest-ip').outerWidth($('[name="ip"]:eq(0)').outerWidth()+0);
    $('.suggest-port').outerWidth($('[name="port"]:eq(0)').outerWidth()+0);
    $('.suggest-map').outerWidth($('[name="map"]:eq(0)').outerWidth()+0);
    $('.suggest-players').outerWidth($('[name="maxplayers"]:eq(0)').outerWidth()+0);
    $('.suggest-server').outerWidth($('[name="srcds"]:eq(0)').outerWidth()+0);
    reTabs();

//--------------------------
//     Restart option
//--------------------------
$("[name=restart]").change(function() {
    if ($(this).val() == 'never' || $(this).val() == 'auto') {
        $("[name=empty]").parent().hide();
    } else {
        $("[name=empty]").parent().show();
    }
});
if ($("[name=restart]").val() == 'never' || $("[name=restart]").val() == 'auto') {
    $("[name=empty]").parent().hide();
} else {
    $("[name=empty]").parent().show();
}
//--------------------------
//     Default options
//--------------------------
    function Default(hide) {
        //updatefile, delay[ping,start,failure,quit,say], priority,cleanlog
        //priority,xcopy,updatefile
        if (hide) {
            $('#server-0 [name="updatefile"]').closest('div').hide();
            $('#server-0 [name="steam"]').closest('div').hide();
            $('#server-0 [name="ping"]').closest('div').hide();
            $('#server-0 [name="start"]').closest('div').hide();
            $('#server-0 [name="failure"]').closest('div').hide();
            $('#server-0 [name="quit"]').closest('div').hide();
            $('#server-0 [name="say"]').closest('div').hide();
            $('#server-0 [name="priority"]').closest('div').hide();
            $('#server-0 [name="cleanlog"]').closest('div').hide();
            $('#server-0 [name="restart"]').closest('div').hide();
            $('#server-0 [name="empty"]').closest('div').hide();
            $('#server-0 [name="pingable"]').closest('div').hide();
        } else {
            $('#server-0 [name="updatefile"]').closest('div').show();
            $('#server-0 [name="steam"]').closest('div').show();
            $('#server-0 [name="ping"]').closest('div').show();
            $('#server-0 [name="start"]').closest('div').show();
            $('#server-0 [name="failure"]').closest('div').show();
            $('#server-0 [name="quit"]').closest('div').show();
            $('#server-0 [name="say"]').closest('div').show();
            $('#server-0 [name="priority"]').closest('div').show();
            $('#server-0 [name="cleanlog"]').closest('div').show();
            $('#server-0 [name="restart"]').closest('div').show();
            $('#server-0 [name="empty"]').closest('div').show();
            $('#server-0 [name="pingable"]').closest('div').show();
        }
    }
    $('[name="default"]').change(function() {
        Default($(this).is(':checked'));
    });
    Default($('[name="default"]').is(':checked'));

//--------------------------
//   Common options
//--------------------------
    function Global(m) {
        var num_tabs = $("div#server ul li").length+1;
        //priority,xcopy,updatefile,pingable
        for (var i=1;i<num_tabs;i++) {
            if (m) {
                $('#server-'+i+' [name="priority"]').closest('div').hide();
                $('#server-'+i+' [name="xcopy"]').closest('div').hide();
                $('#server-'+i+' [name="plugin"]').closest('div').hide();
                $('#server-'+i+' [name="updatefile"]').closest('div').hide();
                $('#server-'+i+' [name="pingable"]').closest('div').hide();
            } else {
                $('#server-'+i+' [name="priority"]').closest('div').show();
                $('#server-'+i+' [name="xcopy"]').closest('div').show();
                $('#server-'+i+' [name="plugin"]').closest('div').show();
                $('#server-'+i+' [name="updatefile"]').closest('div').show();
                $('#server-'+i+' [name="pingable"]').closest('div').show();
           }
        }
    }
    $('[name="global"]').change(function() {
        Global($(this).is(':checked'));
    });
    Global($('[name="global"]').is(':checked'));

//--------------------------
//   Dialog: Browse
//--------------------------
    var drive,
    tips = $( ".validateTips" );
    function updateTips( t ) {
        tips
        .text( t )
        .addClass( "ui-state-highlight" );
        setTimeout(function() {
            tips.removeClass( "ui-state-highlight", 250 ).text('');
        }, 5000 );
    }

    function Explorer() {
        $('.ui-dialog-title').text('Explorer');
        $.ajax({
            type: 'POST',
            url: 'php/drives.php?'+(new Date()).getTime(),
            data: {steam: Sz.tab.id, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            $('#hdd').show().html(data);
            $('#version').removeClass('load');
        });
    } 
  
    function ValidPath() {
        str = $('.ui-dialog-title').html();
        if (Sz.tab.id == 0) {
            if (!str.match(/steamcmd.exe$/)) {
                updateTips( 'Require steamcmd.exe' );
                return false;
            }
            $('.path:eq('+Sz.tab.id+')').text(str);
            if (!$('div#server ul li:eq(1)').is(':visible') ) {
                var saveCFG=getConfig();
                Sz.Ajax('php/config.xml.php',{s:JSON.stringify(saveCFG), token: Sz.token});
            }
        } else {
            if (!str || !str.match(/:\\/) ) {
                updateTips( 'Select a folder' );
                return false;
            }
            $('.path:eq('+Sz.tab.id+')').text(str);
        }
        drive.dialog( "close" );

    } 
  
    drive = $( "#Dialog_Drive" ).dialog({
        autoOpen: false,
        height: 450,
        width: 450,
        modal: true,
        buttons: {
            "Submit": ValidPath,
            Cancel: function() {
               drive.dialog( "close" );
            }
        },
        open: function() {Explorer(this)}
    });

//--------------------------
//   Dialog: MSG
//--------------------------

    $( ".msg_alert" ).click(function() {
        $('.ui-dialog-title').text('Email Alert');
        if ( $('#version').hasClass('load') ) return false;
        $('#version').addClass('load');
        var content = '<div id="msg"></div>';
        Sz.popup.open({header:'Email Alert',content:content,css:''},'',GetMSG);
        $.ajax({
            type: 'POST',
            url: 'php/msg.php?'+(new Date()).getTime(),
            data: {a:$('[name="alert"]').html(), token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            $('#msg').html(data);
            $('#version').removeClass('load');
        });
    });

    function GetMSG(a) {
        var str='';
        $( "#msg input[type=checkbox]" ).each(function(index,elem) {
            if ($(elem).prop('checked')){
                str+=$(elem).val()+',';
            }
        });
        //if (str)
        $('[name="alert"]').text(str.slice(0, -1));
    }

//--------------------------
//   Save Config
//--------------------------
     function getConfig() {
        jsonObj = [];
            Cfg = {};
            Cfg ["steamcmd"] = {};
            Cfg ["steamcmd"]["steamcmddir"] = $('#server-0 [name="steamcmddir"]').html();
            if ($('#server-0 [name="xcopy"]').prop('checked'))
                Cfg ["steamcmd"]["xcopy"]       = true;
            if ($('#server-0 [name="plugin"]').prop('checked'))
                Cfg ["steamcmd"]["plugin"]      = true;
            if (!($('[name="default"]').is(':checked')))
                Cfg ["steamcmd"]["updatefile"]  = $('#server-0 [name="updatefile"]').val();
            if (!($('[name="default"]').is(':checked'))) {
                Cfg ["steamcmd"]["priority"]    = $('#server-0 [name="priority"] option:selected').val();
                Cfg ["steamcmd"]["cleanlog"]    = $('#server-0 [name="cleanlog"] option:selected').val();
                Cfg ["steamcmd"]["restart"]     = $('#server-0 [name="restart"] option:selected').val();
                Cfg ["steamcmd"]["empty"]       = $('#server-0 [name="empty"]').prop('checked');
                Cfg ["steamcmd"]["pingable"]    = $('#server-0 [name="pingable"]').prop('checked');
                Cfg ["steamcmd"]["delay"]       = {
                                                    steam:$('#server-0 [name="steam"] option:selected').val(),
                                                    ping:$('#server-0 [name="ping"] option:selected').val(),
                                                    start:$('#server-0 [name="start"] option:selected').val(),
                                                    failure:$('#server-0 [name="failure"] option:selected').val(),
                                                    quit:$('#server-0 [name="quit"] option:selected').val(),
                                                    say:$('#server-0 [name="say"] option:selected').val()
                                                   }; 
            }
            Cfg ["steamcmd"]["email"] = {};
            if ($('#server-0 [name="smtp"]').val().trim())
                Cfg ["steamcmd"]["email"]["smtp"] = $('#server-0 [name="smtp"]').val().trim();
            if ($('#server-0 [name="smtp_port"]').val().trim())
                Cfg ["steamcmd"]["email"]["smtp_port"] = $('#server-0 [name="smtp_port"]').val().trim();
            if ($('#server-0 [name="smtp_ssl"]').prop('checked'))
                Cfg ["steamcmd"]["email"]["smtp_ssl"] = true;
            if ($('#server-0 [name="auth_username"]').val().trim())
                Cfg ["steamcmd"]["email"]["auth_username"] = $('#server-0 [name="auth_username"]').val().trim();
            if ($('#server-0 [name="auth_password"]').val().trim())
                Cfg ["steamcmd"]["email"]["auth_password"] = $('#server-0 [name="auth_password"]').val().trim();
            if ($('#server-0 [name="sendmail_to"]').val().trim())
                Cfg ["steamcmd"]["email"]["sendmail_to"] = $('#server-0 [name="sendmail_to"]').val().trim();
            if ($('#server-0 [name="sendmail_from"]').val().trim())
                Cfg ["steamcmd"]["email"]["sendmail_from"] = $('#server-0 [name="sendmail_from"]').val().trim();
            if ($('#server-0 [name="alert"]').html().trim())
                Cfg ["steamcmd"]["email"]["alert"] = $('#server-0 [name="alert"]').html().trim();
            if ($('div#server ul li:eq(1)').is(':visible') ) {
                Cfg ["server"]={};
                $('.server-sz').each(function(i, obj) {
                    var y=i+1;

                    Cfg ["server"][i] = {
                                        '@attributes': {appid: $('#server-'+y+' [name="appid"] option:selected').val()},
                                        installdir: $('#server-'+y+' [name="installdir"]').html(),
										srcds: $('#server-'+y+' [name="srcds"]').val().trim(),
                                        ip: $('#server-'+y+' [name="ip"]').val(),
                                        port: $('#server-'+y+' [name="port"]').val()
                                        };
                    if ($('#server-'+y+' [name="fname"]').val().trim() )
                    Cfg ["server"][i]['fname'] = $('#server-'+y+' [name="fname"]').val();
                    if ($('#server-'+y+' [name="login"]').val().trim() && $('#server-'+y+' [name="login"]').is(":visible") )
                    Cfg ["server"][i]['login'] = $('#server-'+y+' [name="login"]').val();
                    if (!($('[name="global"]').is(':checked')))
                        Cfg ["server"][i]['priority'] = $('#server-'+y+' [name="priority"] option:selected').val();
                    if ($('#server-'+y+' [name="map"]').val().trim())
                    Cfg ["server"][i]['map'] = $('#server-'+y+' [name="map"]').val();
                    if ($('#server-'+y+' [name="maxplayers"]').val().trim())
                    Cfg ["server"][i]['maxplayers'] = $('#server-'+y+' [name="maxplayers"]').val();
                    if ($('#server-'+y+' [name="cmd"]').val().replace(/\r?\n/g, " ").trim())
                    Cfg ["server"][i]['cmd'] = $('#server-'+y+' [name="cmd"]').val().replace(/\r?\n/g, " ");
                    if (!($('[name="global"]').is(':checked')))
                        Cfg ["server"][i]['pingable'] = $('#server-'+y+' [name="pingable"]').prop('checked');
                    if ($('#server-'+y+' [name="rcon_say"]').val().trim())
                    Cfg ["server"][i]['rcon_say'] = $('#server-'+y+' [name="rcon_say"]').val().trim();
                    if (!($('[name="global"]').is(':checked'))) {
                        Cfg ["server"][i]['xcopy'] = $('#server-'+y+' [name="xcopy"]').prop('checked');
                        Cfg ["server"][i]['plugin'] = $('#server-'+y+' [name="plugin"]').prop('checked');
                        if ($('#server-'+y+' [name="updatefile"]').val().trim())
                            Cfg ["server"][i]['updatefile'] = $('#server-'+y+' [name="updatefile"]').val().trim();
                    }

                });
            }
            jsonObj.push(Cfg);
            return jsonObj;

    }
   $( "#save-server" ).click( function() {
        var saveCFG = getConfig();
        Sz.Ajax('php/config.xml.php',{s:JSON.stringify(saveCFG), token: Sz.token},'#saved',2);
   });


//-------------------
// Browse Dir & Edit
//-------------------
    $('.opendir').click(function() {

        Sz.Ajax('php/explorer.php',{type: 'folder', id: Sz.tab.id, token: Sz.token},'#saved');

    });
    $('.editcfg').click(function() {

        Sz.Ajax('php/explorer.php',{type: 'cfg', id: Sz.tab.id, token: Sz.token},'#saved');

    });
    $('#log-server').click(function() {

        if ( $('#version').hasClass('load') ) return false;
        $('#version').addClass('load');
        var id = 0;
        var content = '<div id="view-logs"></logs>';
        Sz.popup.open({header:'View Logs',content:content,css:'large'},{'id':id,'file':''},vLogs1,1);
        vLogs1({id:id,file:''});
    });
    function vLogs1(p) {
        var id = p.id;
        var file = p.file;
        $('.popup-confirm').hide();
        $('.popup-confirm').val('Back');
        $.ajax({
            type: "POST",
            url: 'php/viewlogs.php?'+(new Date()).getTime(),
            data: {id : id, file:'',token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            $("#popup .popup-header .popup-title").html('View Logs');
            $('#view-logs').html(data);
            $('#view-logs ul li span').click(function() {
                $('#version').addClass('load');
                var file = $(this).attr('data-name');
                $("#popup .popup-header .popup-title").html('View Logs: '+$(this).html());
                $.ajax({
                    type: "POST",
                    url: 'php/viewlogs.php?'+(new Date()).getTime(),
                    data: {id : id, file:file,token: Sz.token},
                    cache: false,
                    async: true
                }).done(function(data) {
                    $('#view-logs').html(data);
                    $('#version').addClass('load');
                    $('.popup-confirm').show();
                    $('#version').removeClass('load');
                });
            });
            $('#version').removeClass('load');
        });

    }
//-------------------
// Reload Config
//-------------------
<?php if ( preg_match('/a|c/',$_SESSION['level']) && $task_status != 'Create' ) { ?>
$( "#reload-config" ).click( "click", function() {

    if ( $('#version').hasClass('load') ) return false;
    Sz.Response("Sz Manager is restarting. Please wait...");
    $('#version').addClass('load');
    $( "#reload-config" ).addClass('fa-spin');
    $.ajax({
        type: "POST",
        url: 'php/schtasks.php?'+(new Date()).getTime(),
        data: {task: 'reload', sys:0, token: Sz.token},
        cache: false,
        async: false
    }).done(function(data) {
        $('#version').removeClass('load');
        $( "#reload-config" ).removeClass('fa-spin');
        if (data.trim() == 'Running') {
            Sz.Response('Sz Manager has been successfully reloaded and it is running.');

        } else {
            Sz.Response('Error: Sz Manager did not reload and is off. Check logs. - '+data+' - ',1);
        }
    });

});
<?php } ?>
//-------------------
// Send Mail
//-------------------
$( "#sendmail" ).click( "click", function() {
    var smtp = $('#server-0 [name="smtp"]').val();
    var smtp_port = $('#server-0 [name="smtp_port"]').val();
    var smtp_ssl = $('#server-0 [name="smtp_ssl"]').prop('checked');
    var auth_username = $('#server-0 [name="auth_username"]').val();
    var auth_password = $('#server-0 [name="auth_password"]').val();
    var sendmail_to = $('#server-0 [name="sendmail_to"]').val();
    var sendmail_from = $('#server-0 [name="sendmail_from"]').val();
    $('#version').addClass('load');
    $.ajax({
        type: "POST",
        url: 'php/sendmailtest.php?'+(new Date()).getTime(),
        data: {smtp: smtp, smtp_port: smtp_port, smtp_ssl: smtp_ssl, auth_username: auth_username, auth_password: auth_password, sendmail_to: sendmail_to, sendmail_from: sendmail_from, token: Sz.token},
        cache: false,
        async: true
    }).done(function(data) {
        if (data) {

                Sz.popup.open({header:'Email Test Message',content:data,css:''});
                $('.popup-confirm').hide();
                $('.popup-cancel').val('OK');

        }else{

            Sz.Response('No response.',1);

        };
        $('#version').removeClass('load');
    });

});

</script>


