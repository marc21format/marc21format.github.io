<div>
    @if($attendance)
        <table class="min-w-full">
            <tr><th>User</th><td>{{ $attendance->user->name ?? '—' }}</td></tr>
            <tr><th>Date</th><td>{{ optional($attendance->date)->toDateString() }}</td></tr>
            <tr><th>Session</th><td>{{ $attendance->session }}</td></tr>
            <tr><th>Time</th><td>{{ $attendance->attendance_time }}</td></tr>
            <tr><th>Recorded By</th><td>{{ $attendance->recordedBy->name ?? '—' }}</td></tr>
        </table>
    @else
        <div>No attendance found.</div>
    @endif
</div>
