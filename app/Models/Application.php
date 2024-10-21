<?php

namespace App\Models;

class Application extends \Illuminate\Foundation\Application
{
    public function publicPath($path = "")
{
    return __DIR__;
}
}
