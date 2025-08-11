
import { Component, OnInit } from '@angular/core';
import { LeaveService, Leave } from '../../services/leave.service';
import { AuthService } from '../../services/auth.service';

@Component({
  selector: 'app-leave-list',
  template: `
    <div class="container-fluid">
      <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
          <h5>My Leave Applications</h5>
          <button class="btn btn-primary" routerLink="/apply-leave">Apply Leave</button>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Leave Type</th>
                  <th>Start Date</th>
                  <th>End Date</th>
                  <th>Days</th>
                  <th>Reason</th>
                  <th>Status</th>
                  <th>Remarks</th>
                </tr>
              </thead>
              <tbody>
                <tr *ngFor="let leave of leaves">
                  <td>{{ leave.TYPEOFLEAVE }}</td>
                  <td>{{ leave.DATESTART | date }}</td>
                  <td>{{ leave.DATEEND | date }}</td>
                  <td>{{ leave.NODAYS }}</td>
                  <td>{{ leave.REASON }}</td>
                  <td>
                    <span class="badge" 
                          [class.bg-warning]="leave.LEAVESTATUS === 'PENDING'"
                          [class.bg-success]="leave.LEAVESTATUS === 'APPROVED'"
                          [class.bg-danger]="leave.LEAVESTATUS === 'REJECTED'">
                      {{ leave.LEAVESTATUS }}
                    </span>
                  </td>
                  <td>{{ leave.ADMINREMARKS || 'N/A' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  `
})
export class LeaveListComponent implements OnInit {
  leaves: Leave[] = [];

  constructor(
    private leaveService: LeaveService,
    private authService: AuthService
  ) {}

  ngOnInit(): void {
    this.loadLeaves();
  }

  loadLeaves(): void {
    this.leaveService.getMyLeaves().subscribe({
      next: (leaves) => {
        this.leaves = leaves;
      },
      error: (error) => {
        console.error('Error loading leaves:', error);
      }
    });
  }
}
