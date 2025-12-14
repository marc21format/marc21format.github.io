@extends('layouts.app')

@section('body-class', 'welcome-page')

@section('content')
<section class="hero">
  <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel" role="region" aria-label="Homepage carousel">
    <ol class="carousel-indicators">
      <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
      <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
      <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
    </ol>
    <div class="carousel-inner slides" role="list">
      <div class="carousel-item active">
        <img class="d-block w-100" src="https://i.ibb.co/DDZ25Cf0/MG-0211.jpg" alt="Exam preparation scene">
        <div class="carousel-caption d-none d-md-block text-left" aria-live="polite">
          <h5 id="slide-maintext-0"></h5>
          <p id="slide-subtext-0"></p>
          <p id="slide-subsubtext-0" class="subsubtext"></p>
          <div id="slide-buttons-0"></div>
        </div>
      </div>
      <div class="carousel-item">
        <img class="d-block w-100" src="https://i.ibb.co/0j6vR3Nx/IMG-3415.jpg" alt="Attendance tracking">
        <div class="carousel-caption d-none d-md-block text-left" aria-live="polite">
          <h5 id="slide-maintext-1"></h5>
          <p id="slide-subtext-1"></p>
          <p id="slide-subsubtext-1" class="subsubtext"></p>
          <div id="slide-buttons-1"></div>
        </div>
      </div>
      <div class="carousel-item">
        <img class="d-block w-100" src="https://i.ibb.co/RpR6tVnt/IMG-20230423-163159.jpg" alt="FCEER history">
        <div class="carousel-caption d-none d-md-block text-left" aria-live="polite">
          <h5 id="slide-maintext-2"></h5>
          <p id="slide-subtext-2"></p>
          <p id="slide-subsubtext-2" class="subsubtext"></p>
          <div id="slide-buttons-2"></div>
        </div>
      </div>
    </div>
    <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev" aria-label="Previous slide">
      <span class="carousel-control-prev-icon" aria-hidden="true"></span>
      <span class="sr-only">Previous</span>
    </a>
    <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next" aria-label="Next slide">
      <span class="carousel-control-next-icon" aria-hidden="true"></span>
      <span class="sr-only">Next</span>
    </a>
  </div>
</section>
@endsection

@section('info-content')
<section class="info-section">
  <div class="info-content">
    <h2>About the FCEER Mock Exam</h2>
  </div>
</section>

<div class="info-slideshow-container">
  <!-- Left side: text -->
  <div class="info1">
    <p>
      Started in 2006, FCEER and its yearly review sessions and mock exams have been dedicated to helping students prepare for their College Entrance Tests (CETs). Our mission is to provide high-quality resources and practice materials that simulate the actual exam experience.
    </p>
  </div>

  <div class="slideshow">
    <div class="slideshow-container">
        <img src="https://i.ibb.co/hFL5J4P1/image.png" class="slide active" alt="Overview image" tabindex="0">
        <img src="https://i.ibb.co/8tbcVpm/image.png" class="slide" alt="Classroom image" tabindex="0">
        <img src="https://i.ibb.co/ym1F11NV/image.png" class="slide" alt="Students studying" tabindex="0">
        <img src="https://i.ibb.co/gFj3Pr1H/MG-0012.jpg" class="slide" alt="Group session" tabindex="0">
    </div>
  </div>
</div>

@endsection
