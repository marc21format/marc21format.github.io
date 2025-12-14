<div>
    @php
        $canEdit = auth()->check() && auth()->user()->canEditUserProfile($user);
        $roleTitle = optional($user->role)->role_title ?? 'Guest';
    @endphp
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-6">Guest Profile</h1>

            <!-- Account Information -->
            <div class="profile-section">
                <div class="profile-header">
                    <h2 class="profile-title">Account Information</h2>
                    @if($canEdit)
                        <div class="profile-actions">
                            <a href="{{ route('profile.guest.edit', ['user' => $user->id]) }}" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded" data-icon="gear"></a>
                        </div>
                    @endif
                </div>
                <div class="profile-content">
                    <table class="profile-table">
                        <tbody>
                            <tr>
                                <td style="font-weight: 600; color: #6b7280;">Username</td>
                                <td>{{ $user->name }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600; color: #6b7280;">Email</td>
                                <td>{{ $user->email }}</td>
                            </tr>
                            <tr>
                                <td style="font-weight: 600; color: #6b7280;">Role</td>
                                <td>{{ $roleTitle }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-sm text-blue-800">
                    <strong>Guest Account:</strong> You have limited access. To unlock full features including profile information, attendance tracking, and more, please contact an administrator to upgrade your account.
                </p>
            </div>
        </div>
    </div>
</div>
