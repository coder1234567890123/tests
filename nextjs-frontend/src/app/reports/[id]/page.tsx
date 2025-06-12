'use client';
import React, { useState, useEffect, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import Link from 'next/link';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import { useAuth } from '@/contexts/AuthContext';

// Keep in sync with backend workflowService.js
const REPORT_STATUSES = {
  NEW_REQUEST: 'new_request',
  NEEDS_APPROVAL: 'needs_approval',
  REPORT_TYPE_APPROVED: 'report_type_approved',
  UNASSIGNED: 'unassigned',
  SEARCH_STARTED: 'search_started',
  SEARCH_COMPLETED: 'search_completed',
  VALIDATED: 'validated',
  UNDER_INVESTIGATION: 'under_investigation',
  INVESTIGATION_COMPLETED: 'investigation_completed',
  TEAM_LEAD_APPROVED: 'team_lead_approved',
  COMPLETED: 'completed',
  ABANDONED_REQUEST: 'abandoned_request',
  ABANDONED: 'abandoned',
};

interface ReportDetail {
  id: string; sequence: string; status: string | null; requestType: string | null;
  subject?: { id: string; firstName: string; lastName: string; status?: string | null; };
  company?: { name: string; };
  dueDate?: string | null; createdAt: string; updatedAt: string;
}

export default function ViewReportPage() {
  const params = useParams();
  const id = params.id as string;
  const router = useRouter();
  const { user: authUser } = useAuth();

  const [report, setReport] = useState<ReportDetail | null>(null);
  const [error, setError] = useState<string | null>(null); // General page error
  const [isLoading, setIsLoading] = useState(true);
  const [isTransitioning, setIsTransitioning] = useState(false);
  const [transitionError, setTransitionError] = useState<string | null>(null);
  const [isDownloadingPdf, setIsDownloadingPdf] = useState(false);

  const fetchReportData = useCallback(async () => {
    if (id) {
      setIsLoading(true);
      setError(null);
      setTransitionError(null); // Clear transition errors on re-fetch
      try {
        const response = await apiClient.get(`/reports/${id}`);
        setReport(response.data);
      } catch (err: any) {
        setError(err.response?.data?.error || 'Failed to fetch report data.');
        console.error(err);
      } finally {
        setIsLoading(false);
      }
    }
  }, [id]);

  useEffect(() => {
    fetchReportData();
  }, [fetchReportData]);

  const handleStatusTransition = async (newStatus: string) => {
    if (!report) return;
    setIsTransitioning(true);
    setTransitionError(null);
    try {
      const response = await apiClient.post(`/reports/${report.id}/status`, { newStatus });
      setReport(response.data);
      alert(`Report status successfully updated to: ${newStatus.replace(/_/g, ' ')}`);
      // fetchReportData(); // Re-fetch, or trust response.data is the full updated report
    } catch (err: any) {
      const errMsg = err.response?.data?.error || 'Failed to update report status.';
      setTransitionError(errMsg);
      alert(`Error: ${errMsg}`); // Show alert for transition error
      console.error(err);
    } finally {
      setIsTransitioning(false);
    }
  };

  const getAvailableActions = () => {
     if (!report || !report.status || !authUser) return [];
     const actions = [];
     const userRoles = authUser.roles || [];

     const isAnalyst = userRoles.includes('ROLE_ANALYST');
     const isTeamLead = userRoles.includes('ROLE_TEAM_LEAD');
     const isSuperAdmin = userRoles.includes('ROLE_SUPER_ADMIN');
     const currentStatus = report.status.toLowerCase();

     switch (currentStatus) {
         case REPORT_STATUSES.NEW_REQUEST:
         case REPORT_STATUSES.REPORT_TYPE_APPROVED:
              if (isAnalyst || isTeamLead || isSuperAdmin) actions.push({ label: 'Start Search', newStatus: REPORT_STATUSES.SEARCH_STARTED });
              if (isAnalyst || isTeamLead || isSuperAdmin) actions.push({ label: 'Request Abandonment', newStatus: REPORT_STATUSES.ABANDONED_REQUEST });
             break;
         case REPORT_STATUSES.NEEDS_APPROVAL:
             if (isSuperAdmin || isTeamLead) actions.push({ label: 'Approve Report Type (Rush/Test)', newStatus: REPORT_STATUSES.REPORT_TYPE_APPROVED });
             if (isSuperAdmin || isTeamLead) actions.push({ label: 'Reject & Abandon Report', newStatus: REPORT_STATUSES.ABANDONED });
             break;
         case REPORT_STATUSES.SEARCH_COMPLETED:
             if (isAnalyst || isTeamLead || isSuperAdmin) actions.push({ label: 'Mark Subject Validated', newStatus: REPORT_STATUSES.VALIDATED });
             break;
         case REPORT_STATUSES.VALIDATED:
             if (isAnalyst || isTeamLead || isSuperAdmin) actions.push({ label: 'Begin Investigation (Q&A)', newStatus: REPORT_STATUSES.UNDER_INVESTIGATION });
             break;
         case REPORT_STATUSES.UNDER_INVESTIGATION:
             if (isAnalyst || isTeamLead || isSuperAdmin) actions.push({ label: 'Complete Investigation (Submit for Review)', newStatus: REPORT_STATUSES.INVESTIGATION_COMPLETED });
             if (isAnalyst || isTeamLead || isSuperAdmin) actions.push({ label: 'Request Abandonment', newStatus: REPORT_STATUSES.ABANDONED_REQUEST });
             break;
         case REPORT_STATUSES.INVESTIGATION_COMPLETED:
             if (isTeamLead || isSuperAdmin) actions.push({ label: 'Approve Investigation (Team Lead)', newStatus: REPORT_STATUSES.TEAM_LEAD_APPROVED });
             if (isTeamLead || isSuperAdmin) actions.push({ label: 'Reject Investigation (Re-open)', newStatus: REPORT_STATUSES.UNDER_INVESTIGATION });
             if (isTeamLead || isSuperAdmin) actions.push({ label: 'Request Abandonment', newStatus: REPORT_STATUSES.ABANDONED_REQUEST });
             break;
         case REPORT_STATUSES.TEAM_LEAD_APPROVED:
             if (isSuperAdmin || isTeamLead) actions.push({ label: 'Finalize & Complete Report', newStatus: REPORT_STATUSES.COMPLETED }); // Team Lead can also complete
             break;
         case REPORT_STATUSES.ABANDONED_REQUEST:
             if (isSuperAdmin || isTeamLead) actions.push({ label: 'Confirm & Mark Abandoned', newStatus: REPORT_STATUSES.ABANDONED });
             // Example: Allow reverting abandonment request only if not yet fully abandoned.
             // The state it reverts to depends on workflow. Let's assume UNDER_INVESTIGATION or a previous active state.
             if (isSuperAdmin || isTeamLead) actions.push({ label: 'Cancel Abandonment Request (Revert)', newStatus: REPORT_STATUSES.INVESTIGATION_COMPLETED });
             break;
     }
     if (currentStatus !== REPORT_STATUSES.COMPLETED && currentStatus !== REPORT_STATUSES.ABANDONED && (isSuperAdmin || isTeamLead)) {
          if (!actions.find(a => a.newStatus === REPORT_STATUSES.ABANDONED_REQUEST) && !actions.find(a => a.newStatus === REPORT_STATUSES.ABANDONED) ) {
             actions.push({ label: 'Force Mark as Abandoned', newStatus: REPORT_STATUSES.ABANDONED });
          }
     }
     return actions;
  };

  const handleDownloadPdf = async () => {
    if (!report) return;
    setIsDownloadingPdf(true);
    try {
      const response = await apiClient.get(`/reports/${report.id}/pdf`, {
        responseType: 'blob',
      });
      const url = window.URL.createObjectURL(new Blob([response.data]));
      const link = document.createElement('a');
      link.href = url;
      link.setAttribute('download', `report-${report.id}.pdf`);
      document.body.appendChild(link);
      link.click();
      link.parentNode?.removeChild(link);
      window.URL.revokeObjectURL(url);
    } catch (err) {
      console.error('Failed to download PDF', err);
      alert('Failed to download PDF.');
    } finally {
      setIsDownloadingPdf(false);
    }
  };

  if (isLoading) return <ProtectedRoute><MainLayout><p>Loading report...</p></MainLayout></ProtectedRoute>;
  if (error && !report) return <ProtectedRoute><MainLayout><p className="text-red-500">{error}</p></MainLayout></ProtectedRoute>;
  if (!report) return <ProtectedRoute><MainLayout><p>Report not found.</p></MainLayout></ProtectedRoute>;

  const availableActions = getAvailableActions();

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title={`Report: ${report.sequence}`} className="max-w-3xl mx-auto mt-8">
          <div className="p-6 space-y-4">
             <p><strong>Status:</strong> <span className="font-semibold text-blue-600 uppercase">{report.status?.replace(/_/g, ' ') || 'N/A'}</span></p>
             <p><strong>Subject Status:</strong> <span className="font-semibold text-gray-600 uppercase">{report.subject?.status?.replace(/_/g, ' ') || 'N/A'}</span></p>
             <p><strong>Type:</strong> {report.requestType || 'N/A'}</p>
             {report.subject && <p><strong>Subject:</strong> <Link href={`/subjects/${report.subject.id}`} className="text-blue-600 hover:underline">{report.subject.firstName} {report.subject.lastName}</Link></p>}
             <p><strong>Company:</strong> {report.company?.name || 'N/A'}</p>
             <p><strong>Due Date:</strong> {report.dueDate ? new Date(report.dueDate).toLocaleDateString() : 'N/A'}</p>
             <p><strong>Created:</strong> {new Date(report.createdAt).toLocaleString()}</p>
             {transitionError && <p className="text-red-500 bg-red-100 p-3 my-2 rounded">Error: {transitionError}</p>}
             {error && <p className="text-red-500 bg-red-100 p-3 my-2 rounded">Page Error: {error}</p>}
          </div>
          <div className="px-6 py-4 border-t">
             <h3 className="text-lg font-semibold mb-3">Workflow Actions</h3>
             <div className="flex flex-wrap gap-2">
                 {availableActions.map(action => (
                     <Button
                         key={action.newStatus}
                         onClick={() => handleStatusTransition(action.newStatus)}
                         variant="secondary"
                         size="sm"
                         isLoading={isTransitioning}
                         disabled={isTransitioning}
                     >
                         {action.label}
                     </Button>
                 ))}
                 {availableActions.length === 0 && <p className="text-sm text-gray-500">No further workflow actions available for current status or your role.</p>}
             </div>
          </div>
          <div className="px-6 py-4 mt-4 border-t flex flex-wrap gap-3 items-center">
            <Link href={`/investigations/${report.id}`}>
              <Button variant={(report.status === REPORT_STATUSES.UNDER_INVESTIGATION || report.status === REPORT_STATUSES.VALIDATED || report.status === REPORT_STATUSES.SEARCH_COMPLETED) ? "primary" : "outline"}>
                {(report.status === REPORT_STATUSES.UNDER_INVESTIGATION || report.status === REPORT_STATUSES.VALIDATED || report.status === REPORT_STATUSES.SEARCH_COMPLETED) ? "Continue Investigation" : "View Investigation Q&A"}
              </Button>
            </Link>
            <Button onClick={handleDownloadPdf} variant="outline" isLoading={isDownloadingPdf} disabled={isDownloadingPdf}>
                {isDownloadingPdf ? 'Downloading...' : 'Download PDF'}
            </Button>
            <Button onClick={() => router.push('/reports')} variant="ghost">Back to List</Button>
          </div>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
