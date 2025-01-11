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
if ( !preg_match('/a|j/',$_SESSION['level']) ) die('Error: Access is denied.');
require 'config.php';
$total = sizeof($server);
if (!$server[1]['ip']) exit();
?>
<div id="file-manager">

    <table class="files">
        <tr>
            <?php
                for ($i=1; $i<3; $i++) {
                    echo '<td id="files-'.$i.'">
                        <table id="tree-'.$i.'">
                            <thead>
                                <tr id="file-dir-'.$i.'">
                                    <th colspan="4">
                                        <select id="select-folder-'.$i.'"><option value="reset" selected="selected" label=" "></option>';
                    
                                        for ($ii=1; $ii<$total; $ii++) {
                                            if ( ( is_array($users[$_SESSION['login']]['svr']) && in_array($server[$ii]['ip'].':'.$server[$ii]['port'], $users[$_SESSION['login']]['svr'])) ||
                                               ( preg_match('/a/',$_SESSION['level']) && !$users[$_SESSION['login']]['gp'] )
                                               ) {
                    
                                                echo '<option value="'.$ii.'">'.($server[$ii]['fname']?$server[$ii]['fname']:'Server '.$ii).'</option>';

                                            }
                                        }

                                    echo '<option value="ftp-0" data-name="ftp-0" class="ftp-select">&#127758; FTP</option>';
                                if ( isset($users[$_SESSION['login']]['ftp']) ) {
                                    $ftpln=sizeof($users[$_SESSION['login']]['ftp']);
                                    for ($ii=0; $ii<$ftpln; $ii++) {
                                        echo '<option value="ftp-'.($ii+1).'" data-name="ftp-'.($ii+1).'" name="'.($users[$_SESSION['login']]['ftp'][$ii]['name']).'" class="ftp-select">&nbsp;&nbsp;&nbsp;&nbsp;'.($users[$_SESSION['login']]['ftp'][$ii]['name']).'</option>';
                                    }

                                } else { $ftpln=0; };
                            echo '</select>
                                    </th>
                    
                                </tr>
                                <tr>
                                    <th colspan="4" id="file-ctrl-'.$i.'">';
                                        if ($i==1) {
                                            echo '<span class="upload btn disable"><span class="fa fa-upload"></span>&nbsp;Upload</span>
                                                  <span class="new-folder btn disable">New Folder</span>
                                                  <span class="copy btn disable"><span class="fa fa-arrow-right"></span>&nbsp;Copy</span>';
                                        } else {
                                            echo '<span class="copy btn disable"><span class="fa fa-arrow-left"></span>&nbsp;Copy</span>
                                                  <span class="new-folder btn disable">New Folder</span>
                                                  <span class="upload btn disable"><span class="fa fa-upload"></span>&nbsp;Upload</span>';
                                        }
                                    echo '
                                <progress value="0" max="100"></progress></th>
                                </tr>
                            </thead>
                            <tbody id="table-'.$i.'">
                                
                            </tbody>
                        </table>
                    </td>';
            }
            ?>

        </tr>
    </table>
</div>

<script>
    var ftp = [];
    var ftp_i=[0,0];
    var ftp_s=['reset','reset'];
    ftp[0]={host: '',
            prefix: '',
            name:'',
            login:'',
            pw:''};
<?php
    if ($ftpln > 0) {
        for ( $i=0; $i<$ftpln; $i++ ) {
            echo 'ftp['.($i+1).']={host: "'.$users[$_SESSION['login']]['ftp'][$i]['host'].'",
                                   prefix: "'.$users[$_SESSION['login']]['ftp'][$i]['prefix'].'",
                                   name:"'.$users[$_SESSION['login']]['ftp'][$i]['name'].'",
                                   login:"'.$users[$_SESSION['login']]['ftp'][$i]['login'].'",
                                   pw:"'.isset($_SESSION['ftp'][$i]).'"};';
        }
    }
?>
    var sel=new Array(0,0,0);
    $('select').change(function() {
        var id  = $(this).attr('id').split('-').pop();
        var sid = $(this).val();
        var dir ='';
        fControl(id);
        if ( sid.match(/ftp-\d+/) ) {
            var ftpId   = $(this).find("option:selected").attr("data-name").split('-').pop();
            ftp_i[id]   = $(this).find("option:selected").attr("data-name").split('-').pop();
            var content =  '<table id="loginFTP" style="width:98%">'+
                                '<tr>'+
                                    '<td colspan="2"><input class="host" name="host" type="text" value="'+(ftp[ftp_i[id]].host)+'" placeholder="Host" title="host.com (no ftp://)"/></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td colspan="2"><input class="host" name="prefix" type="text" value="'+(ftp[ftp_i[id]].prefix)+'" placeholder="Prefix" title="host.com/Prefix"/></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td>Name:</td>'+
                                    '<td><input name="name" maxlength="20" type="text" title="Simple name to remember and save credentials" value="'+(ftp[ftp_i[id]].name)+'" placeholder="Optional"/></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td>Login:</td>'+
                                    '<td><input name="login" type="text" value="'+(ftp[ftp_i[id]].login)+'" /></td>'+
                                '</tr>'+
                                '<tr>'+
                                    '<td>Password:</td>'+
                                    '<td><input name="password" type="password" /></td>'+
                                '</tr>';
            if (ftp_i[id]>0) {

                content +=  '<tr>'+
                                '<td colspan="2" id="ftp-forget"><span class="ftp-forget">Forget</span></td>'+
                            '</tr>'
            } else { ftp_i[id]  = ftp.length;}

                content +=  '</table>';
            $(this).val(ftp_s[id]);
            if (ftp[ftp_i[id]] && ftp[ftp_i[id]].pw) {cFTP({'id':id,'dir':dir},true)} else {
                Sz.popup.open({header:'FTP Connection', content:content, css:'ftp'},{'id':id,'dir':dir},cFTP);
            }
            //Remove ftp
            $('#popup .ftp-forget').click(function() {
                $.ajax({
                    type: "POST",
                    url: 'php/users.db.php?'+(new Date()).getTime(),
                    data: {fct: 'ftp-del', id : (ftp_i[id]-1), token: Sz.token},
                    cache: false,
                    async: true
                }).done(function(data) {
                    Sz.popup.close();
                    $('select [data-name="ftp-'+ftp_i[id]+'"]').remove();
                    var nftp=[];
                    $('#file-dir-1 .ftp-select').each(function( index ) {
                        var ftpId=$(this).attr("data-name").split('-').pop();
                        nftp[index]={host: ftp[ftpId].host,
                                    prefix: ftp[ftpId].prefix,
                                    name:ftp[ftpId].name,
                                    login:ftp[ftpId].login,
                                    pw:ftp[ftpId].pw};
                        $(this).attr("data-name",'ftp-'+index);
                    }); 
                    ftp=nftp;
                    $('#file-dir-2 .ftp-select').each(function( index ) {$(this).attr("data-name",'ftp-'+index);});

                    $(this).val('reset');
                    $('#table-'+id).text('');
                    Sz.Response(data);
                })
            })
        }else if ( sid == 'reset' ) {
            $('#table-'+id).text('');
        } else {
            fOpen(id,sid,dir);
        }
        ftp_s[id]=$(this).val();
    });
    function cFTP(p,c) {
        if ( $('#version').hasClass('load') ) return false;
        $('#version').addClass('load');
        var id    = p.id;
        var dir   = p.dir;
        sel[id] = 0;
        fControl(id);
        if (!c) {
            var host   = $("#loginFTP [name='host']").val();
            var prefix = $("#loginFTP [name='prefix']").val();
            var name   = $("#loginFTP [name='name']").val();
            var login  = $("#loginFTP [name='login']").val();
            var pw     = $("#loginFTP [name='password']").val();
        } else {
            var host   = ftp[ftp_i[id]].host;
            var prefix = ftp[ftp_i[id]].prefix;
            var name   = ftp[ftp_i[id]].name;
            var login  = ftp[ftp_i[id]].login;
            var pw     = '';
            $('#select-folder-'+id).val('ftp-'+ftp_i[id]);
        }
        $.ajax({
            type: "POST",
            url: 'php/filemanager.ftp.php?'+(new Date()).getTime(),
            data: {'id': ftp_i[id]-1, 'host' : host, 'prefix' : prefix, 'dir': dir, 'name': name, 'login':login, 'pw': pw, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            if (data.slice(0,5) != 'Error') {
                $('#table-'+id).html(data);
                fControl();
                //Checkbox
                $('#table-'+id+' :checkbox').change(function() {
                    var id     = $(this).closest('tbody').attr('id').split('-').pop();
                    if($(this).is(':checked')){
                        sel[id]++;
                    } else {
                        sel[id]--;
                    }
                    fControl();
                });
                //root
                $('.refresh').click(function() {
                
                    if ( $('#version').hasClass('load') ) return false;
                    var id  = $(this).closest('tbody').attr('id').split('-').pop();
                    var sid = $('#select-folder-'+id).val();
                    var dir ='';
                    fControl(id);
                    if ( sid.match(/ftp-\d+/) ) {
                        cFTP({'id':id,'dir':dir},true);
                    } else if ( sid == 'reset' ) {
                        $('#table-'+id).text('');
                    } else {
                        fOpen(id,sid,dir);
                    }
                
                });
                //Open
                $('#table-'+id+' .data-file').click(function() {
                    var id     = $(this).closest('tbody').attr('id').split('-').pop();
                    var dir    = $(this).find('.file-name').attr("data-name");
                    cFTP({'id':id,'dir':dir},true);
                });
                //delete
			    $('#table-'+id+' .del-file').click(function() {
                    if ( $('#version').hasClass('load') ) return false;
                    var id     = $(this).closest('tbody').attr('id').split('-').pop();
                    var sid    = $('#select-folder-'+id).val();
                    var dir    = $(this).closest('tr').find('td .realdir').attr("data-name");
                    var content = '<p class="alert"><span class="fa fa-exclamation-triangle"></span>Permanently delete "<b>'+$(this).closest('tr').find('td .realdir').html()+'</b>"?</p>';
                    Sz.popup.open({header:'Delete File',content:content,css:'small'},{'id':id,'sid':sid,'dir':dir},dFTP);
                });
                //save
                if ( ftp[ftp_i[id]] ) {ftp[ftp_i[id]].pw = 1; $('#select-folder-'+id).val('ftp-'+ftp_i[id]);}
                if ( ftp[ftp_i[id]] && ftp[ftp_i[id]].host == host && ftp[ftp_i[id]].prefix == prefix && ftp[ftp_i[id]].name == name && ftp[ftp_i[id]].login == login ) return $('#version').removeClass('load');
                ftp[0]={host:'',
                        prefix:'',
                        name:'',
                        login:'',
                        pw:''};
                ftp[ftp_i[id]]={ host:host,
                                 prefix:prefix,
                                 name:name,
                                 login:login,
                                 pw:1 };
                $.ajax({
                    type: "POST",
                    url: 'php/users.db.php?'+(new Date()).getTime(),
                    data: {'fct': 'ftp', 'host' : host, 'prefix' : prefix, 'name': name, 'login':login, 'pw': pw, token: Sz.token},
                    cache: false,
                    async: true
                }).done(function(data) {
                    $('#version').removeClass('load');
                    if (!$('[name="'+name+'"]').length) {
                        try {
                            if (!name) {name=host+ftp_i[id]};
                            $('select').append('<option value="ftp-'+ftp_i[id]+'" data-name="ftp-'+ftp_i[id]+'" name="'+name+'" class="ftp-select">&nbsp;&nbsp;&nbsp;&nbsp;'+name+'</option>');
                            $('#select-folder-'+id).val('ftp-'+ftp_i[id]);
                        } catch (e) {};
                    }
                    if (data.slice(0,5) != 'Error') {
                        Sz.Response(data);
                    } else {
                        Sz.Response(data,1);
                    }
                })
            } else {
                ftp[0]={host:host,
                        prefix:prefix,
                        name:name,
                        login:login,
                        pw:''};
                $('#select-folder-'+id).val('reset');
                $('#table-'+id).text('');
                $('#version').removeClass('load');
                return Sz.Response(data,1);
            }
        })


    }

    function fControl(l) {
        for (var i=1; i<3; i++) {
            if ( sel[i] > 0 ) {
                $('#file-ctrl-'+i+' .copy.btn').removeClass('disable').addClass('red')
            } else {
                $('#file-ctrl-'+i+' .copy.btn').removeClass('red').addClass('disable');
            }
            if ( $('#select-folder-'+i).val() == 'reset' || l==i) {
                $('#file-ctrl-'+i+' .upload.btn').addClass('disable');
                $('#file-ctrl-'+i+' .new-folder.btn').removeClass('green').addClass('disable');
            } else {
                $('#file-ctrl-'+i+' .upload.btn').removeClass('disable');
                $('#file-ctrl-'+i+' .new-folder.btn').removeClass('disable').addClass('green');
            }
            if ( l || $('#select-folder-1').val() == 'reset' || $('#select-folder-2').val() == 'reset'
                   || (($('#select-folder-1').val() == $('#select-folder-2').val()) && ($('#table-1 td.root-name').attr("data-name") == $('#table-2 td.root-name').attr("data-name")))
                   ||  ($('#select-folder-1').val().match(/ftp-\d+/) && $('#select-folder-2').val().match(/ftp-\d+/)) ) {
                $('#file-ctrl-'+i+' .copy.btn').removeClass('red').addClass('disable');
            }
        }
    }

    function fOpen(id,sid,dir) {
        if ( $('#version').hasClass('load') ) return false;
        $('#version').addClass('load');
        sel[id] = 0;
        fControl(id);
        $.ajax({
            type: "POST",
            url: 'php/filemanager.td.php?'+(new Date()).getTime(),
            data: {sid : sid, dir: dir, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            if (data.slice(0,5) != 'Error') {
                $('#table-'+id).html(data);
            } else {
                $('#select-folder-'+id).val('reset');
                $('#table-'+id).text('');
                Sz.Response(data);
                return $('#version').removeClass('load');
            }
            fControl();
            //root
            $('.refresh').click(function() {
            
                if ( $('#version').hasClass('load') ) return false;
                var id  = $(this).closest('tbody').attr('id').split('-').pop();
                var sid = $('#select-folder-'+id).val();
                var dir ='';
                fControl(id);
                if ( sid.match(/ftp-\d+/) ) {
                    cFTP({'id':id,'dir':dir},true);
                } else if ( sid == 'reset' ) {
                    $('#table-'+id).text('');
                } else {
                    fOpen(id,sid,dir);
                }
            
            });            //Checkbox
            $('#table-'+id+' :checkbox').change(function() {
                var id     = $(this).closest('tbody').attr('id').split('-').pop();
                if($(this).is(':checked')){
                    sel[id]++;
                } else {
                    sel[id]--;
                }
                fControl();
            });
            //Open
            $('#table-'+id+' .data-file').click(function() {
                var id     = $(this).closest('tbody').attr('id').split('-').pop();
                var sid    = $('#select-folder-'+id).val();
                //var dir    = $(this).attr("data-name");
                var dir    = $(this).find('.file-name').attr("data-name");
                fOpen(id,sid,dir);
            });
            //delete
			$('#table-'+id+' .del-file').click(function() {
                if ( $('#version').hasClass('load') ) return false;
                var id     = $(this).closest('tbody').attr('id').split('-').pop();
                var sid    = $('#select-folder-'+id).val();
                var dir    = $(this).closest('tr').find('td .realdir').attr("data-name");
                var content = '<p class="alert"><span class="fa fa-exclamation-triangle"></span>Permanently delete "<b>'+$(this).closest('tr').find('td .realdir').html()+'</b>"?</p>';
                Sz.popup.open({header:'Delete File',content:content,css:'small'},{'id':id,'sid':sid,'dir':dir},fDel);
            });
            //Extract Files
			$('#table-'+id+' .unzip-file').click(function() {
                if ( $('#version').hasClass('load') ) return false;
                if ($(this).hasClass('disable')) return false;
                var id     = $(this).closest('tbody').attr('id').split('-').pop();
                var sid    = $('#select-folder-'+id).val();
                var dir    = $(this).closest('tr').find('td .realdir').attr("data-name");
                var root   = $('#table-'+(id)+' td.root-name').attr("data-name");
                var content = '<p class="alert"><span class="fa fa-exclamation-triangle"></span>Extract files from "<b>'+$(this).closest('tr').find('td .realdir').html()+'</b>" to "<b>'+(root?root:$('#table-'+id+' .root-name .file-name').html())+'</b>"?</p>';
                Sz.popup.open({header:'Delete File',content:content,css:'small'},{'id':id,'sid':sid,'dir':dir,'root':root},fUnzip);
            });
            //edit
			$('#table-'+id+' .edit-file').click(function() {
                if ( $('#version').hasClass('load') ) return false;
                if ($(this).hasClass('disable')) return false;
                var id      = $(this).closest('tbody').attr('id').split('-').pop();
                var sid     = $('#select-folder-'+id).val();
                var dir     = $(this).closest('tr').find('td .realdir').attr("data-name");
                var content = '<textarea spellcheck="false"></textarea>';
                var file    = $(this).closest('tr').find('td .realdir').html();
                $('#version').addClass('load');
                $.ajax({
                    type: "POST",
                    url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
                    data: {fct: 'edit', sid : sid, dir: dir, token: Sz.token},
                    cache: false,
                    async: true
                }).done(function(data) {
                    $('#version').removeClass('load');
                    if (data.slice(0,5) != 'Error') {
                        Sz.popup.open({header:'Edit: '+file,content:content,css:'large'},{'id':id,'sid':sid,'dir':dir},fSave);
                        $('#popup .popup-confirm').val('Save');
                        $('#popup textarea').text(data);
                    } else {
                        Sz.Response(data,1);
                    }
                });
            });
            //End
            $('#version').removeClass('load');
        });

    };
    function fSave(p) {
        $('#version').addClass('load');
        content=JSON.stringify($('#popup textarea').val());
        $.ajax({
            type: "POST",
            url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
            data: {fct: 'save', sid : p.sid, dir: p.dir, content:content, token: Sz.token},
            cache: false,
            async: true,
            contentType: "application/x-www-form-urlencoded;charset=ISO-8859-1"
        }).done(function(data) {
            $('#version').removeClass('load');
            if (data.slice(0,5) != 'Error') {
                Sz.Response(data)
            } else {
                Sz.Response(data,1);
            }
        });

    }
//Delete
    function fDel(p) {
        $('#version').addClass('load');
        $.ajax({
            type: "POST",
            url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
            data: {fct: 'rmFiles', sid : p.sid, dir: p.dir, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {

            $('#version').removeClass('load');
            if (data.slice(0,5) != 'Error') {
                Sz.Response(data)
                return fOpen(p.id,p.sid,$('#table-'+(p.id)+' td.root-name').attr("data-name"));
            } else {
                Sz.Response(data,1);
            }

        });

    }
//Delete ftp
    function dFTP(p) {
        $('#version').addClass('load');
        $.ajax({
            type: "POST",
            url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
            data: {fct: 'ftprmFiles', sid : p.sid, dir: p.dir, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {

            $('#version').removeClass('load');
            if (data.slice(0,5) != 'Error') {
                Sz.Response(data)
                return cFTP({'id':p.id,'dir':$('#table-'+(p.id)+' td.root-name').attr("data-name")},true);
            } else {
                Sz.Response(data,1);
            }

        });

    }
//Upload
	$('.upload').click(function() {
        if ( $('#version').hasClass('load') ) return false;
        var id     = $(this).closest('th').attr('id').split('-').pop();
        var sid    = $('#select-folder-'+id).val();
        if  ($(this).hasClass('disable') || sid=='reset') return false;
        var dir    = $('#table-'+(id)+' td.root-name').attr("data-name");
        var content = '<form id="uFile" enctype="multipart/form-data">'+
                        '<input name="file" type="file" />'+
                        '<input type="hidden" name="id" value="'+id+'">'+
                        '<input type="hidden" name="sid" value="'+sid+'">'+
                        '<input type="hidden" name="dir" value="'+dir+'">'+
                        '<input type="hidden" name="token" value="'+Sz.token+'">'+
                      '</form>&nbsp Max <?php echo min(ini_get('upload_max_filesize'),ini_get('post_max_size'));?>';
        Sz.popup.open({header:'Upload File',content:content,css:'small'},{'id':id,'sid':sid,'dir':dir},fUpload);
    });

    function fUpload(p) {
        var file = $('#uFile :file')[0].files[0];
        if (!file.name) return false;
        $('#version').addClass('load');
        var formData = new FormData($('#uFile')[0]);
        $('#file-ctrl-'+p.id+' progress').fadeTo(250,1);
        $.ajax({
            url: 'php/upload.php?'+(new Date()).getTime(),
            type: 'POST',
            xhr: function() {  // Custom XMLHttpRequest
                var myXhr = $.ajaxSettings.xhr();
                if(myXhr.upload){
                    myXhr.upload.addEventListener('progress',function(e){
                                                                if (e.lengthComputable) {
                                                                    $('#file-ctrl-'+p.id+' progress').attr({value:e.loaded,max:e.total});
                                                                }
                                                            }, false);
                }
                return myXhr;
            },
            //beforeSend: beforeSendHandler,
            success: function(data) {
                        $('#version').removeClass('load');
                        if (!data.slice(0,5) != 'Error') {
                            Sz.Response(data);
                            $('#file-ctrl-'+p.id+' progress').fadeTo(500,0);
                            if (!(p.sid).match(/ftp-\d+/)) {
                                return fOpen(p.id,p.sid,$('#table-'+(p.id)+' td.root-name').attr("data-name"));
                            } else {
                                return cFTP({'id':p.id,'dir':$('#table-'+(p.id)+' td.root-name').attr("data-name")},true);
                            }
                        } else {
                            Sz.Response(data,1);
                            $('#file-ctrl-'+p.id+' progress').fadeTo(500,0);
                        }
                    },
            error: function(data) {Sz.Response(data,1);$('#file-ctrl-'+p.id+' progress').fadeTo(500,0);},
            data: formData,
            cache: false,
            contentType: false,
            processData: false
        });
    }

//New Folder
    $('.new-folder').click (function(){
        if ( $('#version').hasClass('load') ) return false;
        var id  = $(this).closest('th').attr('id').split('-').pop();
        if ($('#newFolder-'+id).length || $(this).hasClass('disable')) return false;
        var Row =   '<tr class="folders new" id="new-tr-'+id+'">'+
                        '<td class="chk"></td>'+
                            '<td class="file"><span class="fa fa-folder"></span><span class="file-name"><input type="text" id="newFolder-'+id+'" name="newFolder"></span></td>'+
                                '<td class="file-ctrl">'+
                                    '<div><span>&nbsp;</span></div>'+
                                    '<div><span class="fa fa-times cancel-nf-'+id+'"></span></div>'+
                                    '<div><span class="fa fa-check valid enter-nf-'+id+'"></span></div>'+
                                '</td>'+
                                '<td></td>'+
                            '</tr>';

        $('table#tree-'+id+' > tbody > tr:first').after(Row);
        $('table#tree-'+id+' td').show();
        $('#newFolder-'+id).focus();
        //remove nf
        $('.cancel-nf-'+id).click(function(){
            $('#new-tr-'+id).remove();
        });
        //Create nf
        $('.enter-nf-'+id).click(function(){
            if ( $('#version').hasClass('load') ) return false;
            var id     = $(this).closest('tbody').attr('id').split('-').pop();
            var sid    = $('#select-folder-'+id).val();
            var dir    = $('#table-'+id+' td.root-name').attr("data-name");
            var nf     = $('#newFolder-'+id).val();
            if ( sid.match(/ftp-\d+/) ) {
                $.ajax({
                    type: "POST",
                    url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
                    data: {fct: 'ftpnewFolder', sid: sid, dir : dir, nf: nf, token: Sz.token},
                    cache: false,
                    async: true
                }).done(function(data) {
                
                    if (data.slice(0,5) != 'Error') {
                        Sz.Response('New Folder: '+nf)
                        cFTP({'id':id,'dir':dir},true);
                    } else {
                        Sz.Response(data,1);
                    }
                
                    $('#version').removeClass('load');
                });
            } else {
                $.ajax({
                    type: "POST",
                    url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
                    data: {fct: 'newFolder', sid: sid, dir : dir, nf: nf, token: Sz.token},
                    cache: false,
                    async: true
                }).done(function(data) {
                
                    if (data.slice(0,5) != 'Error') {
                        Sz.Response('New Folder: '+nf)
                        return fOpen(id,sid,dir);
                    } else {
                        Sz.Response(data,1);
                    }
                
                    $('#version').removeClass('load');
                });
            }
        });

    });
//Copy
    $('.copy').click(function(){
        if ( $('#version').hasClass('load') ) return false;
        var id  = $(this).closest('th').attr('id').split('-').pop();
        if ($(this).hasClass('disable')) return false;
        var id2 = 1;
        if (id == 1) {id2 = 2};
        var dir2   = $('#table-'+(id2)+' td.root-name').attr("data-name");
        var sid2   = $('#select-folder-'+id2).val();
        var selected = new Array();
        var sid    = $('#select-folder-'+id).val();
        var dir    = $('#table-'+(id)+' td.root-name').attr("data-name");
        $('#table-'+id+' input:checked').each(function() {
            var file     = $(this).closest('tr').find('.realdir').attr("data-name");
            var isFolder = $(this).closest('tr').hasClass("folders");
            selected.push({'file':file,'isFolder':isFolder});
        });
        if ( sid.match(/ftp-\d+/) ) {
            var content = '<p class="alert"><span class="fa fa-exclamation-triangle"></span>Copy '+sel[id]+(sel[id]>1?' files':' file')+' to "<b>'+(dir2?dir2:$('#table-'+id2+' .root-name .file-name').html())+'</b>"? </p>';
            Sz.popup.open({header:'Copy Files from FTP', content:content,css:'small'},{'sid':sid,'dir':dir,'id2':id2,'sid2':sid2,'dir2':dir2,'files':(JSON.stringify(selected))},ftpCopy2);
        } else if (sid2.match(/ftp-\d+/)) {
            var content = '<p class="alert"><span class="fa fa-exclamation-triangle"></span>Copy '+sel[id]+(sel[id]>1?' files':' file')+' to "<b>'+(dir2?dir2:$('#table-'+id2+' .root-name .file-name').html())+'</b>"? </p>';
            <?php if (extension_loaded('bz2')) { ?>
                if (sel[id] == 1 && !selected[0].isFolder) {
                    content+='<p title="Select to compress to bz2 format (ie: map.bsp.bz2)"><input type="checkbox" value="bz2" ">Compress file to bz2</p>';
                }
            <?php } ?>
            Sz.popup.open({header:'Copy Files to FTP', content:content,css:'small'},{'sid':sid,'dir':dir,'id2':id2,'sid2':sid2,'dir2':dir2,'files':(JSON.stringify(selected))},ftpCopy);
        } else {
            var content = '<p class="alert"><span class="fa fa-exclamation-triangle"></span>Copy '+sel[id]+(sel[id]>1?' files':' file')+' to "<b>'+(dir2?dir2:$('#table-'+id2+' .root-name .file-name').html())+'</b>"? </p>';
            Sz.popup.open({header:'Copy Files', content:content,css:'small'},{'sid':sid,'dir':dir,'id2':id2,'sid2':sid2,'dir2':dir2,'files':(JSON.stringify(selected))},fCopy);
        }
    });
//
    function fCopy(p) {
        $('#version').addClass('load');
        $.ajax({
            type: "POST",
            url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
            data: {fct: 'copy', sid: p.sid, dir: p.dir , id2: p.id2, sid2: p.sid2, dir2: p.dir2, files: p.files, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            $('#version').removeClass('load');
            Sz.Response(data);
            if (data.slice(0,5) != 'Error') {
                return fOpen(p.id2,p.sid2,p.dir2);
            }
        });
    }
    function ftpCopy(p) {
        $('#version').addClass('load');
        $.ajax({
            type: "POST",
            url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
            data: {fct: 'ftpcopy', sid: p.sid, dir: p.dir , id2: p.id2, sid2: p.sid2, dir2: p.dir2, files: p.files, bz2:$('#popup :checkbox').is(':checked'), token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            $('#version').removeClass('load');
            Sz.Response(data);
            if (data.slice(0,5) != 'Error') {
                return cFTP({'id':p.id2,'dir':p.dir2},true);
            }
        });
    }
    function ftpCopy2(p) {
        $('#version').addClass('load');
        $.ajax({
            type: "POST",
            url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
            data: {fct: 'ftpcopy2', sid: p.sid, dir: p.dir , id2: p.id2, sid2: p.sid2, dir2: p.dir2, files: p.files, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            $('#version').removeClass('load');
            Sz.Response(data);
            if (data.slice(0,5) != 'Error') {
                return fOpen(p.id2,p.sid2,p.dir2);
            }
        });
    }
//Unzip
    function fUnzip(p) {
        $('#version').addClass('load');
        $.ajax({
            type: "POST",
            url: 'php/filemanager.fct.php?'+(new Date()).getTime(),
            data: {fct: 'unzip', sid: p.sid, dir: p.dir, root: p.root, token: Sz.token},
            cache: false,
            async: true
        }).done(function(data) {
            $('#version').removeClass('load');
            Sz.Response(data);
            fOpen(p.id,p.sid,p.root);
        });
    }

</script>