<?php
namespace Octal\Support;

use Artisan;
use Illuminate\Support\Facades\Blade;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class OProvider extends ServiceProvider
{
    public function boot()
    {
        
    }
    
    public function register()
    {
        include __DIR__ . '/routes.php';
        
        $loader = AliasLoader::getInstance();
        
        $this->app->make('Octal\Support\OController');
    }
}
