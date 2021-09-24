<?php

/**
 * This file is part of 247Commerce BigCommerce Revolut App.
 *
 * (c) 2021 247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Models;

class ApiLogModel extends MainModel
{
    protected $table = 'api_log';

    protected $primaryKey = 'id';

    protected $allowedFields = ['email_id', 'token_validation_id', 'type', 'action', 'api_url', 'api_header', 'api_request', 'api_response'];
}
