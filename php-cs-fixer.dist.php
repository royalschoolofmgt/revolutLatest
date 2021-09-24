<?php

use CodeIgniter\CodingStandard\CodeIgniter4;
use Nexus\CsConfig\Factory;

//return Factory::create(new CodeIgniter4())->forProjects();
return Factory::create(new CodeIgniter4())->forLibrary(
    '247Commerce BigCommerce Revolut App',
    '247 Commerce Limited',
    'info@247commerce.co.uk',
    2021,
);
