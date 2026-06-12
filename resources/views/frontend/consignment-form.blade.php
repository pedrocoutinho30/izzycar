@extends('frontend.partials.layout')

@section('title', 'Avaliar o meu carro para Consignação | Izzycar')
@section('meta_description', 'Preencha o formulário de avaliação e receba uma proposta de consignação. Tratamos de toda a venda do seu veículo sem custos iniciais.')

@push('head')
<link rel="canonical" href="https://izzycar.pt/consignacao/avaliacao">
@endpush

@section('content')

{{-- ── HERO ── --}}
<section class="ef-hero">
    <div class="ef-hero__overlay"></div>
    <div class="ef-hero__inner">
        <div class="ef-hero__badge">
            <i class="bi bi-car-front-fill"></i>
            Consignação de Venda
        </div>
        <h1 class="ef-hero__title">Peça uma avaliação<br>do seu veículo</h1>
        <p class="ef-hero__sub">Preencha os dados do seu carro e entraremos em contacto em menos de 24 horas com uma proposta sem compromisso.</p>
        <div class="ef-hero__pills">
            <span class="ef-pill"><i class="bi bi-check-lg"></i> Resposta em 24h</span>
            <span class="ef-pill"><i class="bi bi-check-lg"></i> Sem compromisso</span>
            <span class="ef-pill"><i class="bi bi-check-lg"></i> Sem custos iniciais</span>
        </div>
    </div>
</section>

{{-- ── FORM ── --}}
<div class="ef-page">
    <div class="ef-container">

        <div id="efSuccess" class="ef-success" style="display:none">
            <div class="ef-success__icon"><i class="bi bi-check-lg"></i></div>
            <div>
                <div class="ef-success__title">Pedido enviado com sucesso!</div>
                <p class="ef-success__text">Recebemos a sua avaliação. Entraremos em contacto em breve com uma proposta personalizada.</p>
            </div>
        </div>

        <div id="efError" class="ef-error" style="display:none">
            <i class="bi bi-exclamation-circle"></i>
            <span>Ocorreu um erro. Verifique os campos e tente novamente.</span>
        </div>

        <form id="efForm" enctype="multipart/form-data" novalidate>
            @csrf

            {{-- ── STEP 1: Veículo ── --}}
            <div class="ef-card ef-reveal">
                <div class="ef-card__head">
                    <div class="ef-step">1</div>
                    <div>
                        <h2 class="ef-card__title">Dados do Veículo</h2>
                        <p class="ef-card__sub">Informações técnicas do seu automóvel</p>
                    </div>
                </div>

                <div class="ef-grid">
                    <div class="ef-field">
                        <label class="ef-label" for="brand">Marca <span class="ef-req">*</span></label>
                        <input type="text" name="brand" id="brand" class="ef-input" placeholder="ex: BMW, Mercedes, Volkswagen" required>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="model">Modelo <span class="ef-req">*</span></label>
                        <input type="text" name="model" id="model" class="ef-input" placeholder="ex: Série 3, Classe C, Golf" required>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="version">Versão / Sub-modelo</label>
                        <input type="text" name="version" id="version" class="ef-input" placeholder="ex: 320d xDrive Sport Line">
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="year">Ano <span class="ef-req">*</span></label>
                        <select name="year" id="year" class="ef-select" required>
                            <option value="">Escolha o ano</option>
                            @php for($y = date('Y'); $y >= 1990; $y--): @endphp
                            <option value="{{ $y }}">{{ $y }}</option>
                            @php endfor; @endphp
                        </select>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="kilometers">Quilómetros <span class="ef-req">*</span></label>
                        <div class="ef-input-suffix">
                            <input type="number" name="kilometers" id="kilometers" class="ef-input" placeholder="ex: 85000" min="0" required>
                            <span>km</span>
                        </div>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="plate">Matrícula <span class="ef-req">*</span></label>
                        <input type="text" name="plate" id="plate" class="ef-input" placeholder="ex: 00-AA-00" style="text-transform:uppercase" required>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="fuel">Combustível <span class="ef-req">*</span></label>
                        <select name="fuel" id="fuel" class="ef-select" required>
                            <option value="">Escolha</option>
                            <option value="Gasolina">Gasolina</option>
                            <option value="Diesel">Diesel</option>
                            <option value="Híbrido Plug-in Gasolina">Híbrido Plug-in (Gasolina)</option>
                            <option value="Híbrido Plug-in Diesel">Híbrido Plug-in (Diesel)</option>
                            <option value="Híbrido Suave Gasolina">Híbrido Suave (Gasolina)</option>
                            <option value="Híbrido Suave Diesel">Híbrido Suave (Diesel)</option>
                            <option value="Elétrico">Elétrico</option>
                            <option value="GPL">GPL</option>
                        </select>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="gearbox">Transmissão <span class="ef-req">*</span></label>
                        <select name="gearbox" id="gearbox" class="ef-select" required>
                            <option value="">Escolha</option>
                            <option value="Manual">Manual</option>
                            <option value="Automática">Automática</option>
                            <option value="Semi-automática">Semi-automática</option>
                        </select>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="power">Potência</label>
                        <div class="ef-input-suffix">
                            <input type="number" name="power" id="power" class="ef-input" placeholder="ex: 150" min="0">
                            <span>cv</span>
                        </div>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="displacement">Cilindrada</label>
                        <div class="ef-input-suffix">
                            <input type="number" name="displacement" id="displacement" class="ef-input" placeholder="ex: 1998" min="0">
                            <span>cc</span>
                        </div>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="color">Cor</label>
                        <input type="text" name="color" id="color" class="ef-input" placeholder="ex: Preto Safira">
                    </div>
                </div>
            </div>

            {{-- ── STEP 2: Estado ── --}}
            <div class="ef-card ef-reveal">
                <div class="ef-card__head">
                    <div class="ef-step">2</div>
                    <div>
                        <h2 class="ef-card__title">Estado do Veículo</h2>
                        <p class="ef-card__sub">Ajuda-nos a perceber a condição geral do seu carro</p>
                    </div>
                </div>

                <div class="ef-grid ef-grid--1">
                    <div class="ef-field">
                        <label class="ef-label">Estado geral <span class="ef-req">*</span></label>
                        <div class="ef-condition-group">
                            <label class="ef-cond" data-color="green">
                                <input type="radio" name="condition" value="Excelente" required>
                                <span class="ef-cond__box">
                                    <span class="ef-cond__dot"></span>
                                    <span class="ef-cond__text">
                                        <span class="ef-cond__label">Excelente</span>
                                        <span class="ef-cond__sub">Como novo, sem defeitos</span>
                                    </span>
                                </span>
                            </label>
                            <label class="ef-cond" data-color="blue">
                                <input type="radio" name="condition" value="Bom">
                                <span class="ef-cond__box">
                                    <span class="ef-cond__dot"></span>
                                    <span class="ef-cond__text">
                                        <span class="ef-cond__label">Bom</span>
                                        <span class="ef-cond__sub">Pequenos sinais de uso</span>
                                    </span>
                                </span>
                            </label>
                            <label class="ef-cond" data-color="orange">
                                <input type="radio" name="condition" value="Razoável">
                                <span class="ef-cond__box">
                                    <span class="ef-cond__dot"></span>
                                    <span class="ef-cond__text">
                                        <span class="ef-cond__label">Razoável</span>
                                        <span class="ef-cond__sub">Marcas visíveis, funciona bem</span>
                                    </span>
                                </span>
                            </label>
                            <label class="ef-cond" data-color="red">
                                <input type="radio" name="condition" value="Para reparação">
                                <span class="ef-cond__box">
                                    <span class="ef-cond__dot"></span>
                                    <span class="ef-cond__text">
                                        <span class="ef-cond__label">Para reparação</span>
                                        <span class="ef-cond__sub">Necessita de intervenção</span>
                                    </span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <div class="ef-field">
                        <label class="ef-label" for="description">
                            Descrição do estado / extras / histórico
                            <span class="ef-hint">Descreva o estado geral, manutenções realizadas, extras e qualquer informação relevante para a avaliação</span>
                        </label>
                        <textarea name="description" id="description" class="ef-textarea" rows="5"
                            placeholder="ex: Carro em excelente estado, com revisões em dia na marca. Tem pack M, teto de abrir panorâmico, bancos em pele. Sem acidentes. Livrete de serviços completo."></textarea>
                    </div>

                    <div class="ef-checks-row">
                        <label class="ef-check">
                            <input type="checkbox" name="has_service_book" value="1">
                            <span class="ef-check__box"></span>
                            <span>Livrete de revisões completo</span>
                        </label>
                        <label class="ef-check">
                            <input type="checkbox" name="has_2nd_key" value="1">
                            <span class="ef-check__box"></span>
                            <span>Segunda chave</span>
                        </label>
                        <label class="ef-check">
                            <input type="checkbox" name="has_iuc" value="1">
                            <span class="ef-check__box"></span>
                            <span>IUC pago / em dia</span>
                        </label>
                        <label class="ef-check">
                            <input type="checkbox" name="has_inspection" value="1">
                            <span class="ef-check__box"></span>
                            <span>Inspeção periódica válida</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- ── STEP 3: Fotos ── --}}
            <div class="ef-card ef-reveal">
                <div class="ef-card__head">
                    <div class="ef-step">3</div>
                    <div>
                        <h2 class="ef-card__title">Fotografias</h2>
                        <p class="ef-card__sub">Adicione fotos do seu veículo para uma avaliação mais precisa</p>
                    </div>
                </div>

                <div class="ef-upload-zone" id="efDropZone">
                    {{-- Input cobre toda a zona — clique funciona em qualquer dispositivo --}}
                    <input type="file" name="photos[]" id="efPhotos" accept="image/*" multiple class="ef-upload-input">
                    <div class="ef-upload-content" aria-hidden="true">
                        <div class="ef-upload-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                        <p class="ef-upload-title">Arraste as fotos para aqui</p>
                        <p class="ef-upload-sub">ou <span class="ef-upload-link">clique para selecionar</span></p>
                        <p class="ef-upload-hint">Máx. 10 fotos · JPG, PNG, WEBP · 5 MB por foto</p>
                    </div>
                </div>

                <div id="efPreviews" class="ef-previews" style="display:none"></div>
                <p id="efPhotoCount" class="ef-photo-count" style="display:none"></p>
            </div>

            {{-- ── STEP 4: Contacto ── --}}
            <div class="ef-card ef-reveal">
                <div class="ef-card__head">
                    <div class="ef-step">4</div>
                    <div>
                        <h2 class="ef-card__title">Dados de Contacto</h2>
                        <p class="ef-card__sub">Como podemos entrar em contacto consigo</p>
                    </div>
                </div>

                <div class="ef-grid">
                    <div class="ef-field">
                        <label class="ef-label" for="name">Nome completo <span class="ef-req">*</span></label>
                        <input type="text" name="name" id="name" class="ef-input" placeholder="João Silva" required>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="phone">Telemóvel <span class="ef-req">*</span></label>
                        <input type="tel" name="phone" id="phone" class="ef-input" placeholder="+351 912 345 678" required>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="email">E-mail <span class="ef-req">*</span></label>
                        <input type="email" name="email" id="email" class="ef-input" placeholder="joao@exemplo.pt" required>
                    </div>
                    <div class="ef-field">
                        <label class="ef-label" for="location">Localização</label>
                        <input type="text" name="location" id="location" class="ef-input" placeholder="ex: Lisboa, Porto, Coimbra…">
                    </div>
                    <div class="ef-field ef-field--full">
                        <label class="ef-label" for="price_expectation">Valor esperado pela venda</label>
                        <div class="ef-input-suffix">
                            <input type="number" name="price_expectation" id="price_expectation" class="ef-input" placeholder="ex: 18500" min="0">
                            <span>€</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Submit ── --}}
            <div class="ef-card ef-reveal">
                <div class="ef-consents">
                    <label class="ef-check">
                        <input type="checkbox" name="privacy_consent" value="1" required>
                        <span class="ef-check__box"></span>
                        <span>Li e aceito o tratamento dos meus dados pessoais para efeitos de resposta a este pedido. <span class="ef-req">*</span></span>
                    </label>
                    <label class="ef-check">
                        <input type="checkbox" name="newsletter_consent" value="1">
                        <span class="ef-check__box"></span>
                        <span>Aceito receber comunicações e novidades por e-mail</span>
                    </label>
                </div>
                <div class="ef-submit-wrap">
                    <div class="ef-privacy">
                        <i class="bi bi-shield-lock"></i>
                        Os seus dados são tratados com total confidencialidade.
                    </div>
                    <button type="submit" class="ef-submit" id="efSubmit">
                        <i class="bi bi-send-fill"></i> Enviar Pedido de Avaliação
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const form     = document.getElementById('efForm');
    const btn      = document.getElementById('efSubmit');
    const success  = document.getElementById('efSuccess');
    const errBox   = document.getElementById('efError');
    const dropZone = document.getElementById('efDropZone');
    const photosInput = document.getElementById('efPhotos');
    const previews = document.getElementById('efPreviews');
    const countEl  = document.getElementById('efPhotoCount');

    let selectedFiles = [];

    /* ── Matrícula: uppercase ── */
    document.getElementById('plate').addEventListener('input', function () {
        this.value = this.value.toUpperCase();
    });

    /* ── Photo drag & drop ── */
    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('ef-drop-active');
    });
    dropZone.addEventListener('dragleave', function (e) {
        /* só remove se o rato saiu mesmo da zona (não para um filho) */
        if (!dropZone.contains(e.relatedTarget)) {
            this.classList.remove('ef-drop-active');
        }
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('ef-drop-active');
        addFiles(e.dataTransfer.files);
    });
    photosInput.addEventListener('change', function () {
        addFiles(this.files);
        /* reset para permitir selecionar os mesmos ficheiros novamente */
        this.value = '';
    });

    function addFiles(newFiles) {
        Array.from(newFiles).forEach(function (f) {
            if (!f.type.startsWith('image/')) return;
            if (f.size > 5 * 1024 * 1024) {
                alert(f.name + ': ficheiro demasiado grande (máx. 5 MB)');
                return;
            }
            if (selectedFiles.length >= 10) {
                alert('Máximo de 10 fotos permitidas.');
                return;
            }
            selectedFiles.push(f);
        });
        renderPreviews();
    }

    function renderPreviews() {
        previews.innerHTML = '';
        if (selectedFiles.length === 0) {
            previews.style.display = 'none';
            countEl.style.display  = 'none';
            return;
        }
        previews.style.display = 'grid';
        countEl.style.display  = 'block';
        countEl.textContent    = selectedFiles.length + ' foto' + (selectedFiles.length > 1 ? 's' : '') + ' selecionada' + (selectedFiles.length > 1 ? 's' : '');

        selectedFiles.forEach(function (f, i) {
            const reader = new FileReader();
            reader.onload = function (e) {
                const wrap = document.createElement('div');
                wrap.className = 'ef-preview-item';
                wrap.innerHTML =
                    '<img src="' + e.target.result + '" alt="">' +
                    '<button type="button" class="ef-preview-remove" data-i="' + i + '"><i class="bi bi-x"></i></button>';
                wrap.querySelector('.ef-preview-remove').addEventListener('click', function () {
                    selectedFiles.splice(parseInt(this.dataset.i), 1);
                    renderPreviews();
                });
                previews.appendChild(wrap);
            };
            reader.readAsDataURL(f);
        });
    }

    /* ── Submit ── */
    form.addEventListener('submit', function (e) {
        e.preventDefault();

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        const fd = new FormData(form);

        /* attach selected files */
        fd.delete('photos[]');
        selectedFiles.forEach(function (f) {
            fd.append('photos[]', f);
        });

        btn.disabled = true;
        btn.innerHTML = '<span class="ef-spinner"></span> A enviar…';
        errBox.style.display = 'none';

        fetch('{{ route('consignment.evaluation.submit') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value,
                'Accept': 'application/json'
            },
            body: fd
        })
        .then(function (r) {
            var ct = r.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                throw new Error('Erro no servidor. Por favor tente novamente ou contacte-nos directamente.');
            }
            return r.json().then(function (data) {
                if (!r.ok) {
                    if (data.errors) {
                        var msgs = [];
                        Object.values(data.errors).forEach(function (arr) {
                            if (Array.isArray(arr)) msgs = msgs.concat(arr);
                            else msgs.push(arr);
                        });
                        throw new Error(msgs.join(' '));
                    }
                    throw new Error(data.message || 'Erro no servidor (' + r.status + ').');
                }
                return data;
            });
        })
        .then(function (data) {
            if (data.status === 'success') {
                form.style.display = 'none';
                success.style.display = 'flex';
                success.scrollIntoView({ behavior: 'smooth', block: 'center' });
            } else {
                throw new Error(data.message || 'Erro desconhecido.');
            }
        })
        .catch(function (err) {
            errBox.querySelector('span').textContent = err.message || 'Ocorreu um erro. Verifique os campos e tente novamente.';
            errBox.style.display = 'flex';
            errBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        })
        .finally(function () {
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-send-fill"></i> Enviar Pedido de Avaliação';
        });
    });

    /* ── Reveal on scroll ── */
    const obs = new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('is-visible');
                obs.unobserve(e.target);
            }
        });
    }, { threshold: 0.08 });
    document.querySelectorAll('.ef-reveal').forEach(function (el) { obs.observe(el); });

});
</script>

<style>
:root {
    --ef-brand:  #6e0707;
    --ef-dark:   #111;
    --ef-gray:   #6b7280;
    --ef-light:  #f9fafb;
    --ef-border: #e5e7eb;
}

/* ── Hero ── */
.ef-hero {
    position: relative;
    background: var(--ef-dark);
    padding: 5rem 1.5rem 4rem;
    overflow: hidden;
}
.ef-hero__overlay {
    position: absolute; inset: 0; pointer-events: none;
    background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%236e0707' fill-opacity='0.06'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/svg%3E");
}
.ef-hero__inner {
    position: relative; max-width: 720px; margin: 0 auto; text-align: center;
}
.ef-hero__badge {
    display: inline-flex; align-items: center; gap: .5rem;
    background: rgba(110,7,7,.2); border: 1px solid rgba(110,7,7,.35);
    color: rgba(255,255,255,.9); font-size: .8rem; font-weight: 600;
    padding: .4rem 1rem; border-radius: 100px; margin-bottom: 1.5rem;
}
.ef-hero__title {
    font-size: clamp(1.8rem, 4.5vw, 3rem); font-weight: 900;
    color: #fff; line-height: 1.15; margin-bottom: 1rem;
}
.ef-hero__sub {
    font-size: clamp(.9rem, 2vw, 1.05rem); color: rgba(255,255,255,.65);
    line-height: 1.7; max-width: 560px; margin: 0 auto 1.75rem;
}
.ef-hero__pills { display: flex; gap: .6rem; justify-content: center; flex-wrap: wrap; }
.ef-pill {
    display: inline-flex; align-items: center; gap: .4rem;
    background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
    color: rgba(255,255,255,.7); font-size: .75rem; font-weight: 500;
    padding: .35rem .9rem; border-radius: 100px;
}

/* ── Page ── */
.ef-page { background: var(--ef-light); }
.ef-container { max-width: 920px; margin: 0 auto; padding: 3rem 1.5rem 4rem; }

/* ── Alerts ── */
.ef-success {
    display: flex; align-items: flex-start; gap: 1rem;
    background: #f0fdf4; border: 1px solid #86efac;
    border-radius: 16px; padding: 1.75rem; margin-bottom: 2rem;
}
.ef-success__icon {
    width: 52px; height: 52px; border-radius: 14px; flex-shrink: 0;
    background: #dcfce7; color: #16a34a; font-size: 1.5rem;
    display: flex; align-items: center; justify-content: center;
}
.ef-success__title { font-weight: 700; color: #15803d; font-size: 1.05rem; margin-bottom: .3rem; }
.ef-success__text  { font-size: .88rem; color: #166534; margin: 0; }
.ef-error {
    display: flex; align-items: center; gap: .65rem;
    background: #fee2e2; border: 1px solid #fecaca;
    border-radius: 12px; padding: 1rem 1.25rem; margin-bottom: 1.5rem;
    font-size: .88rem; color: #991b1b;
}

/* ── Card ── */
.ef-card {
    background: #fff; border: 1px solid var(--ef-border);
    border-radius: 20px; padding: 2.5rem;
    box-shadow: 0 2px 12px rgba(0,0,0,.05); margin-bottom: 1.5rem;
}
.ef-card__head {
    display: flex; align-items: flex-start; gap: 1rem;
    margin-bottom: 2rem; padding-bottom: 1.25rem;
    border-bottom: 1px solid var(--ef-border);
}
.ef-step {
    width: 40px; height: 40px; border-radius: 12px; flex-shrink: 0;
    background: linear-gradient(135deg, var(--ef-brand), #9b1111);
    color: #fff; font-weight: 800; font-size: .9rem;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 12px rgba(110,7,7,.3);
}
.ef-card__title { font-size: 1.2rem; font-weight: 800; color: #111; margin: 0 0 .2rem; }
.ef-card__sub   { font-size: .82rem; color: var(--ef-gray); margin: 0; }

/* ── Grid ── */
.ef-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1.25rem; }
.ef-grid--1 { grid-template-columns: 1fr; }
.ef-field   { display: flex; flex-direction: column; }
.ef-field--full { grid-column: 1 / -1; }

/* ── Labels & inputs ── */
.ef-label {
    font-size: .75rem; font-weight: 700; color: #374151;
    text-transform: uppercase; letter-spacing: .05em;
    margin-bottom: .45rem; display: block;
}
.ef-hint {
    display: block; font-size: .72rem; color: var(--ef-gray);
    font-weight: 400; text-transform: none; letter-spacing: 0; margin-top: .2rem;
}
.ef-req { color: var(--ef-brand); }
.ef-input, .ef-select, .ef-textarea {
    padding: .75rem 1rem; border-radius: 10px;
    border: 1.5px solid var(--ef-border); background: #fff;
    font-size: .9rem; color: #111; outline: none;
    transition: border-color .2s, box-shadow .2s; width: 100%;
}
.ef-input:focus, .ef-select:focus, .ef-textarea:focus {
    border-color: var(--ef-brand);
    box-shadow: 0 0 0 3px rgba(110,7,7,.1);
}
.ef-textarea { resize: vertical; min-height: 110px; }
.ef-input-suffix {
    display: flex; align-items: center;
    border: 1.5px solid var(--ef-border); border-radius: 10px;
    overflow: hidden; background: #fff;
    transition: border-color .2s, box-shadow .2s;
}
.ef-input-suffix:focus-within {
    border-color: var(--ef-brand);
    box-shadow: 0 0 0 3px rgba(110,7,7,.1);
}
.ef-input-suffix .ef-input {
    border: none; border-radius: 0; box-shadow: none; flex: 1;
}
.ef-input-suffix .ef-input:focus { box-shadow: none; }
.ef-input-suffix span {
    padding: 0 .85rem; font-size: .8rem; font-weight: 600;
    color: #9ca3af; background: #f9fafb;
    border-left: 1px solid var(--ef-border); white-space: nowrap;
    align-self: stretch; display: flex; align-items: center;
}

/* ── Condition radio ── */
.ef-condition-group { display: grid; grid-template-columns: repeat(4, 1fr); gap: .75rem; }
.ef-cond { cursor: pointer; }
.ef-cond input { display: none; }
.ef-cond__box {
    display: flex; align-items: center; gap: .85rem;
    border: 1.5px solid var(--ef-border); border-radius: 12px;
    padding: 1rem 1.1rem; transition: .2s; background: #fff; height: 100%;
}
.ef-cond__dot {
    width: 18px; height: 18px; border-radius: 50%;
    border: 2px solid var(--ef-border); flex-shrink: 0;
    transition: .2s; position: relative;
}
.ef-cond__dot::after {
    content: ''; position: absolute; inset: 3px;
    border-radius: 50%; background: var(--ef-brand);
    transform: scale(0); transition: transform .15s;
}
.ef-cond__text { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.ef-cond__label { font-size: .9rem; font-weight: 700; color: #111; line-height: 1.2; }
.ef-cond__sub   { font-size: .72rem; color: var(--ef-gray); line-height: 1.3; }

.ef-cond input:checked ~ .ef-cond__box { border-color: var(--ef-brand); background: rgba(110,7,7,.04); }
.ef-cond input:checked ~ .ef-cond__box .ef-cond__dot { border-color: var(--ef-brand); }
.ef-cond input:checked ~ .ef-cond__box .ef-cond__dot::after { transform: scale(1); }

.ef-cond[data-color="green"] input:checked ~ .ef-cond__box { border-color: #16a34a; background: #f0fdf4; }
.ef-cond[data-color="green"] input:checked ~ .ef-cond__box .ef-cond__dot { border-color: #16a34a; }
.ef-cond[data-color="green"] input:checked ~ .ef-cond__box .ef-cond__dot::after { background: #16a34a; }
.ef-cond[data-color="blue"]   input:checked ~ .ef-cond__box { border-color: #2563eb; background: #eff6ff; }
.ef-cond[data-color="blue"]   input:checked ~ .ef-cond__box .ef-cond__dot { border-color: #2563eb; }
.ef-cond[data-color="blue"]   input:checked ~ .ef-cond__box .ef-cond__dot::after { background: #2563eb; }
.ef-cond[data-color="orange"] input:checked ~ .ef-cond__box { border-color: #d97706; background: #fffbeb; }
.ef-cond[data-color="orange"] input:checked ~ .ef-cond__box .ef-cond__dot { border-color: #d97706; }
.ef-cond[data-color="orange"] input:checked ~ .ef-cond__box .ef-cond__dot::after { background: #d97706; }
.ef-cond[data-color="red"]    input:checked ~ .ef-cond__box { border-color: #dc2626; background: #fef2f2; }
.ef-cond[data-color="red"]    input:checked ~ .ef-cond__box .ef-cond__dot { border-color: #dc2626; }
.ef-cond[data-color="red"]    input:checked ~ .ef-cond__box .ef-cond__dot::after { background: #dc2626; }

/* ── Checks row ── */
.ef-checks-row { display: flex; flex-wrap: wrap; gap: .75rem 2rem; }
.ef-check {
    display: flex; align-items: flex-start; gap: .65rem;
    cursor: pointer; font-size: .88rem; color: #374151; line-height: 1.5;
}
.ef-check input { display: none; }
.ef-check__box {
    width: 18px; height: 18px; border-radius: 5px; flex-shrink: 0; margin-top: .1rem;
    border: 2px solid var(--ef-border); background: #fff;
    transition: .2s; display: flex; align-items: center; justify-content: center;
}
.ef-check__box::after {
    content: ''; width: 10px; height: 10px;
    background: url("data:image/svg+xml,%3Csvg viewBox='0 0 12 9' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M1 4l3.5 3.5L11 1' stroke='white' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E") no-repeat center / contain;
    opacity: 0; transition: opacity .15s;
}
.ef-check input:checked ~ .ef-check__box { background: var(--ef-brand); border-color: var(--ef-brand); }
.ef-check input:checked ~ .ef-check__box::after { opacity: 1; }

/* ── Upload ── */
.ef-upload-zone {
    position: relative;
    border: 2px dashed var(--ef-border); border-radius: 16px;
    text-align: center; transition: .25s; background: #fafafa;
    overflow: hidden;
}
.ef-upload-zone:hover, .ef-drop-active {
    border-color: var(--ef-brand);
    background: rgba(110,7,7,.03);
}
.ef-upload-input {
    position: absolute; inset: 0; opacity: 0; cursor: pointer;
    width: 100%; height: 100%; z-index: 2;
}
.ef-upload-content { padding: 3rem 2rem; pointer-events: none; }
.ef-upload-icon  { font-size: 2.5rem; color: #d1d5db; margin-bottom: .75rem; }
.ef-upload-title { font-size: 1rem; font-weight: 700; color: #374151; margin-bottom: .35rem; }
.ef-upload-sub   { font-size: .9rem; color: var(--ef-gray); margin-bottom: .5rem; }
.ef-upload-link  { color: var(--ef-brand); font-weight: 700; text-decoration: underline; }
.ef-upload-hint  { font-size: .78rem; color: #9ca3af; margin: 0; }
.ef-previews {
    margin-top: 1.25rem; display: grid;
    grid-template-columns: repeat(auto-fill, minmax(110px,1fr)); gap: .75rem;
}
.ef-preview-item {
    position: relative; border-radius: 10px; overflow: hidden;
    aspect-ratio: 1; background: #f3f4f6;
}
.ef-preview-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
.ef-preview-remove {
    position: absolute; top: 4px; right: 4px;
    width: 22px; height: 22px; border-radius: 50%;
    background: rgba(0,0,0,.55); color: #fff; border: none;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: .8rem; line-height: 1;
    transition: background .2s;
}
.ef-preview-remove:hover { background: #dc2626; }
.ef-photo-count { margin-top: .65rem; font-size: .82rem; color: var(--ef-gray); font-weight: 600; }

/* ── Consents & submit ── */
.ef-consents { display: flex; flex-direction: column; gap: .75rem; margin-bottom: 2rem; }
.ef-submit-wrap { display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; }
.ef-privacy { display: flex; align-items: center; gap: .4rem; color: var(--ef-gray); font-size: .75rem; }
.ef-submit {
    display: inline-flex; align-items: center; gap: .65rem;
    padding: 1rem 2.25rem; border-radius: 12px;
    background: linear-gradient(135deg, var(--ef-brand), #9b1111);
    color: #fff; font-size: 1rem; font-weight: 700;
    border: none; cursor: pointer;
    box-shadow: 0 6px 20px rgba(110,7,7,.35);
    transition: transform .15s, box-shadow .2s;
}
.ef-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(110,7,7,.4); }
.ef-submit:disabled { opacity: .7; cursor: not-allowed; }
@keyframes ef-spin { to { transform: rotate(360deg); } }
.ef-spinner {
    width: 16px; height: 16px; border-radius: 50%;
    border: 2px solid rgba(255,255,255,.3);
    border-top-color: #fff;
    animation: ef-spin .7s linear infinite;
    display: inline-block;
}

/* ── Reveal ── */
.ef-reveal { opacity: 0; transform: translateY(20px); transition: opacity .5s ease, transform .5s ease; }
.ef-reveal.is-visible { opacity: 1; transform: none; }

@media (max-width: 768px) {
    .ef-condition-group { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 640px) {
    .ef-card { padding: 1.75rem 1.25rem; }
    .ef-submit-wrap { flex-direction: column; align-items: stretch; }
    .ef-submit { justify-content: center; }
    .ef-condition-group { grid-template-columns: 1fr 1fr; }
}
</style>
@endpush
