'use client';
import React, { useEffect, useState, ChangeEvent, FormEvent, useCallback } from 'react';
import { useParams, useRouter } from 'next/navigation';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';
import { useAuth } from '@/contexts/AuthContext';
import Link from 'next/link'; // Ensure Link is imported

const REPORT_STATUSES = {
  NEW_REQUEST: 'new_request', NEEDS_APPROVAL: 'needs_approval', REPORT_TYPE_APPROVED: 'report_type_approved',
  UNASSIGNED: 'unassigned', SEARCH_STARTED: 'search_started', SEARCH_COMPLETED: 'search_completed',
  VALIDATED: 'validated', UNDER_INVESTIGATION: 'under_investigation',
  INVESTIGATION_COMPLETED: 'investigation_completed', TEAM_LEAD_APPROVED: 'team_lead_approved',
  COMPLETED: 'completed', ABANDONED_REQUEST: 'abandoned_request', ABANDONED: 'abandoned',
};

interface Question {
  id: string;
  question: string;
  answerType: 'text' | 'yes_no' | 'multiple_choice';
  answerOptions?: string[];
  platform: string;
  orderNumber?: number;
}

interface Proof {
  id: string;
  comment: string;
  behaviourScores?: any;
  trait?: boolean;
  filePath?: string;
  originalFilename?: string;
  mimeType?: string;
}

interface Answer {
  id: string;
  answer: string;
  questionId: string;
  reportId: string;
  userId?: string;
  question?: Question;
  proofs?: Proof[];
}

interface Comment {
   id: string;
   comment: string;
   commentType: string;
   approval?: string | null;
   private: boolean;
   hidden: boolean;
   createdAt: string;
   commentBy?: { firstName?: string | null; lastName?: string | null; email: string; };
}

interface ReportDetail {
  id: string;
  sequence: string;
  status: string | null;
  subject: { id: string; firstName: string; lastName: string; };
  questions: Question[];
  answers: Answer[];
  comments?: Comment[];
  riskScore?: number | null;
  reportScores?: {
     platforms?: Record<string, {
         unweighted_platform_score_rounded?: number;
         weighted_platform_score_rounded?: number;
     }>;
     overall_behavior_scores?: Record<string, number>;
     weighted_social_media_score_round?: number;
  } | null;
}

const sanitizeHTML = (text: string | null | undefined): string => {
  if (!text) return '';
  // Basic sanitization (React JSX already handles most direct text rendering)
  return text.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
};

const getIconForMimeType = (mimeType?: string): string => {
  if (!mimeType) return '📄'; // Default file icon (emoji)
  if (mimeType.startsWith('image/')) return '🖼️';
  if (mimeType.includes('pdf')) return '📕'; // PDF icon (emoji)
  if (mimeType.includes('word') || mimeType.includes('officedocument.wordprocessingml')) return 'docx'; // Text placeholder
  if (mimeType.includes('excel') || mimeType.includes('officedocument.spreadsheetml')) return 'xlsx';
  if (mimeType.includes('presentation') || mimeType.includes('officedocument.presentationml')) return 'pptx';
  if (mimeType.startsWith('text/')) return 'TXT';
  return '📄';
};

export default function InvestigationPage() {
  const params = useParams();
  const reportId = params.reportId as string;
  const router = useRouter();
  const { user: authUser } = useAuth();

  const [report, setReport] = useState<ReportDetail | null>(null);
  const [allQuestions, setAllQuestions] = useState<Question[]>([]);
  const [currentAnswers, setCurrentAnswers] = useState<Record<string, string>>({});
  const [currentQuestionIndex, setCurrentQuestionIndex] = useState(0);

  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [submitError, setSubmitError] = useState<string | null>(null);
  const [isSubmittingAnswer, setIsSubmittingAnswer] = useState<Record<string, boolean>>({});
  const [isRefreshingScores, setIsRefreshingScores] = useState(false);
  const [isCompletingInvestigation, setIsCompletingInvestigation] = useState(false);

  const [newProofText, setNewProofText] = useState<Record<string, string>>({});
  const [newProofFile, setNewProofFile] = useState<Record<string, File | null>>({});
  const [isSubmittingProof, setIsSubmittingProof] = useState<Record<string, boolean>>({});
  const [newReportComment, setNewReportComment] = useState('');
  const [isSubmittingReportComment, setIsSubmittingReportComment] = useState(false);

  const fetchReportDetails = useCallback(async (rId: string) => {
    setIsLoading(true); setError(null);
    try {
      const response = await apiClient.get(`/reports/${rId}`);
      const reportData: ReportDetail = response.data;
      const initialAnswers: Record<string, string> = {};
      reportData.answers?.forEach((ans) => {
        if (ans.questionId) initialAnswers[ans.questionId] = ans.answer;
      });
      setAllQuestions(reportData.questions || []);
      setCurrentAnswers(initialAnswers);
      setReport(reportData);
    } catch (err: any) {
      setError(err.response?.data?.error || `Failed to fetch report details for ${rId}`);
    } finally {
      setIsLoading(false);
    }
  }, [reportId]);

  useEffect(() => {
    if (reportId) {
      fetchReportDetails(reportId);
    }
  }, [reportId, fetchReportDetails]);

  const handleAnswerChange = (questionId: string, value: string) => {
    setCurrentAnswers(prev => ({ ...prev, [questionId]: value }));
  };

  const handleAnswerSubmit = async (questionId: string) => {
    if (!report || !authUser) return;
    setSubmitError(null);
    setIsSubmittingAnswer(prev => ({...prev, [questionId]: true}));
    const answerValue = currentAnswers[questionId];
    if (answerValue === undefined || answerValue.trim() === '') {
      setSubmitError("Answer cannot be empty for this question.");
      setIsSubmittingAnswer(prev => ({...prev, [questionId]: false})); return;
    }
    const question = allQuestions.find(q => q.id === questionId);
    try {
      await apiClient.post('/answers', {
        reportId: report.id, questionId, subjectId: report.subject.id,
        answer: answerValue, platform: question?.platform, userId: authUser.id,
      });
      await fetchReportDetails(reportId);
    } catch (err: any) {
      setSubmitError(err.response?.data?.error || 'Failed to submit answer.');
    } finally {
      setIsSubmittingAnswer(prev => ({...prev, [questionId]: false}));
    }
  };

  const handleRefreshScores = async () => {
     if(!report) return;
     setIsRefreshingScores(true); setError(null);
     try {
         const response = await apiClient.post(`/reports/${report.id}/calculate-scores`);
         setReport(response.data);
     } catch(err: any) {
         setError(err.response?.data?.error || "Failed to refresh scores.");
     } finally {
         setIsRefreshingScores(false);
     }
  };

  const handleCompleteInvestigation = async () => {
    if (!report || !authUser) return;
    setIsCompletingInvestigation(true); setSubmitError(null);
    try {
      const response = await apiClient.post(`/reports/${report.id}/status`, { newStatus: REPORT_STATUSES.INVESTIGATION_COMPLETED });
      setReport(response.data);
      alert('Investigation marked as completed and submitted for review!');
    } catch (err: any) {
      setSubmitError(err.response?.data?.error || 'Failed to complete investigation.');
    } finally {
      setIsCompletingInvestigation(false);
    }
  };

  const handleProofFileChange = (answerId: string, file: File | null) => {
    setNewProofFile(prev => ({ ...prev, [answerId]: file }));
  };

  const handleAddProof = async (answerId: string) => {
    const proofText = newProofText[answerId]?.trim();
    const proofFile = newProofFile[answerId];
    if ((!proofText && !proofFile) || !report || !answerId) {
      setSubmitError("Proof text or file is required, and answer must exist.");
      return;
    }
    setIsSubmittingProof(prev => ({...prev, [answerId]: true}));
    setSubmitError(null);
    const formData = new FormData();
    formData.append('answerId', answerId);
    if (proofText) formData.append('comment', proofText);
    if (proofFile) formData.append('proofFile', proofFile);
    try {
        await apiClient.post('/proofs', formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        setNewProofText(prev => ({...prev, [answerId]: ''}));
        setNewProofFile(prev => ({...prev, [answerId]: null}));
        fetchReportDetails(reportId);
    } catch (err: any) {
        setSubmitError(err.response?.data?.error || 'Failed to add proof.');
    } finally {
        setIsSubmittingProof(prev => ({...prev, [answerId]: false}));
    }
  };

  const handleDeleteProof = async (proofId: string) => {
     if (!confirm("Are you sure you want to delete this proof?")) return;
     setSubmitError(null);
     try {
         await apiClient.delete(`/proofs/${proofId}`);
         fetchReportDetails(reportId);
     } catch (err: any) {
         setSubmitError(err.response?.data?.error || 'Failed to delete proof.');
     }
  };

  const handleAddReportComment = async () => {
    if (!newReportComment.trim() || !report || !authUser) return;
    setIsSubmittingReportComment(true); setSubmitError(null);
    try {
      await apiClient.post('/comments', {
        reportId: report.id,
        comment: newReportComment,
        commentType: 'normal',
      });
      setNewReportComment('');
      fetchReportDetails(reportId);
    } catch (err: any) {
      setSubmitError(err.response?.data?.error || 'Failed to add comment.');
    } finally {
      setIsSubmittingReportComment(false);
    }
  };

  const handleDeleteReportComment = async (commentId: string) => {
     if (!confirm("Are you sure you want to delete this comment?")) return;
     setSubmitError(null);
     try {
         await apiClient.delete(`/comments/${commentId}`);
         fetchReportDetails(reportId);
     } catch (err: any) {
         setSubmitError(err.response?.data?.error || 'Failed to delete comment.');
     }
  };

  const currentQuestion = allQuestions[currentQuestionIndex];

  if (isLoading) return <ProtectedRoute><MainLayout><p>Loading investigation...</p></MainLayout></ProtectedRoute>;
  if (error && !report) return <ProtectedRoute><MainLayout><p className="text-red-500">{error}</p></MainLayout></ProtectedRoute>;
  if (!report) return <ProtectedRoute><MainLayout><p>Report data not available.</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <Card title={`Investigation: ${report.sequence} (Subject: ${report.subject.firstName} ${report.subject.lastName})`} className="my-8">
          <div className="mb-6 p-4 border rounded bg-gray-50 shadow">
            <h3 className="text-xl font-semibold mb-2">Report Details</h3>
            <p className="text-md">Current Status: <span className="font-semibold text-blue-700 uppercase">{report?.status?.replace(/_/g, ' ') || 'N/A'}</span></p>
            <p className="text-lg mt-1">Overall Risk Score: <span className="font-bold">{report.riskScore !== null && report.riskScore !== undefined ? `${report.riskScore.toFixed(2)}%` : 'N/A'}</span></p>
            {report.reportScores?.platforms && Object.entries(report.reportScores.platforms).map(([platform, scores]) => (
              <div key={platform} className="mt-1">
                <span className="text-sm capitalize font-medium">{platform}: </span>
                <span className="text-sm">Weighted {scores.weighted_platform_score_rounded?.toFixed(2)}</span>
                (Unweighted {scores.unweighted_platform_score_rounded?.toFixed(2)})
              </div>
            ))}
            {report.reportScores?.overall_behavior_scores && Object.keys(report.reportScores.overall_behavior_scores).length > 0 && (
                <div className="mt-2">
                    <h4 className="text-md font-semibold">Overall Behavior Scores:</h4>
                    {Object.entries(report.reportScores.overall_behavior_scores).map(([key, value]) => (
                        <p key={key} className="text-sm capitalize">{key}: {value?.toFixed(2)}</p>
                    ))}
                </div>
            )}
            <Button onClick={handleRefreshScores} isLoading={isRefreshingScores} disabled={isRefreshingScores} size="sm" className="mt-3">Refresh Scores</Button>
          </div>

          {submitError && <p className="text-red-500 bg-red-100 p-3 rounded mb-4">{submitError}</p>}

          {report?.status === REPORT_STATUSES.UNDER_INVESTIGATION && (
            <div className="my-4 p-4 bg-blue-50 border border-blue-300 rounded text-center">
              <Button
                onClick={handleCompleteInvestigation}
                variant="primary"
                isLoading={isCompletingInvestigation}
                disabled={isCompletingInvestigation}
                className="mx-auto"
              >
                Complete Investigation & Submit for Review
              </Button>
            </div>
          )}

          {!currentQuestion && allQuestions.length > 0 && currentQuestionIndex >= allQuestions.length && <p className="text-center py-4">End of questions. <Button variant="link" onClick={() => setCurrentQuestionIndex(0)}>Review First Question</Button></p>}
          {allQuestions.length === 0 && <p>No questions available for this report.</p>}

          {currentQuestion && (
            <Card key={currentQuestion.id} title={`Question ${currentQuestionIndex + 1} of ${allQuestions.length} (Platform: ${currentQuestion.platform || 'General'})`}>
              <div className="p-4">
                <p className="font-semibold mb-3 text-lg">{sanitizeHTML(currentQuestion.question)}</p>
                {currentQuestion.answerType === 'text' && (
                  <textarea
                    value={currentAnswers[currentQuestion.id] || ''}
                    onChange={(e: ChangeEvent<HTMLTextAreaElement>) => handleAnswerChange(currentQuestion.id, e.target.value)}
                    className="w-full p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    rows={4}
                    placeholder="Type your answer..."
                  />
                )}
                {currentQuestion.answerType === 'yes_no' && (
                  <select
                    value={currentAnswers[currentQuestion.id] || ''}
                    onChange={(e: ChangeEvent<HTMLSelectElement>) => handleAnswerChange(currentQuestion.id, e.target.value)}
                    className="w-full p-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                  >
                    <option value="">Select...</option>
                    <option value="yes">Yes</option>
                    <option value="no">No</option>
                  </select>
                )}
                {currentQuestion.answerType === 'multiple_choice' && Array.isArray(currentQuestion.answerOptions) && (
                  <div className="space-y-2 mt-2">
                    {currentQuestion.answerOptions.map(opt => (
                      <label key={opt} className="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
                        <input
                          type="radio"
                          name={`question_${currentQuestion.id}`}
                          value={opt}
                          checked={(currentAnswers[currentQuestion.id] || '') === opt}
                          onChange={(e: ChangeEvent<HTMLInputElement>) => handleAnswerChange(currentQuestion.id, e.target.value)}
                          className="form-radio h-4 w-4 text-blue-600 focus:ring-blue-500"
                        />
                        <span>{sanitizeHTML(opt)}</span>
                      </label>
                    ))}
                  </div>
                )}
                <Button
                  onClick={() => handleAnswerSubmit(currentQuestion.id)}
                  className="mt-4"
                  variant="primary"
                  isLoading={isSubmittingAnswer[currentQuestion.id] || false}
                  disabled={isSubmittingAnswer[currentQuestion.id] || false}
                >
                  Save Answer
                </Button>

                <div className="mt-6 pt-4 border-t">
                  <h4 className="font-semibold text-md mb-2">Proofs for this Answer:</h4>
                  {(report?.answers.find(a => a.questionId === currentQuestion.id)?.proofs?.length ?? 0) === 0 && <p className="text-xs text-gray-500">No proofs yet for this answer.</p>}
                  <div className="space-y-2 mb-3">
                    {report?.answers.find(a => a.questionId === currentQuestion.id)?.proofs?.map(p => (
                      <div key={p.id} className="text-xs bg-gray-50 p-3 rounded border my-1 shadow-sm">
                        <p className="font-medium text-gray-800 mb-1 whitespace-pre-wrap">{sanitizeHTML(p.comment)}</p>
                        {p.filePath && p.mimeType && p.mimeType.startsWith('image/') && (
                          <div className="mt-2">
                            <img
                              src={`http://localhost:3001/${p.filePath.replace(/\\/g, '/')}`}
                              alt={sanitizeHTML(p.originalFilename || 'Proof image')}
                              className="max-w-xs max-h-48 object-contain rounded border"
                              onError={(e) => {
                                const target = e.target as HTMLImageElement;
                                target.style.display = 'none';
                                const errorMsg = document.createElement('span');
                                errorMsg.textContent = ` (Error loading image: ${sanitizeHTML(p.originalFilename)})`;
                                errorMsg.className = 'text-red-500 text-xs';
                                target.parentNode?.insertBefore(errorMsg, target.nextSibling);
                              }}
                            />
                            <a
                              href={`http://localhost:3001/${p.filePath.replace(/\\/g, '/')}`}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="text-blue-600 hover:underline text-xs block mt-1"
                            >
                              View full image: {sanitizeHTML(p.originalFilename || 'View File')}
                            </a>
                          </div>
                        )}
                        {p.filePath && p.mimeType && !p.mimeType.startsWith('image/') && (
                          <div className="mt-2 flex items-center">
                            <span className="text-xl mr-2">{getIconForMimeType(p.mimeType)}</span>
                            <a
                              href={`http://localhost:3001/${p.filePath.replace(/\\/g, '/')}`}
                              target="_blank"
                              rel="noopener noreferrer"
                              className="text-blue-600 hover:underline text-xs"
                            >
                              {sanitizeHTML(p.originalFilename || 'Download File')} ({sanitizeHTML(p.mimeType)})
                            </a>
                          </div>
                        )}
                        {!p.filePath && <p className="text-xs text-gray-400 italic mt-1">No file attached.</p>}

                        {p.behaviourScores && typeof p.behaviourScores === 'object' && Object.keys(p.behaviourScores).length > 0 && (
                           <div className="text-xs text-gray-600 mt-1 pt-1 border-t border-gray-200">
                               <strong>Behaviour Scores:</strong> {Object.entries(p.behaviourScores).map(([key, value]) => `${sanitizeHTML(key)}: ${sanitizeHTML(String(value))}`).join(', ')}
                           </div>
                        )}
                        {p.trait !== undefined && <p className="text-xs text-gray-600 mt-1">Trait related: {p.trait ? 'Yes' : 'No'}</p>}
                        <Button size="sm" variant="danger" className="mt-2 !py-0.5 !px-1.5 !text-xs" onClick={() => handleDeleteProof(p.id)}>Delete Proof</Button>
                      </div>
                    ))}
                  </div>
                  {report?.answers.find(a => a.questionId === currentQuestion.id)?.id && (
                  <div className="mt-2">
                    <h5 className="text-xs font-semibold mb-1">Add New Proof to this Answer:</h5>
                    <textarea
                      value={newProofText[report?.answers.find(a => a.questionId === currentQuestion.id)?.id || ''] || ''}
                      onChange={(e) => setNewProofText(prev => ({...prev, [report?.answers.find(a => a.questionId === currentQuestion.id)?.id || '']: e.target.value}))}
                      placeholder="Proof text/comment..."
                      className="w-full p-1.5 border rounded text-sm mb-1"
                      rows={2}
                    />
                    <div className="mt-1">
                      <label className="block text-xs font-medium text-gray-700">Attach File (Optional)</label>
                      <input
                        type="file"
                        onChange={(e) => handleProofFileChange(report?.answers.find(a => a.questionId === currentQuestion.id)?.id || '', e.target.files ? e.target.files[0] : null)}
                        className="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-1 file:px-2 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                      />
                    </div>
                    <Button
                      onClick={() => handleAddProof(report?.answers.find(a => a.questionId === currentQuestion.id)?.id || '')}
                      size="sm" variant="secondary" className="mt-2"
                      isLoading={isSubmittingProof[report?.answers.find(a => a.questionId === currentQuestion.id)?.id || '']}
                      disabled={isSubmittingProof[report?.answers.find(a => a.questionId === currentQuestion.id)?.id || ''] || !report?.answers.find(a => a.questionId === currentQuestion.id)?.id}
                    >
                      Add Proof
                    </Button>
                  </div>
                  )}
                </div>
              </div>
            </Card>
          )}
          <div className="mt-6 flex justify-between items-center">
            <Button onClick={() => setCurrentQuestionIndex(prev => Math.max(0, prev - 1))} disabled={currentQuestionIndex === 0 || allQuestions.length === 0}>
              Previous
            </Button>
            <span>Question {allQuestions.length > 0 ? currentQuestionIndex + 1 : 0} / {allQuestions.length}</span>
            <Button onClick={() => setCurrentQuestionIndex(prev => Math.min(allQuestions.length - 1, prev + 1))} disabled={currentQuestionIndex >= allQuestions.length - 1 || allQuestions.length === 0}>
              Next
            </Button>
          </div>

          {/* Report-Level Comments Section */}
          <div className="mt-10 pt-6 border-t">
            <h3 className="text-xl font-semibold mb-3">Report Comments</h3>
            <div className="space-y-3 mb-4">
              {report?.comments?.length === 0 && <p className="text-sm text-gray-500">No general comments for this report yet.</p>}
              {report?.comments?.map(comment => (
                <div key={comment.id} className="bg-gray-100 p-3 rounded-md shadow-sm flex justify-between items-start">
                  <div>
                    <p className="text-sm whitespace-pre-wrap">{sanitizeHTML(comment.comment)}</p>
                    <p className="text-xs text-gray-500 mt-1">
                      By: {sanitizeHTML(comment.commentBy?.firstName) || 'User'} {sanitizeHTML(comment.commentBy?.lastName) || ''} on {new Date(comment.createdAt).toLocaleDateString()}
                    </p>
                  </div>
                  <Button size="sm" variant="danger" className="ml-2 !py-0.5 !px-1.5 !text-xs flex-shrink-0" onClick={() => handleDeleteReportComment(comment.id)}>Del</Button>
                </div>
              ))}
            </div>
            <div>
              <textarea
                value={newReportComment}
                onChange={(e) => setNewReportComment(e.target.value)}
                placeholder="Add a general comment to the report..."
                className="w-full p-2 border rounded"
                rows={3}
              />
              <Button
                onClick={handleAddReportComment}
                className="mt-2"
                variant="primary"
                isLoading={isSubmittingReportComment}
                disabled={isSubmittingReportComment}
              >
                Add Report Comment
              </Button>
            </div>
          </div>

          <div className="mt-8 border-t pt-6">
             <Button onClick={() => router.push(`/reports/${reportId}`)} variant="ghost">Back to Report Details</Button>
          </div>
        </Card>
      </MainLayout>
    </ProtectedRoute>
  );
}
