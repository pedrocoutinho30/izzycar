@extends('frontend.partials.layout')

@section('title', 'Termos e Condições | Izzycar')

@section('content')
@php
$settings = \App\Models\Setting::all();
$email    = $settings->where('label', 'email')->first()->value ?? 'geral@izzycar.pt';
$phone    = $settings->where('label', 'phone')->first()->value ?? '';
$address  = $settings->where('label', 'address')->first()->value != 0 ? $settings->where('label', 'address')->first()->value :  'Rua Bento Landureza, 245 3720-261 Oliveira de Azeméis, Portugal';
$vat      = $settings->where('label', 'vat_number')->first()->value ?? '';

$sections = [
    ['id' => 'ambito',             'num' => '01', 'title' => 'Âmbito e identificação'],
    ['id' => 'servicos',           'num' => '02', 'title' => 'Serviços prestados'],
    ['id' => 'utilizacao',         'num' => '03', 'title' => 'Utilização do website'],
    ['id' => 'precos',             'num' => '04', 'title' => 'Informação e preços'],
    ['id' => 'propriedade',        'num' => '05', 'title' => 'Propriedade intelectual'],
    ['id' => 'responsabilidade',   'num' => '06', 'title' => 'Responsabilidade'],
    ['id' => 'privacidade',        'num' => '07', 'title' => 'Privacidade e dados pessoais'],
    ['id' => 'cookies',            'num' => '08', 'title' => 'Cookies'],
    ['id' => 'links',              'num' => '09', 'title' => 'Links externos'],
    ['id' => 'lei',                'num' => '10', 'title' => 'Lei aplicável e litígios'],
    ['id' => 'contacto',           'num' => '11', 'title' => 'Contactos'],
];
@endphp

{{-- ── HERO ──────────────────────────────────────────────────── --}}
<section class="lp-hero">
    <div class="lp-hero__overlay"></div>
    <div class="container">
        <div class="lp-hero__inner">
            <div class="lp-hero__breadcrumb">
                <a href="{{ route('frontend.home') }}">Início</a>
                <span>/</span>
                <span>Termos e Condições</span>
            </div>
            <div class="lp-hero__badge">
                <i class="bi bi-file-earmark-text"></i>
                Termos de Utilização
            </div>
            <h1 class="lp-hero__title">Termos e Condições</h1>
            <p class="lp-hero__sub">Regras e condições de utilização dos nossos serviços e website</p>
            <div class="lp-hero__meta">
                <span><i class="bi bi-calendar3"></i> Última atualização: Junho de 2026</span>
                <span><i class="bi bi-file-text"></i> {{ count($sections) }} secções</span>
            </div>
        </div>
    </div>
</section>

{{-- ── CONTEÚDO ───────────────────────────────────────────────── --}}
<section class="lp-body section-padding">
    <div class="container">
        <div class="lp-layout">

            {{-- Sidebar ToC --}}
            <aside class="lp-toc">
                <div class="lp-toc__inner">
                    <p class="lp-toc__label"><i class="bi bi-list-ul me-1"></i> Índice</p>
                    <nav>
                        @foreach($sections as $s)
                        <a href="#{{ $s['id'] }}" class="lp-toc__link">
                            <span class="lp-toc__num">{{ $s['num'] }}</span>
                            {{ $s['title'] }}
                        </a>
                        @endforeach
                    </nav>
                    <div class="lp-toc__cta">
                        <a href="{{ route('frontend.privacy') }}" class="lp-toc__cta-link">
                            <i class="bi bi-shield-lock me-1"></i> Política de Privacidade
                        </a>
                    </div>
                </div>
            </aside>

            {{-- Conteúdo principal --}}
            <div class="lp-content">

                {{-- Intro card --}}
                <div class="lp-intro">
                    <div class="lp-intro__icon">
                        <i class="bi bi-file-earmark-check"></i>
                    </div>
                    <div>
                        <p>O website <strong>www.izzycar.pt</strong> é propriedade da <strong>Izzycar</strong> (NIF {{ $vat }}), com sede em {{ $address }}. A utilização deste website e dos nossos serviços implica a leitura e aceitação integral dos presentes Termos e Condições.</p>
                    </div>
                </div>

                {{-- 01 --}}
                <div class="lp-section" id="ambito">
                    <div class="lp-section__num">01</div>
                    <h2 class="lp-section__title">Âmbito e identificação</h2>
                    <div class="lp-section__body">
                        <p>Os presentes Termos e Condições regulam a utilização do website <strong>www.izzycar.pt</strong> e a contratação de serviços prestados pela Izzycar.</p>
                        <div class="lp-entity-box">
                            <div class="lp-entity-row"><i class="bi bi-building"></i> <strong>Izzycar</strong></div>
                            <div class="lp-entity-row"><i class="bi bi-hash"></i> NIF: <strong>{{ $vat }}</strong></div>
                            <div class="lp-entity-row"><i class="bi bi-geo-alt"></i> {{ $address }}</div>
                            <div class="lp-entity-row"><i class="bi bi-envelope"></i> <a href="mailto:{{ $email }}" class="lp-link">{{ $email }}</a></div>
                        </div>
                        <p>A Izzycar reserva o direito de alterar estes Termos a qualquer momento. As alterações entram em vigor a partir da data de publicação no website.</p>
                    </div>
                </div>

                {{-- 02 --}}
                <div class="lp-section" id="servicos">
                    <div class="lp-section__num">02</div>
                    <h2 class="lp-section__title">Serviços prestados</h2>
                    <div class="lp-section__body">
                        <p>A Izzycar presta os seguintes serviços no âmbito automóvel:</p>
                        <div class="lp-services-grid">
                            <div class="lp-service-card">
                                <i class="bi bi-truck"></i>
                                <strong>Importação automóvel</strong>
                                <span>Aconselhamento, pesquisa, aquisição e legalização de veículos provenientes do estrangeiro</span>
                            </div>
                            <div class="lp-service-card">
                                <i class="bi bi-handshake"></i>
                                <strong>Consignação</strong>
                                <span>Venda do seu veículo em regime de consignação, com avaliação e divulgação incluídas</span>
                            </div>
                            <div class="lp-service-card">
                                <i class="bi bi-calculator"></i>
                                <strong>Simulação de custos</strong>
                                <span>Cálculo indicativo de ISV, IVA e custos de legalização para veículos importados</span>
                            </div>
                            <div class="lp-service-card">
                                <i class="bi bi-file-earmark-text"></i>
                                <strong>Cotações personalizadas</strong>
                                <span>Elaboração de cotações detalhadas para projetos de importação específicos</span>
                            </div>
                        </div>
                        <p>A prestação de cada serviço fica sujeita aos termos específicos acordados entre as partes no momento da contratação.</p>
                    </div>
                </div>

                {{-- 03 --}}
                <div class="lp-section" id="utilizacao">
                    <div class="lp-section__num">03</div>
                    <h2 class="lp-section__title">Utilização do website</h2>
                    <div class="lp-section__body">
                        <p>O utilizador compromete-se a utilizar este website de forma lícita e de acordo com os presentes Termos. Em particular, compromete-se a:</p>
                        <ul class="lp-list">
                            <li>Fornecer informações verdadeiras, precisas e completas ao preencher formulários</li>
                            <li>Não utilizar o website para fins ilícitos, fraudulentos ou que prejudiquem terceiros</li>
                            <li>Não introduzir vírus, malware ou qualquer código informático malicioso</li>
                            <li>Não realizar atividades que possam sobrecarregar ou danificar a infraestrutura do site</li>
                            <li>Não tentar aceder a áreas restritas sem autorização</li>
                        </ul>
                        <p>O incumprimento destas regras poderá resultar no bloqueio do acesso ao website e, se aplicável, na participação às autoridades competentes.</p>
                    </div>
                </div>

                {{-- 04 --}}
                <div class="lp-section" id="precos">
                    <div class="lp-section__num">04</div>
                    <h2 class="lp-section__title">Informação e preços</h2>
                    <div class="lp-section__body">
                        <p>A Izzycar procura manter toda a informação publicada no website atualizada e correta. No entanto:</p>
                        <ul class="lp-list">
                            <li>Os valores apresentados para veículos, taxas, ISV e custos de legalização são <strong>meramente indicativos</strong> e podem variar sem aviso prévio</li>
                            <li>As simulações de custos disponibilizadas no website têm carácter informativo e não constituem proposta contratual vinculativa</li>
                            <li>Os preços definitivos são os constantes nas cotações formais emitidas pela Izzycar</li>
                            <li>Variações cambiais, alterações fiscais ou alfandegárias podem afetar os valores apresentados</li>
                        </ul>
                        <div class="lp-note">
                            <i class="bi bi-info-circle"></i>
                            Para obter valores precisos e atualizados, solicite sempre uma cotação personalizada através dos nossos canais de contacto.
                        </div>
                    </div>
                </div>

                {{-- 05 --}}
                <div class="lp-section" id="propriedade">
                    <div class="lp-section__num">05</div>
                    <h2 class="lp-section__title">Propriedade intelectual</h2>
                    <div class="lp-section__body">
                        <p>Todos os conteúdos do website — incluindo, mas não se limitando a, textos, imagens, fotografias, logótipos, marcas, design, código-fonte e bases de dados — são propriedade exclusiva da Izzycar ou de terceiros que autorizaram a sua utilização.</p>
                        <p>É expressamente proibido:</p>
                        <ul class="lp-list">
                            <li>Copiar, reproduzir, distribuir ou modificar qualquer conteúdo sem autorização prévia e escrita</li>
                            <li>Utilizar a marca "Izzycar" ou qualquer elemento gráfico identificativo sem autorização</li>
                            <li>Criar obras derivadas baseadas nos conteúdos do website</li>
                        </ul>
                        <p>Para pedidos de autorização de uso de conteúdos, contacte-nos em <a href="mailto:{{ $email }}" class="lp-link">{{ $email }}</a>.</p>
                    </div>
                </div>

                {{-- 06 --}}
                <div class="lp-section" id="responsabilidade">
                    <div class="lp-section__num">06</div>
                    <h2 class="lp-section__title">Responsabilidade</h2>
                    <div class="lp-section__body">
                        <p>A Izzycar não se responsabiliza por:</p>
                        <ul class="lp-list">
                            <li>Interrupções, erros técnicos ou indisponibilidade temporária do website</li>
                            <li>Danos resultantes do uso ou impossibilidade de uso do website</li>
                            <li>Conteúdos, produtos ou serviços disponibilizados por websites de terceiros acessíveis através de links</li>
                            <li>Informações incorretas fornecidas pelo utilizador nos formulários</li>
                            <li>Alterações inesperadas em legislação fiscal, aduaneira ou automóvel que afetem os serviços contratados</li>
                        </ul>
                        <p>A responsabilidade máxima da Izzycar perante o cliente, em qualquer circunstância, não excederá o valor pago pelo serviço em causa.</p>
                    </div>
                </div>

                {{-- 07 --}}
                <div class="lp-section" id="privacidade">
                    <div class="lp-section__num">07</div>
                    <h2 class="lp-section__title">Privacidade e dados pessoais</h2>
                    <div class="lp-section__body">
                        <p>O tratamento de dados pessoais dos utilizadores deste website rege-se pela nossa <a href="{{ route('frontend.privacy') }}" class="lp-link">Política de Privacidade</a>, em conformidade com o RGPD (Regulamento UE 2016/679).</p>
                        <p>Ao submeter qualquer formulário neste website, o utilizador declara ter lido e aceite a Política de Privacidade e consente no tratamento dos seus dados para as finalidades indicadas.</p>
                    </div>
                </div>

                {{-- 08 --}}
                <div class="lp-section" id="cookies">
                    <div class="lp-section__num">08</div>
                    <h2 class="lp-section__title">Cookies</h2>
                    <div class="lp-section__body">
                        <p>Este website utiliza cookies para melhorar a experiência de navegação e analisar o tráfego. Para mais informações sobre os tipos de cookies utilizados e como geri-los, consulte a nossa <a href="{{ route('frontend.privacy') }}#cookies" class="lp-link">Política de Privacidade — secção Cookies</a>.</p>
                        <p>A continuação da navegação neste website implica o consentimento na utilização de cookies essenciais. Para cookies opcionais (analíticos e de marketing), será solicitado consentimento explícito.</p>
                    </div>
                </div>

                {{-- 09 --}}
                <div class="lp-section" id="links">
                    <div class="lp-section__num">09</div>
                    <h2 class="lp-section__title">Links externos</h2>
                    <div class="lp-section__body">
                        <p>O website pode conter hiperligações para websites de terceiros (ex.: portal IMT, Autoridade Tributária, institutos de inspeção). Estas hiperligações são disponibilizadas apenas por conveniência do utilizador.</p>
                        <p>A Izzycar não tem controlo sobre esses websites externos e não se responsabiliza pelo seu conteúdo, disponibilidade ou políticas de privacidade. A visita a websites externos é da inteira responsabilidade do utilizador.</p>
                    </div>
                </div>

                {{-- 10 --}}
                <div class="lp-section" id="lei">
                    <div class="lp-section__num">10</div>
                    <h2 class="lp-section__title">Lei aplicável e resolução de litígios</h2>
                    <div class="lp-section__body">
                        <p>Estes Termos e Condições são regidos e interpretados de acordo com a <strong>lei portuguesa</strong>.</p>
                        <p>Em caso de litígio decorrente da utilização do website ou dos serviços prestados, as partes deverão procurar, em primeiro lugar, uma resolução amigável. Caso não seja possível, será competente o tribunal da comarca da sede da Izzycar, salvo disposição legal imperativa em contrário.</p>
                        <div class="lp-highlight">
                            <i class="bi bi-person-check"></i>
                            <span>Para resolução alternativa de litígios de consumo, o utilizador pode recorrer ao <strong>Centro de Arbitragem de Conflitos de Consumo</strong> ou a outras entidades de RAL registadas em Portugal.</span>
                        </div>
                    </div>
                </div>

                {{-- 11 --}}
                <div class="lp-section" id="contacto">
                    <div class="lp-section__num">11</div>
                    <h2 class="lp-section__title">Contactos</h2>
                    <div class="lp-section__body">
                        <p>Para qualquer questão relacionada com estes Termos e Condições, pedidos de informação ou reclamações, contacte-nos através dos seguintes meios:</p>
                        <div class="lp-contact-row">
                            <a href="mailto:{{ $email }}" class="lp-contact-chip">
                                <i class="bi bi-envelope"></i> {{ $email }}
                            </a>
                            @if($phone)
                            <a href="tel:{{ $phone }}" class="lp-contact-chip">
                                <i class="bi bi-telephone"></i> {{ $phone }}
                            </a>
                            @endif
                        </div>
                        <div class="lp-update-badge" style="margin-top:1.5rem">
                            <i class="bi bi-clock-history"></i>
                            <strong>Última atualização:</strong> Janeiro de 2026
                        </div>
                    </div>
                </div>

            </div>{{-- /lp-content --}}
        </div>{{-- /lp-layout --}}
    </div>
</section>
@endsection

@push('styles')
<style>
/* ── Hero ─────────────────────────────────────────────────── */
.lp-hero {
    position: relative;
    background: linear-gradient(135deg, #111 0%, #1a1a1a 60%, #111 100%);
    padding: 3.5rem 0 3rem; overflow: hidden;
}
.lp-hero__overlay {
    position: absolute; inset: 0;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%236e0707' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
}
.lp-hero__inner { position: relative; max-width: 700px; }
.lp-hero__breadcrumb {
    display: flex; align-items: center; gap: .5rem;
    font-size: .85rem; color: rgba(255,255,255,.5); margin-bottom: 1.25rem;
}
.lp-hero__breadcrumb a { color: rgba(255,255,255,.5); text-decoration: none; }
.lp-hero__breadcrumb a:hover { color: #fff; }
.lp-hero__breadcrumb span { color: rgba(255,255,255,.3); }
.lp-hero__badge {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .5rem 1.25rem;
    background: rgba(110,7,7,.25); border: 1px solid rgba(110,7,7,.4);
    border-radius: 50px; color: #fff; font-size: .85rem; font-weight: 600;
    margin-bottom: 1.25rem;
}
.lp-hero__title {
    font-size: clamp(2rem, 4vw, 2.75rem);
    font-weight: 900; color: #fff; line-height: 1.15; margin-bottom: .75rem;
}
.lp-hero__sub { font-size: 1.1rem; color: rgba(255,255,255,.7); margin-bottom: 1.5rem; }
.lp-hero__meta {
    display: flex; gap: 1.5rem; flex-wrap: wrap;
    font-size: .85rem; color: rgba(255,255,255,.5);
}
.lp-hero__meta span { display: flex; align-items: center; gap: .4rem; }

/* ── Layout ───────────────────────────────────────────────── */
.lp-layout {
    display: flex;
    gap: 2.5rem;
    align-items: flex-start;
}
@media (max-width: 991px) {
    .lp-layout { flex-direction: column; }
    .lp-toc { width: 100% !important; position: static !important; }
}

/* ── ToC ──────────────────────────────────────────────────── */
.lp-toc {
    width: 260px;
    flex-shrink: 0;
    position: sticky;
    top: 90px;
    align-self: flex-start;
}
.lp-toc__inner {
    background: #fff; border-radius: 16px; padding: 1.5rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.07); border: 1px solid #f0f0f0;
}
.lp-toc__label {
    font-size: .75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #999; margin-bottom: 1rem;
}
.lp-toc__link {
    display: flex; align-items: center; gap: .6rem;
    padding: .45rem .75rem; border-radius: 8px; margin-bottom: .2rem;
    font-size: .85rem; color: #444; text-decoration: none; transition: all .2s;
}
.lp-toc__link:hover, .lp-toc__link.active {
    background: rgba(110,7,7,.06); color: #6e0707; font-weight: 600;
}
.lp-toc__num {
    font-size: .7rem; font-weight: 700; color: #6e0707;
    background: rgba(110,7,7,.08); border-radius: 4px;
    padding: .1rem .4rem; flex-shrink: 0;
}
.lp-toc__cta { margin-top: 1.25rem; padding-top: 1.25rem; border-top: 1px solid #f0f0f0; }
.lp-toc__cta-link {
    display: flex; align-items: center; gap: .5rem;
    font-size: .85rem; color: #6e0707; text-decoration: none; font-weight: 600;
}
.lp-toc__cta-link:hover { text-decoration: underline; }

/* ── Sections ─────────────────────────────────────────────── */
.lp-intro {
    display: flex; gap: 1.5rem; align-items: flex-start;
    background: linear-gradient(135deg, #6e0707, #900);
    border-radius: 18px; padding: 2rem; margin-bottom: 2rem; color: #fff;
}
.lp-intro__icon {
    flex-shrink: 0; width: 56px; height: 56px;
    background: rgba(255,255,255,.15); border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 1.5rem;
}
.lp-intro p { font-size: 1.05rem; line-height: 1.7; margin: 0; opacity: .95; }

.lp-section {
    background: #fff; border-radius: 18px; padding: 2.5rem 2.5rem 2rem;
    margin-bottom: 1.75rem; box-shadow: 0 4px 20px rgba(0,0,0,.06);
    border-left: 4px solid #6e0707; position: relative; scroll-margin-top: 90px;
}
.lp-section__num {
    position: absolute; top: -16px; left: 2rem;
    background: linear-gradient(135deg, #6e0707, #900);
    color: #fff; width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 14px rgba(110,7,7,.3);
}
.lp-section__title { font-size: 1.4rem; font-weight: 700; color: #111; margin: .5rem 0 1.25rem; padding-top: .25rem; }
.lp-section__body { font-size: 1rem; line-height: 1.75; color: #111; }
.lp-section__body p,
.lp-section__body ul li,
.lp-section__body ol li,
.lp-list li,
.lp-entity-row,
.lp-note,
.lp-highlight span { color: #111 !important; }
.lp-section__body p { margin-bottom: .9rem; }
.lp-section__body p:last-child { margin-bottom: 0; }

/* ── Lists ─────────────────────────────────────────────────── */
.lp-list { list-style: none; padding: 0; margin: .75rem 0 1rem; }
.lp-list li { position: relative; padding-left: 1.6rem; margin-bottom: .6rem; }
.lp-list li::before {
    content: ''; position: absolute; left: 0; top: .6rem;
    width: 7px; height: 7px; border-radius: 50%; background: #6e0707;
}

/* ── Entity / contact ─────────────────────────────────────── */
.lp-entity-box {
    background: #f8f9fa; border-radius: 12px; padding: 1.25rem 1.5rem;
    margin: 1rem 0; display: flex; flex-direction: column; gap: .6rem;
}
.lp-entity-row { display: flex; align-items: center; gap: .6rem; font-size: .95rem; }
.lp-entity-row i { color: #6e0707; width: 18px; }
.lp-contact-row { display: flex; gap: .75rem; flex-wrap: wrap; margin-top: 1rem; }
.lp-contact-chip {
    display: inline-flex; align-items: center; gap: .5rem;
    padding: .6rem 1rem; background: #f8f9fa; border-radius: 10px;
    color: #333; text-decoration: none; font-size: .9rem; font-weight: 500; transition: all .2s;
}
.lp-contact-chip:hover { background: #f0e8e8; color: #6e0707; }
.lp-contact-chip i { color: #6e0707; }

/* ── Services grid ────────────────────────────────────────── */
.lp-services-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0; }
@media (max-width: 600px) { .lp-services-grid { grid-template-columns: 1fr; } }
.lp-service-card {
    background: #f8f9fa; border-radius: 12px; padding: 1.1rem;
    display: flex; flex-direction: column; gap: .4rem;
}
.lp-service-card i { font-size: 1.4rem; color: #6e0707; }
.lp-service-card strong { font-size: .9rem; color: #111; }
.lp-service-card span { font-size: .85rem; color: #666; line-height: 1.5; }

/* ── Note / highlight ─────────────────────────────────────── */
.lp-note {
    display: flex; gap: .6rem; align-items: flex-start;
    background: rgba(110,7,7,.04); border-left: 3px solid #6e0707;
    border-radius: 0 10px 10px 0; padding: .9rem 1.1rem;
    font-size: .9rem; color: #555; margin-top: 1rem;
}
.lp-note i { color: #6e0707; flex-shrink: 0; margin-top: .1rem; }
.lp-highlight {
    display: flex; gap: .75rem; align-items: flex-start;
    background: rgba(40,167,69,.06); border: 1px solid rgba(40,167,69,.2);
    border-radius: 12px; padding: 1rem 1.25rem; margin-top: 1rem; font-size: .95rem;
}
.lp-highlight i { color: #28a745; font-size: 1.2rem; flex-shrink: 0; }

/* ── Misc ─────────────────────────────────────────────────── */
.lp-link { color: #6e0707; font-weight: 600; text-decoration: none; }
.lp-link:hover { text-decoration: underline; }
.lp-update-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: #f8f9fa; border-radius: 10px; padding: .75rem 1.25rem;
    font-size: .9rem; color: #555;
}
.lp-update-badge i { color: #6e0707; }

@media (max-width: 768px) {
    .lp-section { padding: 2rem 1.5rem 1.5rem; }
    .lp-section__title { font-size: 1.2rem; }
    .lp-intro { flex-direction: column; padding: 1.5rem; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    const links    = document.querySelectorAll('.lp-toc__link');
    const sections = document.querySelectorAll('.lp-section[id]');

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                links.forEach(l => l.classList.remove('active'));
                const active = document.querySelector(`.lp-toc__link[href="#${entry.target.id}"]`);
                if (active) active.classList.add('active');
            }
        });
    }, { rootMargin: '-20% 0px -70% 0px' });

    sections.forEach(s => observer.observe(s));

    links.forEach(link => {
        link.addEventListener('click', e => {
            e.preventDefault();
            const target = document.querySelector(link.getAttribute('href'));
            if (target) target.scrollIntoView({ behavior: 'smooth' });
        });
    });
})();
</script>
@endpush
