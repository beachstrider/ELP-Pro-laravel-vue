<?php
// autoloaded file

if (!function_exists('get_auto_login_string')) {
    function get_auto_login_string()
    {
        if (auth()->check() && auth()->id() > 0) {
            $guid = encrypt(auth()->user()->guid);
            return '<meta name="string" id="credstring" content="' . $guid . '" />';
        } else if (session()->has('autoLogin')) {
            $guid = encrypt(session()->get('autoLogin'));
            session()->forget('autoLogin');
            return '<meta name="string" id="credstring" content="' . $guid . '" />';
        }

        return '';
    }
}

if (!function_exists('get_script_to_remove_localstorage')) {
    function get_script_to_remove_localstorage()
    {
        $prefix = env('MIX_STORAGE_PREFIX', 'alcolmEpl_');
        return "
            <script type='application/javascript'>
                localStorage.removeItem('" . $prefix . "auth')
            </script>
        ";
    }
}
