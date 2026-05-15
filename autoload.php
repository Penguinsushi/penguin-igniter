<?php 

function autoloader($class) {
    
    if (substr($class,0,6) == 'Model_' AND is_file(Config::val('app_dir')."models/$class.php"))
    {
        require_once(Config::val('app_dir')."models/$class.php");
    }
    elseif (substr($class,0,8) == 'Library_' AND is_file(Config::val('app_dir')."libraries/$class.php"))
    {
        require_once(Config::val('app_dir')."libraries/$class.php");
    }
    elseif (substr($class,0,11) == 'Controller_' AND is_file(Config::val('app_dir')."controllers/$class.php"))
    {
        require_once(Config::val('app_dir')."controllers/$class.php");
    }
    elseif (is_file(Config::val('core_dir')."classes/$class.php"))
    {
        require_once(Config::val('core_dir')."classes/$class.php");
    }
    else
    {
        return FALSE;
    }
    
}
