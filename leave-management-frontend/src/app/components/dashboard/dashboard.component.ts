
import { Component, OnInit } from '@angular/core';
import { Router } from '@angular/router';
import { AuthService, User } from '../../services/auth.service';
import { LeaveService, Leave } from '../../services/leave.service';

@Component({
  selector: 'app-dashboard',
  template: `
    <div class="dashboard">
      <nav class="navbar">
        <div class="nav-brand">
          <h1>Leave Management System</h1>
        </div>
        <div class="nav-user">
          <span>Welcome, {{ currentUser?.EMPNAME }}</span>
          <button class="btn btn-outline" (click)="logout()">Logout</button>
        </div>
      </nav>

      <div class="dashboard-content">
        <div class="sidebar">
          <ul class="nav-menu">
            <li><a [routerLink]="['/dashboard']" routerLinkActive="active" [routerLinkActiveOptions]="{exact: true}">Dashboard</a></li>
            <li><a [routerLink]="['/apply-leave']" routerLinkActive="active">Apply Leave</a></li>
            <li><a [routerLink]="['/my-leaves']" routerLinkActive="active">My Leaves</a></li>
            <li *ngIf="authService.isAdmin() || authService.isHR()">
              <a [routerLink]="['/manage-leaves']" routerLinkActive="active">Manage Leaves</a>
            </li>
            <li *ngIf="authService.isAdmin()">
              <a [routerLink]="['/companies']" routerLinkActive="active">Companies</a>
            </li>
            <li *ngIf="authService.isAdmin()">
              <a [routerLink]="['/employees']" routerLinkActive="active">Employees</a>
            </li>
          </ul>
        </div>

        <div class="main-content">
          <div class="stats-cards" *ngIf="router.url === '/dashboard'">
            <div class="stat-card">
              <h3>{{ pendingLeaves.length }}</h3>
              <p>Pending Applications</p>
            </div>
            <div class="stat-card">
              <h3>{{ approvedLeaves.length }}</h3>
              <p>Approved Applications</p>
            </div>
            <div class="stat-card">
              <h3>{{ rejectedLeaves.length }}</h3>
              <p>Rejected Applications</p>
            </div>
            <div class="stat-card">
              <h3>{{ totalLeaves.length }}</h3>
              <p>Total Applications</p>
            </div>
          </div>

          <router-outlet></router-outlet>
        </div>
      </div>
    </div>
  `,
  styles: [`
    .dashboard {
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .navbar {
      background-color: #2c3e50;
      color: white;
      padding: 1rem 2rem;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .nav-brand h1 {
      margin: 0;
      font-size: 1.5rem;
    }

    .nav-user {
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .dashboard-content {
      flex: 1;
      display: flex;
    }

    .sidebar {
      width: 250px;
      background-color: #34495e;
      color: white;
    }

    .nav-menu {
      list-style: none;
      padding: 0;
      margin: 0;
    }

    .nav-menu li {
      border-bottom: 1px solid #2c3e50;
    }

    .nav-menu a {
      display: block;
      padding: 1rem;
      color: white;
      text-decoration: none;
      transition: background-color 0.3s;
    }

    .nav-menu a:hover, .nav-menu a.active {
      background-color: #2c3e50;
    }

    .main-content {
      flex: 1;
      padding: 2rem;
      background-color: #f8f9fa;
    }

    .stats-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 1rem;
      margin-bottom: 2rem;
    }

    .stat-card {
      background: white;
      padding: 1.5rem;
      border-radius: 8px;
      box-shadow: 0 2px 4px rgba(0,0,0,0.1);
      text-align: center;
    }

    .stat-card h3 {
      font-size: 2rem;
      margin: 0 0 0.5rem 0;
      color: #007bff;
    }

    .stat-card p {
      margin: 0;
      color: #666;
    }

    .btn {
      padding: 0.5rem 1rem;
      border: 1px solid;
      border-radius: 4px;
      cursor: pointer;
      text-decoration: none;
      display: inline-block;
    }

    .btn-outline {
      background: transparent;
      color: white;
      border-color: white;
    }

    .btn-outline:hover {
      background: white;
      color: #2c3e50;
    }
  `]
})
export class DashboardComponent implements OnInit {
  currentUser: User | null = null;
  pendingLeaves: Leave[] = [];
  approvedLeaves: Leave[] = [];
  rejectedLeaves: Leave[] = [];
  totalLeaves: Leave[] = [];

  constructor(
    public authService: AuthService,
    private leaveService: LeaveService,
    public router: Router
  ) {}

  ngOnInit(): void {
    this.currentUser = this.authService.getCurrentUser();
    this.loadLeaveStats();
  }

  private loadLeaveStats(): void {
    if (this.authService.isEmployee()) {
      this.leaveService.getMyLeaves().subscribe(leaves => {
        this.totalLeaves = leaves;
        this.pendingLeaves = leaves.filter(l => l.LEAVESTATUS === 'PENDING');
        this.approvedLeaves = leaves.filter(l => l.LEAVESTATUS === 'APPROVED');
        this.rejectedLeaves = leaves.filter(l => l.LEAVESTATUS === 'REJECTED');
      });
    } else {
      this.leaveService.getAllLeaves().subscribe(leaves => {
        this.totalLeaves = leaves;
        this.pendingLeaves = leaves.filter(l => l.LEAVESTATUS === 'PENDING');
        this.approvedLeaves = leaves.filter(l => l.LEAVESTATUS === 'APPROVED');
        this.rejectedLeaves = leaves.filter(l => l.LEAVESTATUS === 'REJECTED');
      });
    }
  }

  logout(): void {
    this.authService.logout().subscribe(() => {
      this.router.navigate(['/login']);
    });
  }
}
