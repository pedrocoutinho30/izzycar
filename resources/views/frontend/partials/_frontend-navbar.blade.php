<nav class="navbar navbar-expand-lg">
  <div class="container d-flex align-items-center justify-content-between">
    <a class="navbar-brand" href="#top">
      <img src="{{ asset('img/logo-transparente.png') }}" alt="Logo" class="navbar-logo" style="height:auto; width:120px;" loading="lazy">
    </a>

    <!-- Título inserido junto do logo, por exemplo -->
    <h2 class="icon-colored mb-0 ms-3">{{ $title }}</h2>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" 
            aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-lg-5 me-lg-auto">
        <!-- itens do menu -->
        <li class="nav-item"><a class="nav-link" href="{{ route('frontend.home') }}">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('vehicles.list') }}">Usados</a></li>
      </ul>
    </div>
  </div>
</nav>
