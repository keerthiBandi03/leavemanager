
import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Leave {
  LEAVEID?: number;
  EMPLOYID: string;
  EMPNAME?: string;
  DATESTART: string;
  DATEEND: string;
  NODAYS: number;
  SHIFTTIME: string;
  TYPEOFLEAVE: string;
  REASON: string;
  LEAVESTATUS: string;
  ADMINREMARKS: string;
  DATEPOSTED: string;
}

export interface LeaveRequest {
  DATESTART: string;
  DATEEND: string;
  SHIFTTIME: string;
  TYPEOFLEAVE: string;
  REASON: string;
}

@Injectable({
  providedIn: 'root'
})
export class LeaveService {
  private apiUrl = 'http://0.0.0.0:5000/api/leaves';

  constructor(private http: HttpClient) {}

  getAllLeaves(): Observable<Leave[]> {
    return this.http.get<Leave[]>(this.apiUrl);
  }

  getMyLeaves(): Observable<Leave[]> {
    return this.http.get<Leave[]>(`${this.apiUrl}/my-leaves`);
  }

  getPendingLeaves(): Observable<Leave[]> {
    return this.http.get<Leave[]>(`${this.apiUrl}/pending`);
  }

  getApprovedLeaves(): Observable<Leave[]> {
    return this.http.get<Leave[]>(`${this.apiUrl}/approved`);
  }

  getRejectedLeaves(): Observable<Leave[]> {
    return this.http.get<Leave[]>(`${this.apiUrl}/rejected`);
  }

  createLeave(leaveRequest: LeaveRequest): Observable<any> {
    return this.http.post(this.apiUrl, leaveRequest);
  }

  updateLeaveStatus(id: number, status: string, remarks: string): Observable<any> {
    return this.http.put(`${this.apiUrl}/${id}`, {
      LEAVESTATUS: status,
      ADMINREMARKS: remarks
    });
  }

  deleteLeave(id: number): Observable<any> {
    return this.http.delete(`${this.apiUrl}/${id}`);
  }
}
