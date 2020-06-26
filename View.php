<?php

namespace Ez;

/**
 * View sebagai render view untuk load template
 * @author prade.nugroho@gin.co.id
 */
class View
{

    private static
        $path = 'view',
        $layout = 'layout',
        $content = false,
        $css = [],
        $js = [],
        $render_variable = [];

    public static function layout($file)
    {

        static::$render_variable['layout'] = $file;
    }

    public static function share($data = [])
    {

        static::$render_variable = array_merge(static::$render_variable, $data);
    }


    public static function registerCss($css)
    {
        if (is_array($css)){
            foreach ($css as $item_css){
                
                array_push(static::$css, $item_css);
            }

        } else {

            array_push(static::$css, $css);
        }
    }

    public static function registerJs($js)
    {
        if (is_array($js)){
            foreach ($js as $item_js){
                
                array_push(static::$js, $item_js);
            }

        } else {

            array_push(static::$js, $js);
        }
    }

    public static function path($path)
    {

        static::$path = $path;
    }

    /**
     * view() digunakan untuk load seluruh template yang dipilih
     * @param string $file file yang akan diload
     * @param object $data data yang akan dikirimkan ke dalam view
     */
    public static function render($file, $data = [])
    {

        $layout = static::$layout;
        
        static::$render_variable = array_merge(static::$render_variable, $data);

        extract(static::$render_variable);

        if (false == $layout) {

            unset($layout);
        }

        if (isset($layout)) {


            static::$content = base_dir('view/' . rdot($file).'.php');

            include base_dir(rdot(static::$path . '.' . $layout).'.php');

        } else {

            include base_dir(rdot(static::$path . '.' . $file) . '.php');
        }
    }

    // nggo nampilken content sek nganggo layout
    public static function content()
    {
        extract(static::$render_variable);

        include static::$content;
    }

    // podo ro js()
    public static function css()
    {

        $files = array_unique(static::$css);

        foreach ($files as $css){

            $css = url($css);

            echo "<link rel=\"stylesheet\" type=\"text/css\" href=\"$css\">";
        }
    }
    // nggo load js sek ke include barengan karo content
    public static function js()
    {

        $files = array_unique(static::$js);

        foreach ($files as $js){

            $js = url($js);

            echo "<script type=\"text/javascript\" src=\"$js\"></script>";
        }
    }
}