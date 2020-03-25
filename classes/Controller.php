<?php 

// =============================================================================================================
// USAGE EXAMPLE
// =============================================================================================================
// class Controller_new extends Controller
// {
//      public function index()
//      {
//          $content = 'page/view.inc';
//          $display_vars['var1'] = 'info needed in view, accessed via $var1';
//          $this->render_page($content,$display_vars);
//      }
// }
// =============================================================================================================

class Controller
{
    
    // PROPERTIES
    

    
    // METHODS
    
    public function __construct()
    {
        
    }
    
    // placeholder method
    public function index()
    {
        // redirect to error page
        header("Location: ".$GLOBALS['config']['content_error']);
        die;
    }
    
    // PROTECTED METHODS
    
    protected function render_page($CONTENT,$variable_array=array(),$layout=NULL)
    {
        // verify valid $CONTENT
        if (!is_file($GLOBALS['config']['app_dir']."views/".$GLOBALS['config']['views_theme_dir']."$CONTENT"))
        {
                // redirect to error page
                header("Location: ".$GLOBALS['config']['content_error']);
                die;
        }
        else
        {
            // $CONTENT passes directly through to $layout >>
            // extract variable_array for intuitive access in layout >>
            extract($variable_array);
            // show layout
            if (empty($layout))
            {
                    $layout = $GLOBALS['config']['default_layout'];
            }
            include($GLOBALS['config']['app_dir']."views/".$GLOBALS['config']['views_theme_dir']."$layout");
        }
    }
    
    protected function view($view,$variable_array=array(),$output=TRUE)
    {
        // verify valid $CONTENT
        if (!is_file($GLOBALS['config']['app_dir']."views/$view"))
        {
                // redirect to error page
                header("Location: ".$GLOBALS['config']['content_error']);
                die;
        }
        else
        {
            extract($variable_array);
            if ($output)
            {
                include($GLOBALS['config']['app_dir']."views/".$view);
            }
            else
            {
                ob_start();
                include($GLOBALS['config']['app_dir']."views/".$view);
                return ob_get_clean();
            }
        }
    }
    
}

?>