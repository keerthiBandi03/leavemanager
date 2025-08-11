
<?php
class AuthController {
    
    public function handleRequest($method, $action, $id) {
        switch($method) {
            case 'POST':
                if($action === 'login') {
                    $this->login();
                } elseif($action === 'logout') {
                    $this->logout();
                }
                break;
            case 'GET':
                if($action === 'verify') {
                    $this->verifyToken();
                }
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    private function login() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!isset($input['username']) || !isset($input['password'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password required']);
            return;
        }
        
        global $mydb;
        $username = $mydb->escape_value($input['username']);
        $password = sha1($input['password']);
        
        $mydb->setQuery("SELECT * FROM tblemployee WHERE USERNAME = '$username' AND PASSWRD = '$password' AND ACCSTATUS = 'YES'");
        $user = $mydb->loadSingleResult();
        
        if($user) {
            session_start();
            $_SESSION['EMPID'] = $user->EMPID;
            $_SESSION['EMPLOYID'] = $user->EMPLOYID;
            $_SESSION['EMPNAME'] = $user->EMPNAME;
            $_SESSION['EMPPOSITION'] = $user->EMPPOSITION;
            $_SESSION['COMPANY'] = $user->COMPANY;
            $_SESSION['DEPARTMENT'] = $user->DEPARTMENT;
            
            echo json_encode([
                'success' => true,
                'user' => [
                    'EMPID' => $user->EMPID,
                    'EMPLOYID' => $user->EMPLOYID,
                    'EMPNAME' => $user->EMPNAME,
                    'EMPPOSITION' => $user->EMPPOSITION,
                    'COMPANY' => $user->COMPANY,
                    'DEPARTMENT' => $user->DEPARTMENT,
                    'EMPSEX' => $user->EMPSEX
                ],
                'token' => session_id()
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
        }
    }
    
    private function logout() {
        session_start();
        session_destroy();
        echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
    }
    
    private function verifyToken() {
        session_start();
        if(isset($_SESSION['EMPID'])) {
            echo json_encode([
                'valid' => true,
                'user' => [
                    'EMPID' => $_SESSION['EMPID'],
                    'EMPLOYID' => $_SESSION['EMPLOYID'],
                    'EMPNAME' => $_SESSION['EMPNAME'],
                    'EMPPOSITION' => $_SESSION['EMPPOSITION'],
                    'COMPANY' => $_SESSION['COMPANY'],
                    'DEPARTMENT' => $_SESSION['DEPARTMENT']
                ]
            ]);
        } else {
            http_response_code(401);
            echo json_encode(['valid' => false]);
        }
    }
}
?>
