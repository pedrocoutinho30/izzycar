@extends('frontend.partials.layout')


@include('frontend.partials.seo', [
'seo' => $seo
])
@section('content')

@include('frontend.partials.hero-section', ['title' => 'Notícias', 'subtitle' => 'Fique por dentro das novidades do mundo automotivo e da Izzycar'])

<section class="section-padding mb-2">
    <div class="container">
        <div class="desktop-only">
            @include('frontend.news-desktop')
        </div>

        <div class="mobile-only">
            @include('frontend.news-mobile')
        </div>
    </div>
</section>

@endsection

@push('styles')
<style>
    /* News Cards Desktop */
    .news-card-modern {
        background: white;
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
        border: 1px solid #f1f3f5;
    }

    .news-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 50px rgba(0,0,0,0.12);
    }

    .news-card-link {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .news-card-content {
        display: flex;
        flex-direction: row;
        gap: 0;
    }

    .news-image-wrapper {
        position: relative;
        width: 350px;
        min-width: 350px;
        overflow: hidden;
    }

    .news-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .news-card-modern:hover .news-image {
        transform: scale(1.1);
    }

    .news-image-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(to right, transparent 0%, rgba(0,0,0,0.05) 100%);
    }

    .news-info {
        padding: 2rem 2.5rem;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        flex: 1;
    }

    .news-meta {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .news-date {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6c757d;
        font-size: 0.9rem;
        font-weight: 500;
    }

    .news-date svg {
        color: var(--accent-color);
    }

    .news-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 1rem;
        line-height: 1.3;
        transition: color 0.3s ease;
    }

    .news-card-modern:hover .news-title {
        color: var(--accent-color);
    }

    .news-excerpt {
        font-size: 1rem;
        color: #6c757d;
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }

    .news-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .news-tag {
        display: inline-block;
        padding: 6px 14px;
        background: #f8f9fa;
        color: #495057;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .news-card-modern:hover .news-tag {
        background: var(--accent-color);
        color: white;
    }

    .news-read-more {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: var(--accent-color);
        font-weight: 600;
        font-size: 0.95rem;
        margin-top: auto;
    }

    .news-read-more svg {
        transition: transform 0.3s ease;
    }

    .news-card-modern:hover .news-read-more svg {
        transform: translateX(5px);
    }

    /* News Cards Mobile */
    .news-card-mobile {
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .news-card-mobile:active {
        transform: scale(0.98);
    }

    .news-card-link-mobile {
        text-decoration: none;
        color: inherit;
        display: block;
    }

    .news-image-wrapper-mobile {
        position: relative;
        width: 100%;
        height: 200px;
        overflow: hidden;
    }

    .news-image-mobile {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .news-content-mobile {
        padding: 1.25rem;
    }

    .news-title-mobile {
        font-size: 1.1rem;
        font-weight: 700;
        color: #111;
        margin-bottom: 0.75rem;
        line-height: 1.4;
    }

    .news-date-mobile {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        color: #6c757d;
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 0.75rem;
    }

    .news-date-mobile svg {
        color: var(--accent-color);
    }

    .news-tags-mobile {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .news-tag-mobile {
        display: inline-block;
        padding: 5px 12px;
        background: #f8f9fa;
        color: #495057;
        border-radius: 50px;
        font-size: 0.75rem;
        font-weight: 500;
    }

    /* Responsive */
    @media (max-width: 992px) {
        .news-card-content {
            flex-direction: column;
        }

        .news-image-wrapper {
            width: 100%;
            min-width: 100%;
            height: 250px;
        }

        .news-info {
            padding: 1.5rem;
        }

        .news-title {
            font-size: 1.5rem;
        }
    }

    @media (max-width: 768px) {
        .news-info {
            padding: 1.25rem;
        }

        .news-title {
            font-size: 1.25rem;
        }

        .news-excerpt {
            font-size: 0.95rem;
        }
    }
</style>
@endpush