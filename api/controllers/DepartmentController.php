
<?php
require_once('../include/department.php');

class DepartmentController {
    
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
                    $this->getDepartment($id);
                } else {
                    $this->getAllDepartments();
                }
                break;
            case 'POST':
                $this->createDepartment();
                break;
            case 'PUT':
                $this->updateDepartment($id);
                break;
            case 'DELETE':
                $this->deleteDepartment($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    private function getAllDepartments() {
        global $mydb;
        $mydb->setQuery("SELECT d.*, c.COMPANY FROM tbldepartment d 
                        LEFT JOIN tblcompany c ON d.COMPID = c.COMPID 
                        ORDER BY d.DEPARTMENT");
        $departments = $mydb->loadResultList();
        echo json_encode($departments);
    }
    
    private function getDepartment($id) {
        global $mydb;
        $mydb->setQuery("SELECT d.*, c.COMPANY FROM tbldepartment d 
                        LEFT JOIN tblcompany c ON d.COMPID = c.COMPID 
                        WHERE d.DEPTID = $id");
        $department = $mydb->loadSingleResult();
        
        if($department) {
            echo json_encode($department);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Department not found']);
        }
    }
    
    private function createDepartment() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!isset($input['DEPARTMENT']) || !isset($input['COMPID'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Department name and company ID required']);
            return;
        }
        
        $department = new Department();
        $department->DEPARTMENT = $input['DEPARTMENT'];
        $department->COMPID = $input['COMPID'];
        
        $result = $department->create();
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Department created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create department']);
        }
    }
    
    private function updateDepartment($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!$id || !isset($input['DEPARTMENT'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Department ID and name required']);
            return;
        }
        
        $department = new Department();
        $department->DEPARTMENT = $input['DEPARTMENT'];
        $department->COMPID = $input['COMPID'] ?? null;
        
        $result = $department->update($id);
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Department updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update department']);
        }
    }
    
    private function deleteDepartment($id) {
        if(!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Department ID required']);
            return;
        }
        
        $department = new Department();
        $result = $department->delete($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Department deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete department']);
        }
    }
}
?>
