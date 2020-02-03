@php
  use App\Http\Controllers\NewsletterController;
  $totalAgendamentos = App\Http\Controllers\Helpers\AgendamentoControllerHelper::countAgendamentos();
  $totalInscritos = App\Http\Controllers\Helpers\CursoHelper::totalInscritos();
@endphp

<div class="col">
  <div class="info-box">
    <span class="info-box-icon bg-success">
      <i class="fas fa-globe"></i>
    </span>
    <div class="info-box-content">
      <span class="info-box-text">Visitas no último mês</span>
      <span class="info-box-number">113.941</span>
    </div>
  </div>
</div>
<div class="col">
  <div class="info-box">
    <span class="info-box-icon bg-danger">
      <i class="fas fa-user-clock"></i>
    </span>
    <div class="info-box-content">
      <span class="info-box-text">Agendamentos</span>
      <span class="info-box-number">{{ $totalAgendamentos }}</span>
    </div>
  </div>
</div>
<div class="col">
  <div class="info-box">
    <span class="info-box-icon bg-warning">
      <i class="fas fa-chalkboard-teacher text-white"></i>
    </span>
    <div class="info-box-content">
      <span class="info-box-text">Inscrições em Cursos</span>
      <span class="info-box-number">{{ $totalInscritos }}</span>
    </div>
  </div>
</div>
<div class="col">
  @if(session('idperfil') === 1 || session('idperfil') === 3)
  <a href="/admin/newsletter/download" class="inherit">
  @endif
  <div class="info-box">
    <span class="info-box-icon bg-info">
      <i class="fas fa-newspaper"></i>
    </span>
    <div class="info-box-content">
      <span class="info-box-text inherit">Inscrições na Newsletter</span>
      <span class="info-box-number inherit d-inline">{{ NewsletterController::countNewsletter() }}</span>&nbsp;<span class="linkDownload d-inline">(Baixar CSV)</span>
    </div>
  </div>
  @if(session('idperfil') === 1 || session('idperfil') === 3)
  </a>
  @endif
</div>