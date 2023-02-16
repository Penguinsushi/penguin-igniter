<?php 

function autoloader($class) {
    
    if (substr($class,0,6) == 'Model_' AND is_file($GLOBALS['config']['app_dir']."models/$class.php"))
    {
        require_once($GLOBALS['config']['app_dir']."models/$class.php");
    }
    elseif (substr($class,0,11) == 'Controller_' AND is_file($GLOBALS['config']['app_dir']."controllers/$class.php"))
    {
        require_once($GLOBALS['config']['app_dir']."controllers/$class.php");
    }
    elseif (is_file($GLOBALS['config']['core_dir']."classes/$class.php"))
    {
        require_once($GLOBALS['config']['core_dir']."classes/$class.php");
    }
    else
    {
        return FALSE;
    }
    
}

?>