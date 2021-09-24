<?php

/**
 * This file is part of 247Commerce BigCommerce Revolut App.
 *
 * (c) 2021 247 Commerce Limited <info@247commerce.co.uk>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace App\Controllers;

/**
 * Class Settings
 *
 * Permit to connect revolut with Bigcommerce and vice-versa.
 * Configure connector and launch configuration.
 */
class Settings extends BaseController
{
    /**
     * Bcredirect - redirect from Bigcommerce
     * callback url.
     *
     * @param mixed $emailId
     * @param mixed $validationId
     *
     * @return Load setting page based on redirect param
     */
    public function bcredirect($emailId, $validationId)
    {
        $session = session();
        if (! empty($emailId) && ! empty($validationId)) {
            $session->set('email_id', $emailId);

            $session->set('validation_id', $validationId);
        }

        return redirect()->to('/');
    }

    /**
     * BcAuthcallback - BigCommerce app installation redirect funtion to store
     * details in DB.
     *
     * @return redirect to setting page
     */
    public function bcAuthcallback()
    {
        helper('settingsviews');
        helper('curl');
        log_message('info', 'bcAuthcallback' . json_encode($_REQUEST, true));
        $headers = [
            'query' => [
                'client_id'     => getenv('bigcommerceapp.APP_CLIENT_ID'),
                'client_secret' => getenv('bigcommerceapp.APP_CLIENT_SECRET'),
                'redirect_uri'  => getenv('app.baseURL') . 'settings/bcAuthcallback',
                'grant_type'    => 'authorization_code',
                'code'          => $_GET['code'],
                'scope'         => $_GET['scope'],
                'context'       => $_GET['context'],
            ],
        ];

        $url          = getenv('bigcommerceapp.oauthloginUrl');
        $api_response = \curl_helper::APICall($url, 'POST', '', $headers, 'BigCommerce', 'Authentication', 'info@revolut.com', '0', 'application/json');
        if ($api_response['status']) {
            $response = $api_response['data'];
            if (isset($response['access_token'])) {
                $validationResponse = \settingsviews_helper::storeTokenData($response);
                if (isset($validationResponse['email'])) {
                    $emailId      = $validationResponse['email'];
                    $validationId = $validationResponse['id'];
                    $accessToken  = $response['access_token'];

                    return redirect()->to('/settings/bcredirect/' . $emailId . '/' . $validationId);
                }
            }
        }
    }

    /**
     * BcLoadcallback - BigCommerce app iframe redirect default funtion.
     *
     * @return redirect to setting page
     */
    public function bcLoadcallback()
    {
        helper('settingsviews');
        $data = $_REQUEST;
        log_message('info', 'bcLoadcallback' . json_encode($data, true));
        $jsonData = \settingsviews_helper::verifySignedRequest($_GET['signed_payload']);
        if ($jsonData !== null && $jsonData !== '') {
            $storeContext = @$jsonData['context'];
            //$storeHash    = str_replace('stores/', '', $storeContext);
            $storeHash = @$jsonData['store_hash'];
            $email     = @$jsonData['user']['email'];

            $condition = [
                'email_id'   => $email,
                'store_hash' => $storeHash,
            ];
            $tokenValidation = new \App\Models\RevolutTokenValidationModel();
            $db_resp         = $tokenValidation->getData($condition);
            if ($db_resp['status']) {
                $result       = $db_resp['data'];
                $validationId = $result['validation_id'];
                $emailId      = $result['email_id'];

                return redirect()->to('/settings/bcredirect/' . $emailId . '/' . $validationId);
            }
        }
    }

    /**
     * BcRemovecallback - BigCommerce app remove redirect funtion.
     * Update the status to 0
     *
     * @return success
     */
    public function bcRemovecallback()
    {
        helper('settingsviews');
        $data = $_REQUEST;
        log_message('info', 'bcRemovecallback' . json_encode($data, true));
        $jsonData = \settingsviews_helper::verifySignedRequest($_GET['signed_payload']);
        if ($jsonData !== null && $jsonData !== '') {
            $storeContext = @$jsonData['context'];
            $storeHash    = str_replace('stores/', '', $storeContext);
        }
    }

    /**
     * BcUninstallcallback - BigCommerce app uninstall redirect funtion.
     * Delete entry from app DB
     */
    public function bcUninstallcallback()
    {
        $data = $_REQUEST;
        log_message('info', 'bcUninstallCallback' . json_encode($data, true));
        helper('settingsviews');
        if (isset($data['signed_payload'])) {
            $payload = explode('.', $data['signed_payload']);
            if (isset($payload[0])) {
                $signedPayload = $payload[0];
                $userData      = json_decode(base64_decode($signedPayload, true), true);
                if (isset($userData['context'])) {
                    $storeHash = str_replace('stores/', '', $userData['context']);
                    $email     = @$userData['user']['email'];
                    if ($storeHash !== '' && ! empty($email)) {
                        $condition = [
                            'email_id'   => $email,
                            'store_hash' => $storeHash,
                        ];
                        $tokenValidation = new \App\Models\RevolutTokenValidationModel();
                        $db_resp         = $tokenValidation->getData($condition);
                        if ($db_resp['status']) {
                            $result = $db_resp['data'];
                            \settingsviews_helper::unInstallCustomPages($result);
                        }
                    }
                }
            }
        }
    }

    /**
     * BCEnabePayment - enable Payment in bigcommerce redirect funtion to store
     * details in DB.
     *
     * @return redirect to setting page
     */
    public function bcEnablePayment()
    {
        helper('settingsviews');
        $clientDetails = \settingsviews_helper::getClientDetails();
        if (! empty($clientDetails)) {
            $result = \settingsviews_helper::enablePayment();

            return redirect()->to('/home/index?enabled=1');
        }

        return redirect()->to('/');
    }

    /**
     * bcDisablePayment - disable Payment in bigcommerce redirect funtion to store
     * details in DB.
     *
     * @return redirect to setting page
     */
    public function bcDisablePayment()
    {
        helper('settingsviews');
        $clientDetails = \settingsviews_helper::getClientDetails();
        if (! empty($clientDetails)) {
            $result = \settingsviews_helper::disablePayment();

            return redirect()->to('/home/index?disabled=1');
        }

        return redirect()->to('/');
    }

    /**
     * customButton - to load custom button payment template
     * and valid segments.
     *
     * @return Load Custom payment button template page
     */
    public function customButton()
    {
        helper('settingsviews');
        $data                  = [];
        $data['buttonDetails'] = [];
        $clientDetails         = \settingsviews_helper::getClientDetails();
        if (! empty($clientDetails)) {
            $condition = [
                'email_id'            => $clientDetails['email_id'],
                'token_validation_id' => $clientDetails['validation_id'],
            ];
            $revolutPayButton = new \App\Models\CustomRevolutPayButtonModel();
            $db_resp          = $revolutPayButton->getData($condition);
            if ($db_resp['status']) {
                $result                = $db_resp['data'];
                $data['buttonDetails'] = $result;
            }

            return view('customButton', $data);
        }

        return redirect()->to('/');
    }

    /**
     * updateCustomButton - update
     * details in DB.
     *
     * @return redirect to custom button payment template page
     */
    public function updateCustomButton()
    {
        if ($this->request->getMethod() === 'post') {
            $container_id = $this->request->getPost('container_id');
            $css_prop     = $this->request->getPost('css_prop');
            $buttoncolor  = $this->request->getPost('buttoncolor');
            $textcolor    = $this->request->getPost('textcolor');
            $outlinecolor = $this->request->getPost('outlinecolor');
            $is_enabled   = $this->request->getPost('is_enabled');
            $payment_name = $this->request->getPost('payment_name');
            $powerby_logo = $this->request->getPost('powerby_logo');

            $enable = 0;
            if ($is_enabled === 'on') {
                $enable = 1;
            }

            helper('settingsviews');
            $clientDetails = \settingsviews_helper::getClientDetails();
            if (! empty($clientDetails)) {
                $condition = [
                    'email_id'            => $clientDetails['email_id'],
                    'token_validation_id' => $clientDetails['validation_id'],
                ];
                $revolutPayButton = new \App\Models\CustomRevolutPayButtonModel();
                $db_resp          = $revolutPayButton->getData($condition);
                if ($db_resp['status']) {
                    $data = [
                        'container_id' => $container_id,
                        'css_prop'     => $css_prop,
                        'is_enabled'   => $enable,
                        'payment_name' => $payment_name,
                        'powerby_logo' => $powerby_logo,
                        'buttoncolor'  => $buttoncolor,
                        'textcolor'    => $textcolor,
                        'outlinecolor' => $outlinecolor,
                    ];
                    $revolutPayButton->updateData($condition, $data);
                } else {
                    $data = [
                        'email_id'            => $clientDetails['email_id'],
                        'token_validation_id' => $clientDetails['validation_id'],
                        'container_id'        => $container_id,
                        'css_prop'            => $css_prop,
                        'is_enabled'          => $enable,
                        'payment_name'        => $payment_name,
                        'powerby_logo'        => $powerby_logo,
                        'buttoncolor'         => $buttoncolor,
                        'textcolor'           => $textcolor,
                        'outlinecolor'        => $outlinecolor,
                    ];
                    $revolutPayButton->insertData($data);
                }
                helper('custompaymentscript');
                \custompaymentscript_helper::createPaymentScript($clientDetails['sellerdb'], $clientDetails['email_id'], $clientDetails['validation_id'], $clientDetails['is_test_live'], $clientDetails['payment_option']);
            }
        }

        return redirect()->to('/settings/customButton?updated=1');
    }

    /**
     * switchToggle - to save revolut api details and payment option details
     * details in DB.
     *
     * @return redirect to dashboard page
     */
    public function switchToggle()
    {
        $rdata           = [];
        $rdata['status'] = false;
        $rdata['msg']    = 'Something went wrong';
        if ($this->request->getMethod() === 'post') {
            $key            = $this->request->getVar('api_key');
            $payment_option = $this->request->getVar('payment_option');
            $is_test_live   = empty($this->request->getVar('is_test_live')) ? 0 : $this->request->getVar('is_test_live');
            if (empty($payment_option)) {
                $payment_option = 'CFO';
            }
            helper('customrevolutwebhooks');
            $verify = \customrevolutwebhooks_helper::checkRevolutValidKey($is_test_live, $key);
            if (isset($verify['status']) && ($verify['status'] === true)) {
                helper('settingsviews');
                $clientDetails = \settingsviews_helper::getClientDetails();
                if (! empty($clientDetails)) {
                    $cstatus = \settingsviews_helper::unInstallRevolutWebhooks($clientDetails);
                    if (isset($cstatus['status']) && ($cstatus['status'] === true)) {
                        if ($is_test_live === 0) {
                            $data = [
                                'is_test_live'         => $is_test_live,
                                'revolut_api_key_test' => $key,
                                'payment_option'       => $payment_option,
                            ];
                        } else {
                            $data = [
                                'revolut_api_key' => $key,
                                'is_test_live'    => $is_test_live,
                                'payment_option'  => $payment_option,
                            ];
                        }
                        $condition = [
                            'email_id'      => $clientDetails['email_id'],
                            'validation_id' => $clientDetails['validation_id'],
                        ];
                        $tokenValidation = new \App\Models\RevolutTokenValidationModel();
                        $tokenValidation->updateData($condition, $data);

                        helper('custompaymentscript');
                        \custompaymentscript_helper::createPaymentScript($clientDetails['sellerdb'], $clientDetails['email_id'], $clientDetails['validation_id'], $is_test_live, $payment_option);

                        \customrevolutwebhooks_helper::createCustomWebhooks($clientDetails['email_id'], $clientDetails['validation_id'], $clientDetails['sellerdb']);

                        $rdata['status'] = true;
                    }
                }
            } else {
                $rdata['msg'] = 'Please enter valid Api Key';
            }
        }
        echo json_encode($rdata, true);
    }
}
