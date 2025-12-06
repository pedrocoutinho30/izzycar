@push('styles')
<style>
    .process-steps-modern {
        display: flex;
        gap: 2rem;
        margin-top: 2rem;
    }

    .process-nav-modern {
        flex: 0 0 300px;
    }

    .process-nav-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
        background: white;
        border: 2px solid #e9ecef;
        border-radius: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .process-nav-item::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: linear-gradient(180deg, var(--accent-color) 0%, #990000 100%);
        transform: scaleY(0);
        transition: transform 0.3s ease;
    }

    .process-nav-item:hover {
        border-color: var(--accent-color);
        /* box-shadow: 0 4px 15px rgba(110, 7, 7, 0.15); */
    }

    .process-nav-item.active {
        background: linear-gradient(135deg, rgba(110, 7, 7, 0.05) 0%, rgba(153, 0, 0, 0.05) 100%);
        border-color: var(--accent-color);
        /* box-shadow: 0 8px 25px rgba(110, 7, 7, 0.2); */
    }

    .process-nav-item.active::before {
        transform: scaleY(1);
    }

    .process-step-number {
        flex-shrink: 0;
        width: 45px;
        height: 45px;
        background: #f8f9fa;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 1.1rem;
        color: #6c757d;
        transition: all 0.3s ease;
    }

    .process-nav-item.active .process-step-number {
        background: linear-gradient(135deg, var(--accent-color) 0%, #990000 100%);
        color: white;
        transform: scale(1.1);
    }

    .process-step-title {
        flex: 1;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
        margin: 0;
        transition: color 0.3s ease;
    }

    .process-nav-item.active .process-step-title {
        color: var(--accent-color);
    }

    .process-content-modern {
        flex: 1;
        background: white;
        border-radius: 24px;
        padding: 3rem;
        border: 2px solid #990000;
        /* box-shadow: 0 10px 40px rgba(0,0,0,0.08); */
        min-height: 400px;
    }

    .process-content-inner {
        display: none;
    }

    .process-content-inner.active {
        display: block;
        animation: fadeInContent 0.4s ease;
    }

    @keyframes fadeInContent {
        from {
            opacity: 0;
            transform: translateY(15px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .process-content-text {
        font-size: 1.05rem;
        line-height: 1.8;
        color: #495057;
        margin-bottom: 2rem;
    }

    .process-content-image {
        text-align: center;
        margin-top: 2rem;
    }

    .process-content-image img {
        max-width: 400px;
        width: 100%;
        height: 300px;
        object-fit: cover;
        border-radius: 20px;
        box-shadow: 0 15px 50px rgba(0,0,0,0.12);
        transition: transform 0.3s ease;
    }

    .process-content-image img:hover {
        transform: scale(1.02);
    }

    @media (max-width: 992px) {
        .process-steps-modern {
            flex-direction: column;
        }

        .process-nav-modern {
            flex: 1;
        }

        .process-content-modern {
            padding: 2rem;
        }
    }

    @media (max-width: 768px) {
        .process-nav-item {
            padding: 1rem;
        }

        .process-step-number {
            width: 40px;
            height: 40px;
            font-size: 1rem;
        }

        .process-step-title {
            font-size: 0.95rem;
        }

        .process-content-modern {
            padding: 1.5rem;
        }

        .process-content-text {
            font-size: 1rem;
        }

        .process-content-image img {
            height: 200px;
        }
    }
</style>
@endpush

<div class="row mt-4">
    <div class="col-12">
        <h3 class="text-center section-title-import mb-4">{{$title}}</h3>
        
        <div class="process-steps-modern">
            <div class="process-nav-modern">
                @foreach ($data as $index => $tab)
                @php
                $slug = \Illuminate\Support\Str::slug($tab['title']);
                @endphp
                <div class="process-nav-item {{ $index === 0 ? 'active' : '' }}" 
                     data-target="pane-import-{{ $slug }}"
                     onclick="switchProcessTab(this, 'pane-import-{{ $slug }}')">
                    <div class="process-step-number">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</div>
                    <div class="process-step-title">{{ $tab['title'] }}</div>
                </div>
                @endforeach
            </div>

            <div class="process-content-modern">
                @foreach ($data as $index => $tab)
                @php
                $slug = \Illuminate\Support\Str::slug($tab['title']);
                @endphp
                <div class="process-content-inner {{ $index === 0 ? 'active' : '' }}" id="pane-import-{{ $slug }}">
                    <div class="process-content-text">
                        {!! $tab['content'] !!}
                    </div>
                    @if(!empty($tab['image']))
                    <div class="process-content-image">
                        <img src="{{ asset('storage/'. $tab['image'] ) }}" 
                             onerror="this.src='{{ asset('img/logo-simples.png') }}';"
                             alt="{{ $tab['title'] }}">
                    </div>
                    @else
                    <div class="process-content-image">
                        <img src="{{ asset('img/logo-simples.png') }}" 
                             alt="{{ $tab['title'] }}">
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<script>
function switchProcessTab(element, targetId) {
    // Remove active class from all nav items
    document.querySelectorAll('.process-nav-item').forEach(item => {
        item.classList.remove('active');
    });
    
    // Add active class to clicked item
    element.classList.add('active');
    
    // Hide all content panels
    document.querySelectorAll('.process-content-inner').forEach(panel => {
        panel.classList.remove('active');
    });
    
    // Show target content panel
    document.getElementById(targetId).classList.add('active');
}
</script>