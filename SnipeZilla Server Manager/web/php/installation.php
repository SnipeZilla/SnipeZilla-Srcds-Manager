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
if ( !preg_match('/a|c/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

include "games.php";
include "proc.php";
include "rfile.php";
//Path to sz_srcds
$root = exec('chdir',$o,$r);
$root = preg_replace('/\\\\web.*+$/','\\',$root).'bin';
$appdir = preg_replace('/\\\\bin.*+$/','\\',$root);
$ExecFile = 'php.exe';
$ExecPath = $root.'\\'.$ExecFile.' -f '.$root.'\SzMgr.php';
//
$SzMgr = "SnipeZilla Srcds Manager";
$version = str_replace(array("'",';'),'',rfile(__DIR__ . '..\..\..\bin\SzMgr.php','version\s+?='));
//Check if service is installed
if ( preg_match('/'.$SzMgr.'/i', shell_exec('schtasks /Query')) ) {

    $task_status = TaskMgr($ExecFile) ? 'Running' : 'Ready'; 

} else {

    $task_status = 'Create';

}

//OS support xml?
if ( preg_match('/\/xml/i', shell_exec('schtasks /Create /?')) ) {

    $os = true;

} else {

    $os = false;

}

?>

<div id="installation_page">

<h1>Task Scheduler</h1>

<p class="taskstatus">
SnipeZilla Srcds Manager <?php echo $version;?> => <span id="task_status"><?php echo $task_status; ?></span>
</p>

<div class="create">
<button id="create" type="button" class="btn">Create Task</button>
<span style="vertical-align:bottom" title="Run whether user is logged on or not (background application).">&nbsp;<input type="checkbox" name="system" value="1" checked>&nbsp;SYSTEM</span>
<?php
if (!$os) {
?>
<br/>
<div style="margin-top:15px;padding:10px;border:4px solid red;color:red;font-weight:bold;font-size:18px;">
Automatic Installation is partially supported by your OS (Require >= Vista).<br/>
The task will stop in 72h and may not run without your login password if you don't select 'SYSTEM'.<br/>
Open Task Scheduler to uncheck this option (under 'Settings') and set your login password.<br/>
<img class="install-img" src="css/images/help_task.png" style="width:312px;margin:5px auto;"/>
</div>
<br/>
<?php
}
?>
<div style="margin-top:15px">
You must be logged on <span style="color:red;font-weight:bold">as an administrator</span> to perform these steps.
<br/>
<img class="install-img" title="if you are not admin, run start.bat as an administrator. Or you will have to manually create the task!" src="css/images/runas.png" style="width:602px;margin:5px auto;"/>
<br/>
The task will be create <span style="color:red;font-weight:bold">without any password.</span><br/>Your account needs a password to run the task if you do not select SYSTEM.<br/><br/>
If 'SYSTEM' is selected, the task will run whether you are logged or not (this is the recommended setup); the server(s) will run in the background.
<br/><br/>
By deselected 'SYSTEM', the task will run only once you log at startup and the applications will not run in the background.
<br/><br/>
If you prefer to manually create the Task, you have to name it <b>"SnipeZilla Srcds Manager"</b> to control the script from this page and the <b>'Start in'</b> has to be 'xx\bin'
<br/>
<img class="install-img" src="css/images/help_task2.png" style="width:312px;margin:5px auto;"/>
<img class="install-img" src="css/images/help_task3.png" style="width:344px;margin:5px auto;"/>
<br/>
<br/>
</div>
</div>


<p>
<button id="delete" type="button" class="btn red" title="Will not stop Srcds.exe">Delete Task</button> 
<button id="run" type="button" class="btn green">Run Task</button>
<button id="end" type="button" class="btn" title="Will not stop Srcds.exe">End Task</button>
<br/>
</p>
<p>
<b>"Delete Task"</b>, <b>"Run Task"</b> or <b>"End Task"</b> has no effect over the server(s). The task control the script.<br/>
<b>You can delete or create the task at anytime: The server(s) will not be stopped.</b>
</p>
<p>
For more information about 'SnipeZilla Srcds Manager' with Task Scheduler visit <a href="http://www.snipezilla.com" target="_blank">snipezilla.com</a>
</p>
<h1>Dedicated Server</h1>
<div title="If xcopy is selected, the games will be downloaded automatically. Install now to save time..." class="openapps"><span title="browse" class="fa fa-folder-open openapps"></span> Pre-installed Game Server for xcopy:</div>

	<dl class="cachelist">
	<?php
    foreach ($games as $app=>$id) {
        $login='';
        if ($games[$app]['login']) {
            $login = ' login';
        }
        if ( file_exists($appdir.'apps\\'.$game[$app]['server'].'\\'.$game[$app]['game'].'\steam.inf') ) {
            echo '<dt class="ready"><span id="i'.$app.'" title="Ready for xcopy. Click to update." class="fa fa-server downloadgame available'.$login.'"></span><span class="server_id">'.$game[$app]['server'].'</span></dt><dd class="ready">'.$game[$app]['name'].'</dd>';
        } else {
            echo '<dt><span id="i'.$app.'" title="Download Game ['.$app.']" class="fa fa-download downloadgame'.$login.'"></span><span class="server_id">'.$game[$app]['server'].'</span></dt><dd>'.$game[$app]['name'].'</dd>';
        }
    }
	?>
	</dl>
<div style="clear:both"></div>

<script>
//Task
function TaskStatus(query,id) {
    s = query[0].trim();
    if ( s == 'Create' ) {
        $( "#create,.create" ).show();
        $( "#delete" ).hide();
        $( "#run" ).hide();
        $( "#end" ).hide();
    } else {
        $( "#create,.create" ).hide();
        $( "#delete" ).show();
        if (s == 'Running') {
            $( "#run" ).addClass('disable').prop( "disabled", true).show();
            $( "#end" ).removeClass('disable').prop( "disabled",false).show();
        } else {
            $( "#run" ).removeClass('disable').prop( "disabled",false).show();
            $( "#end" ).addClass('disable').prop( "disabled", true).show();
        }
    }
    $('#task_status').html(query[0]+query[1]);

}
Sz.TaskStatus(['<?php echo $task_status;?>','']);

$( "#create" ).click(function() {
    $.ajax({
        type: "POST",
        url: 'php/schtasks.php?'+(new Date()).getTime(),
        data: {task: <?php echo ($os? '"create"':'"createxp"');?>,sys: $('[name="system"]').prop('checked'), token: Sz.token},
        cache: false,
        async: true
    }).done(function(data) {
        query = JSON.parse(/(\[.*\]$)/.exec(data)[1]);
        TaskStatus(query);
    });
});
$( "#delete" ).click( "click", function() {
    Sz.Response("Deleting Sz Manager. Please wait...");
    $.ajax({
        type: "POST",
        url: 'php/schtasks.php?'+(new Date()).getTime(),
        data: {task:'delete', sys:0, token: Sz.token},
        cache: false,
        async: true
    }).done(function(data) {
        query = JSON.parse(/(\[.*\]$)/.exec(data)[1]);
        TaskStatus(query);
        Sz.Response(query);
    });
});
$( "#run" ).click( "click", function() {
    Sz.Response("Starting Sz Manager. Please wait...");
    $.ajax({
        type: "POST",
        url: 'php/schtasks.php?'+(new Date()).getTime(),
        data: {task:'run', sys:0, token: Sz.token},
        cache: false,
        async: true
    }).done(function(data) {
        query = JSON.parse(/(\[.*\]$)/.exec(data)[1]);
        TaskStatus(query);
        Sz.Response(query);
    });
});
$( "#end" ).click( "click", function() {
    Sz.Response("Stopping Sz Manager. Please wait...");
    $.ajax({
        type: "POST",
        url: 'php/schtasks.php?'+(new Date()).getTime(),
        data: {task:'end', sys:0, token: Sz.token},
        cache: false,
        async: true
    }).done(function(data) {
        query = JSON.parse(/(\[.*\]$)/.exec(data)[1]);
        TaskStatus(query);
        Sz.Response(query);
    });
});
//Games
function Download(p) {
        var login='';
        if (p.login) {
            var login=$('#popup [name="user"]').val()+' '+$('#popup [name="password"]').val();
        }
        var app_id=p.app_id;
        var server_id=p.server_id;
    $.ajax({
        type: "POST",
        url: 'php/setup.php?'+(new Date()).getTime(),
        data: {fct: 'apps', par: app_id, server_id: server_id, login: login, token: Sz.token},
        cache: false,
        async: true
    }).done(function(data) {
        Sz.Response(data);
    });
}
//---
$('.openapps').click(function() {
    $.ajax({
        type: "POST",
        url: 'php/explorer.php?'+(new Date()).getTime(),
        data: {type: 'apps', id: '', token: Sz.token},
        cache: false,
        async: true
    }).done();
});
$( ".downloadgame" ).each(function(i,v){
    $('.downloadgame:eq('+i+')').click(function() {
        var id=i;
        var app_id=$('.downloadgame:eq('+id+')').attr('id').substring(1);
        var server_id=$('.server_id:eq('+id+')').html();
        if ($('.downloadgame:eq('+id+')').hasClass('login')) {
            var content =  '<table>'+
                                '<tr>'+
                                    '<td>User Name:</td>'+
                                    '<td><input name="user" type="text" /></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td>Optional<br>Password:</td>'+
                                    '<td><input name="password" type="password" autocomplete="new-password"/></td>'+
                                '</tr>'+
                            '</table>';
            Sz.popup.open({header:'Login (Steam Account)', content:content,css:'small'},{'login':1,'app_id':app_id,'server_id':server_id},Download);
        }else{
            Download({'login':1,'app_id':app_id,'server_id':server_id});
        }

    });
});
</script>