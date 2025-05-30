<?php
require_once 'Database.php';

class Payment {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function createPayment($data) {
        try {
            $sql = "INSERT INTO payment (Pat_ID, Amount, PaymentMeth_ID, PaymentStat_ID) VALUES (?, ?, ?, ?)";
            $this->db->execute($sql, [
                $data['patient_id'],
                $data['amount'],
                $data['payment_method_id'],
                $data['payment_status_id'] ?? 1 // Default to pending
            ]);
            
            return [
                'success' => true,
                'message' => 'Payment record created successfully.',
                'payment_id' => $this->db->lastInsertId()
            ];
        } catch (Exception $e) {
            error_log("Create payment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to create payment record.'];
        }
    }
    
    public function updatePayment($id, $data) {
        try {
            $sql = "UPDATE payment SET Amount = ?, PaymentMeth_ID = ?, PaymentStat_ID = ? WHERE Payment_ID = ?";
            $this->db->execute($sql, [
                $data['amount'],
                $data['payment_method_id'],
                $data['payment_status_id'],
                $id
            ]);
            
            return ['success' => true, 'message' => 'Payment updated successfully.'];
        } catch (Exception $e) {
            error_log("Update payment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update payment.'];
        }
    }
    
    public function getPaymentById($id) {
        try {
            $sql = "SELECT p.*, pat.Pat_Name, pat.Pat_Email, pm.MethodName, ps.Status_Descr 
                    FROM payment p 
                    JOIN patient pat ON p.Pat_ID = pat.Pat_ID 
                    JOIN paymentmethod pm ON p.PaymentMeth_ID = pm.PaymentMeth_ID 
                    JOIN paymentstatus ps ON p.PaymentStat_ID = ps.PaymentStat_ID 
                    WHERE p.Payment_ID = ?";
            
            return $this->db->fetch($sql, [$id]);
        } catch (Exception $e) {
            error_log("Get payment by ID error: " . $e->getMessage());
            return null;
        }
    }
    
    public function getAllPayments($limit = 50, $offset = 0, $filters = []) {
        try {
            $sql = "SELECT p.*, pat.Pat_Name, pat.Pat_Email, pm.MethodName, ps.Status_Descr 
                    FROM payment p 
                    JOIN patient pat ON p.Pat_ID = pat.Pat_ID 
                    JOIN paymentmethod pm ON p.PaymentMeth_ID = pm.PaymentMeth_ID 
                    JOIN paymentstatus ps ON p.PaymentStat_ID = ps.PaymentStat_ID 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['patient_id'])) {
                $sql .= " AND p.Pat_ID = ?";
                $params[] = $filters['patient_id'];
            }
            
            if (!empty($filters['status_id'])) {
                $sql .= " AND p.PaymentStat_ID = ?";
                $params[] = $filters['status_id'];
            }
            
            if (!empty($filters['method_id'])) {
                $sql .= " AND p.PaymentMeth_ID = ?";
                $params[] = $filters['method_id'];
            }
            
            if (!empty($filters['min_amount'])) {
                $sql .= " AND p.Amount >= ?";
                $params[] = $filters['min_amount'];
            }
            
            if (!empty($filters['max_amount'])) {
                $sql .= " AND p.Amount <= ?";
                $params[] = $filters['max_amount'];
            }
            
            if (!empty($filters['patient_search'])) {
                $sql .= " AND (pat.Pat_Name LIKE ? OR pat.Pat_Email LIKE ?)";
                $searchTerm = "%{$filters['patient_search']}%";
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            $sql .= " ORDER BY p.Payment_ID DESC LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            return $this->db->fetchAll($sql, $params);
        } catch (Exception $e) {
            error_log("Get all payments error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPatientPayments($patientId, $limit = 20) {
        try {
            $sql = "SELECT p.*, pm.MethodName, ps.Status_Descr 
                    FROM payment p 
                    JOIN paymentmethod pm ON p.PaymentMeth_ID = pm.PaymentMeth_ID 
                    JOIN paymentstatus ps ON p.PaymentStat_ID = ps.PaymentStat_ID 
                    WHERE p.Pat_ID = ? 
                    ORDER BY p.Payment_ID DESC 
                    LIMIT ?";
            
            return $this->db->fetchAll($sql, [$patientId, $limit]);
        } catch (Exception $e) {
            error_log("Get patient payments error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPaymentMethods() {
        try {
            return $this->db->fetchAll("SELECT * FROM paymentmethod ORDER BY MethodName");
        } catch (Exception $e) {
            error_log("Get payment methods error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getPaymentStatuses() {
        try {
            return $this->db->fetchAll("SELECT * FROM paymentstatus ORDER BY PaymentStat_ID");
        } catch (Exception $e) {
            error_log("Get payment statuses error: " . $e->getMessage());
            return [];
        }
    }
    
    public function updatePaymentStatus($id, $statusId) {
        try {
            $sql = "UPDATE payment SET PaymentStat_ID = ? WHERE Payment_ID = ?";
            $this->db->execute($sql, [$statusId, $id]);
            
            return ['success' => true, 'message' => 'Payment status updated successfully.'];
        } catch (Exception $e) {
            error_log("Update payment status error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Failed to update payment status.'];
        }
    }
    
    public function processPayment($paymentId, $transactionData) {
        try {
            $this->db->beginTransaction();
            
            // Update payment status to paid
            $this->updatePaymentStatus($paymentId, 2); // 2 = Paid status
            
            // Log transaction details (you would implement transaction logging table)
            // $this->logTransaction($paymentId, $transactionData);
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Payment processed successfully.'];
        } catch (Exception $e) {
            $this->db->rollback();
            error_log("Process payment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Payment processing failed.'];
        }
    }
    
    public function refundPayment($paymentId, $refundAmount = null) {
        try {
            $payment = $this->getPaymentById($paymentId);
            if (!$payment) {
                return ['success' => false, 'message' => 'Payment not found.'];
            }
            
            if ($payment['Status_Descr'] !== 'Paid') {
                return ['success' => false, 'message' => 'Can only refund paid payments.'];
            }
            
            $refundAmount = $refundAmount ?? $payment['Amount'];
            
            // Update payment status to refunded
            $this->updatePaymentStatus($paymentId, 4); // 4 = Refunded status
            
            return ['success' => true, 'message' => 'Payment refunded successfully.'];
        } catch (Exception $e) {
            error_log("Refund payment error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Payment refund failed.'];
        }
    }
    
    public function getPaymentStats($filters = []) {
        try {
            $stats = [
                'total_payments' => 0,
                'total_amount' => 0,
                'pending_payments' => 0,
                'pending_amount' => 0,
                'completed_payments' => 0,
                'completed_amount' => 0,
                'failed_payments' => 0,
                'refunded_amount' => 0
            ];
            
            $sql = "SELECT ps.Status_Descr, COUNT(*) as count, SUM(p.Amount) as total_amount 
                    FROM payment p 
                    JOIN paymentstatus ps ON p.PaymentStat_ID = ps.PaymentStat_ID 
                    WHERE 1=1";
            
            $params = [];
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND DATE(p.created_at) >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND DATE(p.created_at) <= ?";
                $params[] = $filters['date_to'];
            }
            
            $sql .= " GROUP BY p.PaymentStat_ID";
            
            $results = $this->db->fetchAll($sql, $params);
            
            foreach ($results as $result) {
                $stats['total_payments'] += $result['count'];
                $stats['total_amount'] += $result['total_amount'];
                
                switch ($result['Status_Descr']) {
                    case 'Pending':
                        $stats['pending_payments'] = $result['count'];
                        $stats['pending_amount'] = $result['total_amount'];
                        break;
                    case 'Paid':
                    case 'Completed':
                        $stats['completed_payments'] += $result['count'];
                        $stats['completed_amount'] += $result['total_amount'];
                        break;
                    case 'Failed':
                    case 'Declined':
                        $stats['failed_payments'] += $result['count'];
                        break;
                    case 'Refunded':
                        $stats['refunded_amount'] += $result['total_amount'];
                        break;
                }
            }
            
            return $stats;
        } catch (Exception $e) {
            error_log("Get payment stats error: " . $e->getMessage());
            return [];
        }
    }
    
    public function getMonthlyPaymentStats($year = null) {
        try {
            $year = $year ?? date('Y');
            
            $sql = "SELECT MONTH(p.created_at) as month, 
                           COUNT(*) as payment_count, 
                           SUM(p.Amount) as total_amount,
                           AVG(p.Amount) as avg_amount
                    FROM payment p 
                    WHERE YEAR(p.created_at) = ? AND p.PaymentStat_ID = 2 
                    GROUP BY MONTH(p.created_at) 
                    ORDER BY month";
            
            return $this->db->fetchAll($sql, [$year]);
        } catch (Exception $e) {
            error_log("Get monthly payment stats error: " . $e->getMessage());
            return [];
        }
    }
}
?>
