<?php

/**
 * This file is part of 247Commerce BigCommerce Revolut App.
 *
 * (c) 2021 247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

/**
 * Class curl_helper
 *
 * Represents a helper class to call API interfaces
 */
class curl_helper
{
    /**
     * APICall - interface to call 3rd party api calls
     *
     * @param $url(api url)
     * @param $method(method of curl request[get,post,put,delete])
     * @param $request(request of the body)
     * @param $header(header for the curl request)
     * @param $type(3rd paty type[BigCommerce,Revolut])
     * @param $action(3rd pary action[settleOrder,scriptInjection,CreateWebhooks])
     * @param $email_id(email id of user logged in)
     * @param $token_validation_id(validation id of user logged in)
     * @param $content_type(type of response in return of curl request [application/json,text/html])
     *
     * @return respinse from api call
     */
    public static function APICall($url, $method, $request, $header, $type, $action, $email_id, $token_validation_id, $content_type = 'application/json')
    {
        $resp           = [];
        $resp['status'] = false;
        $resp['data']   = [];
        $resp['msg']    = '';
        $apiLog         = new \App\Models\ApiLogModel();

        try {
            $client = \Config\Services::curlrequest();
            if (! empty($request)) {
                $response = $client->setBody($request)->request($method, $url, $header);
            } else {
                $response = $client->request($method, $url, $header);
            }
            if (strpos($response->getHeader('content-type'), $content_type) !== false) {
                $body           = $response->getBody();
                $response       = json_decode($body, true);
                $resp['status'] = true;
                $resp['data']   = $response;

                $data = [
                    'email_id'            => $email_id,
                    'type'                => $type,
                    'action'              => $action,
                    'api_url'             => addslashes($url),
                    'api_header'          => addslashes(json_encode($header, true)),
                    'api_request'         => addslashes($request),
                    'api_response'        => addslashes($body),
                    'token_validation_id' => $token_validation_id,
                ];
                $apiLog->insert($data);
            }
        } catch (\Exception $e) {
            log_message('info', 'exception:' . $e->getMessage());
            $data = [
                'email_id'            => $email_id,
                'type'                => $type,
                'action'              => $action,
                'api_url'             => addslashes($url),
                'api_header'          => addslashes(json_encode($header, true)),
                'api_request'         => addslashes($request),
                'api_response'        => $e,
                'token_validation_id' => $token_validation_id,
            ];
            $apiLog->insert($data);
        }

        return $resp;
    }
}
