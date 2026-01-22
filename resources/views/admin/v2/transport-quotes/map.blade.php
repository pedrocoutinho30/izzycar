@extends('layouts.admin-v2')

@section('title', 'Mapa de Transportes')

@section('content')

<!-- PAGE HEADER -->
@include('components.admin.page-header', [
'breadcrumbs' => [
['icon' => 'bi bi-house-door', 'label' => 'Dashboard', 'href' => route('admin.v2.dashboard')],
['icon' => 'bi bi-truck', 'label' => 'Transportes', 'href' => route('admin.transport-quotes.index')],
['icon' => 'bi bi-map', 'label' => 'Mapa', 'href' => ''],
],
'title' => 'Mapa de Transportes',
'subtitle' => 'Visualização geográfica dos orçamentos de transporte',
'actionHref' => route('admin.transport-quotes.index'),
'actionLabel' => 'Ver Lista'
])

<!-- FILTROS -->
<div class="modern-card mb-4">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-funnel"></i>
            Filtros
        </h5>
    </div>
    <div class="modern-card-body">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Marca</label>
                <select id="filter-brand" class="form-select">
                    <option value="">Todas as marcas</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand }}">{{ $brand }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Modelo</label>
                <select id="filter-model" class="form-select">
                    <option value="">Todos os modelos</option>
                    @foreach($models as $model)
                        <option value="{{ $model }}">{{ $model }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Transportadora</label>
                <select id="filter-supplier" class="form-select">
                    <option value="">Todas as transportadoras</option>
                    @foreach($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-primary w-100" onclick="applyFilters()">
                    <i class="bi bi-funnel"></i> Aplicar
                </button>
            </div>
        </div>
        <div class="row g-3 mt-2">
            <div class="col-md-12">
                <label class="form-label fw-bold">Origem Customizada (Opcional)</label>
            </div>
            <div class="col-md-3">
                <label class="form-label">Latitude</label>
                <input type="number" step="0.0000001" id="filter-dest-lat" class="form-control" placeholder="Ex: 40.8397">
            </div>
            <div class="col-md-3">
                <label class="form-label">Longitude</label>
                <input type="number" step="0.0000001" id="filter-dest-lng" class="form-control" placeholder="Ex: -8.4775">
            </div>
            <div class="col-md-4">
                <label class="form-label d-block">&nbsp;</label>
                <button type="button" class="btn btn-success" onclick="addCustomDestination()">
                    <i class="bi bi-geo-alt"></i> Mostrar Destino
                </button>
                <button type="button" class="btn btn-outline-secondary" onclick="removeCustomDestination()">
                    <i class="bi bi-x"></i> Remover
                </button>
            </div>
        </div>
        <div class="mt-2">
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearFilters()">
                <i class="bi bi-x-circle"></i> Limpar Filtros
            </button>
        </div>
    </div>
</div>

<!-- MAPA -->
<div class="modern-card">
    <div class="modern-card-body p-0">
        <div id="map" style="height: 600px; width: 100%;"></div>
    </div>
</div>

<!-- LEGENDA -->
<div class="modern-card mt-4">
    <div class="modern-card-header">
        <h5 class="modern-card-title">
            <i class="bi bi-info-circle"></i>
            Legenda
        </h5>
    </div>
    <div class="modern-card-body">
        <div class="row">
            <div class="col-md-6">
                <p><strong>Raio de Clusterização:</strong> 200km a partir de Oliveira de Azeméis</p>
                <p><strong>Destino Fixo:</strong> Oliveira de Azeméis, Portugal (40.8397, -8.4775)</p>
            </div>
            <div class="col-md-6">
                <p><i class="bi bi-geo-alt-fill text-primary"></i> Marcadores Azuis: Origens dos Transportes</p>
                <p><i class="bi bi-geo-alt-fill text-danger"></i> Marcador Vermelho: Origem Customizad (quando definido)</p>
                <p class="text-muted"><small>Clique nos marcadores para ver detalhes do orçamento</small></p>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    let map;
    let markers = [];
    let markerCluster;
    let allQuotes = [];
    let customDestinationMarker = null;

    // Coordenadas de Oliveira de Azeméis
    const destination = {
        lat: 40.8397,
        lng: -8.4775
    };

    function initMap() {
        // Criar mapa centrado em Oliveira de Azeméis
        map = new google.maps.Map(document.getElementById('map'), {
            center: destination,
            zoom: 5,
            mapTypeId: 'roadmap'
        });

        // Carregar orçamentos
        loadQuotes();
    }

    function loadQuotes(filters = {}) {
        // Construir URL com filtros
        const params = new URLSearchParams();
        if (filters.brand) params.append('brand', filters.brand);
        if (filters.model) params.append('model', filters.model);
        if (filters.supplier_id) params.append('supplier_id', filters.supplier_id);
        
        const url = '{{ route('admin.transport-quotes.map-data') }}' + (params.toString() ? '?' + params.toString() : '');
        
        fetch(url)
            .then(response => response.json())
            .then(data => {
                console.log('Orçamentos carregados:', data);
                console.log('Total de orçamentos:', data.length);
                
                allQuotes = data;
                clearMarkers();
                
                data.forEach(quote => {
                    console.log('Adicionando marcador para:', quote);
                    addQuoteMarker(quote);
                });

                console.log('Total de marcadores criados:', markers.length);

                // Inicializar clustering
                if (typeof MarkerClusterer !== 'undefined' && markers.length > 0) {
                    if (markerCluster) {
                        markerCluster.clearMarkers();
                    }
                    markerCluster = new MarkerClusterer(map, markers, {
                        maxZoom: 12,
                        gridSize: 50,
                        imagePath: 'https://developers.google.com/maps/documentation/javascript/examples/markerclusterer/m'
                    });
                }
            })
            .catch(error => {
                console.error('Erro ao carregar orçamentos:', error);
            });
    }

    function clearMarkers() {
        // Remover marcadores existentes
        markers.forEach(marker => marker.setMap(null));
        markers = [];
        
        // Limpar cluster
        if (markerCluster) {
            markerCluster.clearMarkers();
        }
    }

    function applyFilters() {
        const filters = {
            brand: document.getElementById('filter-brand').value,
            model: document.getElementById('filter-model').value,
            supplier_id: document.getElementById('filter-supplier').value
        };
        
        loadQuotes(filters);
    }

    function clearFilters() {
        document.getElementById('filter-brand').value = '';
        document.getElementById('filter-model').value = '';
        document.getElementById('filter-supplier').value = '';
        loadQuotes();
    }

    function addCustomDestination() {
        const lat = parseFloat(document.getElementById('filter-dest-lat').value);
        const lng = parseFloat(document.getElementById('filter-dest-lng').value);
        
        if (isNaN(lat) || isNaN(lng)) {
            alert('Por favor, insira valores válidos para latitude e longitude.');
            return;
        }
        
        if (lat < -90 || lat > 90) {
            alert('Latitude deve estar entre -90 e 90.');
            return;
        }
        
        if (lng < -180 || lng > 180) {
            alert('Longitude deve estar entre -180 e 180.');
            return;
        }
        
        // Remover marcador anterior se existir
        removeCustomDestination();
        
        const position = { lat, lng };
        
        // Criar marcador vermelho
        customDestinationMarker = new google.maps.Marker({
            position: position,
            map: map,
            title: 'Origem Customizada',
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
            },
            zIndex: 1000 // Garantir que fica por cima de outros marcadores
        });
        
        // Info window do destino customizado
        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div style="padding: 10px;">
                    <h6 style="margin: 0 0 10px 0; font-weight: bold; color: #dc3545;">Origem Customizada</h6>
                    <p style="margin: 5px 0;"><strong>Latitude:</strong> ${lat}</p>
                    <p style="margin: 5px 0;"><strong>Longitude:</strong> ${lng}</p>
                </div>
            `
        });
        
        customDestinationMarker.addListener('click', function() {
            infoWindow.open(map, customDestinationMarker);
        });
        
        // Centrar mapa no novo destino
        map.setCenter(position);
        map.setZoom(8);
    }
    
    function removeCustomDestination() {
        if (customDestinationMarker) {
            customDestinationMarker.setMap(null);
            customDestinationMarker = null;
        }
        
        document.getElementById('filter-dest-lat').value = '';
        document.getElementById('filter-dest-lng').value = '';
        
        // Re-centrar no destino padrão
        map.setCenter(destination);
        map.setZoom(5);
    }

    function addQuoteMarker(quote) {
        const position = {
            lat: quote.lat,
            lng: quote.lng
        };

        const marker = new google.maps.Marker({
            position: position,
            map: map,
            title: `${quote.brand} ${quote.model}`,
            icon: {
                url: 'http://maps.google.com/mapfiles/ms/icons/blue-dot.png'
            }
        });

        // Calcular distância aproximada
        const distance = calculateDistance(position.lat, position.lng, destination.lat, destination.lng);

        const infoWindow = new google.maps.InfoWindow({
            content: `
            <div style="padding: 10px; max-width: 250px;">
                <h6 style="margin: 0 0 10px 0; font-weight: bold; color: var(--admin-primary);">
                    ${quote.brand} ${quote.model}
                </h6>
                <p style="margin: 5px 0;"><strong>Origem:</strong> ${quote.origin_city}, ${quote.origin_country}</p>
                <p style="margin: 5px 0;"><strong>Transportadora:</strong> ${quote.supplier}</p>
                <p style="margin: 5px 0;"><strong>Preço:</strong> ${quote.price.toFixed(2)} €</p>
                <p style="margin: 5px 0;"><strong>Data:</strong> ${quote.quote_date}</p>
                <p style="margin: 5px 0;"><strong>Distância:</strong> ${distance.toFixed(0)} km</p>
                <a href="/gestao/v2/transport-quotes/${quote.id}/edit" 
                   style="display: inline-block; margin-top: 10px; padding: 5px 10px; background: var(--admin-primary); color: white; text-decoration: none; border-radius: 4px; font-size: 0.875rem;">
                    Ver Detalhes
                </a>
            </div>
        `
        });

        marker.addListener('click', function() {
            infoWindow.open(map, marker);
        });

        markers.push(marker);
    }

    // Fórmula de Haversine para calcular distância entre coordenadas
    function calculateDistance(lat1, lon1, lat2, lon2) {
        const R = 6371; // Raio da Terra em km
        const dLat = toRad(lat2 - lat1);
        const dLon = toRad(lon2 - lon1);
        const a = Math.sin(dLat / 2) * Math.sin(dLat / 2) +
            Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
            Math.sin(dLon / 2) * Math.sin(dLon / 2);
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function toRad(degrees) {
        return degrees * (Math.PI / 180);
    }

    // Carregar Google Maps API
    window.initMap = initMap;
</script>

<!-- Google Maps API -->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAcPG8ohsi6DQXwaWfxj0KAW3-YcjN6v0c&callback=initMap" async defer></script>

<!-- Marker Clusterer -->
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>

<style>
    .modern-card {
        overflow: hidden;
    }

    #map {
        border-radius: 8px;
    }
</style>
@endpush