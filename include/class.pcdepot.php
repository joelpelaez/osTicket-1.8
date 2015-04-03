<?php
class Pcdepot {

    static function isValidEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL)
            && preg_match('/@.+\./', $email);
    }

    public static function check_service($vars, $service) {
        if (!isset($vars))
            return false;

        foreach ($vars as $st) {
            if ($st == $service)
                return true;
        }

        return false;
    }

    public static function parse(&$vars) {
        global $cfg;
        // Check computer type
        switch ($vars['pctype']) {
            case 'desktop':
                $type = __('Desktop');
                break;
            case 'laptop':
                $type = __('Laptop');
                break;
            case 'tablet':
                $type = __('Tablet');
                break;
            case 'printer':
                $type = __('Impresora');
                break;
            case 'mac':
                $type = __('Apple Computer');
                break;
            case 'other':
                $type = __('Other');
                break;
        }
        $pctype = __('Tipo de equipo') . ': ' . $type;

        // Add if the computer can turn on
        if ($vars['turn'] == 'yes')
            $turn = __('El equipo enciende');
        elseif ($vars['turn'] == 'noboot')
            $turn = __('El equipo enciende pero no arranca sistema');
        else  
            $turn = __('El equipo no enciende');

        // Check service type and additional info
        $backup = $format = $install = false;
        $extra = '';
        $sname = __('Servicios') . ':';
        $stypes = $vars['service'];
        foreach ($stypes as $st) {
            if ($st == 'diagnostic')
                $sname .= "\n  - " . __('Diagnóstico');
            else if ($st == 'mhardware')
                $sname .= "\n  - " . __('Mantenimiento de Hardware');
            else if ($st == 'msoftware')
                $sname .= "\n  - " . __('Limpieza de Software');
            else if ($st == 'price')
                $sname .= "\n  - " . __('Cotización');
            else if ($st == 'format') {
                $sname .= "\n  - " . __('Formateo');
                $backup = $format = $install = true;
            }
            else if ($st == 'install') {
                $sname .= "\n  - " . __('Instalación de software');
                $install = true;
            }
            else if ($st == 'backup') {
                $sname .= "\n  - " . __('Respaldo de información');
                $backup = true;
            }
        }

        // Check items
        $item = __('El equipo trae los siguientes objetos');
        if ($vars['adapter'] == 'yes')
            $item = $item . "\n  - " . __('Adaptador de corriente');
        if ($vars['battery'] == 'yes')
            $item = $item . "\n  - " . __('Batería');
        if ($vars['usb'] == 'yes')
            $item = $item . "\n  - " . __('Cable USB');
        if ($vars['mouse'] == 'yes')
            $item = $item . "\n  - " . __('Ratón');
        if ($vars['keyboard'] == 'yes')
            $item = $item . "\n  - " . __('Teclado');
        if ($vars['bag'] == 'yes')
            $item = $item . "\n  - " . __('Funda');
        if ($vars['manual'] == 'yes')
            $item = $item . "\n  - " . __('Manuales y CD\'s');
        if ($vars['hdd'] == 'yes')
            $item = $item . "\n  - " . __('Disco Duro');
        if ($vars['ram'] == 'yes')
            $item = $item . "\n  - " . __('Memoria RAM');
        if ($vars['other'] == 'yes' && $vars['other_obj'] != '')
            $item = $item . "\n  - " . $vars['other_obj']; 

        // Check if has password
        if ($vars['password'] != '')
            $pass = __('La contraseña es') . ': ' . $vars['password'];
        else
            $pass = __('No tiene contraseña');

        // Check backup info
        if ($backup) {
            $extra .= __('Respaldar') . ':';

            if ($vars['home'] == 'yes')
                $extra .= "\n  - " . __('Carpeta de usuario');
            if ($vars['outlook'] == 'yes')
                $extra .= "\n  - " . __('Outlook');
            if ($vars['backup'] == 'yes' && $vars['backup_obj'] != '')
                $extra .= "\n  - " . $vars['backup_obj'];

            $extra .= "\n\n";
        }

        // Check outlook
        if ($vars['outlook'] == 'yes') {
            $outlook_exist = false;
            $outlook .= __('Cuentas de Outlook a respaldar') . ':';

            for ($i = 0; $i < count($vars['account']); $i++) {
                if (Pcdepot::isValidEmail($vars['account'][$i])) {
                    $outlook_exist = true;

                    $outlook .= "\n  - " . __('Cuenta') . ': ' . $vars['account'][$i];

                    if ($vars['acc_pass'][$i] != '')
                        $outlook .= "\n  - " . __('Contraseña') . ': ' . $vars['acc_pass'][$i];
                    else
                        $outlook .= "\n  - " . __('Sin contraseña o no proporcionada');

                    $outlook .= "\n";
                }
            }

            if ($outlook_exist) {
                $outlook .= "\n";
                $extra .= $outlook;
            }
        }

        // Check format
        if ($format) {
            $extra .= __('Sistema Operativo a instalar') . ': ';

            switch ($vars['ostype']) {
                case 'win7':
                    $extra .= __('Windows 7');
                    break;
                case 'win8':
                    $extra .= __('Windows 8');
                    break;
                case 'win81':
                    $extra .= __('Windows 8.1');
                    break;
                case 'osx':
                    $extra .= __('OS X');
                    break;
                case 'linux':
                    $extra .= __('Linux');
            }

            if ($vars['oslang'] != '')
                $extra .= ' ' . __('en') . ' ' . $vars['oslang'];

            $extra .= "\n\n";
        }

        // Check programs to install
        if ($install) {
            $extra .= __('Programas a instalar') . ':';

            if ($vars['utility'] == 'yes')
                $extra .= "\n  - " . __('Utilidades');
            if ($vars['norton'] == 'yes')
                $extra .= "\n  - " . __('Norton');
            if ($vars['kaspersky'] == 'yes')
                $extra .= "\n  - " . __('Kaspersky');
            if ($vars['autocad'] == 'yesy')
                $extra .= "\n  - " . __('AutoCAD');
            if ($vars['corel'] == 'yes')
                $extra .= "\n  - " . __('Corel');
            if ($vars['adobe_cc'] == 'yes')
                $extra .= "\n  - " . __('Adobe CS6/CC');
            if ($vars['contpaqi'] == 'yes')
                $extra .= "\n  - " . __('CONTPAQi');
            if ($vars['sother'] == 'yes' && $vars['sother_obj'] != '')
                $extra .= "\n  - " . $vars['sother_obj'];

            $extra .= "\n\n";
        }

        // Put all
        $old_note = $vars['note'];
        $vars['note'] = $pctype . "\n\n" . $sname . "\n\n" . $turn . "\n\n" . $item .
             "\n\n" . $pass . "\n\n" . $extra . $old_note;

        // If we have HTML Thread enabled, convert new lines to <br>
        if ($cfg->isHtmlThreadEnabled())
            $vars['note'] = nl2br($vars['note']);
    }
}
?>
