
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    exit(0);
}

require_once('../include/initialize.php');

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$path = str_replace('/api', '', $path);
$segments = explode('/', trim($path, '/'));

$controller = $segments[0] ?? '';
$action = $segments[1] ?? '';
$id = $segments[2] ?? '';

switch($controller) {
    case 'auth':
        require_once('controllers/AuthController.php');
        $authController = new AuthController();
        $authController->handleRequest($_SERVER['REQUEST_METHOD'], $action, $id);
        break;
    
    case 'companies':
        require_once('controllers/CompanyController.php');
        $companyController = new CompanyController();
        $companyController->handleRequest($_SERVER['REQUEST_METHOD'], $action, $id);
        break;
    
    case 'departments':
        require_once('controllers/DepartmentController.php');
        $departmentController = new DepartmentController();
        $departmentController->handleRequest($_SERVER['REQUEST_METHOD'], $action, $id);
        break;
    
    case 'employees':
        require_once('controllers/EmployeeController.php');
        $employeeController = new EmployeeController();
        $employeeController->handleRequest($_SERVER['REQUEST_METHOD'], $action, $id);
        break;
    
    case 'leave-types':
        require_once('controllers/LeaveTypeController.php');
        $leaveTypeController = new LeaveTypeController();
        $leaveTypeController->handleRequest($_SERVER['REQUEST_METHOD'], $action, $id);
        break;
    
    case 'leaves':
        require_once('controllers/LeaveController.php');
        $leaveController = new LeaveController();
        $leaveController->handleRequest($_SERVER['REQUEST_METHOD'], $action, $id);
        break;
    
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
?>
