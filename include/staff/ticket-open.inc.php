<?php
if(!defined('OSTSCPINC') || !$thisstaff || !$thisstaff->canCreateTickets()) die('Access Denied');
$info=array();
$info=Format::htmlchars(($errors && $_POST)?$_POST:$info);

if (!$info['topicId'])
    $info['topicId'] = $cfg->getDefaultTopicId();

$form = null;
if ($info['topicId'] && ($topic=Topic::lookup($info['topicId']))) {
    $form = $topic->getForm();
    if ($_POST && $form) {
        $form = $form->instanciate();
        $form->isValid();
    }
}

if ($_POST)
    $info['duedate'] = Format::date($cfg->getDateFormat(),
       strtotime($info['duedate']));
?>
<form action="tickets.php?a=open" method="post" id="save"  enctype="multipart/form-data">
 <?php csrf_token(); ?>
 <input type="hidden" name="do" value="create">
 <input type="hidden" name="a" value="open">
 <h2><?php echo __('Open a New Ticket');?></h2>
 <table class="form_table fixed" width="940" border="0" cellspacing="0" cellpadding="2">
    <thead>
    <!-- This looks empty - but beware, with fixed table layout, the user
         agent will usually only consult the cells in the first row to
         construct the column widths of the entire toable. Therefore, the
         first row needs to have two cells -->
        <tr><td></td><td></td></tr>
        <tr>
            <th colspan="2">
                <h4><?php echo __('New Ticket');?></h4>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('User Information'); ?></strong>: </em>
            </th>
        </tr>
        <?php
        if ($user) { ?>
        <tr><td><?php echo __('User'); ?>:</td><td>
            <div id="user-info">
                <input type="hidden" name="uid" id="uid" value="<?php echo $user->getId(); ?>" />
            <a href="#" onclick="javascript:
                $.userLookup('ajax.php/users/<?php echo $user->getId(); ?>/edit',
                        function (user) {
                            $('#user-name').text(user.name);
                            $('#user-email').text(user.email);
                        });
                return false;
                "><i class="icon-user"></i>
                <span id="user-name"><?php echo Format::htmlchars($user->getName()); ?></span>
                &lt;<span id="user-email"><?php echo $user->getEmail(); ?></span>&gt;
                </a>
                <a class="action-button" style="overflow:inherit" href="#"
                    onclick="javascript:
                        $.userLookup('ajax.php/users/select/'+$('input#uid').val(),
                            function(user) {
                                $('input#uid').val(user.id);
                                $('#user-name').text(user.name);
                                $('#user-email').text('<'+user.email+'>');
                        });
                        return false;
                    "><i class="icon-edit"></i> <?php echo __('Change'); ?></a>
            </div>
        </td></tr>
        <?php
        } else { //Fallback: Just ask for email and name
            ?>
        <tr>
            <td width="160" class="required"> <?php echo __('Email Address'); ?>: </td>
            <td>
                <span style="display:inline-block;">
                    <input type="text" size=45 name="email" id="user-email"
                        autocomplete="off" autocorrect="off" value="<?php echo $info['email']; ?>" /> </span>
                <font class="error">* <?php echo $errors['email']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160" class="required"> <?php echo __('Full Name'); ?>: </td>
            <td>
                <span style="display:inline-block;">
                    <input type="text" size=45 name="name" id="user-name" value="<?php echo $info['name']; ?>" /> </span>
                <font class="error">* <?php echo $errors['name']; ?></font>
            </td>
        </tr>
        <?php
        } ?>

        <?php
        if($cfg->notifyONNewStaffTicket()) {  ?>
        <tr>
            <td width="160"><?php echo __('Ticket Notice'); ?>:</td>
            <td>
            <input type="checkbox" name="alertuser" <?php echo (!$errors || $info['alertuser'])? 'checked="checked"': ''; ?>><?php
                echo __('Send alert to user.'); ?>
            </td>
        </tr>
        <?php
        } ?>
    </tbody>
    <tbody>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Ticket Information and Options');?></strong>:</em>
            </th>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo __('Ticket Source');?>:
            </td>
            <td>
                <select name="source">
                    <option value="Phone" selected="selected"><?php echo __('Phone'); ?></option>
                    <option value="Email" <?php echo ($info['source']=='Email')?'selected="selected"':''; ?>><?php echo __('Email'); ?></option>
                    <option value="Other" <?php echo ($info['source']=='Other')?'selected="selected"':''; ?>><?php echo __('Other'); ?></option>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['source']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160" class="required">
                <?php echo __('Help Topic'); ?>:
            </td>
            <td>
                <select name="topicId" onchange="javascript:
                        var data = $(':input[name]', '#dynamic-form').serialize();
                        $.ajax(
                          'ajax.php/form/help-topic/' + this.value,
                          {
                            data: data,
                            dataType: 'json',
                            success: function(json) {
                              $('#dynamic-form').empty().append(json.html);
                              $(document.head).append(json.media);
                            }
                          });">
                    <?php
                    if ($topics=Topic::getHelpTopics()) {
                        if (count($topics) == 1)
                            $selected = 'selected="selected"';
                        else { ?>
                        <option value="" selected >&mdash; <?php echo __('Select Help Topic'); ?> &mdash;</option>
<?php                   }
                        foreach($topics as $id =>$name) {
                            echo sprintf('<option value="%d" %s %s>%s</option>',
                                $id, ($info['topicId']==$id)?'selected="selected"':'',
                                $selected, $name);
                        }
                        if (count($topics) == 1 && !$form) {
                            if (($T = Topic::lookup($id)))
                                $form =  $T->getForm();
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error"><b>*</b>&nbsp;<?php echo $errors['topicId']; ?></font>
            </td>
        </tr>
        <tr>
            <td width="160">
                <?php echo __('Department'); ?>:
            </td>
            <td>
                <select name="deptId">
                    <option value="" selected >&mdash; <?php echo __('Select Department'); ?>&mdash;</option>
                    <?php
                    if($depts=Dept::getDepartments()) {
                        foreach($depts as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['deptId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error"><?php echo $errors['deptId']; ?></font>
            </td>
        </tr>

         <tr>
            <td width="160">
                <?php echo __('SLA Plan');?>:
            </td>
            <td>
                <select name="slaId">
                    <option value="0" selected="selected" >&mdash; <?php echo __('System Default');?> &mdash;</option>
                    <?php
                    if($slas=SLA::getSLAs()) {
                        foreach($slas as $id =>$name) {
                            echo sprintf('<option value="%d" %s>%s</option>',
                                    $id, ($info['slaId']==$id)?'selected="selected"':'',$name);
                        }
                    }
                    ?>
                </select>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['slaId']; ?></font>
            </td>
         </tr>

         <tr>
            <td width="160">
                <?php echo __('Due Date');?>:
            </td>
            <td>
                <input class="dp" id="duedate" name="duedate" value="<?php echo Format::htmlchars($info['duedate']); ?>" size="12" autocomplete=OFF>
                &nbsp;&nbsp;
                <?php
                $min=$hr=null;
                if($info['time'])
                    list($hr, $min)=explode(':', $info['time']);

                echo Misc::timeDropdown($hr, $min, 'time');
                ?>
                &nbsp;<font class="error">&nbsp;<?php echo $errors['duedate']; ?> &nbsp; <?php echo $errors['time']; ?></font>
                <em><?php echo __('Time is based on your time zone');?> (GMT <?php echo $thisstaff->getTZoffset(); ?>)</em>
            </td>
        </tr>

        <?php
        if($thisstaff->canAssignTickets()) { ?>
        <tr>
            <td width="160"><?php echo __('Assign To');?>:</td>
            <td>
                <select id="assignId" name="assignId">
                    <option value="0" selected="selected">&mdash; <?php echo __('Select an Agent OR a Team');?> &mdash;</option>
                    <?php
                    if(($users=Staff::getAvailableStaffMembers())) {
                        echo '<OPTGROUP label="'.sprintf(__('Agents (%d)'), count($users)).'">';
                        foreach($users as $id => $name) {
                            $k="s$id";
                            echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                        }
                        echo '</OPTGROUP>';
                    }

                    if(($teams=Team::getActiveTeams())) {
                        echo '<OPTGROUP label="'.sprintf(__('Teams (%d)'), count($teams)).'">';
                        foreach($teams as $id => $name) {
                            $k="t$id";
                            echo sprintf('<option value="%s" %s>%s</option>',
                                        $k,(($info['assignId']==$k)?'selected="selected"':''),$name);
                        }
                        echo '</OPTGROUP>';
                    }
                    ?>
                </select>&nbsp;<span class='error'>&nbsp;<?php echo $errors['assignId']; ?></span>
            </td>
        </tr>
        <?php } ?>
        </tbody>
        <tbody id="dynamic-form">
        <?php
            if ($form) {
                print $form->getForm()->getMedia();
                include(STAFFINC_DIR .  'templates/dynamic-form.tmpl.php');
            }
        ?>
        </tbody>
        <tbody> <?php
        $tform = TicketForm::getInstance();
        if ($_POST && !$tform->errors())
            $tform->isValidForStaff();
        $tform->render(true);
        ?>
        </tbody>
        <tbody>
        <?php
        //is the user allowed to post replies??
        if($thisstaff->canPostReply()) { ?>
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Response');?></strong>: <?php echo __('Optional response to the above issue.');?></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
            <?php
            if(($cannedResponses=Canned::getCannedResponses())) {
                ?>
                <div style="margin-top:0.3em;margin-bottom:0.5em">
                    <?php echo __('Canned Response');?>:&nbsp;
                    <select id="cannedResp" name="cannedResp">
                        <option value="0" selected="selected">&mdash; <?php echo __('Select a canned response');?> &mdash;</option>
                        <?php
                        foreach($cannedResponses as $id =>$title) {
                            echo sprintf('<option value="%d">%s</option>',$id,$title);
                        }
                        ?>
                    </select>
                    &nbsp;&nbsp;&nbsp;
                    <label><input type='checkbox' value='1' name="append" id="append" checked="checked"><?php echo __('Append');?></label>
                </div>
            <?php
            }
                $signature = '';
                if ($thisstaff->getDefaultSignatureType() == 'mine')
                    $signature = $thisstaff->getSignature(); ?>
                <textarea class="richtext ifhtml draft draft-delete"
                    data-draft-namespace="ticket.staff.response"
                    data-signature="<?php
                        echo Format::htmlchars(Format::viewableImages($signature)); ?>"
                    data-signature-field="signature" data-dept-field="deptId"
                    placeholder="<?php echo __('Initial response for the ticket'); ?>"
                    name="response" id="response" cols="21" rows="8"
                    style="width:80%;"><?php echo $info['response']; ?></textarea>
                    <div class="attachments">
<?php
print $response_form->getField('attachments')->render();
?>
                    </div>

                <table border="0" cellspacing="0" cellpadding="2" width="100%">
            <tr>
                <td width="100"><?php echo __('Ticket Status');?>:</td>
                <td>
                    <select name="statusId">
                    <?php
                    $statusId = $info['statusId'] ?: $cfg->getDefaultTicketStatusId();
                    $states = array('open');
                    if ($thisstaff->canCloseTickets())
                        $states = array_merge($states, array('closed'));
                    foreach (TicketStatusList::getStatuses(
                                array('states' => $states)) as $s) {
                        if (!$s->isEnabled()) continue;
                        $selected = ($statusId == $s->getId());
                        echo sprintf('<option value="%d" %s>%s</option>',
                                $s->getId(),
                                $selected
                                 ? 'selected="selected"' : '',
                                __($s->getName()));
                    }
                    ?>
                    </select>
                </td>
            </tr>
             <tr>
                <td width="100"><?php echo __('Signature');?>:</td>
                <td>
                    <?php
                    $info['signature']=$info['signature']?$info['signature']:$thisstaff->getDefaultSignatureType();
                    ?>
                    <label><input type="radio" name="signature" value="none" checked="checked"> <?php echo __('None');?></label>
                    <?php
                    if($thisstaff->getSignature()) { ?>
                        <label><input type="radio" name="signature" value="mine"
                            <?php echo ($info['signature']=='mine')?'checked="checked"':''; ?>> <?php echo __('My signature');?></label>
                    <?php
                    } ?>
                    <label><input type="radio" name="signature" value="dept"
                        <?php echo ($info['signature']=='dept')?'checked="checked"':''; ?>> <?php echo sprintf(__('Department Signature (%s)'), __('if set')); ?></label>
                </td>
             </tr>
            </table>
            </td>
        </tr>
        <?php
        } //end canPostReply
        ?>
        <!-- PCDEPOT Ticket specific information. Added to internal note as text. -->
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Información del equipo');?></strong>
                <font class="error">&nbsp;<?php echo $errors['pcdepot']; ?></font></em>
            </th>
        </tr>
        <tr>
            <td colspan="2">
            <table border="0" cellspacing="0" cellpadding="2" width="100%">
                <tr>
                    <td width="100"><?php echo __('Tipo de equipo');?>:</td>
                    <td>
                        <select name="pctype">
                            <option value="desktop" selected="selected"><?php echo __('Desktop');?></option>
                            <option value="laptop" <?php echo ($info['pctype']=='laptop')?'selected="selected"':'';?>><?php echo __('Laptop');?></option>
                            <option value="tablet" <?php echo ($info['pctype']=='tablet')?'selected="selected"':'';?>><?php echo __('Tablet');?></option>
                            <option value="printer" <?php echo ($info['pctype']=='printer')?'selected="selected"':'';?>><?php echo __('Impresora');?></option>
                            <option value="mac"<?php echo ($info['pctype']=='mac')?'selected="selected"':'';?>><?php echo __('Apple Computer');?></option>
                            <option value="other"<?php echo ($info['pctype']=='other')?'selected="selected"':'';?>><?php echo __('Other');?></option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td width="100"><?php echo __('Servicios que necesita');?>:</td>
                    <td>
                        <select name="service[]" id="servicetype" multiple="multiple">
                            <option value="diagnostic" selected="selected"><?php echo __('Diagnóstico');?></option>
                            <option value="mhardware" 
                                <?php echo ($info['service']=='mhardware')?'selected="selected"':'';?>><?php echo __('Mantenimiento de hardware');?></option>
                            <option value="msoftware" 
                                <?php echo ($info['service']=='msoftware')?'selected="selected"':'';?>><?php echo __('Limpieza de software');?></option>
                            <option value="price" 
                                <?php echo ($info['service']=='price')?'selected="selected"':'';?>><?php echo __('Cotización');?></option>
                            <option value="format" 
                                <?php echo ($info['service']=='format')?'selected="selected"':'';?>><?php echo __('Formato');?></option>
                            <option value="install" 
                                <?php echo ($info['service']=='install')?'selected="selected"':'';?>><?php echo __('Instalación de software');?></option>
                            <option value="backup" 
                                <?php echo ($info['service']=='backup')?'selected="selected"':'';?>><?php echo __('Respaldo de información');?></option>
                        </select>
                        <script type="text/javascript">
                            var backup_count;
                            var format_count;
                            var install_count;
                            function show_forms() {
                                if (backup_count) {
                                    $("#backup_info").show();
                                    outlook_enable();
                                }
                                else {
                                    $("#backup_info").hide();
                                    $("#outlook_info").hide();
                                }
                                if (format_count)
                                    $("#format_info").show();
                                else
                                    $("#format_info").hide();
                                if (install_count)
                                    $("#install_info").show();
                                else
                                    $("#install_info").hide();
                            }
                            function forms_update() {
                                backup_count = format_count = install_count = false;
                                $("#servicetype :selected").each(function(i, selected) {
                                    if ($(selected).val() == "format") {
                                        backup_count = true;
                                        format_count = true;
                                        install_count = true;
                                    }
                                    if ($(selected).val() == "backup") {
                                        backup_count = true;
                                    }
                                    if ($(selected).val() == "install") {
                                        install_count = true;
                                    }
                                });
                                show_forms();
                            }
                            $("#servicetype").change(forms_update);
                            $(document).ready(forms_update);
                        </script>
                    </td>
                <tr>
                    <td width="100"><?php echo __('¿El equipo enciende?');?></td>
                    <td>
                        <label><input type="radio" name="turn" value="yes" checked="checked"><?php echo __('Yes');?></label><br>
                        <label><input type="radio" name="turn" value="noboot"
                            <?php echo ($info['turn']=='noboot')?'checked="checked"':'';?>><?php echo __('Prende pero no arranca sistema');?></label><br>
                        <label><input type="radio" name="turn" value="no"
                            <?php echo ($info['turn']=='no')?'checked="checked"':'';?>><?php echo __('No');?></label>
                    </td>
                </tr>
                <tr>
                    <td width="100"><?php echo __('¿Cuáles elementos trae el equipo?');?></td>
                    <td>
                        <label><input type="checkbox" name="adapter" value="yes"
                            <?php echo ($info['adapter']=='yes')?'checked="checked"':'';?>><?php echo __('Adaptador de corriente');?></label><br>
                        <label><input type="checkbox" name="battery" value="yes"
                            <?php echo ($info['battery']=='yes')?'checked="checked"':'';?>><?php echo __('Batería');?></label><br>
                        <label><input type="checkbox" name="usb" value="yes"
                            <?php echo ($info['usb']=='yes')?'checked="checked"':'';?>><?php echo __('Cable USB');?></label><br>
                        <label><input type="checkbox" name="mouse" value="yes"
                            <?php echo ($info['mouse']=='yes')?'checked="checked"':'';?>><?php echo __('Ratón');?></label><br>
                        <label><input type="checkbox" name="keyboard" value="yes"
                            <?php echo ($info['keyboard']=='yes')?'checked="checked"':'';?>><?php echo __('Teclado');?></label><br>
                        <label><input type="checkbox" name="bag" value="yes"
                            <?php echo ($info['bag']=='yes')?'checked="checked"':'';?>><?php echo __('Funda');?></label><br>
                        <label><input type="checkbox" name="manual" value="yes"
                            <?php echo ($info['manual']=='yes')?'checked="checked"':'';?>><?php echo __('Manuales y CD\'s');?></label><br>
                        <label><input type="checkbox" name="hdd" value="yes"
                            <?php echo ($info['hdd']=='yes')?'checked="checked"':'';?>><?php echo __('Disco Duro');?></label><br>
                        <label><input type="checkbox" name="ram" value="yes"
                            <?php echo ($info['ram']=='yes')?'checked="checked"':'';?>><?php echo __('Memoria RAM');?></label><br>
                        <label><input type="checkbox" name="other" value="yes"
                            <?php echo ($info['other']=='yes')?'checked="checked"':'';?>><?php echo __('Other');?>:&nbsp;</label>
                        <input type="text" name="other_obj" value="<?php echo $info['other_obj'];?>">
                    </td>
                </tr>
                <tr>
                    <td width="100"><?php echo __('Contraseña del equipo');?>:</td>
                    <td>
                        <input type="text" name="password" value="<?php echo $info['password'];?>">
                    </td>
                </tr>
                <tr id="backup_info">
                    <td width="100"><?php echo __('¿Que información se respaldará?');?></td>
                    <td>
                        <label><input type="checkbox" name="home" value="yes"
                            <?php echo ($info['home']=='yes')?'checked="checked"':'';?>><?php echo __('Carpeta de usuario');?></label><br>
                        <label><input type="checkbox" name="outlook" value="yes" id="outlook"
                            <?php echo ($info['outlook']=='yes')?'checked="checked"':'';?>><?php echo __('Outlook');?></label><br>
                        <script type="text/javascript">
                             function outlook_enable() {
                                 if ($("#outlook").prop("checked")) {
                                     $("#outlook_info").show();
                                 }
                                 else {
                                     $("#outlook_info").hide();
                                 }
                             }
                             $("#outlook").change(outlook_enable);
                        </script>
                        <label><input type="checkbox" name="backup" value="yes"
                            <?php echo ($info['backup']=='yes')?'checked="checked"':'';?>><?php echo __('Other');?></label>
                        <input type="text" name="backup_obj" value="<?php echo $info['backup_obj'];?>">
                    </td>
                </tr>
                <tr id="outlook_info">
                    <td width="100"><?php echo __('Información de Outlook');?></td>
                    <td id="acc_list">
                        <div id="acc_info">
                            <?php echo __('Cuenta de correo');?> 1:&nbsp;<input type="text" class="outlook_email" name="account[]"><br>
                            <?php echo __('Contraseña');?> 1:&nbsp;<input type="text" class="outlook_pass" name="acc_pass[]"><br>
                        </div>
                        <a href="#" id="acc_add" onclick="return add_account();"><?php echo __('Añadir cuenta');?></a>
                        <script type="text/javascript">
                            var count = 1;
                            function add_account( email, pass ) {
                                email = email || "";
                                pass = pass || "";
                                var element = $("#acc_info").clone();
                                element.removeAttr("id");
                                element.appendTo("#acc_list");
                                element.html(element.html().replace("<?php echo __('Cuenta de correo');?>" + " " + 1, 
                                    "<?php echo __('Cuenta de correo');?>" + " " + (count + 1)));
                                element.html(element.html().replace("<?php echo __('Contraseña');?>" + " " + 1, 
                                    "<?php echo __('Contraseña');?>" + " " + (count + 1)));
                                count++;
                                $(element).children(".outlook_email").val(email);
                                $(element).children(".outlook_pass").val(pass);
                                $("#acc_add").appendTo("#acc_list");
                                return false;
                            }
                            $("#acc_list .outlook_email").val("<?php echo $info['account'][0];?>");
                            $("#acc_list .outlook_pass").val("<?php echo $info['acc_pass'][0];?>");
                            <?php
                                $n = count($info['account']);
                                for ($i = 1; $i < $n; $i++) {
                                    echo "add_account(\"{$info['account'][$i]}\",\"{$info['acc_pass'][$i]}\");";
                                }
                            ?>
                        </script>
                </tr>
                <tr id="format_info">
                    <td width="100"><?php echo __('Información de Formateo')?></td>
                    <td>
                        <?php echo __('Versión del sistema');?>:&nbsp;
                        <select name="ostype">
                            <option value="win7" selected="selected"><?php echo __('Windows 7');?></option>
                            <option value="win8"
                                <?php echo ($info['ostype']=='win8')?'selected="selected"':'';?>><?php echo __('Windows 8');?></option>
                            <option value="win81"
                                <?php echo ($info['ostype']=='win81')?'selected="selected"':'';?>><?php echo __('Windows 8.1');?></option>
                            <option value="osx"
                                <?php echo ($info['ostype']=='osx')?'selected="selected"':'';?>><?php echo __('OS X');?></option>
                            <option value="linux"
                                <?php echo ($info['ostype']=='linux')?'selected="selected"':'';?>><?php echo __('Linux')?></option>
                        </select>
                        <br>
                        <?php echo ('Idioma del sistema');?>:&nbsp;
                        <input type="text" name="oslang" value="<?php echo $info['oslang'];?>">
                    </td>
                </tr>
                <tr id="install_info">
                    <td width="100"><?php echo __('Programas a instalar');?>:</td>
                    <td>
                        <label><input type="checkbox" name="utility" value="yes" checked="checked"><?php echo __('Utilerias');?></label><br>
                        <label><input type="checkbox" name="norton" value="yes"
                            <?php echo ($info['norton']=='yes')?'checked="checked"':'';?>><?php echo __('Norton')?></label><br>
                        <label><input type="checkbox" name="kaspersky" value="yes"
                            <?php echo ($info['kaspersky']=='yes')?'checked="checked"':'';?>><?php echo __('Kaspersky')?></label><br>
                        <label><input type="checkbox" name="autocad" value="yes"
                            <?php echo ($info['autocad']=='yes')?'checked="checked"':'';?>><?php echo __('AutoCAD')?></label><br>
                        <label><input type="checkbox" name="corel" value="yes"
                            <?php echo ($info['corel']=='yes')?'checked="checked"':'';?>><?php echo __('Corel');?></label><br>
                        <label><input type="checkbox" name="adobe_cc" value="yes"
                            <?php echo ($info['adobe_cc']=='yes')?'checked="checked"':'';?>><?php echo __('Adobe CS6/CC');?></label><br>
                        <label><input type="checkbox" name="contpaqi" value="yes"
                            <?php echo ($info['contpaqi']=='yes')?'checked="checked"':'';?>><?php echo __('CONTPAQi');?></label><br>
                        <label><input type="checkbox" name="sother" value="yes"
                            <?php echo ($info['sother']=='yes')?'checked="checked"':'';?>><?php echo __('Other');?>:&nbsp;</label>
                        <input type="text" name="sother_obj" value="<?php echo $info['sother_obj'];?>">
                    </td>
                </tr>
            </table>
            </td>
        </tr>
        <!-- End specific ticket information -->
        <tr>
            <th colspan="2">
                <em><strong><?php echo __('Internal Note');?></strong>
                <font class="error">&nbsp;<?php echo $errors['note']; ?></font></em>
            </th>
        </tr>
        <tr>
            <td colspan=2>
                <textarea class="richtext ifhtml draft draft-delete"
                    placeholder="<?php echo __('Optional internal note (recommended on assignment)'); ?>"
                    data-draft-namespace="ticket.staff.note" name="note"
                    cols="21" rows="6" style="width:80%;"
                    ><?php echo $info['note']; ?></textarea>
            </td>
        </tr>
    </tbody>
</table>
<p style="text-align:center;">
    <input type="submit" name="submit" value="<?php echo _P('action-button', 'Open');?>">
    <input type="reset"  name="reset"  value="<?php echo __('Reset');?>">
    <input type="button" name="cancel" value="<?php echo __('Cancel');?>" onclick="javascript:
        $('.richtext').each(function() {
            var redactor = $(this).data('redactor');
            if (redactor && redactor.opts.draftDelete)
                redactor.deleteDraft();
        });
        window.location.href='tickets.php';
    ">
</p>
</form>
<script type="text/javascript">
$(function() {
    $('input#user-email').typeahead({
        source: function (typeahead, query) {
            $.ajax({
                url: "ajax.php/users?q="+query,
                dataType: 'json',
                success: function (data) {
                    typeahead.process(data);
                }
            });
        },
        onselect: function (obj) {
            $('#uid').val(obj.id);
            $('#user-name').val(obj.name);
            $('#user-email').val(obj.email);
        },
        property: "/bin/true"
    });

   <?php
    // Popup user lookup on the initial page load (not post) if we don't have a
    // user selected
    if (!$_POST && !$user) {?>
    setTimeout(function() {
      $.userLookup('ajax.php/users/lookup/form', function (user) {
        window.location.href = window.location.href+'&uid='+user.id;
      });
    }, 100);
    <?php
    } ?>
});
</script>

