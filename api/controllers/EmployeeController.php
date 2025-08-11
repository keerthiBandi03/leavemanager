
<?php
require_once('../include/member.php');

class EmployeeController {
    
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
                    $this->getEmployee($id);
                } else {
                    $this->getAllEmployees();
                }
                break;
            case 'POST':
                $this->createEmployee();
                break;
            case 'PUT':
                $this->updateEmployee($id);
                break;
            case 'DELETE':
                $this->deleteEmployee($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    private function getAllEmployees() {
        global $mydb;
        $position = $_SESSION['EMPPOSITION'];
        
        if($position === 'Administrator') {
            $mydb->setQuery("SELECT * FROM tblemployee ORDER BY EMPNAME");
        } else {
            $company = $_SESSION['COMPANY'];
            $department = $_SESSION['DEPARTMENT'];
            $mydb->setQuery("SELECT * FROM tblemployee 
                           WHERE COMPANY = '$company' AND DEPARTMENT = '$department' 
                           ORDER BY EMPNAME");
        }
        
        $employees = $mydb->loadResultList();
        
        // Remove password from response
        foreach($employees as $employee) {
            unset($employee->PASSWRD);
        }
        
        echo json_encode($employees);
    }
    
    private function getEmployee($id) {
        global $mydb;
        $mydb->setQuery("SELECT * FROM tblemployee WHERE EMPID = '$id'");
        $employee = $mydb->loadSingleResult();
        
        if($employee) {
            unset($employee->PASSWRD);
            echo json_encode($employee);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Employee not found']);
        }
    }
    
    private function createEmployee() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $required = ['EMPLOYID', 'EMPNAME', 'EMPPOSITION', 'USERNAME', 'PASSWRD', 'EMPSEX', 'COMPANY', 'DEPARTMENT'];
        foreach($required as $field) {
            if(!isset($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field is required"]);
                return;
            }
        }
        
        $employee = new Employee();
        $employee->EMPLOYID = $input['EMPLOYID'];
        $employee->EMPNAME = $input['EMPNAME'];
        $employee->EMPPOSITION = $input['EMPPOSITION'];
        $employee->USERNAME = $input['USERNAME'];
        $employee->PASSWRD = sha1($input['PASSWRD']);
        $employee->ACCSTATUS = $input['ACCSTATUS'] ?? 'YES';
        $employee->EMPSEX = $input['EMPSEX'];
        $employee->COMPANY = $input['COMPANY'];
        $employee->DEPARTMENT = $input['DEPARTMENT'];
        $employee->AVELEAVE = $input['AVELEAVE'] ?? 15;
        
        $result = $employee->create();
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Employee created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create employee']);
        }
    }
    
    private function updateEmployee($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Employee ID required']);
            return;
        }
        
        $employee = new Employee();
        if(isset($input['EMPNAME'])) $employee->EMPNAME = $input['EMPNAME'];
        if(isset($input['EMPPOSITION'])) $employee->EMPPOSITION = $input['EMPPOSITION'];
        if(isset($input['USERNAME'])) $employee->USERNAME = $input['USERNAME'];
        if(isset($input['PASSWRD'])) $employee->PASSWRD = sha1($input['PASSWRD']);
        if(isset($input['ACCSTATUS'])) $employee->ACCSTATUS = $input['ACCSTATUS'];
        if(isset($input['EMPSEX'])) $employee->EMPSEX = $input['EMPSEX'];
        if(isset($input['COMPANY'])) $employee->COMPANY = $input['COMPANY'];
        if(isset($input['DEPARTMENT'])) $employee->DEPARTMENT = $input['DEPARTMENT'];
        if(isset($input['AVELEAVE'])) $employee->AVELEAVE = $input['AVELEAVE'];
        
        $result = $employee->update($id);
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Employee updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update employee']);
        }
    }
    
    private function deleteEmployee($id) {
        if(!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Employee ID required']);
            return;
        }
        
        $employee = new Employee();
        $result = $employee->delete($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete employee']);
        }
    }
}
?>
