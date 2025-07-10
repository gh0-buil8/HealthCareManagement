
<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../middleware/auth.php';
requireAuth();

require_once '../classes/Payment.php';

$user = getCurrentUser();
$payment = new Payment();

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

try {
    switch ($action) {
        case 'process_payment':
            $payment_id = $input['payment_id'] ?? '';
            $payment_method = $input['payment_method'] ?? '';
            $card_details = $input['card_details'] ?? [];
            
            if (!$payment_id || !$payment_method) {
                throw new Exception('Missing required payment information');
            }
            
            // Get payment details
            $paymentDetails = $payment->getPaymentById($payment_id);
            if (!$paymentDetails) {
                throw new Exception('Payment not found');
            }
            
            // Verify user owns this payment (for patients)
            if ($user['role'] === 'patient') {
                $patientId = $payment->getPatientIdByUserId($user['user_id']);
                if ($paymentDetails['Pat_ID'] != $patientId) {
                    throw new Exception('Unauthorized access to payment');
                }
            }
            
            // Simulate payment processing
            // In a real application, you would integrate with a payment gateway like Stripe, PayPal, etc.
            $transaction_id = 'TXN_' . time() . '_' . rand(1000, 9999);
            
            // Validate card details (basic validation)
            if (empty($card_details['number']) || strlen($card_details['number']) < 15) {
                throw new Exception('Invalid card number');
            }
            
            if (empty($card_details['expiry']) || !preg_match('/^\d{2}\/\d{2}$/', $card_details['expiry'])) {
                throw new Exception('Invalid expiry date');
            }
            
            if (empty($card_details['cvv']) || strlen($card_details['cvv']) < 3) {
                throw new Exception('Invalid CVV');
            }
            
            // Process payment (simulate success)
            $result = $payment->processPayment($payment_id, [
                'transaction_id' => $transaction_id,
                'card_last_four' => substr($card_details['number'], -4),
                'payment_method' => $payment_method
            ]);
            
            if ($result['success']) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Payment processed successfully',
                    'transaction_id' => $transaction_id
                ]);
            } else {
                throw new Exception($result['message']);
            }
            break;
            
        case 'get_payment_details':
            $payment_id = $input['payment_id'] ?? '';
            
            if (!$payment_id) {
                throw new Exception('Payment ID is required');
            }
            
            $paymentDetails = $payment->getPaymentById($payment_id);
            if (!$paymentDetails) {
                throw new Exception('Payment not found');
            }
            
            // Verify user access
            if ($user['role'] === 'patient') {
                $patientId = $payment->getPatientIdByUserId($user['user_id']);
                if ($paymentDetails['Pat_ID'] != $patientId) {
                    throw new Exception('Unauthorized access to payment');
                }
            }
            
            echo json_encode([
                'success' => true,
                'payment' => $paymentDetails
            ]);
            break;
            
        default:
            throw new Exception('Invalid action');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
