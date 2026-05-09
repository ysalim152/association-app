<?php
// Routeur simple
class Router {
    protected $routes = [];

    public function get($path, $handler) {
        $this->routes['GET'][$path] = $handler;
        return $this;
    }

    public function post($path, $handler) {
        $this->routes['POST'][$path] = $handler;
        return $this;
    }

    public function put($path, $handler) {
        $this->routes['PUT'][$path] = $handler;
        return $this;
    }

    public function delete($path, $handler) {
        $this->routes['DELETE'][$path] = $handler;
        return $this;
    }

    public function dispatch($method, $path) {
        if (!isset($this->routes[$method])) {
            return null;
        }

        foreach ($this->routes[$method] as $route => $handler) {
            if ($this->matchRoute($route, $path)) {
                return $handler;
            }
        }
        return null;
    }

    protected function matchRoute($route, $path) {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        return preg_match($pattern, $path);
    }

    public function getParams($route, $path) {
        $pattern = preg_replace('/\{(\w+)\}/', '(?P<$1>[^/]+)', $route);
        $pattern = '#^' . $pattern . '$#';
        if (preg_match($pattern, $path, $matches)) {
            return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
        }
        return [];
    }
}

// Classe de réponse API
class Response {
    public static function json($data, $status = 200) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode(['data' => $data, 'status' => $status]);
        exit;
    }

    public static function error($message, $status = 400, $errors = []) {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode([
            'error' => $message,
            'errors' => $errors,
            'status' => $status
        ]);
        exit;
    }

    public static function success($message, $data = null) {
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode([
            'message' => $message,
            'data' => $data,
            'status' => 200
        ]);
        exit;
    }
}

// Logger
class Logger {
    protected $file;

    public function __construct($filename = 'app.log') {
        $this->file = LOGS_ROOT . '/' . $filename;
    }

    public function info($message) {
        $this->write('INFO', $message);
    }

    public function error($message) {
        $this->write('ERROR', $message);
    }

    public function warning($message) {
        $this->write('WARNING', $message);
    }

    public function debug($message) {
        if (DEBUG) {
            $this->write('DEBUG', $message);
        }
    }

    protected function write($level, $message) {
        $timestamp = date('Y-m-d H:i:s');
        $log_entry = "[$timestamp] [$level] $message\n";
        error_log($log_entry, 3, $this->file);
    }
}
