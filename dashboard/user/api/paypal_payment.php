<?php
    session_start();

    // Always respond as JSON from this endpoint, and never let raw PHP
    // warnings/errors leak into the body — that breaks fetch().json() on
    // the frontend and shows up there as a generic "could not connect" error.
    header('Content-Type: application/json');
    ini_set('display_errors', '0');
    error_reporting(E_ALL);

    register_shutdown_function(function() {
        $error = error_get_last();
        if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR])) {
            if (!headers_sent()) {
                header('Content-Type: application/json');
            }
            // Clear any partial output that may have already been printed
            if (ob_get_length()) {
                ob_clean();
            }
            echo json_encode([
                'success' => false,
                'message' => 'Server error: ' . $error['message'] . ' (in ' . basename($error['file']) . ' line ' . $error['line'] . ')'
            ]);
        }
    });
    ob_start();

    require '../../../config/config.php';

    if (!extension_loaded('curl')) {
        echo json_encode([
            'success' => false,
            'message' => 'The cURL PHP extension is not enabled on this server, so PayPal payments cannot be processed. Please contact hosting support to enable it.'
        ]);
        exit();
    }

    // Security: Validate session
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'couple') {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
        exit();
    }

    $user_id = $_SESSION['user_id'];

    try {
        // Check action
        $action = $_POST['action'] ?? '';
        
        if ($action === 'create') {
            // Create new PayPal order
            $package = $_POST['package'] ?? 'basic';
            $add_gallery = isset($_POST['add_gallery']) ? 1 : 0;
            
            if ($package === 'premium') {
                $add_gallery = 1;
            }

            // Calculate amount based on package (in LKR — our real pricing currency)
            $amount = 2500; // Basic
            if ($package === 'standard') $amount = 5000;
            if ($package === 'premium') $amount = 10000;
            if ($add_gallery == 1 && $package !== 'premium') $amount += 2000;

            // PayPal does not support LKR as a checkout currency, so we convert to USD
            // using a fixed approximate rate. Update LKR_TO_USD_RATE if the rate changes.
            if (!defined('LKR_TO_USD_RATE')) {
                define('LKR_TO_USD_RATE', 310);
            }
            $amount_usd = round($amount / LKR_TO_USD_RATE, 2);

            // Set currency and order description
            $currency = 'USD';
            $description = 'Wedding Invitation - ' . ucfirst($package) . ' Plan' . ($add_gallery ? ' + Guest Gallery' : '');

            // Store order details in session for later use (LKR is our real internal amount)
            $_SESSION['paypal_package'] = $package;
            $_SESSION['paypal_gallery'] = $add_gallery;
            $_SESSION['paypal_amount'] = $amount;
            $_SESSION['paypal_amount_usd'] = $amount_usd;

            // Create PayPal order (charged in USD)
            $response = createPayPalOrder($amount_usd, $currency, $description, $user_id);

            if ($response['success']) {
                $_SESSION['paypal_order_id'] = $response['order_id'];
                
                echo json_encode([
                    'success' => true,
                    'order_id' => $response['order_id'],
                    'approval_url' => $response['approval_url'],
                    'amount' => $amount,          // LKR — for on-page display
                    'amount_usd' => $amount_usd,   // USD — what PayPal actually charges
                    'currency' => 'LKR'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => $response['message'] ?? 'Failed to create PayPal order'
                ]);
            }
        }
        
        else if ($action === 'capture') {
            // Capture the payment and update database
            $order_id = $_POST['order_id'] ?? '';
            $package = $_POST['package'] ?? 'basic';
            $add_gallery = isset($_POST['add_gallery']) ? 1 : 0;
            $amount = $_POST['amount'] ?? 0;
            
            if ($package === 'premium') {
                $add_gallery = 1;
            }

            // Capture payment via PayPal API
            $capture_response = capturePayPalOrder($order_id);
            
            if ($capture_response['success']) {
                // Update user database to mark payment as completed
                $update_stmt = $pdo->prepare("UPDATE users SET status = 'active', package = ?, has_guest_gallery = ?, payment_slip = NULL, refund_status = 'none', updated_at = NOW() WHERE id = ?");
                $update_result = $update_stmt->execute([$package, $add_gallery, $user_id]);
                
                if ($update_result) {
                    echo json_encode([
                        'success' => true,
                        'capture_id' => $capture_response['capture_id'],
                        'amount' => $capture_response['amount'],
                        'message' => 'Payment captured successfully'
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Failed to update user account'
                    ]);
                }
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false,
                    'message' => $capture_response['message'] ?? 'Failed to capture payment'
                ]);
            }
        }
        
        else {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Invalid action specified'
            ]);
        }
        
    } catch (Exception $e) {
        http_response_code(500);
        error_log("PayPal Payment Error: " . $e->getMessage());
        echo json_encode(['success' => false, 'message' => 'An error occurred while processing your request']);
    }

    function extractPayPalErrorMessage($response, $curl_error) {
        if (!empty($curl_error)) {
            return $curl_error;
        }
        if (!$response) {
            return 'No response received from PayPal.';
        }
        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            return $response;
        }
        // OAuth token errors look like: { error, error_description }
        if (!empty($decoded['error_description'])) {
            return $decoded['error_description'];
        }
        // Order/capture errors look like: { name, message, details: [{issue, description}] }
        $parts = [];
        if (!empty($decoded['message'])) $parts[] = $decoded['message'];
        if (!empty($decoded['details']) && is_array($decoded['details'])) {
            foreach ($decoded['details'] as $d) {
                $parts[] = trim(($d['issue'] ?? '') . ': ' . ($d['description'] ?? ''), ': ');
            }
        }
        return $parts ? implode(' | ', $parts) : $response;
    }

    function createPayPalOrder($amount, $currency, $description, $user_id) {
        $paypal_api_url = 'https://api-m.sandbox.paypal.com/v2/checkout/orders';
        
        // Read PayPal credentials from .env file
        $envPath = __DIR__ . '/../../../.env';
        $client_id = '';
        $client_secret = '';
        
        if (file_exists($envPath)) {
            $envVars = parse_ini_file($envPath);
            if ($envVars) {
                $client_id = $envVars['PAYPAL_CLIENT_ID'] ?? '';
                $client_secret = $envVars['PAYPAL_CLIENT_SECRET'] ?? '';
            }
        }
        
        if (empty($client_id) || empty($client_secret)) {
            return ['success' => false, 'message' => 'PayPal credentials not configured'];
        }

        // Get access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($http_code != 200) {
            error_log('PayPal getToken failed (HTTP ' . $http_code . '): ' . $response);
            return ['success' => false, 'message' => 'Failed to get PayPal access token (HTTP ' . $http_code . '): ' . extractPayPalErrorMessage($response, $curl_error)];
        }

        $token_data = json_decode($response, true);
        if (!isset($token_data['access_token'])) {
            return ['success' => false, 'message' => 'Invalid PayPal response'];
        }

        $access_token = $token_data['access_token'];

        // Build a dynamic return/cancel URL that matches whatever domain this is running on.
        // payment.php lives ONE folder above this file (this file is in an api/ subfolder).
        $pp_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $pp_base_url = $pp_protocol . $_SERVER['HTTP_HOST'] . dirname(dirname($_SERVER['PHP_SELF']));
        $pp_return_url = $pp_base_url . '/payment.php?paypal_action=success';
        $pp_cancel_url = $pp_base_url . '/payment.php?paypal_action=cancel';

        // Create order
        $order_data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $user_id,
                    'description' => $description,
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount, 2, '.', '')
                    ]
                ]
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'return_url' => $pp_return_url,
                        'cancel_url' => $pp_cancel_url,
                        'brand_name' => 'Lumos Studio',
                        'user_action' => 'PAY_NOW'
                    ]
                ]
            ]
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $paypal_api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($order_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($http_code != 200 && $http_code != 201) {
            error_log('PayPal createOrder failed (HTTP ' . $http_code . '): ' . $response);
            return ['success' => false, 'message' => 'Failed to create PayPal order (HTTP ' . $http_code . '): ' . extractPayPalErrorMessage($response, $curl_error)];
        }

        $order_result = json_decode($response, true);
        if (isset($order_result['id']) && isset($order_result['links'])) {
            $approval_url = '';
            foreach ($order_result['links'] as $link) {
                // Newer PayPal API responses (with payment_source.paypal.experience_context)
                // use rel "payer-action" instead of the older "approve".
                if ($link['rel'] === 'approve' || $link['rel'] === 'payer-action') {
                    $approval_url = $link['href'];
                    break;
                }
            }

            if ($approval_url) {
                return [
                    'success' => true,
                    'order_id' => $order_result['id'],
                    'approval_url' => $approval_url,
                    'amount' => $amount
                ];
            }
        }

        return ['success' => false, 'message' => 'Invalid PayPal order response'];
    }
    
    function capturePayPalOrder($order_id) {
        $paypal_api_url = 'https://api-m.sandbox.paypal.com/v2/checkout/orders/' . $order_id . '/capture';
        
        // Read PayPal credentials from .env file
        $envPath = __DIR__ . '/../../../.env';
        $client_id = '';
        $client_secret = '';
        
        if (file_exists($envPath)) {
            $envVars = parse_ini_file($envPath);
            if ($envVars) {
                $client_id = $envVars['PAYPAL_CLIENT_ID'] ?? '';
                $client_secret = $envVars['PAYPAL_CLIENT_SECRET'] ?? '';
            }
        }
        
        if (empty($client_id) || empty($client_secret)) {
            return ['success' => false, 'message' => 'PayPal credentials not configured'];
        }

        // Get access token
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api-m.sandbox.paypal.com/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'grant_type=client_credentials');
        curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($http_code != 200) {
            error_log('PayPal getToken (capture) failed (HTTP ' . $http_code . '): ' . $response);
            return ['success' => false, 'message' => 'Failed to get PayPal access token (HTTP ' . $http_code . '): ' . extractPayPalErrorMessage($response, $curl_error)];
        }

        $token_data = json_decode($response, true);
        if (!isset($token_data['access_token'])) {
            return ['success' => false, 'message' => 'Invalid PayPal response'];
        }

        $access_token = $token_data['access_token'];

        // Capture the order
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $paypal_api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $access_token
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curl_error = curl_error($ch);
        curl_close($ch);

        if ($http_code != 201 && $http_code != 200) {
            error_log('PayPal captureOrder failed (HTTP ' . $http_code . '): ' . $response);
            return ['success' => false, 'message' => 'Failed to capture PayPal order (HTTP ' . $http_code . '): ' . extractPayPalErrorMessage($response, $curl_error)];
        }

        $capture_result = json_decode($response, true);
        
        if (isset($capture_result['id']) && $capture_result['status'] === 'COMPLETED') {
            return [
                'success' => true,
                'capture_id' => $capture_result['id'],
                'amount' => $capture_result['purchase_units'][0]['amount']['value'],
                'status' => $capture_result['status']
            ];
        }

        return ['success' => false, 'message' => 'Payment capture failed: ' . json_encode($capture_result)];
    }
?>