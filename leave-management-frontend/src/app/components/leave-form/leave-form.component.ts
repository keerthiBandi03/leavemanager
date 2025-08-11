
import { Component, OnInit } from '@angular/core';
import { FormBuilder, FormGroup, Validators } from '@angular/forms';
import { Router } from '@angular/router';
import { LeaveService } from '../../services/leave.service';

@Component({
  selector: 'app-leave-form',
  template: `
    <div class="container-fluid">
      <div class="card">
        <div class="card-header">
          <h5>Apply for Leave</h5>
        </div>
        <div class="card-body">
          <form [formGroup]="leaveForm" (ngSubmit)="onSubmit()">
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="leaveType">Leave Type</label>
                  <select class="form-control" id="leaveType" formControlName="TYPEOFLEAVE">
                    <option value="">Select Leave Type</option>
                    <option value="SICK LEAVE">Sick Leave</option>
                    <option value="CASUAL LEAVE">Casual Leave</option>
                    <option value="EARNED LEAVE">Earned Leave</option>
                    <option value="MATERNITY LEAVE">Maternity Leave</option>
                    <option value="PATERNITY LEAVE">Paternity Leave</option>
                    <option value="UNPAID LEAVE">Unpaid Leave</option>
                  </select>
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="shiftTime">Shift Time</label>
                  <select class="form-control" id="shiftTime" formControlName="SHIFTTIME">
                    <option value="All Day">All Day</option>
                    <option value="Morning">Morning</option>
                    <option value="Afternoon">Afternoon</option>
                  </select>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="startDate">Start Date</label>
                  <input type="date" class="form-control" id="startDate" formControlName="DATESTART">
                </div>
              </div>
              <div class="col-md-6">
                <div class="form-group mb-3">
                  <label for="endDate">End Date</label>
                  <input type="date" class="form-control" id="endDate" formControlName="DATEEND">
                </div>
              </div>
            </div>
            <div class="form-group mb-3">
              <label for="reason">Reason</label>
              <textarea class="form-control" id="reason" rows="4" formControlName="REASON" placeholder="Enter reason for leave"></textarea>
            </div>
            <div class="form-group">
              <button type="submit" class="btn btn-primary" [disabled]="leaveForm.invalid || loading">
                {{ loading ? 'Submitting...' : 'Submit Application' }}
              </button>
              <button type="button" class="btn btn-secondary ms-2" routerLink="/dashboard">Cancel</button>
            </div>
            <div *ngIf="error" class="alert alert-danger mt-3">
              {{ error }}
            </div>
            <div *ngIf="success" class="alert alert-success mt-3">
              {{ success }}
            </div>
          </form>
        </div>
      </div>
    </div>
  `
})
export class LeaveFormComponent implements OnInit {
  leaveForm: FormGroup;
  loading = false;
  error = '';
  success = '';

  constructor(
    private fb: FormBuilder,
    private leaveService: LeaveService,
    private router: Router
  ) {
    this.leaveForm = this.fb.group({
      TYPEOFLEAVE: ['', Validators.required],
      DATESTART: ['', Validators.required],
      DATEEND: ['', Validators.required],
      SHIFTTIME: ['All Day', Validators.required],
      REASON: ['', Validators.required]
    });
  }

  ngOnInit(): void {}

  onSubmit(): void {
    if (this.leaveForm.valid) {
      this.loading = true;
      this.error = '';
      this.success = '';

      this.leaveService.createLeave(this.leaveForm.value).subscribe({
        next: (response) => {
          this.loading = false;
          this.success = 'Leave application submitted successfully!';
          setTimeout(() => {
            this.router.navigate(['/leaves']);
          }, 2000);
        },
        error: (error) => {
          this.loading = false;
          this.error = error.error?.message || 'Failed to submit leave application';
        }
      });
    }
  }
}
