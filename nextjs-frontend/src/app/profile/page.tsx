'use client';
import React, { useState, useEffect, FormEvent } from 'react';
import { useRouter } from 'next/navigation'; // Keep if any navigation is added back
import apiClient from '@/lib/api';
import MainLayout from '@/components/layout/MainLayout';
import Input from '@/components/ui/Input';
import Button from '@/components/ui/Button';
import Card from '@/components/ui/Card';
import ProtectedRoute from '@/components/auth/ProtectedRoute';
import { useAuth } from '@/contexts/AuthContext';

interface ProfileData {
  email: string;
  firstName: string | null;
  lastName: string | null;
  primaryMobile?: string | null;
  // For AuthContext update, include all fields AuthContext.User expects
  id?: string;
  roles?: string[];
  enabled?: boolean;
  company?: { id: string; name: string } | null;
  team?: { id: string; teamName?: string } | null;
}

export default function ProfilePage() {
  const { user: authUser, login: updateAuthContextUser, token: authToken } = useAuth();
  // const router = useRouter(); // Not used in current snippet
  const [profileData, setProfileData] = useState<Partial<ProfileData>>({
     email: '', firstName: '', lastName: '', primaryMobile: ''
  });
  const [passwordData, setPasswordData] = useState({ currentPassword: '', newPassword: '', confirmNewPassword: '' });
  const [profileError, setProfileError] = useState<string | null>(null);
  const [passwordError, setPasswordError] = useState<string | null>(null);
  const [profileSuccess, setProfileSuccess] = useState<string | null>(null);
  const [passwordSuccess, setPasswordSuccess] = useState<string | null>(null);
  const [isProfileLoading, setIsProfileLoading] = useState(false);
  const [isPasswordLoading, setIsPasswordLoading] = useState(false);
  const [isFetchingProfile, setIsFetchingProfile] = useState(true);

  useEffect(() => {
    if (authUser) { // Check if authUser exists before fetching
        setIsFetchingProfile(true);
        apiClient.get('/users/me/profile')
            .then(response => {
            setProfileData({
                email: response.data.email,
                firstName: response.data.firstName,
                lastName: response.data.lastName,
                primaryMobile: response.data.primaryMobile,
                // Store other fields from response if needed for AuthContext update
                id: response.data.id,
                roles: response.data.roles,
                enabled: response.data.enabled,
                company: response.data.company,
                team: response.data.team,
            });
            setIsFetchingProfile(false);
            })
            .catch(err => {
            setProfileError('Failed to load profile data.');
            setIsFetchingProfile(false);
            });
    } else {
        setIsFetchingProfile(false); // No user, so not fetching
    }
  }, [authUser]); // Depend on authUser

  const handleProfileChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setProfileData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handlePasswordChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    setPasswordData(prev => ({ ...prev, [e.target.name]: e.target.value }));
  };

  const handleProfileSubmit = async (e: FormEvent) => {
    e.preventDefault();
    setProfileError(null); setProfileSuccess(null); setIsProfileLoading(true);
    try {
      const dataToSubmit: Partial<ProfileData> = {};
      // Only send fields that are meant to be updated by this form
      dataToSubmit.firstName = profileData.firstName || null; // Allow clearing by sending null
      dataToSubmit.lastName = profileData.lastName || null;   // Allow clearing by sending null
      if (profileData.email) dataToSubmit.email = profileData.email; // Email is required
      dataToSubmit.primaryMobile = profileData.primaryMobile || null; // Allow clearing

      if (!dataToSubmit.email) { // Basic client-side check for email
        setProfileError("Email is required.");
        setIsProfileLoading(false);
        return;
      }

      const response = await apiClient.patch('/users/me/profile', dataToSubmit);
      setProfileSuccess('Profile updated successfully!');

      if (authUser && authToken && response.data) {
         const updatedUserForContext: any = { // Build the user object for context
            id: response.data.id || authUser.id,
            email: response.data.email || authUser.email,
            firstName: response.data.firstName,
            lastName: response.data.lastName,
            roles: response.data.roles || authUser.roles,
            enabled: response.data.enabled !== undefined ? response.data.enabled : authUser.enabled,
            primaryMobile: response.data.primaryMobile, // Ensure this is part of response.data
            company: response.data.company || authUser.company,
            team: response.data.team || authUser.team
         };
         updateAuthContextUser(authToken, updatedUserForContext);
         // Update local form state to reflect backend state accurately, including what might not have been sent but is on user object
         setProfileData(prev => ({
            ...prev, // Keep other potential form fields not directly part of ProfileData if any
            email: updatedUserForContext.email,
            firstName: updatedUserForContext.firstName,
            lastName: updatedUserForContext.lastName,
            primaryMobile: updatedUserForContext.primaryMobile
         }));
      }

    } catch (err: any) {
      setProfileError(err.response?.data?.error || 'Failed to update profile.');
    } finally {
      setIsProfileLoading(false);
    }
  };

  const handleChangePasswordSubmit = async (e: FormEvent) => {
    e.preventDefault();
    if (passwordData.newPassword !== passwordData.confirmNewPassword) {
      setPasswordError('New passwords do not match.'); return;
    }
    if (passwordData.newPassword.length < 6) {
       setPasswordError('New password must be at least 6 characters long.'); return;
    }
    setPasswordError(null); setPasswordSuccess(null); setIsPasswordLoading(true);
    try {
      await apiClient.post('/users/me/change-password', {
        currentPassword: passwordData.currentPassword,
        newPassword: passwordData.newPassword,
      });
      setPasswordSuccess('Password changed successfully!');
      setPasswordData({ currentPassword: '', newPassword: '', confirmNewPassword: '' });
    } catch (err: any) {
      setPasswordError(err.response?.data?.error || 'Failed to change password.');
    } finally {
      setIsPasswordLoading(false);
    }
  };

  if (isFetchingProfile && !authUser) return <ProtectedRoute><MainLayout><p>Authenticating...</p></MainLayout></ProtectedRoute>; // AuthUser still loading
  if (isFetchingProfile) return <ProtectedRoute><MainLayout><p>Loading profile...</p></MainLayout></ProtectedRoute>;


  return (
    <ProtectedRoute>
      <MainLayout>
        <div className="space-y-10 max-w-2xl mx-auto mt-8 mb-8">
          <Card title="Edit Your Profile">
            {profileSuccess && <p className="text-green-600 bg-green-100 p-3 rounded mb-4 text-sm">{profileSuccess}</p>}
            {profileError && <p className="text-red-500 bg-red-100 p-3 rounded mb-4 text-sm">{profileError}</p>}
            <form onSubmit={handleProfileSubmit} className="space-y-4">
              <Input label="First Name" name="firstName" value={profileData.firstName || ''} onChange={handleProfileChange} />
              <Input label="Last Name" name="lastName" value={profileData.lastName || ''} onChange={handleProfileChange} />
              <Input label="Email" name="email" type="email" value={profileData.email || ''} onChange={handleProfileChange} required />
              <Input label="Primary Mobile (e.g., +1234567890)" name="primaryMobile" type="tel" value={profileData.primaryMobile || ''} onChange={handleProfileChange} placeholder="+1234567890"/>
              <Button type="submit" variant="primary" isLoading={isProfileLoading} disabled={isProfileLoading}>Update Profile</Button>
            </form>
          </Card>

          <Card title="Change Password">
            {passwordSuccess && <p className="text-green-600 bg-green-100 p-3 rounded mb-4 text-sm">{passwordSuccess}</p>}
            {passwordError && <p className="text-red-500 bg-red-100 p-3 rounded mb-4 text-sm">{passwordError}</p>}
            <form onSubmit={handleChangePasswordSubmit} className="space-y-4">
              <Input label="Current Password" name="currentPassword" type="password" value={passwordData.currentPassword} onChange={handlePasswordChange} required />
              <Input label="New Password" name="newPassword" type="password" value={passwordData.newPassword} onChange={handlePasswordChange} required />
              <Input label="Confirm New Password" name="confirmNewPassword" type="password" value={passwordData.confirmNewPassword} onChange={handlePasswordChange} required />
              <Button type="submit" variant="primary" isLoading={isPasswordLoading} disabled={isPasswordLoading}>Change Password</Button>
            </form>
          </Card>
        </div>
      </MainLayout>
    </ProtectedRoute>
  );
}
