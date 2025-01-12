Srcds server manager for windows (has been tested on 10, 11, server 2012, server 2022, server 2025).
For windows server 2025 you need WMIC (enable optional feature). I will change to powershell if it causes problems or WMIC is discontinued.

<span style="text-decoration: underline"><b>Main features for the script (SzMgr.php):</b></span>
<ul>
<li>Manage one instance of SteamCMD</li>
<li>Support Source Game base only (unless 'pingable' is deselected)<br>
</li>
<li>Unlimited monitoring of Servers</li>
<li>Automatically Update<br>
</li>
<li>Crash control<br>
</li>
<li>Unresponsiveness control<br>
</li>
<li>Email alert</li>
<li>No plugin needs</li>
<li>Option to use a plugin to stop the server on update<br>
</li>
<li>Option to update all servers from a cache (minimize downtime)</li>
<li>Super light in resource (close to zero)<br>
</li>
<li>Options from xml format file<br>
</li>
<li>Easy to use</li>
<li>Comprehensive log system</li>
<li>Automatically Group server by app (e.g. Update will be trigger for all TF2 servers and queue)</li>
<li>RCON 'say' to inform current players before an update (can be replace with a plugin like sm_hsay)<br>
</li>
<li>Automatic Repair (works better with a cache)</li>
<li>Super stable(Run SnipeZilla servers for almost 10 years)<br>
</li>
<li>Write in php language (easy to read and maintain)</li>
<li>No needs to have php installed.</li>
<li>Possibility to not use a srcds.exe server (e.g hlds.exe)<br>
</li>
<li>And probably more features...<br>
</li>
<li>Voted best Srcds Manager ever</li>
</ul>

<span style="text-decoration: underline"><b><br>
How to install?</b></span><br>
SnipeZilla Srcds Manager read a configuration file ('config.xml') (Which can be manually edited if the server is not standard);<br>
To create one simply click start-web.bat(Run As Administrator if you're not)<br>
The web interface has various options available:
<ol>
<li><span style="color: #99ffff">CONFIGURATION</span> : setup SteamCMD and SRCDS, Trigger Update etc...If you already have a server and it's running, just point the folder location.<br>
</li>
<li><span style="color: #99ffff">INSTALLATION</span>: To create the Run Task (with an option to run apps in background or not) or reload the new configuration. Servers are not stopped if the Task is ended (To modify a running server, you will need to stop the task)<br>
</li>
<li><span style="color: #99ffff">STATUS</span>: Quick overlook of all servers monitoring by SzMgr, and option to individually stop or start a server and Give RCON command.<br>
</li>
<li><span style="color: #99ffff">FILE MANAGER</span>: Useful if you have more than 1 server.<br>
</li>
<li><span style="color: #99ffff">HELP</span>: Useless links and legal stuff (GNU General Public License)</li>
</ol>


