
<?php
require_once('../include/company.php');

class CompanyController {
    
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
                    $this->getCompany($id);
                } else {
                    $this->getAllCompanies();
                }
                break;
            case 'POST':
                $this->createCompany();
                break;
            case 'PUT':
                $this->updateCompany($id);
                break;
            case 'DELETE':
                $this->deleteCompany($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    private function getAllCompanies() {
        global $mydb;
        $mydb->setQuery("SELECT * FROM tblcompany ORDER BY COMPANY");
        $companies = $mydb->loadResultList();
        echo json_encode($companies);
    }
    
    private function getCompany($id) {
        global $mydb;
        $mydb->setQuery("SELECT * FROM tblcompany WHERE COMPID = $id");
        $company = $mydb->loadSingleResult();
        
        if($company) {
            echo json_encode($company);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Company not found']);
        }
    }
    
    private function createCompany() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!isset($input['COMPANY'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Company name required']);
            return;
        }
        
        $company = new Company();
        $company->COMPANY = $input['COMPANY'];
        
        $result = $company->create();
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Company created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create company']);
        }
    }
    
    private function updateCompany($id) {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!$id || !isset($input['COMPANY'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Company ID and name required']);
            return;
        }
        
        $company = new Company();
        $company->COMPANY = $input['COMPANY'];
        
        $result = $company->update($id);
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Company updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update company']);
        }
    }
    
    private function deleteCompany($id) {
        if(!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Company ID required']);
            return;
        }
        
        $company = new Company();
        $result = $company->delete($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Company deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete company']);
        }
    }
}
?>
