'use client';
import React, { useEffect, useState, useCallback } from 'react';
import Link from 'next/link';
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import Card from '@/components/ui/Card';
import Button from '@/components/ui/Button';

interface Message {
  id: string;
  messageHeader: string;
  message: string;
  messageType: string;
  messageRead: boolean;
  createdAt: string;
  report?: { id: string; sequence: string; };
  subject?: { id: string; firstName: string; lastName: string; };
}
interface MessagesApiResponse {
  data: Message[];
  total: number;
  page: number;
  limit: number;
  totalPages: number;
  unreadCount?: number;
}

export default function MessagesPage() {
  const [messages, setMessages] = useState<Message[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [currentPage, setCurrentPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  // const [unreadCount, setUnreadCount] = useState(0); // For global context or navbar refresh

  const fetchMessages = useCallback(async (page = 1) => {
    setLoading(true);
    setError(null);
    try {
      const response = await apiClient.get<MessagesApiResponse>('/messages/my', { params: { page, limit: 10 } });
      setMessages(response.data.data);
      setTotalPages(response.data.totalPages);
      setCurrentPage(response.data.page);
      // if (response.data.unreadCount !== undefined) {
      //    // Optionally update a global unread count here via context if Navbar isn't making its own call
      // }
    } catch (e: any) {
      setError(e.response?.data?.error || 'Failed to fetch messages');
    } finally {
      setLoading(false);
    }
  }, []); // Removed currentPage from here, it's an argument

  useEffect(() => {
    fetchMessages(currentPage);
  }, [fetchMessages, currentPage]); // Re-fetch if currentPage changes

  const handleMarkAsRead = async (messageId: string) => {
    try {
      await apiClient.patch(`/messages/${messageId}/read`);
      setMessages(prev => prev.map(msg => msg.id === messageId ? { ...msg, messageRead: true } : msg));
      // Consider refreshing unread count for navbar if it's not polling or using context
      // e.g., trigger a context function: authContext.refreshUnreadCount();
    } catch (err) {
      alert('Failed to mark message as read.'); // Basic feedback
    }
  };

  if (loading && messages.length === 0 && currentPage === 1) return <ProtectedRoute><MainLayout><p>Loading messages...</p></MainLayout></ProtectedRoute>;
  if (error) return <ProtectedRoute><MainLayout><p className="text-red-500 p-4">{error}</p></MainLayout></ProtectedRoute>;

  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="flex justify-between items-center mb-6">
            <h1 className="text-3xl font-bold">My Messages</h1>
            <Button onClick={() => fetchMessages(currentPage)} disabled={loading} variant="outline">Refresh</Button>
        </div>

        {messages.length === 0 && !loading ? (
            <p>You have no messages.</p>
        ) : (
          <div className="space-y-4">
            {messages.map(msg => (
              <Card key={msg.id} className={`${!msg.messageRead ? 'border-l-4 border-blue-600 bg-blue-50/30' : 'border'}`}>
                <div className="p-4">
                  <h3 className={`text-lg font-semibold ${!msg.messageRead ? 'text-blue-700' : ''}`}>{msg.messageHeader}</h3>
                  <p className="text-sm text-gray-800 mt-1 whitespace-pre-wrap">{msg.message}</p>
                  <p className="text-xs text-gray-500 mt-3">Type: <span className="font-medium">{msg.messageType.replace(/_/g, ' ').toLowerCase()}</span></p>
                  <p className="text-xs text-gray-500">Received: {new Date(msg.createdAt).toLocaleString()}</p>

                  <div className="mt-2 text-xs">
                    {msg.report && <p><Link href={`/reports/${msg.report.id}`} className="text-blue-600 hover:underline">View Report: {msg.report.sequence}</Link></p>}
                    {msg.subject && <p><Link href={`/subjects/${msg.subject.id}`} className="text-blue-600 hover:underline">View Subject: {msg.subject.firstName} {msg.subject.lastName}</Link></p>}
                  </div>

                  {!msg.messageRead && (
                    <Button onClick={() => handleMarkAsRead(msg.id)} size="sm" variant="secondary" className="mt-3">Mark as Read</Button>
                  )}
                </div>
              </Card>
            ))}
          </div>
        )}
        {totalPages > 1 && (
          <div className="mt-8 flex justify-center items-center space-x-3">
            <Button onClick={() => setCurrentPage(p => Math.max(1, p - 1))} disabled={currentPage === 1 || loading}>Previous</Button>
            <span>Page {currentPage} of {totalPages}</span>
            <Button onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))} disabled={currentPage === totalPages || loading}>Next</Button>
          </div>
        )}
      </MainLayout>
    </ProtectedRoute>
  );
}
