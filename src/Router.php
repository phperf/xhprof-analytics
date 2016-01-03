<?php

namespace Phperf\Xhprof;

use Yaoi\Io\Request;
use Yaoi\String\StringValue;

class Router extends \Yaoi\Router
{
    public function route(Request $request)
    {
        $path = new StringValue($request->path());

        if ($path->starts('/api/')) {
            Api\Router::create('/api/')->route($request);
            return;
        }
        if ($path->starts('/')) {

        }
    }
}