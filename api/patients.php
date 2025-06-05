<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

require_once '../config/database.php';
require_once '../classes/Patient.php';

try {
    $database = new Database();
    $patient = new Patient();
    
    $method = $_SERVER['REQUEST_METHOD'];
    $input = json_decode(file_get_contents('php://input'), true);
    
    switch ($method) {
        case 'GET':
            if (isset($_GET['action'])) {
                switch ($_GET['action']) {
                    case 'get_patient_details':
                        $patientId = $_GET['patient_id'] ?? null;
                        if ($patientId) {
                            $patientDetails = $patient->getPatientById($patientId);
                            if ($patientDetails) {
                                echo json_encode([
                                    'success' => true,
                                    'patient' => $patientDetails
                                ]);
                            } else {
                                echo json_encode([
                                    'success' => false,
                                    'message' => 'Patient not found'
                                ]);
                            }
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Patient ID required'
                            ]);
                        }
                        break;
                    
                    case 'search':
                        $search = $_GET['search'] ?? '';
                        $limit = $_GET['limit'] ?? 20;
                        $offset = $_GET['offset'] ?? 0;
                        
                        $patients = $patient->searchPatients($search, $limit, $offset);
                        echo json_encode([
                            'success' => true,
                            'patients' => $patients,
                            'total' => count($patients)
                        ]);
                        break;
                    
                    default:
                        $limit = $_GET['limit'] ?? 20;
                        $offset = $_GET['offset'] ?? 0;
                        $search = $_GET['search'] ?? '';
                        
                        $patients = $patient->getAllPatients($limit, $offset, $search);
                        echo json_encode([
                            'success' => true,
                            'patients' => $patients
                        ]);
                }
            } else {
                $limit = $_GET['limit'] ?? 20;
                $offset = $_GET['offset'] ?? 0;
                $search = $_GET['search'] ?? '';
                
                $patients = $patient->getAllPatients($limit, $offset, $search);
                echo json_encode([
                    'success' => true,
                    'patients' => $patients
                ]);
            }
            break;
            
        case 'POST':
            $action = $input['action'] ?? '';
            
            switch ($action) {
                case 'get_patient_details':
                    $patientId = $input['patient_id'] ?? null;
                    if ($patientId) {
                        $patientDetails = $patient->getPatientDetailsWithAppointments($patientId);
                        if ($patientDetails) {
                            echo json_encode([
                                'success' => true,
                                'patient' => $patientDetails
                            ]);
                        } else {
                            echo json_encode([
                                'success' => false,
                                'message' => 'Patient not found'
                            ]);
                        }
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Patient ID required'
                        ]);
                    }
                    break;
                
                case 'create':
                    $result = $patient->createPatient($input);
                    echo json_encode($result);
                    break;
                
                case 'update':
                    $patientId = $input['patient_id'] ?? null;
                    if ($patientId) {
                        $result = $patient->updatePatient($patientId, $input);
                        echo json_encode($result);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Patient ID required'
                        ]);
                    }
                    break;
                
                case 'delete':
                    $patientId = $input['patient_id'] ?? null;
                    if ($patientId) {
                        $result = $patient->deletePatient($patientId);
                        echo json_encode($result);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Patient ID required'
                        ]);
                    }
                    break;
                
                default:
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid action'
                    ]);
            }
            break;
            
        default:
            echo json_encode([
                'success' => false,
                'message' => 'Method not allowed'
            ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
}
?>