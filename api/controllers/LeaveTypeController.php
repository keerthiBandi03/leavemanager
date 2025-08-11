
<?php
require_once('../include/leavetype.php');

class LeaveTypeController {
    
    public function handleRequest($method, $action, $id) {
        session_start();
        if(!isset($_SESSION['EMPID'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        switch($method) {
            case 'GET':
                if($id) {
                    $this->getLeaveType($id);
                } else {
                    $this->getAllLeaveTypes();
                }
                break;
            case 'POST':
                $this->createLeaveType();
                break;
            case 'PUT':
                $this->updateLeaveType($id);
                break;
            case 'DELETE':
                $this->deleteLeaveType($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    private function getAllLeaveTypes() {
        global $mydb;
        $mydb->setQuery("SELECT * FROM tblleavetype ORDER BY LEAVETYPE");
        $leaveTypes = $mydb->loadResultList();
        echo json_encode($leaveTypes);
    }
    
    private function getLeaveType($id) {
        global $mydb;
        $mydb->setQuery("SELECT * FROM tblleavetype WHERE LEAVETYPEID = $id");
        $leaveType = $mydb->loadSingleResult();
        
        if($leaveType) {
            echo json_encode($leaveType);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Leave type not found']);
        }
    }
    
    private function createLeaveType() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!isset($input['LEAVETYPE'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Leave type name required']);
            return;
        }
        
        $leaveType = new LeaveType();
        $leaveType->LEAVETYPE = $input['LEAVETYPE'];
        $leaveType->DESCRIPTION = $input['DESCRIPTION'] ?? '';
        
        $result = $leaveType->create();
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Leave type created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create leave type']);
        }
    }
    
    private function updateLeaveType($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!$id || !isset($input['LEAVETYPE'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Leave type ID and name required']);
            return;
        }
        
        $leaveType = new LeaveType();
        $leaveType->LEAVETYPE = $input['LEAVETYPE'];
        $leaveType->DESCRIPTION = $input['DESCRIPTION'] ?? '';
        
        $result = $leaveType->update($id);
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Leave type updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update leave type']);
        }
    }
    
    private function deleteLeaveType($id) {
        if(!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Leave type ID required']);
            return;
        }
        
        $leaveType = new LeaveType();
        $result = $leaveType->delete($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Leave type deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete leave type']);
        }
    }
}
?>
