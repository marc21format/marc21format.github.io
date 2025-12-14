<nav>
  <div class="logo">
    <img src="https://i.ibb.co/gLMFf5YR/image-transparent-Craiyon.png" alt="Logo">
    <div class="logo-text">
      <div class="logo-slide active">FCEER</div>
      <div class="logo-slide">
        San Jose Del Monte<br>
        Free College Entrance Exam Review<br>
        <span class="light-text">Serve and Inspire</span>
      </div>
    </div>
  </div>

  <div class="nav-links">
    <a href="/">Home</a>

    @guest
      <a href="#" onclick="openRegisterModal(); return false;">Register</a>
      <a href="#" onclick="openLoginModal(); return false;">Log in</a>
    @endguest

    @auth
      {{-- show FCEER roster to Executive (1), Admin (config), and Instructor (config) --}}
      @php
        $adminRoleId = (int) config('roles.admin_id', 3);
        $instructorRoleId = (int) config('roles.instructor_id', 4);
        $allowedRoles = [1, $adminRoleId, $instructorRoleId];
        $userRoleId = (int) auth()->user()->role_id;
      @endphp

      @if(in_array($userRoleId, $allowedRoles, true))
        <div class="dropdown">
          <a href="#" class="dropdown-toggle">FCEER Roster</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('roster.students.index') }}">Students</a></li>
                <li><a class="dropdown-item" href="{{ route('roster.volunteers.index') }}">Volunteers</a></li>
                <li><a class="dropdown-item" href="{{ route('roster.users.index') }}">User Roster</a></li>
            </ul>
        </div>
      @endif

      <div class="dropdown">
        <a href="#" class="dropdown-toggle">{{ auth()->user()->name }}</a>
        <div class="dropdown-menu">
          {{-- Profile link: student (role_id 4) goes to student profile, volunteer (1,2,3) goes to volunteer profile, guest (5) goes to guest profile --}}
          @php $roleId = auth()->user()->role_id ?? null; @endphp
          @if ($roleId == 4)
            <a href="{{ route('profile.student.show') }}">Student Profile</a>
            <a href="{{ route('attendance.user', ['userId' => auth()->user()->id]) }}">Attendance</a>
          @elseif (in_array($roleId, [1,2,3]))
            <a href="{{ route('profile.volunteer.show') }}">Volunteer Profile</a>
            <a href="{{ route('attendance.user', ['userId' => auth()->user()->id]) }}">Attendance</a>
          @elseif ($roleId == 5)
            <a href="{{ route('profile.guest.show') }}">Guest Profile</a>
          @else
            <a href="{{ route('profile.show') }}">Profile</a>
          @endif
          <form method="POST" action="{{ route('logout') }}" class="logout-form">
            @csrf
            <button type="submit">Logout</button>
          </form>
        </div>
      </div>
    @endauth
  </div>
</nav>