<?php
namespace App\Http\Middlewares;
use Illuminate\Http\Request;

class InputSanitizationMiddleware {
    public function handle(Request $request, callable $next){
        // Sanitasi input
        // $request->request->replace(array_map('htmlspecialchars', $request->request->all()));

        return $next($request);
    }
}
