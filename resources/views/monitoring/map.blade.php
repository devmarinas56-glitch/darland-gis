<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Land Survey Monitoring — DarLand GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { height: 100%; font-family: 'Segoe UI', Tahoma, sans-serif; overflow: hidden; }
        body { display: flex; flex-direction: column; background: #0d1b3e; }

        /* ── Top bar ── */
        .top-bar {
            background: #1a2744;
            padding: 0 20px;
            height: 54px;
            display: flex; align-items: center; justify-content: space-between;
            flex-shrink: 0; z-index: 500;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }
        .brand { display: flex; align-items: center; gap: 12px; }
        .brand img { width: 34px; height: 34px; object-fit: contain; }
        .brand-name { font-size: 15px; font-weight: 700; color: white; }
        .brand-sub  { font-size: 11px; color: rgba(255,255,255,0.5); }
        .monitor-badge {
            background: rgba(255,255,255,0.12); color: rgba(255,255,255,0.8);
            border: 1px solid rgba(255,255,255,0.2); border-radius: 20px;
            padding: 3px 12px; font-size: 10.5px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.8px;
        }

        /* Search */
        .top-search { display: flex; align-items: center; gap: 8px; flex: 1; max-width: 340px; margin: 0 20px; }
        .top-search-wrap { position: relative; flex: 1; }
        .top-search-wrap i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.4); font-size: 13px; }
        .top-search-input {
            width: 100%; padding: 7px 10px 7px 32px;
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);
            border-radius: 8px; font-size: 13px; color: white; outline: none;
        }
        .top-search-input::placeholder { color: rgba(255,255,255,0.35); }
        .top-search-input:focus { background: rgba(255,255,255,0.15); border-color: rgba(255,255,255,0.3); }

        /* Right controls */
        .top-right { display: flex; align-items: center; gap: 12px; }
        .user-chip { display: flex; align-items: center; gap: 8px; }
        .user-av {
            width: 30px; height: 30px; border-radius: 50%;
            background: rgba(255,255,255,0.15); color: white;
            display: flex; align-items: center; justify-content: center;
            font-size: 11px; font-weight: 700;
        }
        .user-name { font-size: 12.5px; font-weight: 600; color: rgba(255,255,255,0.85); }
        .user-role { font-size: 10px; color: rgba(255,255,255,0.45); text-transform: uppercase; }
        .logout-btn {
            padding: 6px 14px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7);
            border: 1px solid rgba(255,255,255,0.15); border-radius: 7px;
            font-size: 12px; font-weight: 600; cursor: pointer; text-decoration: none;
            display: flex; align-items: center; gap: 6px; transition: all 0.15s;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.2); color: white; }

        /* ── Map wrapper ── */
        .map-wrap { flex: 1; position: relative; }
        #map { width: 100%; height: 100%; }

        /* ── Title bar on map ── */
        .map-title {
            position: absolute; top: 0; left: 0; right: 0; z-index: 400;
            padding: 10px 16px;
            background: rgba(255,255,255,0.93); backdrop-filter: blur(4px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex; align-items: center; justify-content: space-between;
        }
        .map-title-left { font-size: 13.5px; font-weight: 700; color: #1a2744; display: flex; align-items: center; gap: 8px; }
        .map-title-left .dot { width: 8px; height: 8px; border-radius: 50%; background: #2e7d32; animation: pulse 1.6s infinite; }
        @keyframes pulse { 0%,100%{opacity:1} 50%{opacity:0.4} }

        /* Layer chips */
        .layer-chips { display: flex; gap: 6px; }
        .layer-chip {
            padding: 4px 12px; border-radius: 20px; font-size: 11.5px; font-weight: 600;
            cursor: pointer; border: 1px solid #ddd; background: white; color: #555; transition: all 0.15s;
        }
        .layer-chip.active { background: #1a2744; color: white; border-color: #1a2744; }
        .layer-chip:hover:not(.active) { border-color: #1a2744; color: #1a2744; }

        /* ── Search dropdown ── */
        .search-panel {
            position: absolute; top: 48px; left: 14px; z-index: 450;
            background: white; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            width: 260px; display: none; overflow: hidden;
        }
        .search-panel.open { display: block; }
        .search-panel-hdr { padding: 9px 14px; font-size: 11px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.4px; border-bottom: 1px solid #f0f0f0; }
        .search-list { max-height: 240px; overflow-y: auto; }
        .search-item { padding: 10px 14px; cursor: pointer; border-bottom: 1px solid #f8f8f8; transition: background 0.1s; }
        .search-item:hover { background: #f5f5f5; }
        .search-item:last-child { border-bottom: none; }
        .si-lsn { font-size: 13px; font-weight: 700; color: #1a2744; }
        .si-owner { font-size: 11px; color: #888; }

        /* ── Bottom stats ── */
        .map-stats {
            position: absolute; bottom: 16px; left: 14px; z-index: 400;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(6px);
            border-radius: 10px; padding: 9px 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.12);
            display: flex; gap: 20px;
        }
        .stat-item { text-align: center; }
        .stat-num { font-size: 17px; font-weight: 800; color: #1a2744; }
        .stat-lbl { font-size: 9.5px; color: #aaa; text-transform: uppercase; font-weight: 600; letter-spacing: 0.3px; }

        /* ── Legend ── */
        .map-legend {
            position: absolute; bottom: 16px; right: 14px; z-index: 400;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(6px);
            border-radius: 10px; padding: 10px 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.12);
        }
        .legend-title { font-size: 10px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 6px; }
        .legend-row { display: flex; align-items: center; gap: 7px; font-size: 11px; color: #444; margin-bottom: 4px; }
        .legend-row:last-child { margin-bottom: 0; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        /* ── Filter panel ── */
        .filter-panel {
            position: absolute; top: 48px; right: 14px; z-index: 450;
            background: white; border-radius: 10px; box-shadow: 0 4px 16px rgba(0,0,0,0.12);
            width: 220px; padding: 12px 14px; display: none;
        }
        .filter-panel.open { display: block; }
        .filter-btn {
            position: absolute; top: 48px; right: 14px; z-index: 400;
            background: white; border: none; cursor: pointer;
            border-radius: 8px; padding: 7px 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            font-size: 12px; font-weight: 600; color: #1a2744;
            display: flex; align-items: center; gap: 6px; transition: background 0.15s;
        }
        .filter-btn:hover { background: #f0f4ff; }

        /* ── Loader ── */
        .map-loader {
            position: absolute; top: 48px; left: 50%; transform: translateX(-50%);
            z-index: 800; background: white; border-radius: 20px;
            padding: 7px 18px; font-size: 12px; color: #555;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            display: flex; align-items: center; gap: 8px;
        }
        .spinner { width: 14px; height: 14px; border: 2px solid #e0e0e0; border-top-color: #1a2744; border-radius: 50%; animation: spin 0.7s linear infinite; }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Popup ── */
        .leaflet-popup-content-wrapper { border-radius: 12px !important; box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important; padding: 0 !important; }
        .leaflet-popup-content { margin: 16px 18px !important; }
        .leaflet-popup-tip-container { display: none; }
        .popup-lsn { font-size: 11px; font-weight: 700; color: #aaa; text-transform: uppercase; margin-bottom: 2px; }
        .popup-lot { font-size: 16px; font-weight: 800; color: #1a2744; margin-bottom: 8px; }
        .popup-row { font-size: 12.5px; color: #555; margin-bottom: 4px; display: flex; align-items: center; gap: 7px; }
        .popup-row i { color: #1a2744; font-size: 11px; width: 14px; }
        .popup-row strong { color: #333; }
    </style>
</head>
<body>

    <!-- Top bar -->
    <div class="top-bar">
        <div class="brand">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR">
            <div>
                <div class="brand-name">DarLand GIS</div>
                <div class="brand-sub">Land Survey Monitoring</div>
            </div>
        </div>

        <div class="top-search">
            <div class="top-search-wrap">
                <i class="fas fa-search"></i>
                <input type="text" class="top-search-input" id="topSearch"
                       placeholder="Search surveys, owners…"
                       oninput="handleSearch(this.value)" autocomplete="off">
            </div>
        </div>

        <div class="top-right">
            <span class="monitor-badge"><i class="fas fa-eye" style="margin-right:4px"></i>Monitoring</span>
            <div class="user-chip">
                <div class="user-av">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ strtoupper(auth()->user()->role) }}</div>
                </div>
            </div>
            <a href="{{ route('monitoring.logout') }}" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i> Sign Out
            </a>
        </div>
    </div>

    <!-- Map wrap -->
    <div class="map-wrap">

        <!-- Title bar -->
        <div class="map-title">
            <div class="map-title-left">
                <div class="dot"></div>
                Land Survey Map — Pangasinan
            </div>
            <div class="layer-chips">
                <div class="layer-chip active" id="chip-satellite" onclick="switchLayer('satellite')">Satellite</div>
                <div class="layer-chip" id="chip-street" onclick="switchLayer('street')">Street</div>
                <div class="layer-chip" id="chip-topo" onclick="switchLayer('topo')">Topo</div>
            </div>
        </div>

        <!-- Search dropdown -->
        <div class="search-panel" id="searchPanel">
            <div class="search-panel-hdr">Surveys</div>
            <div class="search-list" id="searchList"></div>
        </div>

        <!-- Map -->
        <div id="map"></div>

        <!-- Loader -->
        <div class="map-loader" id="mapLoader">
            <div class="spinner"></div> Loading surveys…
        </div>

        <!-- Bottom stats -->
        <div class="map-stats" id="mapStats" style="display:none">
            <div class="stat-item">
                <div class="stat-num" id="statSurveys">0</div>
                <div class="stat-lbl">Surveys</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" id="statLots">0</div>
                <div class="stat-lbl">Plots</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" id="statHa">0</div>
                <div class="stat-lbl">Hectares</div>
            </div>
            <div class="stat-item">
                <div class="stat-num" id="statOwners">0</div>
                <div class="stat-lbl">Owners</div>
            </div>
        </div>

        <!-- Legend -->
        <div class="map-legend">
            <div class="legend-title">Map Legend</div>
            <div class="legend-row"><div class="legend-dot" style="background:#2979ff;border-radius:2px"></div>Survey boundary</div>
            <div class="legend-row"><div class="legend-dot" style="background:#43a047"></div>Individual lot</div>
        </div>

    </div><!-- /map-wrap -->

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const CSRF = document.querySelector('meta[name="csrf-token"]').content;

        // ── Tile layers ──────────────────────────────────────────
        const tileLayers = {
            satellite: L.tileLayer(
                'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
                { attribution: 'Tiles &copy; Esri', maxZoom: 20 }
            ),
            street: L.tileLayer(
                'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',
                { attribution: '© OpenStreetMap', maxZoom: 19 }
            ),
            topo: L.tileLayer(
                'https://{s}.tile.opentopomap.org/{z}/{x}/{y}.png',
                { attribution: '© OpenTopoMap', maxZoom: 17 }
            ),
        };

        const map = L.map('map', {
            center: [15.9754, 120.5701],
            zoom: 14,
            zoomControl: false,
            layers: [tileLayers.satellite],
        });
        L.control.zoom({ position: 'topright' }).addTo(map);

        let currentLayer = 'satellite';
        function switchLayer(name) {
            map.removeLayer(tileLayers[currentLayer]);
            map.addLayer(tileLayers[name]);
            currentLayer = name;
            document.querySelectorAll('.layer-chip').forEach(c => c.classList.remove('active'));
            document.getElementById('chip-' + name).classList.add('active');
        }

        // ── Survey data ──────────────────────────────────────────
        let allSurveys = [];
        const surveyLayers = {};
        const COLORS = [
            '#2979ff','#00897b','#e53935','#f57c00','#6d4c41',
            '#1e88e5','#43a047','#8e24aa','#00acc1','#d81b60',
        ];

        async function loadSurveys() {
            try {
                const res = await fetch('/api/surveys', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                });
                allSurveys = await res.json();
                renderAll();
            } catch(e) {
                console.error('Failed to load surveys:', e);
            } finally {
                document.getElementById('mapLoader').style.display = 'none';
                document.getElementById('mapStats').style.display = 'flex';
            }
        }

        function renderAll() {
            // Clear
            Object.values(surveyLayers).forEach(s => {
                s.lots.forEach(l => map.removeLayer(l));
                if (s.boundary) map.removeLayer(s.boundary);
                if (s.label)    map.removeLayer(s.label);
            });
            Object.keys(surveyLayers).forEach(k => delete surveyLayers[k]);

            let totalLots = 0, totalArea = 0;
            const ownerSet = new Set();

            allSurveys.forEach((survey, si) => {
                const color = COLORS[si % COLORS.length];
                const lotLayers = [];

                (survey.lots || []).forEach(lot => {
                    let polygons;
                    try { polygons = JSON.parse(lot.polygons); } catch(e) { return; }
                    if (!polygons?.length) return;

                    polygons.forEach(poly => {
                        if (!poly || poly.length < 3) return;
                        const latlngs = poly.map(p => Array.isArray(p) ? [p[0], p[1]] : [p.lat, p.lng]);
                        const layer = L.polygon(latlngs, {
                            color, fillColor: color, fillOpacity: 0.2, weight: 1.5,
                        });

                        layer.bindPopup(buildPopup(survey, lot), { maxWidth: 240, minWidth: 180 });
                        layer.on('mouseover', function() { this.setStyle({ fillOpacity: 0.38, weight: 2.5 }); });
                        layer.on('mouseout',  function() { this.setStyle({ fillOpacity: 0.2,  weight: 1.5 }); });
                        layer.addTo(map);
                        lotLayers.push(layer);
                    });

                    totalLots++;
                    totalArea += parseFloat(lot.area || 0);
                    ownerSet.add(lot.owner_name);
                });

                if (lotLayers.length > 0) {
                    try {
                        const bounds = L.featureGroup(lotLayers).getBounds();
                        const boundary = L.rectangle(bounds, {
                            color, fillOpacity: 0, weight: 2, dashArray: '5 4',
                        }).addTo(map);

                        const label = L.marker(bounds.getCenter(), {
                            icon: L.divIcon({
                                className: '',
                                html: `<div style="background:${color};color:white;font-size:10px;font-weight:700;padding:2px 7px;border-radius:4px;white-space:nowrap;box-shadow:0 1px 4px rgba(0,0,0,0.25)">${survey.lsn}</div>`,
                                iconAnchor: [30, 10],
                            }),
                            interactive: false,
                            zIndexOffset: -100,
                        }).addTo(map);

                        surveyLayers[survey.id] = { boundary, lots: lotLayers, label, data: survey };
                    } catch(e) {
                        surveyLayers[survey.id] = { lots: lotLayers, boundary: null, label: null, data: survey };
                    }
                }
            });

            document.getElementById('statSurveys').textContent = allSurveys.length;
            document.getElementById('statLots').textContent    = totalLots;
            document.getElementById('statHa').textContent      = (totalArea / 10000).toFixed(1);
            document.getElementById('statOwners').textContent  = ownerSet.size;

            const allLotLayers = Object.values(surveyLayers).flatMap(s => s.lots);
            if (allLotLayers.length > 0) {
                try { map.fitBounds(L.featureGroup(allLotLayers).getBounds(), { padding: [40, 40] }); } catch(e) {}
            }
        }

        function buildPopup(survey, lot) {
            const area = parseFloat(lot.area || 0);
            return `<div>
                <div class="popup-lsn">${esc(survey.lsn)}</div>
                <div class="popup-lot">Lot ${lot.lot_number}</div>
                <div class="popup-row"><i class="fas fa-user"></i><strong>${esc(lot.owner_name)}</strong></div>
                <div class="popup-row"><i class="fas fa-ruler-combined"></i>${area.toLocaleString()} sqm (${(area/10000).toFixed(4)} ha)</div>
                ${survey.municipality ? `<div class="popup-row"><i class="fas fa-map-marker-alt"></i>${esc(survey.municipality)}</div>` : ''}
                ${lot.notes ? `<div class="popup-row"><i class="fas fa-sticky-note"></i>${esc(lot.notes)}</div>` : ''}
            </div>`;
        }

        function esc(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }

        // ── Search ───────────────────────────────────────────────
        function handleSearch(q) {
            const panel = document.getElementById('searchPanel');
            const list  = document.getElementById('searchList');
            if (!q.trim()) { panel.classList.remove('open'); return; }

            const term = q.toLowerCase();
            const matches = allSurveys.filter(s =>
                s.lsn.toLowerCase().includes(term) ||
                s.original_owner.toLowerCase().includes(term) ||
                (s.municipality || '').toLowerCase().includes(term) ||
                (s.lots || []).some(l => l.owner_name.toLowerCase().includes(term))
            ).slice(0, 20);

            if (!matches.length) { panel.classList.remove('open'); return; }

            list.innerHTML = matches.map(s => `
                <div class="search-item" onclick="zoomTo(${s.id})">
                    <div class="si-lsn">${esc(s.lsn)}</div>
                    <div class="si-owner">${esc(s.original_owner)} · ${s.lot_count} lots${s.municipality ? ' · ' + esc(s.municipality) : ''}</div>
                </div>
            `).join('');
            panel.classList.add('open');
        }

        function zoomTo(id) {
            const e = surveyLayers[id];
            if (!e?.lots.length) return;
            document.getElementById('searchPanel').classList.remove('open');
            document.getElementById('topSearch').value = '';
            try { map.fitBounds(L.featureGroup(e.lots).getBounds(), { padding: [60, 60], maxZoom: 18 }); } catch(err) {}
        }

        document.addEventListener('click', ev => {
            if (!ev.target.closest('#searchPanel') && !ev.target.closest('#topSearch')) {
                document.getElementById('searchPanel').classList.remove('open');
            }
        });

        // ── Boot ─────────────────────────────────────────────────
        loadSurveys();
    </script>
</body>
</html>
