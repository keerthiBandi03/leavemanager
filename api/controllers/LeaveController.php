
<?php
require_once('../include/leave.php');

class LeaveController {
    
    public function handleRequest($method, $action, $id) {
        session_start();
        if(!isset($_SESSION['EMPID'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            return;
        }
        
        switch($method) {
            case 'GET':
                if($action === 'my-leaves') {
                    $this->getMyLeaves();
                } elseif($action === 'pending') {
                    $this->getPendingLeaves();
                } elseif($action === 'approved') {
                    $this->getApprovedLeaves();
                } elseif($action === 'rejected') {
                    $this->getRejectedLeaves();
                } elseif($id) {
                    $this->getLeave($id);
                } else {
                    $this->getAllLeaves();
                }
                break;
            case 'POST':
                $this->createLeave();
                break;
            case 'PUT':
                $this->updateLeave($id);
                break;
            case 'DELETE':
                $this->deleteLeave($id);
                break;
            default:
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
        }
    }
    
    private function getAllLeaves() {
        global $mydb;
        $position = $_SESSION['EMPPOSITION'];
        
        if($position === 'Administrator') {
            $mydb->setQuery("SELECT l.*, e.EMPNAME FROM tblleave l 
                           LEFT JOIN tblemployee e ON l.EMPLOYID = e.EMPLOYID 
                           ORDER BY l.DATEPOSTED DESC");
        } elseif($position === 'Supervisor user' || $position === 'Manager user') {
            $company = $_SESSION['COMPANY'];
            $department = $_SESSION['DEPARTMENT'];
            $mydb->setQuery("SELECT l.*, e.EMPNAME FROM tblleave l 
                           LEFT JOIN tblemployee e ON l.EMPLOYID = e.EMPLOYID 
                           WHERE e.COMPANY = '$company' AND e.DEPARTMENT = '$department' 
                           ORDER BY l.DATEPOSTED DESC");
        } else {
            $this->getMyLeaves();
            return;
        }
        
        $leaves = $mydb->loadResultList();
        echo json_encode($leaves);
    }
    
    private function getMyLeaves() {
        global $mydb;
        $employId = $_SESSION['EMPLOYID'];
        $mydb->setQuery("SELECT * FROM tblleave WHERE EMPLOYID = '$employId' ORDER BY DATEPOSTED DESC");
        $leaves = $mydb->loadResultList();
        echo json_encode($leaves);
    }
    
    private function getPendingLeaves() {
        global $mydb;
        $position = $_SESSION['EMPPOSITION'];
        
        if($position === 'Normal user') {
            $employId = $_SESSION['EMPLOYID'];
            $mydb->setQuery("SELECT * FROM tblleave WHERE EMPLOYID = '$employId' AND LEAVESTATUS = 'PENDING' ORDER BY DATEPOSTED DESC");
        } else {
            $mydb->setQuery("SELECT l.*, e.EMPNAME FROM tblleave l 
                           LEFT JOIN tblemployee e ON l.EMPLOYID = e.EMPLOYID 
                           WHERE l.LEAVESTATUS = 'PENDING' ORDER BY l.DATEPOSTED DESC");
        }
        
        $leaves = $mydb->loadResultList();
        echo json_encode($leaves);
    }
    
    private function getApprovedLeaves() {
        global $mydb;
        $position = $_SESSION['EMPPOSITION'];
        
        if($position === 'Normal user') {
            $employId = $_SESSION['EMPLOYID'];
            $mydb->setQuery("SELECT * FROM tblleave WHERE EMPLOYID = '$employId' AND LEAVESTATUS = 'APPROVED' ORDER BY DATEPOSTED DESC");
        } else {
            $mydb->setQuery("SELECT l.*, e.EMPNAME FROM tblleave l 
                           LEFT JOIN tblemployee e ON l.EMPLOYID = e.EMPLOYID 
                           WHERE l.LEAVESTATUS = 'APPROVED' ORDER BY l.DATEPOSTED DESC");
        }
        
        $leaves = $mydb->loadResultList();
        echo json_encode($leaves);
    }
    
    private function getRejectedLeaves() {
        global $mydb;
        $position = $_SESSION['EMPPOSITION'];
        
        if($position === 'Normal user') {
            $employId = $_SESSION['EMPLOYID'];
            $mydb->setQuery("SELECT * FROM tblleave WHERE EMPLOYID = '$employId' AND LEAVESTATUS = 'REJECTED' ORDER BY DATEPOSTED DESC");
        } else {
            $mydb->setQuery("SELECT l.*, e.EMPNAME FROM tblleave l 
                           LEFT JOIN tblemployee e ON l.EMPLOYID = e.EMPLOYID 
                           WHERE l.LEAVESTATUS = 'REJECTED' ORDER BY l.DATEPOSTED DESC");
        }
        
        $leaves = $mydb->loadResultList();
        echo json_encode($leaves);
    }
    
    private function createLeave() {
        $input = json_decode(file_get_contents('php://input'), true);
        
        $required = ['DATESTART', 'DATEEND', 'SHIFTTIME', 'TYPEOFLEAVE', 'REASON'];
        foreach($required as $field) {
            if(!isset($input[$field])) {
                http_response_code(400);
                echo json_encode(['error' => "$field is required"]);
                return;
            }
        }
        
        $EMPLOYID = $_SESSION['EMPLOYID'];
        $DATESTART = $input['DATESTART'];
        $DATEEND = $input['DATEEND'];
        $SHIFTTIME = $input['SHIFTTIME'];
        $TYPEOFLEAVE = $input['TYPEOFLEAVE'];
        $REASON = $input['REASON'];
        $LEAVESTATUS = 'PENDING';
        $ADMINREMARKS = 'N/A';
        $DATEPOSTED = date("Y-m-d");
        
        // Calculate number of days
        $NODAYS = 0;
        if ($SHIFTTIME == 'AM' || $SHIFTTIME == 'PM') {
            $DF = date_create($DATESTART);
            $DT = date_create($DATEEND);
            $diff = date_diff($DF, $DT);
            $NODAYS = ((1 + ($diff->format("%a"))) / 2);
        } elseif ($SHIFTTIME == 'All Day') {
            $DF = date_create($DATESTART);
            $DT = date_create($DATEEND);
            $diff = date_diff($DF, $DT);
            $NODAYS = (1 + $diff->format("%a"));
        }
        
        $leave = new Leave();
        $leave->EMPLOYID = $EMPLOYID;
        $leave->DATESTART = $DATESTART;
        $leave->DATEEND = $DATEEND;
        $leave->NODAYS = $NODAYS;
        $leave->SHIFTTIME = $SHIFTTIME;
        $leave->TYPEOFLEAVE = $TYPEOFLEAVE;
        $leave->REASON = $REASON;
        $leave->LEAVESTATUS = $LEAVESTATUS;
        $leave->ADMINREMARKS = $ADMINREMARKS;
        $leave->DATEPOSTED = $DATEPOSTED;
        
        $result = $leave->create();
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Leave application created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create leave application']);
        }
    }
    
    private function updateLeave($id) {
        if($_SESSION['EMPPOSITION'] === 'Normal user') {
            http_response_code(403);
            echo json_encode(['error' => 'Access denied']);
            return;
        }
        
        $input = json_decode(file_get_contents('php://input'), true);
        
        if(!$id || !isset($input['LEAVESTATUS'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Leave ID and status required']);
            return;
        }
        
        $leave = new Leave();
        $leave->LEAVESTATUS = $input['LEAVESTATUS'];
        $leave->ADMINREMARKS = $input['ADMINREMARKS'] ?? '';
        $leave->DATEPOSTED = date("Y-m-d");
        
        $result = $leave->update($id);
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Leave status updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to update leave status']);
        }
    }
    
    private function deleteLeave($id) {
        if(!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Leave ID required']);
            return;
        }
        
        $leave = new Leave();
        $result = $leave->delete($id);
        
        if($result) {
            echo json_encode(['success' => true, 'message' => 'Leave deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete leave']);
        }
    }
}
?>
