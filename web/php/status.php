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

require 'config.php';
//Check if service is installed
$root = exec('chdir',$o,$r);
$root = preg_replace('/\\\\web.*+$/','\\',$root);
$ExecFile = 'php.exe';
$ExecPath = $root.'\\'.$ExecFile.' -f '.$root.'\SzMgr.php';
$SzMgr = "SnipeZilla Srcds Manager";
if ( preg_match('/'.$SzMgr.'/i', shell_exec('schtasks /Query')) ) {

    $task_status = TaskMgr( $ExecFile, $ExecPath) ? 'Running' : 'Ready'; 

} else {

    $task_status = 'Create';

}
//
$stopFile   = 'sz.stop.txt';
$total = sizeof($server);
if (!$server[1]['ip'] || ( !preg_match('/a|e/',$_SESSION['level']) && !($users[$_SESSION['login']]['svr']) ) ) exit();
?>
<div>
	<table id="a2s-info">
		<tr>
			<th>Game</th>
			<th>IP:Port</th>
			<th>Players</th>
			<th>Bots</th>
			<th>Map</th>
			<th>Control</th>
		</tr>
<?php
    $lsvr=array();
    for ($i=1; $i<$total; $i++) {
        $own = preg_match('/a/',$_SESSION['level']) || @in_array($server[$i]['ip'].':'.$server[$i]['port'], $users[$_SESSION['login']]['svr']) ;
        if ( !preg_match('/a|e/',$_SESSION['level']) && !$own ) continue;
        $lsvr[]=$i;
        $cmdFile = $server[$i]['installdir'].'\\'.$server[$i]['game'].'\\cfg\sz.cmd.cfg';
        $updateFile = $server[$i]['installdir'].'\\'.$server[$i]['game'].'\\'.$server[$i]['updatefile'];
        echo '
		<tr id="svr-'.$i.'"'.(!$own?' class="disable svr"':' class="svr"').'>
			<td class="game" title="Server '.$i.' - '.$server[$i]['fname'].'">'.($server[$i]['fname']?$server[$i]['fname']:$games[$server[$i]['appid']]['name']).'</td>
			<td class="ipport">'.$server[$i]['ip'].':'.$server[$i]['port'].'</td>
			<td class="players"></td>
			<td class="bots"></td>
			<td class="map"></td>
			<td class="command">
				<div class="refresh"><span title="Refresh Server '.$i.'" class="fa fa-refresh"></span></div>
				<div class="rcon">'.(preg_match('/a|f|g/',$_SESSION['level']) && $own?'<span title="Send RCON command" class="fa fa-cog rcon-icon"></span>':'&nbsp;').'</div>
				<div class="status"></div>
				<div class="password">&nbsp;</div>
				<div class="vac">&nbsp;</div>
				<div '.($own && preg_match('/a|i/',$_SESSION['level'])?'class="ctrl"':'class="ctrl disable"').'>&nbsp;</div>
			</td>
		</tr>';
        if ( !preg_match('/a|f|g/',$_SESSION['level']) || !$own ) continue;
        echo '<tr id="rcon-'.$i.'" class="rcon-cmd">
            <td colspan="6">
                <table>
                    <tr id="rcon-view-logs-'.$i.'" >
                        <td colspan="3" class="rcon-view-logs" title="Sz Manager Logs"><span class="fa fa-file-text-o"></span> View Logs</td>
                    </tr>';
                    
                if ( preg_match('/a|g/',$_SESSION['level']) && $own) {
                
                 echo '<tr>
                    <td colspan="3"><h3>RCON Command</h3></td>
                    </tr>
                    <tr>
                        <td>
                            <select id="rcon-pre-'.$i.'" name="prercon">
                                <option value="reset" selected="selected"></option>
                                <option value="">Status</option>
                                <option value="">Say &lt;something&gt;</option>
                                <option value="">Users</option>
                                <option value="">maps *</option>
                                <option value="">changelevel &lt;mapname&gt;</option>
                                '.(preg_match('/a|i/',$_SESSION['level'])?'<option value="">Quit</option>':'').'
                            </select>
                        </td>
                        <td>
                            <input type="text" id="rcon-msg-'.$i.'" name="rcon" value="" title="e.g. SAY  Welcome to the game">
                        </td>
                        <td>
                            <div><span id="rcon-send-'.$i.'" class="fa fa-paper-plane-o rcon-send" title="Send RCON"></span></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="3" title="Command will be output here">
                            <div id="rcon-resp-'.$i.'" class="output"></div>
                        </td>
                    </tr>';
                }
                if ( preg_match('/a|f/',$_SESSION['level']) && $own) {
                 echo '<tr>
                        <td colspan="3"><h3>Additionnal Command Line Options</h3></td>
                    </tr>
                    <tr>
                        <td colspan="2"><textarea id="cmd-'.$i.'" rows="2" cols="100" name="cmd" title="Will overwrite the start parameters for srcds.exe (e.g. -nohltv -maxplayers_override 32 +sv_lan 1 +map de_dust2), Note: ip/port/console/usercon will be added automatically." spellcheck="false">'.(file_exists($cmdFile)?file_get_contents($cmdFile):'').'</textarea></td>
                        <td>
                            <div class="ctrl-48">
                                <div><span id="cmd-save-'.$i.'" class="fa fa-floppy-o cmd-save" title="command line will be executed on next start"></span></div>
                                <div><span id="cmd-del-'.$i.'" class="fa fa-times cmd-del" title="Use default options"></span></div>
                            </div>
                        </td>
                    </tr>';
                    
                }
                 if ( preg_match('/a|h/',$_SESSION['level']) && $own) {
                 echo '<tr>
                        <td colspan="3"><h3>Update Server</h3></td>
                    </tr>
                    <tr>
                        <td colspan="3">
                            <input id="update-'.$i.'" type="checkbox" name="update" class="update" value="1"'.(file_exists($updateFile)?' checked':'').' title="steamcmd" > Force update at the next start up
                        </td>
                    </tr>';
                    
                }
               echo '</table>
            </td>
        </tr>';
    }
?>
	</table>
</div>
<div class="SzMgr_status">
<?php
if ($task_status != 'Running') {
    echo '<p><span class="fa fa-exclamation-triangle"></span>&nbsp;SnipeZilla Srcds Manager is not running. '.($total>2?'Servers are':'Server is').' unmonitored.</p>';
}
?>

</div>
<script>
    task_status = '<?php echo $task_status;?>';
    lsvr = <?php echo json_encode($lsvr);?>;
    content = '<p class="alert"><span class="fa fa-exclamation-triangle"></span>This server will be permanently stopped and can be restarted only from this page.</p>';

    $(".refresh").click(function() {
        var id      = $(this).closest('tr').attr('id').split('-').pop();
        var address = $(this).closest('tr').find('.ipport').text().split(":");
        var ip      = address[0];
        var port    = address[1];
        Refresh(ip,port,id);
    });

    function Refresh(ip,port,id) {
        if ( $('#a2s-info #svr-'+id).find('.fa-refresh').hasClass('fa-spin') ) return false;
        $('#a2s-info #svr-'+id).find('.fa-refresh').addClass('fa-spin');
        $('#version').addClass('load');
        $.ajax({
            type: "POST",
            url: 'php/a2s_info.php?'+(new Date()).getTime(),
            data: {id : lsvr[id-1], ip: ip, port: port, token: Sz.token},
            cache: false,
			json:false,
			jsonp: false,
            async: true
        }).done(function(data) {

            try{
                var info = JSON.parse(/({.*})/.exec(data)[1]);
                if (!info.error) {
                    $('#a2s-info #svr-'+id).find('.game').text(info.name);
                    if (info.vac) {
                        $('#a2s-info #svr-'+id).find('.vac').html('<span title="Valve Anti-Cheat System" class="fa fa-shield"></span>');
                    } else {
                        $('#a2s-info #svr-'+id).find('.vac').html("&nbsp;");
                    }
                    $('#a2s-info #svr-'+id).find('.players').text(info.players+'/'+info.playersmax);
                    $('#a2s-info #svr-'+id).find('.bots').text(info.bots);
                    $('#a2s-info #svr-'+id).find('.bots').text(info.bots);
                    $('#a2s-info #svr-'+id).find('.map').text(info.map);
                    if (info.password) {
                        $('#a2s-info #svr-'+id).find('.password').html('<span title="Private" class="fa fa-lock"></span>');
                    }else{
                        $('#a2s-info #svr-'+id).find('.password').html('<span title="Public" class="fa fa-unlock"></span>');
                    }
                    $('#a2s-info #svr-'+id).find('.status').html('<span title="ONLINE" class="fa fa-steam"></span>');
                    if (task_status == 'Running') {

                        $('#a2s-info #svr-'+id).find('.ctrl').html('<span title="Stop Server"class="fa fa-stop red"></span>');
                        $(".fa-stop").click(function() {
                            if ( $('#version').hasClass('load') ) return false;
                            if ($(this).parent().hasClass('disable')) return false;
                            var id  = $(this).closest('tr').attr('id').split('-').pop();
                            Sz.popup.open({header:'Stop the server?',content:content,css:'small'},{'id':id,'fct':'stop'},Ctrl);
                        });

                    } else {

                        $('#a2s-info #svr-'+id).find('.ctrl').html('<span title="Stop Server"class="fa fa-stop green"></span>');
                        $(".fa-stop").click(function() {
                            if ( $('#version').hasClass('load') ) return false;
                            if ($(this).parent().hasClass('disable')) return false;
                            var id  = $(this).closest('tr').attr('id').split('-').pop();
                            Sz.popup.open({header:'Stop the server?',content:content,css:'small'},{'id':id,'fct':'stop'},Ctrl);
                        });

                    }
                }else{
                    if (info.stop) {
                        $('#a2s-info #svr-'+id).find('.status').html('<span title="STOP Requested" class="fa fa-plug"></span>');

                        if (task_status == 'Running') {
                            $('#a2s-info #svr-'+id).find('.ctrl').html('<span title="Start Server" class="fa fa-play green"></span>');
                            $(".fa-play").click(function() {
                                if ( $('#version').hasClass('load') ) return false;
                                if ($(this).parent().hasClass('disable')) return false;
                                var id  = $(this).closest('tr').attr('id').split('-').pop();
                                Ctrl({id:id,fct:'start'});
                            });

                        } else {
                            $('#a2s-info #svr-'+id).find('.ctrl').html('<span title="Allow Server to start"class="fa fa-play red"></span>');
                            $(".fa-play").click(function() {
                                if ( $('#version').hasClass('load') ) return false;
                                if ($(this).parent().hasClass('disable')) return false;
                                var id  = $(this).closest('tr').attr('id').split('-').pop();
                                Ctrl({id:id,fct:'start'});
                            });
                        }

                    } else {
                        $('#a2s-info #svr-'+id).find('.status').html('<span title="No response" class="fa fa-exclamation-triangle"></span>');
                        $('#a2s-info #svr-'+id).find('.ctrl').html('<span title="Stop Server"class="fa fa-stop red"></span>');
                        $(".fa-stop").click(function() {
                            if ( $('#version').hasClass('load') ) return false;
                            if ($(this).parent().hasClass('disable')) return false;
                            var id  = $(this).closest('tr').attr('id').split('-').pop();
                            Sz.popup.open({header:'Stop the server?',content:content,css:'small'},{'id':id,'fct':'stop'},Ctrl);
                        });

                    }
                }


                setTimeout(function() { 
                    $('#a2s-info #svr-'+id).find('.fa-refresh').removeClass('fa-spin');
                    if ( !$('.fa-refresh').hasClass('fa-spin') ) $('#version').removeClass('load');
                },500);
            }catch(err){
                $('#a2s-info #svr-'+id).find('.fa-refresh').removeClass('fa-spin');
                if ( !$('.fa-refresh').hasClass('fa-spin') ) $('#version').removeClass('load');
                console.log(data);
            }
        });

    }

    function Ctrl(v) {
        var id      = v.id;
        var fct     = v.fct;
        var address = $('#a2s-info #svr-'+id).find('.ipport').text().split(":");
        var ip      = address[0];
        var port    = address[1];
        $.ajax({
            type: "POST",
            url: 'php/ctrl.php?'+(new Date()).getTime(),
            data: {ctrl: fct, id: lsvr[id-1], ip: ip, port:port, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            try{
                if ( data.trim() == 0 ){
                    if (fct == 'stop') {
                        if (task_status == 'Running') {
                            $('#a2s-info #svr-'+id).find('.ctrl').html('<span title="Start Server" class="fa fa-play green"></span>');
                            $(".fa-play").click(function() {
                                if ( $('#a2s-info #svr-'+id).find('.fa-refresh').hasClass('fa-spin') ) return false;
                                if ($(this).parent().hasClass('disable')) return false;
                                var id  = $(this).closest('tr').attr('id').split('-').pop();
                                Ctrl({id:id,fct:'start'});
                            });
                        } else {
                            $('#a2s-info #svr-'+id).find('.ctrl').html('<span title="Allow Server to start"class="fa fa-play red"></span>');
                            $(".fa-play").click(function() {
                                if ( $('#a2s-info #svr-'+id).find('.fa-refresh').hasClass('fa-spin') ) return false;
                                if ($(this).parent().hasClass('disable')) return false;
                                var id  = $(this).closest('tr').attr('id').split('-').pop();
                                Ctrl({id:id,fct:'start'});
                            });
                        }
                        $('#a2s-info #svr-'+id).find('.status').html('<span title="STOP Requested" class="fa fa-plug"></span>');
                        $('#a2s-info #svr-'+id).find('.players').text(' ');
                        $('#a2s-info #svr-'+id).find('.bots').text(' ');
                        $('#a2s-info #svr-'+id).find('.map').text(' ');
                        $('#a2s-info #svr-'+id).find('.password').html("&nbsp;")
                        $('#a2s-info #svr-'+id).find('.vac').html("&nbsp;")

                    } else {
                        if (task_status == 'Running') {
                            $('#a2s-info #svr-'+id).find('.ctrl').html('<span title="Stop Server"class="fa fa-stop red"></span>');
                            $('#update-'+id).prop( "checked", false );
                        } else {
                            $('#a2s-info #svr-'+id).find('.ctrl').html('<span title="Stop Server"class="fa fa-stop red"></span>');
                        }
                        $('#a2s-info #svr-'+id).find('.status').html('<span title="?" class="fa fa-question"></span>');
                        setTimeout( Refresh, 10000,ip,port,id);
                        $(".fa-stop").click(function() {
                            if ( $('#a2s-info #svr-'+id).find('.fa-refresh').hasClass('fa-spin') ) return false;
                            if ($(this).parent().hasClass('disable')) return false;
                            var id  = $(this).closest('tr').attr('id').split('-').pop();
                            Sz.popup.open({header:'Stop the server?',content:content,css:'small'},{'id':id,'fct':'stop'},Ctrl);
                        });
                    }
                } else { 
                    Sz.Response(data);
                }

            }catch(err){
                alert(data);
            }
        });

    }
	
    for (var i=0; i<$('#a2s-info tr.svr').length; i++) {
        var id      = $('#a2s-info tr.svr:eq('+i+')').attr('id').split('-').pop();
        var address = $('#a2s-info tr.svr:eq('+i+')').find('.ipport').text().split(":");
        var ip      = address[0];
        var port    = address[1];
		var time    = i*500
        setTimeout( Refresh, time,ip,port,id);
    }

<?php
if ( preg_match('/a|f|g/',$_SESSION['level']) ) {
?>
//--------------------------
//   CMD
//--------------------------
    $(".rcon-icon").click(function() {
        var pre = '';
        if ($('tr').hasClass('selected')) {
            pre = $('tr.selected').attr('id').split('-').pop();
        }
        $('tr.show').removeClass('show selected').hide();
        $('tr.selected').removeClass('selected');
        var id = $(this).closest('tr').attr('id').split('-').pop();
        if (pre == id) return false;
        $('tr#rcon-'+id).addClass('show').show(500);
        $('tr#svr-'+id).addClass('selected');

    });
    //logs
    $(".rcon-view-logs").click(function() {
        if ( $('#version').hasClass('load') ) return false;
        $('#version').addClass('load');
        var id = $(this).closest('tr').attr('id').split('-').pop();
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
<?php
    if ( preg_match('/a|f/',$_SESSION['level']) ) {
?>
    function cmdSave(id,del) {
        if ( $('#version').hasClass('load') ) return false;
        $('#version').addClass('load');
        var id      = id;
        var address = $('#svr-'+id).find('.ipport').text().split(":");
        var ip      = address[0];
        var port    = address[1];
        var cmd     = 0 == del? $('#cmd-'+id).val().trim() : '';
        $.ajax({
            type: "POST",
            url: 'php/cmd.php?'+(new Date()).getTime(),
            data: {id : lsvr[id-1], ip: ip, port: port, cmd: cmd, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            if (data.slice(0,5) != 'Error') {
                $('#cmd-'+id).val(data);
                if (!data) {
                    Sz.Response('Command Line Options deleted. Effective on next start.');
                } else {
                    Sz.Response('New Command Line Options. Effective on next start.');
                }
            } else {
                Sz.Response(data);
            }
            $('#version').removeClass('load');

        });

    };
    $(".cmd-save").click(function() {
        cmdSave($(this).attr('id').split('-').pop(),0);
    });
    $(".cmd-del").click(function() {
        cmdSave($(this).attr('id').split('-').pop(),1);
    });
<?php
    }
    if ( preg_match('/a|g/',$_SESSION['level']) ) {
?>
//--------------------------
//   RCON
//--------------------------
    $('[name="prercon"]').change(function() {
        var id = $(this).attr('id').split('-').pop();
        var txt = $('#rcon-pre-'+id+' option:selected').html();
        $('#rcon-msg-'+id+ '[name="rcon"]').val(txt.replace(/&lt;.*&gt;/,''));
    });

    $(".rcon-send").click(function() {
        if ( $('#version').hasClass('load') ) return false;
        var id  = $(this).attr('id').split('-').pop();
        var rcon = $('#rcon-msg-'+id+ '[name="rcon"]').val().trim();
        if (!rcon) return false;
        $('#version').addClass('load');
        var address = $('#svr-'+id).find('.ipport').text().split(":");
        var ip      = address[0];
        var port    = address[1];
        Sz.Response(ip+':'+port +' - '+ rcon);
        $.ajax({
            type: "POST",
            url: 'php/rcon.php?'+(new Date()).getTime(),
            data: {id : lsvr[id-1], ip: ip, port: port, rcon: rcon, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            if (data.slice(0,5) != 'Error') {
                $('#rcon-resp-'+id).html(data);
                Refresh(ip,port,id);
            } else {
                Sz.Response(data,1);
            }
            $('#rcon-pre-'+id).val('reset');
            $('#version').removeClass('load');

        });

    });
<?php
    }
    if ( preg_match('/a|h/',$_SESSION['level']) ) {
?>
//--------------------------
//   Update
//--------------------------
$('.update').change(function() {
    if ( $('#version').hasClass('load') ) {
        $(this).prop('checked',!$(this).is(':checked'));
        return false;
    }
    var id      = $(this).attr('id').split('-').pop();
    var address = $('#svr-'+id).find('.ipport').text().split(":");
    var ip      = address[0];
    var port    = address[1];
    var update  = $(this).is(':checked');
    $('#version').addClass('load');
    $.ajax({
        type: "POST",
        url: 'php/update.php?'+(new Date()).getTime(),
        data: {id : lsvr[id-1], ip: ip, port: port, update: update, token: Sz.token},
        cache: false,
        async: true
    }).done(function(data) {
        if (data.slice(0,5) != 'Error') {
            Sz.Response(data);
        } else {
            Sz.Response(data,1);
        }
        $('#version').removeClass('load');

    });

});

<?php
    }
}

?>
</script>