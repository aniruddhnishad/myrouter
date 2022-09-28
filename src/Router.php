<?php

namespace App\Config;

class Router {

    protected $routes = [];
    protected $callback404;
    protected $trailingSlash;
    protected $callbackMaintenance;
    protected $maintenance;

    public function __construct($trailingSlash = false, $mode = 0) {
        $this->trailingSlash = $trailingSlash;
        $this->mode = $mode;
    }

    public function get($route, $callback, $options = []) {
        $this->routes['GET'][] = [ $route, $callback, $options ];
    }

    public function post($route, $callback, $options = []) {
        $this->routes['POST'][] = [ $route, $callback, $options ];
    }

    public function put($route, $callback, $options = []) {
        $this->routes['PUT'][] = [ $route, $callback, $options ];
    }

    public function patch($route, $callback, $options = []) {
        $this->routes['PATCH'][] = [ $route, $callback, $options ];
    }

    public function delete($route, $callback, $options = []) {
        $this->routes['DELETE'][] = [ $route, $callback, $options ];
    }
    public function options($route, $callback, $options = []) {
        $this->routes['OPTIONS'][] = [ $route, $callback, $options ];
    }

    public function run($maintenance = false) {
        $url = $this->getUrl() == '' ? '/' : $this->getUrl();
        $method = $this->getMethod();
        $allRoutes = $this->getRouteUrl($method);
        $this->maintenance = $maintenance;
        if(!empty($allRoutes)) {
            foreach( $allRoutes as $routes) {
                $routes[0] = trim($routes[0], '/');
                $routes[0] = $routes[0] == '' ? '/' : $routes[0];
                if($this->maintenance) {
                   $this->maintenance();
                   exit();
                }
                if( in_array($routes[0], $routes) && in_array($routes[1], $routes) && is_callable($routes[1]) ) {
                    if(preg_match("#" . $routes[0] . "#i", $url, $matches)) {
                            if( $matches[0] == $url ) {
                                if( in_array($routes[2], $routes) ) {
                                    if( is_Array($routes[2]) && array_key_exists('before', $routes[2]) && is_callable($routes[2]['before']) ) {
                                        call_user_func_array($routes[2]['before'], []);
                                        $urlArray = explode('/', $url);
                                    }
                                    $urlArray = explode('/', $url);
                                    $routesArray = explode('/', $routes[0]);
                                    $args = array_diff($urlArray, $routesArray );
                                    call_user_func_array($routes[1], $args);
                                    if( is_Array($routes[2]) && array_key_exists('after', $routes[2]) && is_callable($routes[2]['after']) ) {
                                        call_user_func_array($routes[2]['after'], []);
                                    }
                                    return;
                                }
                            $urlArray = explode('/', $url);
                            $routesArray = explode('/', $routes[0]);
                            $args = array_diff($urlArray, $routesArray );
                            call_user_func_array($routes[1], $args);
                            return;
                        }
                    }
                }
            }
        }
        $this->error404();
    }
    
    public function getUrl() {
        if($this->trailingSlash) {
            $url = $this->mode ? $this->trailingSlash() : $this->noTrailingSlash();
            $url = $url = '' ? '/' : $url;
        }else{
            return trim(strtok($_SERVER['REQUEST_URI'], '?'), '/');
        }
    }


    public function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }

    public function redirect($scheme, $url) {
        http_response_code($scheme);
        $url = header("Location: $url");
        return $url;
    }

    public function noTrailingSlash() {
        $url = strtok($_SERVER['REQUEST_URI'], '?');
        if(substr($url, -1) == '/') {
            $url = trim($url, '/');
            $url = $url !== '' ? $this->redirect(301, '/' . $url) : $url;
        }
        return $url;
    }

    public function trailingSlash() {
        $url = strtok($_SERVER['REQUEST_URI'], '?');
        if(substr($url, -1) !== '/') {
            $url = trim($url, '/');
            $url = $url !== '' ? $this->redirect(301, '/' . $url . '/') : $url;
        }
        return $url;
    }

    public function set404($callback) {
        $this->callback404 = $callback; 
    }

    public function error404() {
        http_response_code(404);
        header_remove('x-powered-by');
        if( !empty($this->callback404) ) {
            call_user_func_array($this->callback404, []);
        }else {
            echo "404 page not found!";
        }
    }

    public function setMaintenance($callbackMaintenance) {
        $this->callbackMaintenance = $callbackMaintenance; 
    }

    public function maintenance() {

        if( !empty($this->callbackMaintenance) ) {
            call_user_func_array($this->callbackMaintenance, []);
        }else {
            echo "Site under maintenance!";
        }
    }

    function getRouteUrl($method) {
        return $this->routes[$method] ?? [];
    }

    function test() {
    
    }
}