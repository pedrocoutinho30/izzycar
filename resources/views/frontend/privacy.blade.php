@extends('frontend.partials.layout')

@section('title', 'Política de Privacidade | Izzycar')

@section('content')
@php
$settings = \App\Models\Setting::all();
$email    = $settings->where('label', 'email')->first()->value ?? 'geral@izzycar.pt';
$phone    = $settings->where('label', 'phone')->first()->value ?? '';
$address  = $settings->where('label', 'address')->first()->value != 0 ? $settings->where('label', 'address')->first()->value :  'Rua Bento Landureza, 245 3720-261 Oliveira de Azeméis, Portugal';
$vat      = $settings->where('label', 'vat_number')->first()->value ?? '';

$sections = [
    ['id' => 'responsavel',    'num' => '01', 'title' => 'Responsável pelo tratamento'],
    ['id' => 'dados',          'num' => '02', 'title' => 'Dados recolhidos'],
    ['id' => 'base-juridica',  'num' => '03', 'title' => 'Base jurídica'],
    ['id' => 'finalidade',     'num' => '04', 'title' => 'Finalidade do tratamento'],
    ['id' => 'conservacao',    'num' => '05', 'title' => 'Conservação dos dados'],
    ['id' => 'partilha',       'num' => '06', 'title' => 'Partilha de dados'],
    ['id' => 'direitos',       'num' => '07', 'title' => 'Os seus direitos'],
    ['id' => 'cookies',        'num' => '08', 'title' => 'Cookies'],
    ['id' => 'transferencias', 'num' => '09', 'title' => 'Transferências internacionais'],
    ['id' => 'alteracoes',     'num' => '10', 'title' => 'Alterações a esta Política'],
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
                <span>Política de Privacidade</span>
            </div>
            <div class="lp-hero__badge">
                <i class="bi bi-shield-lock"></i>
                Proteção de Dados · RGPD
            </div>
            <h1 class="lp-hero__title">Política de Privacidade</h1>
            <p class="lp-hero__sub">Transparência e respeito pelos seus dados pessoais</p>
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
            <aside class="lp-toc" id="lp-toc">
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
                        <a href="{{ route('frontend.terms') }}" class="lp-toc__cta-link">
                            <i class="bi bi-file-earmark-text me-1"></i> Termos e Condições
                        </a>
                    </div>
                </div>
            </aside>

            {{-- Conteúdo principal --}}
            <div class="lp-content">

                {{-- Intro card --}}
                <div class="lp-intro">
                    <div class="lp-intro__icon">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <p>Na <strong>Izzycar</strong> respeitamos a sua privacidade e estamos empenhados em proteger os seus dados pessoais em plena conformidade com o <strong>Regulamento Geral sobre a Proteção de Dados (RGPD — Regulamento UE 2016/679)</strong> e a legislação portuguesa aplicável.</p>
                    </div>
                </div>

                {{-- 01 --}}
                <div class="lp-section" id="responsavel">
                    <div class="lp-section__num">01</div>
                    <h2 class="lp-section__title">Responsável pelo tratamento</h2>
                    <div class="lp-section__body">
                        <p>A entidade responsável pelo tratamento dos seus dados pessoais é:</p>
                        <div class="lp-entity-box">
                            <div class="lp-entity-row"><i class="bi bi-building"></i> <strong>Izzycar</strong></div>
                            <div class="lp-entity-row"><i class="bi bi-hash"></i> NIF: <strong>{{ $vat }}</strong></div>
                            <div class="lp-entity-row"><i class="bi bi-geo-alt"></i> {{ $address }}</div>
                        </div>
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
                    </div>
                </div>

                {{-- 02 --}}
                <div class="lp-section" id="dados">
                    <div class="lp-section__num">02</div>
                    <h2 class="lp-section__title">Dados recolhidos</h2>
                    <div class="lp-section__body">
                        <p>Podemos recolher as seguintes categorias de dados pessoais:</p>
                        <div class="lp-grid-2">
                            <div class="lp-data-card">
                                <i class="bi bi-person-vcard"></i>
                                <strong>Dados de identificação</strong>
                                <span>Nome completo, endereço de e-mail, número de telefone/telemóvel</span>
                            </div>
                            <div class="lp-data-card">
                                <i class="bi bi-chat-dots"></i>
                                <strong>Dados de contacto e pedidos</strong>
                                <span>Informações fornecidas através dos formulários de cotação, importação ou consignação</span>
                            </div>
                            <div class="lp-data-card">
                                <i class="bi bi-globe"></i>
                                <strong>Dados de navegação</strong>
                                <span>Endereço IP, tipo de browser, páginas visitadas, duração da sessão (via cookies)</span>
                            </div>
                            <div class="lp-data-card">
                                <i class="bi bi-car-front"></i>
                                <strong>Dados sobre veículos</strong>
                                <span>Informações sobre o veículo a importar ou a consignar, quando fornecidas voluntariamente</span>
                            </div>
                        </div>
                        <p class="lp-note"><i class="bi bi-info-circle"></i> Não recolhemos dados de categorias especiais (dados sensíveis) como origem racial, opiniões políticas, dados de saúde ou dados biométricos.</p>
                    </div>
                </div>

                {{-- 03 --}}
                <div class="lp-section" id="base-juridica">
                    <div class="lp-section__num">03</div>
                    <h2 class="lp-section__title">Base jurídica do tratamento</h2>
                    <div class="lp-section__body">
                        <p>O tratamento dos seus dados assenta nas seguintes bases jurídicas previstas no RGPD:</p>
                        <ul class="lp-list">
                            <li><strong>Execução de contrato ou pré-contrato</strong> — para responder a pedidos de cotação, gerir processos de importação ou consignação.</li>
                            <li><strong>Consentimento</strong> — para o envio de comunicações comerciais e newsletter, que pode retirar a qualquer momento.</li>
                            <li><strong>Interesse legítimo</strong> — para fins estatísticos e de melhoria do website, garantindo que os seus interesses e direitos não são prejudicados.</li>
                            <li><strong>Cumprimento de obrigação legal</strong> — quando exigido por lei portuguesa ou comunitária.</li>
                        </ul>
                    </div>
                </div>

                {{-- 04 --}}
                <div class="lp-section" id="finalidade">
                    <div class="lp-section__num">04</div>
                    <h2 class="lp-section__title">Finalidade do tratamento</h2>
                    <div class="lp-section__body">
                        <p>Os dados recolhidos são utilizados exclusivamente para:</p>
                        <ul class="lp-list">
                            <li>Responder a pedidos de contacto e enviar cotações personalizadas</li>
                            <li>Gerir processos de importação, consignação e serviços contratados</li>
                            <li>Enviar comunicações comerciais ou informativas (apenas com consentimento)</li>
                            <li>Analisar estatísticas de utilização do website para melhorar a experiência</li>
                            <li>Cumprir obrigações legais e fiscais</li>
                        </ul>
                        <p>Os seus dados <strong>nunca são utilizados para tomadas de decisão automatizadas</strong> com efeitos jurídicos ou que o afetem de forma significativa.</p>
                    </div>
                </div>

                {{-- 05 --}}
                <div class="lp-section" id="conservacao">
                    <div class="lp-section__num">05</div>
                    <h2 class="lp-section__title">Conservação dos dados</h2>
                    <div class="lp-section__body">
                        <p>Os dados pessoais são conservados pelo período estritamente necessário à finalidade para que foram recolhidos:</p>
                        <div class="lp-retention-table">
                            <div class="lp-retention-row lp-retention-header">
                                <span>Tipo de dados</span>
                                <span>Prazo de conservação</span>
                            </div>
                            <div class="lp-retention-row">
                                <span>Pedidos de cotação / contacto</span>
                                <span>2 anos após o último contacto</span>
                            </div>
                            <div class="lp-retention-row">
                                <span>Contratos e documentação de serviços</span>
                                <span>10 anos (obrigação legal fiscal)</span>
                            </div>
                            <div class="lp-retention-row">
                                <span>Dados de newsletter</span>
                                <span>Até cancelar a subscrição</span>
                            </div>
                            <div class="lp-retention-row">
                                <span>Dados de navegação / cookies analíticos</span>
                                <span>13 meses</span>
                            </div>
                        </div>
                        <p>Findo o prazo, os dados são eliminados ou anonimizados de forma irreversível.</p>
                    </div>
                </div>

                {{-- 06 --}}
                <div class="lp-section" id="partilha">
                    <div class="lp-section__num">06</div>
                    <h2 class="lp-section__title">Partilha de dados</h2>
                    <div class="lp-section__body">
                        <p>Os seus dados podem ser partilhados com prestadores de serviços que atuam como subcontratantes, nomeadamente:</p>
                        <ul class="lp-list">
                            <li>Fornecedores de alojamento web e infraestrutura tecnológica</li>
                            <li>Ferramentas de análise de tráfego (ex.: Google Analytics)</li>
                            <li>Plataformas de email marketing (quando subscreveu a newsletter)</li>
                            <li>Autoridades públicas, quando exigido por lei</li>
                        </ul>
                        <div class="lp-highlight">
                            <i class="bi bi-shield-fill-check"></i>
                            <span><strong>Nunca vendemos os seus dados pessoais a terceiros</strong> nem os partilhamos para fins comerciais não relacionados com os nossos serviços.</span>
                        </div>
                    </div>
                </div>

                {{-- 07 --}}
                <div class="lp-section" id="direitos">
                    <div class="lp-section__num">07</div>
                    <h2 class="lp-section__title">Os seus direitos</h2>
                    <div class="lp-section__body">
                        <p>Ao abrigo do RGPD, tem os seguintes direitos relativamente aos seus dados pessoais:</p>
                        <div class="lp-rights-grid">
                            <div class="lp-right-card">
                                <i class="bi bi-eye"></i>
                                <strong>Acesso</strong>
                                <span>Saber quais os dados que tratamos sobre si</span>
                            </div>
                            <div class="lp-right-card">
                                <i class="bi bi-pencil"></i>
                                <strong>Retificação</strong>
                                <span>Corrigir dados incorretos ou incompletos</span>
                            </div>
                            <div class="lp-right-card">
                                <i class="bi bi-trash"></i>
                                <strong>Eliminação</strong>
                                <span>Pedir a eliminação dos seus dados ("direito ao esquecimento")</span>
                            </div>
                            <div class="lp-right-card">
                                <i class="bi bi-slash-circle"></i>
                                <strong>Oposição</strong>
                                <span>Opor-se ao tratamento para fins de marketing direto</span>
                            </div>
                            <div class="lp-right-card">
                                <i class="bi bi-pause-circle"></i>
                                <strong>Limitação</strong>
                                <span>Solicitar a limitação do tratamento em determinadas circunstâncias</span>
                            </div>
                            <div class="lp-right-card">
                                <i class="bi bi-download"></i>
                                <strong>Portabilidade</strong>
                                <span>Receber os seus dados num formato estruturado e legível</span>
                            </div>
                        </div>
                        <p>Para exercer qualquer destes direitos, contacte-nos por <a href="mailto:{{ $email }}" class="lp-link">{{ $email }}</a>. Tem ainda o direito de apresentar reclamação à <strong>CNPD</strong> (Comissão Nacional de Proteção de Dados) em <a href="https://www.cnpd.pt" target="_blank" rel="noopener" class="lp-link">www.cnpd.pt</a>.</p>
                    </div>
                </div>

                {{-- 08 --}}
                <div class="lp-section" id="cookies">
                    <div class="lp-section__num">08</div>
                    <h2 class="lp-section__title">Cookies</h2>
                    <div class="lp-section__body">
                        <p>Utilizamos cookies e tecnologias semelhantes para melhorar a sua experiência de navegação:</p>
                        <ul class="lp-list">
                            <li><strong>Cookies essenciais</strong> — necessários para o funcionamento do site (sessão, segurança CSRF). Não podem ser desativados.</li>
                            <li><strong>Cookies analíticos</strong> — permitem-nos perceber como os visitantes utilizam o site (Google Analytics). São ativados apenas com o seu consentimento.</li>
                            <li><strong>Cookies de marketing</strong> — utilizados para personalizar anúncios em plataformas como Google ou Meta. Apenas com consentimento explícito.</li>
                        </ul>
                        <p>Pode gerir ou revogar o consentimento relativo a cookies a qualquer momento através das definições do seu browser ou do painel de gestão de cookies do site.</p>
                    </div>
                </div>

                {{-- 09 --}}
                <div class="lp-section" id="transferencias">
                    <div class="lp-section__num">09</div>
                    <h2 class="lp-section__title">Transferências internacionais</h2>
                    <div class="lp-section__body">
                        <p>Alguns dos nossos prestadores de serviços (ex.: Google, Meta) podem processar dados fora do Espaço Económico Europeu (EEE). Nestes casos, garantimos que as transferências se realizam ao abrigo de mecanismos legais adequados, nomeadamente:</p>
                        <ul class="lp-list">
                            <li>Decisões de adequação da Comissão Europeia</li>
                            <li>Cláusulas contratuais-tipo aprovadas pela Comissão Europeia</li>
                            <li>Certificação pelo EU-U.S. Data Privacy Framework (quando aplicável)</li>
                        </ul>
                    </div>
                </div>

                {{-- 10 --}}
                <div class="lp-section" id="alteracoes">
                    <div class="lp-section__num">10</div>
                    <h2 class="lp-section__title">Alterações a esta Política</h2>
                    <div class="lp-section__body">
                        <p>Esta Política de Privacidade pode ser atualizada periodicamente para refletir alterações legais, regulatórias ou operacionais. Em caso de alterações significativas, será publicado um aviso visível no site.</p>
                        <p>Recomendamos que consulte esta página regularmente. A versão em vigor é sempre identificada pela data de atualização no topo da página.</p>
                        <div class="lp-update-badge">
                            <i class="bi bi-clock-history"></i>
                            <strong>Última atualização:</strong> Junho de 2026
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
    padding: 3.5rem 0 3rem;
    overflow: hidden;
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

/* ── ToC Sidebar ──────────────────────────────────────────── */
.lp-toc {
    width: 260px;
    flex-shrink: 0;
    position: sticky;
    top: 90px;
    align-self: flex-start;
}
.lp-toc__inner {
    background: #fff; border-radius: 16px;
    padding: 1.5rem; box-shadow: 0 4px 20px rgba(0,0,0,.07);
    border: 1px solid #f0f0f0;
}
.lp-toc__label {
    font-size: .75rem; font-weight: 700; text-transform: uppercase;
    letter-spacing: .06em; color: #999; margin-bottom: 1rem;
}
.lp-toc__link {
    display: flex; align-items: center; gap: .6rem;
    padding: .5rem .75rem; border-radius: 8px; margin-bottom: .25rem;
    font-size: .875rem; color: #444; text-decoration: none;
    transition: all .2s;
}
.lp-toc__link:hover, .lp-toc__link.active {
    background: rgba(110,7,7,.06); color: #6e0707; font-weight: 600;
}
.lp-toc__num {
    font-size: .7rem; font-weight: 700; color: #6e0707;
    background: rgba(110,7,7,.08); border-radius: 4px;
    padding: .1rem .4rem; flex-shrink: 0;
}
.lp-toc__cta {
    margin-top: 1.25rem; padding-top: 1.25rem;
    border-top: 1px solid #f0f0f0;
}
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
    display: flex; align-items: center; justify-content: center;
    font-size: 1.5rem;
}
.lp-intro p { font-size: 1.05rem; line-height: 1.7; margin: 0; opacity: .95; }

.lp-section {
    background: #fff; border-radius: 18px;
    padding: 2.5rem 2.5rem 2rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 4px 20px rgba(0,0,0,.06);
    border-left: 4px solid #6e0707;
    position: relative; scroll-margin-top: 90px;
}
.lp-section__num {
    position: absolute; top: -16px; left: 2rem;
    background: linear-gradient(135deg, #6e0707, #900);
    color: #fff; width: 48px; height: 48px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 1.1rem;
    box-shadow: 0 4px 14px rgba(110,7,7,.3);
}
.lp-section__title {
    font-size: 1.4rem; font-weight: 700; color: #111;
    margin: .5rem 0 1.25rem; padding-top: .25rem;
}
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
.lp-list li {
    position: relative; padding-left: 1.6rem; margin-bottom: .6rem;
    font-size: 1rem;
}
.lp-list li::before {
    content: ''; position: absolute; left: 0; top: .6rem;
    width: 7px; height: 7px; border-radius: 50%;
    background: #6e0707;
}

/* ── Entity box ───────────────────────────────────────────── */
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
    color: #333; text-decoration: none; font-size: .9rem; font-weight: 500;
    transition: all .2s;
}
.lp-contact-chip:hover { background: #f0e8e8; color: #6e0707; }
.lp-contact-chip i { color: #6e0707; }

/* ── Data cards grid ──────────────────────────────────────── */
.lp-grid-2 {
    display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin: 1rem 0;
}
@media (max-width: 600px) { .lp-grid-2 { grid-template-columns: 1fr; } }
.lp-data-card {
    background: #f8f9fa; border-radius: 12px; padding: 1.1rem;
    display: flex; flex-direction: column; gap: .4rem;
}
.lp-data-card i { font-size: 1.3rem; color: #6e0707; }
.lp-data-card strong { font-size: .9rem; color: #111; }
.lp-data-card span { font-size: .85rem; color: #666; line-height: 1.5; }

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
    border-radius: 12px; padding: 1rem 1.25rem; margin-top: 1rem;
    font-size: .95rem;
}
.lp-highlight i { color: #28a745; font-size: 1.2rem; flex-shrink: 0; }

/* ── Rights grid ──────────────────────────────────────────── */
.lp-rights-grid {
    display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin: 1rem 0;
}
@media (max-width: 768px) { .lp-rights-grid { grid-template-columns: 1fr 1fr; } }
@media (max-width: 480px) { .lp-rights-grid { grid-template-columns: 1fr; } }
.lp-right-card {
    background: #f8f9fa; border-radius: 12px; padding: 1.1rem;
    display: flex; flex-direction: column; gap: .4rem; text-align: center;
    transition: box-shadow .2s;
}
.lp-right-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.1); }
.lp-right-card i { font-size: 1.5rem; color: #6e0707; }
.lp-right-card strong { font-size: .9rem; color: #111; }
.lp-right-card span { font-size: .8rem; color: #666; line-height: 1.5; }

/* ── Retention table ──────────────────────────────────────── */
.lp-retention-table {
    border-radius: 12px; overflow: hidden;
    border: 1px solid #e9ecef; margin: 1rem 0;
}
.lp-retention-row {
    display: grid; grid-template-columns: 1fr 1fr;
    padding: .75rem 1.25rem; font-size: .9rem; border-bottom: 1px solid #e9ecef;
}
.lp-retention-row:last-child { border-bottom: none; }
.lp-retention-header {
    background: #6e0707; color: #fff; font-weight: 600; font-size: .85rem;
}
.lp-retention-row:not(.lp-retention-header):nth-child(even) { background: #f8f9fa; }

/* ── Link / update badge ──────────────────────────────────── */
.lp-link { color: #6e0707; font-weight: 600; text-decoration: none; }
.lp-link:hover { text-decoration: underline; }
.lp-update-badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: #f8f9fa; border-radius: 10px; padding: .75rem 1.25rem;
    font-size: .9rem; color: #555; margin-top: .5rem;
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
    const links = document.querySelectorAll('.lp-toc__link');
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
