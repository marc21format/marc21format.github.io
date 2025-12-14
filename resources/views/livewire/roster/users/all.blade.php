<div class="container mx-auto py-8 px-4">
    <h1 class="text-2xl font-bold mb-6">User Roster</h1>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <colgroup>
                <col style="width: 6%">
                <col style="width: 14%">
                <col style="width: 30%">
                <col style="width: 30%">
                <col style="width: 20%">
            </colgroup>
            <thead>
                <tr>
                    <th class="px-6 py-3 border-b text-left">#</th>
                    <th class="px-6 py-3 border-b text-left">User ID</th>
                    <th class="px-6 py-3 border-b text-left">Username</th>
                    <th class="px-6 py-3 border-b text-left">Email</th>
                    <th class="px-6 py-3 border-b text-left">Role</th>
                </tr>
            </thead>
            <tbody>
                @foreach($users as $i => $user)
                    @php
                        $roleId = (int) ($user->role_id ?? 0);
                        $roleTitle = optional($user->role)->role_title;
                        $guestRoleId = (int) config('roles.guest_id', 5);
                        if (in_array($roleId, [1,2,3]) || $roleTitle === 'Volunteer') {
                            $profileRoute = route('profile.volunteer.show', ['user' => $user->id]);
                        } elseif ($roleId === $guestRoleId || $roleTitle === 'Guest') {
                            $profileRoute = route('profile.guest.show', ['user' => $user->id]);
                        } else {
                            $profileRoute = route('profile.student.show', ['user' => $user->id]);
                        }
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 border-b text-blue-600 font-semibold">
                            <a href="{{ $profileRoute }}" class="hover:underline">{{ $i + 1 }}</a>
                        </td>
                        <td class="px-6 py-3 border-b">{{ $user->id }}</td>
                        <td class="px-6 py-3 border-b">{{ $user->name }}</td>
                        <td class="px-6 py-3 border-b">{{ $user->email }}</td>
                        <td class="px-6 py-3 border-b">{{ optional($user->role)->role_title ?? 'N/A' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
