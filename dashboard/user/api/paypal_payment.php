<?php
    session_start();
    require '../../config/config.php';

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

            // Calculate amount based on package
            $amount = 2500; // Basic
            if ($package === 'standard') $amount = 5000;
            if ($package === 'premium') $amount = 10000;
            if ($add_gallery == 1 && $package !== 'premium') $amount += 2000;

            // Set currency and order description
            $currency = 'LKR';
            $description = 'Wedding Invitation - ' . ucfirst($package) . ' Plan' . ($add_gallery ? ' + Guest Gallery' : '');

            // Store order details in session for later use
            $_SESSION['paypal_package'] = $package;
            $_SESSION['paypal_gallery'] = $add_gallery;
            $_SESSION['paypal_amount'] = $amount;

            // Create PayPal order
            $response = createPayPalOrder($amount, $currency, $description, $user_id);

            if ($response['success']) {
                $_SESSION['paypal_order_id'] = $response['order_id'];
                
                echo json_encode([
                    'success' => true,
                    'order_id' => $response['order_id'],
                    'approval_url' => $response['approval_url'],
                    'amount' => $amount,
                    'currency' => $currency
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'success' => false, 
                    'message' => $response['message'] ?? 'Failed to create PayPal order'
                ]);
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

    function createPayPalOrder($amount, $currency, $description, $user_id) {
        $paypal_api_url = 'https://api-m.sandbox.paypal.com/v2/checkout/orders';
        
        // Read PayPal credentials from .env file
        $envPath = __DIR__ . '/../../.env';
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
            return ['success' => false, 'message' => 'Failed to get PayPal access token: ' . $curl_error];
        }

        $token_data = json_decode($response, true);
        if (!isset($token_data['access_token'])) {
            return ['success' => false, 'message' => 'Invalid PayPal response'];
        }

        $access_token = $token_data['access_token'];

        // Create order
        $order_data = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => $user_id,
                    'description' => $description,
                    'amount' => [
                        'currency_code' => $currency,
                        'value' => number_format($amount / 100, 2, '.', '')
                    ]
                ]
            ],
            'payment_source' => [
                'paypal' => [
                    'experience_context' => [
                        'return_url' => 'https://localhost/invite/dashboard/user/payment.php?paypal_action=success',
                        'cancel_url' => 'https://localhost/invite/dashboard/user/payment.php?paypal_action=cancel',
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

        if ($http_code != 201) {
            return ['success' => false, 'message' => 'Failed to create PayPal order: ' . $curl_error];
        }

        $order_result = json_decode($response, true);
        if (isset($order_result['id']) && isset($order_result['links'])) {
            $approval_url = '';
            foreach ($order_result['links'] as $link) {
                if ($link['rel'] === 'approve') {
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
        $envPath = __DIR__ . '/../../.env';
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
            return ['success' => false, 'message' => 'Failed to get PayPal access token: ' . $curl_error];
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
            return ['success' => false, 'message' => 'Failed to capture PayPal order: ' . $curl_error];
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