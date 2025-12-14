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

    <?php if(auth()->guard()->guest()): ?>
      <a href="#" onclick="openRegisterModal(); return false;">Register</a>
      <a href="#" onclick="openLoginModal(); return false;">Log in</a>
    <?php endif; ?>

    <?php if(auth()->guard()->check()): ?>
      
      <?php
        $adminRoleId = (int) config('roles.admin_id', 3);
        $instructorRoleId = (int) config('roles.instructor_id', 4);
        $allowedRoles = [1, $adminRoleId, $instructorRoleId];
        $userRoleId = (int) auth()->user()->role_id;
      ?>

      <?php if(in_array($userRoleId, $allowedRoles, true)): ?>
        <div class="dropdown">
          <a href="#" class="dropdown-toggle">FCEER Roster</a>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="<?php echo e(route('roster.students.index')); ?>">Students</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('roster.volunteers.index')); ?>">Volunteers</a></li>
                <li><a class="dropdown-item" href="<?php echo e(route('roster.users.index')); ?>">User Roster</a></li>
            </ul>
        </div>
      <?php endif; ?>

      <div class="dropdown">
        <a href="#" class="dropdown-toggle"><?php echo e(auth()->user()->name); ?></a>
        <div class="dropdown-menu">
          
          <?php $roleId = auth()->user()->role_id ?? null; ?>
          <?php if($roleId == 4): ?>
            <a href="<?php echo e(route('profile.student.show')); ?>">Student Profile</a>
            <a href="<?php echo e(route('attendance.user', ['userId' => auth()->user()->id])); ?>">Attendance</a>
          <?php elseif(in_array($roleId, [1,2,3])): ?>
            <a href="<?php echo e(route('profile.volunteer.show')); ?>">Volunteer Profile</a>
            <a href="<?php echo e(route('attendance.user', ['userId' => auth()->user()->id])); ?>">Attendance</a>
          <?php elseif($roleId == 5): ?>
            <a href="<?php echo e(route('profile.guest.show')); ?>">Guest Profile</a>
          <?php else: ?>
            <a href="<?php echo e(route('profile.show')); ?>">Profile</a>
          <?php endif; ?>
          <form method="POST" action="<?php echo e(route('logout')); ?>" class="logout-form">
            <?php echo csrf_field(); ?>
            <button type="submit">Logout</button>
          </form>
        </div>
      </div>
    <?php endif; ?>
  </div>
</nav><?php /**PATH C:\Users\Marc Ian C. Young\laravel\fceerweb\resources\views/partials/navbar.blade.php ENDPATH**/ ?>