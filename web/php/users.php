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
<?php
// Add user permission
if ( preg_match('/a|d/',$_SESSION['level']) ) {
?>
<div id="adduser">

	

		<fieldset >
		<legend>New User</legend>
			<table>
				<tr>
					<td>
						<label for='username' >UserName:</label>
					</td>
					<td>
						<input type='text' name='username' id='username'  maxlength="50"/>
					</td>
					<td>
						<label for='password' >Password:</label>
					</td>
					<td>
						<input type='password' name='password' id='password' maxlength="50" />
					</td>
                    <td rowspan="2" style="vertical-align:top"><input type='submit' name='submit' id='submit' value='Submit' class='btn'/></td>
				</tr>
				<tr>
                    <td colspan="2"></td>
					<td>
						<label for='password' >Confirm:</label>
					</td>
					<td>
						<input type='password' name='password2' id='password2' maxlength="50" />
					</td>
				</tr>

			</table>

		</fieldset>

</div>
<?php
 }
if ( $users) {

?>

<div>
	<table id="user-table" class="user-table">
        <thead>
		<tr>
			<th>Name</th>
			<th>Password</th>
			<th>Server Controls</th>
			<th>Permission</th>
            <th><span class="fa fa-user"></span></th>
		</tr>
        </thead>
        <tbody>
<?php

include 'users.list.php';

echo '</tbody></table></div>';
} else {
echo '<div>Create first user with all permissions.</div>';

}

?>

</div>
<script>
<?php
if ( preg_match('/a|d/',$_SESSION['level']) ) {
?>
    $("#submit").click(function() {
        if ( $('#version').hasClass('load') ) return false;
        var username  = $("#username").val();
        var password  = $("#password").val();
        var password2 = $("#password2").val();
        if (username == '') {
            return false;
        } else if (/(\s|\n)/.test(password)) {
            Sz.Response("No spaces allow in Password.",1);
        } else if ((password.length) < 6) {
            Sz.Response("Password needs at least 6 characters in length.",1);
        } else if (password!=password2) {
            Sz.Response("Passwords do not match.",1);
        } else {
            $('#version').addClass('load');
            $.ajax({
                type: "POST",
                url: 'php/users.db.php?'+(new Date()).getTime(),
                data: {fct: 'new', username : username, password: password, password2: password2, token: Sz.token},
                cache: false,
                async: false
            }).done(function(data) {
                if (data.slice(0,6) != 'Error:') {
                    $("#username").val('');
                    $("#password").val('');
                    $("#password2").val('');
                    $('#user-table tbody').html(data);
                    $('#user-table tr:last').removeClass('user');
                    $("html, body").animate({ scrollTop: $(document).height() }, 250);
                    $('#user-table tr:last').find('td').stop(true,true).effect('highlight', {color:'#009933'},2000);
                    fctClick();
                } else {
                    if (data.match(/refresh/i)){location.reload();}
                    Sz.Response(data);
                }
                $('#version').removeClass('load');
            });

        };
    });
<?php
}
?>
    function DelUser(p) {
        if ( $('#version').hasClass('load') ) return false;
        //var username = $(p.row).closest('tr').find('td:first span.username').html();
        //var row = t;
        if (!username) return false;
            $.ajax({
                type: "POST",
                url: 'php/users.db.php?'+(new Date()).getTime(),
                data: {fct: 'delete', username : p.username, token: Sz.token},
                cache: false,
                async: false
            }).done(function(data) {
                if (data.slice(0,6) != 'Error:') {
                    Sz.Response(p.username+' removed successfully' );
                    $(p.row).closest('tr').fadeOut(1000,function() {
                        $('#user-table tbody').html(data);
                        fctClick();
                    });
                } else {
                    Sz.Response(data,1);
                }
                $('#version').removeClass('load');
            });


    }

    function UpdateUser(t) {
        if ( $('#version').hasClass('load') ) return false;
        var username = $(t).closest('tr').find('td:first span.username').html();
        var password = $(t).closest('tr').find('[name="password"]').val();
        var password2 = $(t).closest('tr').find('[name="password2"]').val();
        if (/(\s|\n)/.test(password)){
            $(t).closest('tr').find('[name="password"]').val('');
            $(t).closest('tr').find('[name="password2"]').val('');
            Sz.Response("No spaces in Password.",1);
            return false;
        }
        if (password && (password.length) < 6) {
            Sz.Response("Password needs at least 6 characters in length.",1);
            return false;
        } else if (password && password!=password2) {
            Sz.Response("Passwords do not match.",1);
            return false;
        }

        var lvl='';
        var svr=new Array();
        $(t).closest('tr').find('td:eq(3) :checked').each(function() {
            lvl+=($(this).val());
        });
        $(t).closest('tr').find('td:eq(2) :checked').each(function() {
            svr.push($(this).val());
        });
        if (!svr.length) svr='';
        var row = $(t).closest('tr').index();
        if (!username) return false;
            $.ajax({
                type: "POST",
                url: 'php/users.db.php?'+(new Date()).getTime(),
                data: {fct: 'update', username : username, password: password, password2:password2, svr: svr, lvl:lvl, token: Sz.token},
                cache: false,
                async: false
            }).done(function(data) {
            
            if (data.slice(0,6) != 'Error:') {
                $('#user-table tbody').html(data);
                $('#user-table tr:eq('+(row+1)+')').find('td').stop(true,true).effect('highlight', {color:'#009933'},1000);
                Sz.Response($('#user-table tr:eq('+(row+1)+') .username').html()+' updated successfully');
                fctClick();
            } else { Sz.Response(data,1); }
                $('#version').removeClass('load');
            });


    }

    function fctClick() {

        $(".del-user").click(function(event) {
            if ( $('#version').hasClass('load') ) return false;
            var username = $(this).closest('tr').find('td:first span.username').html();
            var row = this;
            if (!username) return false;
            var content = '<p class="alert"><span class="fa fa-exclamation-triangle"></span>Permanently Remove "'+username+'"?</p>';
            Sz.popup.open({header:'Remove User', content:content,css:'small'},{'username':username,'row':row},DelUser);
        });
        $(".update-user").click(function(event) {
            UpdateUser(this);
        });
        $('#user-table tr:last').removeClass('user');

    }

    fctClick();
   
</script>

