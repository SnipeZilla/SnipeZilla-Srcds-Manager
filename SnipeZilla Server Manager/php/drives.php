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
$_TOKEN=$_POST['token'];
require_once 'users.crc32.php';
if ( !preg_match('/a|b/',$_SESSION['level']) ) die('Error 403 - Forbidden: Access is denied.');

$steam = $_POST['steam'] == '0';
$active_drive= $_POST['drive'];
$output = shell_exec("powershell -Command \"Get-CimInstance -ClassName Win32_LogicalDisk | Select-Object DeviceID, VolumeName | ForEach-Object { \\\"$(\$_.DeviceID),$(\$_.VolumeName)\\\" }\"");
$drives = array_filter(explode("\n", $output));
$disk=array();
foreach ($drives as $drive) {

    $driveData = explode(',', $drive);
    if (count($driveData) == 2) {
        $disk[]=array('letter' => trim($driveData[0]),
                        'name'   => trim($driveData[1])?trim($driveData[1]):"Local Disk");
    }

}
?>

<div class="drives">

	<span><select id="drives">
	<?php
		$s=' selected="selected"';
		foreach ($disk as $drive) {
            $letter=str_replace(':','',$drive['letter']);
			echo "<option value=\"".$letter."\"".($letter==$active_drive?' selected="selected"':'').">".$drive['name']." (".$drive['letter'].")</option>";

		}
	?>
	</select></span>
    <div class="create-new"><div class="create-icons">
	<?php
	if ($steam) {
		echo '<span style="opacity:0.5;cursor:default;" class="install-steam fa fa-steam-square" title="Install SteamCMD"></span>';
	}else{
        echo '<span style="width:20px;"></span>';
    }
	?>
	<span class="create-new-folder fa fa-folder" title="Create New Folder"></span>
	</div></div>
	<div id="drive" class="demo drive"></div>
<div id="path"></div>
</div>

<script type="text/javascript">
	$('#drive').fileTree({ root: $('#drives').val()+':/', script: 'php/jqueryFileTree.php' });

	$( "#drives" ).change(function() {
		$('.ui-dialog-title').text('Explorer');
		$('.drive a').removeClass('selected');
		$('#drive').fileTree({ root: this.value+':/', script: 'php/jqueryFileTree.php' });
	});

$( ".create-new-folder" ).click( function() {
    $('#new-folder').remove();
    var Li = '<li id="new-folder" class="directory"><input type="text" name="new-folder" value=""><span title="Create New Folder" onclick="newFolder()" class="fa fa-check"></span><span onclick="delFolder()" class="fa fa-close" title="Cancel" ></span></li>'

    if ( $('li.directory a').hasClass('selected') && !$('li.directory a.selected').parent().children().hasClass('denied') ) {
        $('li.directory a.selected').parent().append( Li );
    }else{
        $('.jqueryFileTree:eq(0)').prepend( Li );
    }
    $('#new-folder [name="new-folder"]').focus();
});

function delFolder() {

    $('#new-folder').remove();

}

function newFolder() {
    var value = $('#new-folder [name="new-folder"]').val().trim();
    var regex = new RegExp("^[a-zA-Z0-9-_.]+$");
    var dir = $('.ui-dialog-title').html();
    if (dir == 'Explorer') {dir = $('#drives option:selected').val()+':'};

    if (!regex.test(value)) {
        return false;
    }

        $('#new-folder').addClass('directory collapsed').html('<a href="#" rel="'+dir+'\\'+value+'">'+value+'</a>');//.attr('id', '');

        $.ajax({
            async: false,
            type: "POST",
            url: 'php/setup.php?'+(new Date()).getTime(),
            data: {fct:'folder',par: dir+'\\'+value, token: Sz.token},
            cache: false
        }).done(function(data) {
            try{
                if (data == 'Success') {
                    $('.validateTips').text( 'Success: New Folder created' ).addClass( "ui-state-highlight" );
                    setTimeout(function() {
                        $('.validateTips').removeClass( "ui-state-highlight", 250 ).text('');
                    }, 5000 );
                    $('#drive').fileTree({ root: $('#drives').val()+':/', script: 'php/jqueryFileTree.php' }, function(file) {
                        if (Sz.tab.id == 0) {
                            $('.ui-dialog-title').text( file.replace(/\//g,'\\'));
                        }
                    });
                } else {
                    $('.validateTips').text( data ).addClass( "ui-state-highlight" );
                    setTimeout(function() {
                        $('.validateTips').removeClass( "ui-state-highlight", 250 ).text('');
                    }, 5000 );
                    delFolder();
                }
            }catch(err){
                alert(err.message);
            }
        });

    return '';

}

</script>