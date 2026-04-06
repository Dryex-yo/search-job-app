<?php

use App\Providers\AppServiceProvider;
use App\Providers\MultitenancyBootstrapProvider;

return [
    MultitenancyBootstrapProvider::class,
    AppServiceProvider::class,
];
