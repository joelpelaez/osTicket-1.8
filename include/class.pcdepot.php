<?php
class Pcdepot {

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

        // Check items
        $item = 'El equipo trae los siguientes objetos';
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
        if ($vars['other'] == 'yes')
            $item = $item . "\n  - " . $vars['other_obj']; 

        // Check if has password
        if ($vars['password'] != '')
            $pass = __('La contraseña es') . ': ' . $vars['password'];
        else
            $pass = __('No tiene contraseña');

        $old_note = $vars['note'];
        $vars['note'] = $pctype . "\n\n" . $turn . "\n\n" . $item .
             "\n\n" . $pass . "\n\n" . $old_note;

        // If we have HTML Thread enabled, convert new lines to <br>
        if ($cfg->isHtmlThreadEnabled())
            $vars['note'] = nl2br($vars['note']);
    }
}
?>
