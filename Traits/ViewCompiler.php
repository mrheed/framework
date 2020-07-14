<?php

namespace Gi\Traits;

use Gi\Collection;

trait ViewCompiler {

    private
        $extends = null,
        $stacks = [],
        $yields = [];

    public function push($name){

        if (ob_start()) $this->stacks[] = $name;
    }

    public function endpush(){

        $name = array_pop($this->stacks);

        if (isset($this->stacks[$name])) {
            
            $this->stacks[$name] .= ob_get_clean();

        } else {

            $this->stacks[$name] = ob_get_clean();
        }
    }

    public function stack($name){

        return isset($this->stacks[$name]) ? $this->stacks[$name] : null;
    }

    public function section($name){

        if (ob_start()) $this->yields[] = $name;
    }

    public function endsection(){

        $name = array_pop($this->yields);

        $this->yields[$name] = ob_get_clean();
    }

    public function yield($name){

        return isset($this->yields[$name]) ? $this->yields[$name] : null;
    }

    private function compileStatement($match){


        switch ($match[1]) {
            case 'if':
            case 'elseif':
            case 'for':
            case 'foreach':
            case 'while':
                return "<?php $match[1]$match[3]: ?>";
                break;

            case 'else':
                return '<?php else: ?>';
                break;

            case 'endif':
            case 'endfor':
            case 'endforeach':
            case 'endwhile':
                return "<?php $match[1] ?>";
                break;

            case 'push':
            case 'section':
                return '<?php $this->' . "$match[1]$match[3] ?>";
                break;

            case 'stack':
            case 'yield':
                return '<?= $this->' . "$match[1]$match[3] ?>";
                break;

            case 'extends':
                $this->extends = str_replace(['\'', '"'], null, $match[4]);
                break;

            case 'endpush':
            case 'endsection':
                return '<?php $this->' . "$match[1]() ?>";
                break;

            case 'include':
                $include = str_replace(['\'', '"'], null, $match[4]);
                return '<?php include $this->getCompiled(\'' . $include . '\') ?>';

            default:
                break;
        }
    }

    private function compileStatements($string){

        return preg_replace_callback(
            '/\B@(@?\w+(?:::\w+)?)([ \t]*)(\( ( (?>[^()]+) | (?3) )* \))?/x',
            function ($match){
                return $this->compileStatement($match);
            },
            $string
        );
    }

    private function compile($string){

        $replace = [
            '{{' => '<?=',
            '}}' => '?>'
        ];

        $compiled = strtr($this->compileStatements($string), $replace);

        if (!is_null($this->extends)) {
            $compiled .= '<?php include $this->getCompiled(\'' .
                $this->extends . '\') ?>';
        }

        return $compiled;
    }

    private function map($file, $compiled = null, $time = null){

        $path = config('app.tmppath') . '/view';

        // generate map file for the first time
        if(!file_exists("$path/map.log")) {
            
            if (!file_exists($path)) {

                mkdir($path, PERMISSION, true);
            }
            
            $new_map = [
                $file => [
                    'compiled' => null,
                    'time' => null
                ]
            ];

            file_put_contents("$path/map.log", serialize($new_map));
        }

        $map = unserialize(file_get_contents("$path/map.log"));

        // get map data
        if (isset($map[$file]) and is_null($compiled) and is_null($time)) {

            return new Collection($map[$file]);
        }

        // push map data if not isset
        $map[$file] = [
            'compiled' => $compiled,
            'time' => $time
        ];

        file_put_contents($path . '/map.log', serialize($map));

        return new Collection($map[$file]);
    }

    private function createCompiledView($file, $time){

        $string = $this->compile(file_get_contents($file));
        $random = generate_token();
        $compiled = config('app.tmppath') . "/view/$random.php";

        file_put_contents($compiled, $string);

        return $this->map($file, $compiled, $time);
    }

    public function getCompiled($name){
        
        $this->extends = null;
        
        $file = "$this->path/$name.php";
        
        $updated = fileatime($file);
        $map = $this->map($file);

        if ($map->time !== $updated){
            $map = $this->createCompiledView($file, $updated);
        }

        return $map->compiled;
    }
}
