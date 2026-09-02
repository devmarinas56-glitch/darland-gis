<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mapa Viewer - Land GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #1a2744;
            overflow: hidden;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 200px;
            background: #1a2744;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0; top: 0;
            z-index: 1000;
        }

        .logo-section {
            padding: 20px 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .logo-section img { width: 50px; height: 50px; object-fit: contain; }

        .nav-menu { flex: 1; padding: 15px 0; }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            gap: 12px;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            transition: all 0.2s;
        }
        .nav-item i { font-size: 16px; width: 18px; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.15); color: white; }

        .logout-section { padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn {
            width: 100%;
            padding: 11px;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            text-transform: uppercase;
            transition: all 0.2s;
            text-decoration: none;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.2); color: white; }

        /* ── Main layout ── */
        .main-content {
            margin-left: 200px;
            height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Top bar ── */
        .top-bar {
            background: white;
            padding: 11px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            flex-shrink: 0;
            z-index: 500;
        }

        .search-box { flex: 1; max-width: 380px; position: relative; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 13px; }
        .search-input {
            width: 100%;
            padding: 8px 14px 8px 34px;
            border: 1px solid #e8e8e8;
            border-radius: 20px;
            font-size: 13px;
            background: #f7f7f7;
            color: #444;
        }
        .search-input:focus { outline: none; border-color: #1a2744; background: white; }

        .top-right { display: flex; align-items: center; gap: 18px; }
        .bell-icon { font-size: 18px; color: #aaa; cursor: pointer; }
        .bell-icon:hover { color: #1a2744; }

        .user-chip { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: #1a2744;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 12px;
        }
        .user-name { font-size: 13px; font-weight: 600; color: #333; }
        .user-role-label { font-size: 11px; color: #aaa; text-transform: uppercase; }
        .chevron { font-size: 11px; color: #ccc; }

        /* ── Map area ── */
        .map-area {
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        /* Page title overlay */
        .map-title-bar {
            position: absolute;
            top: 0; left: 0; right: 0;
            padding: 12px 20px;
            font-size: 16px;
            font-weight: 700;
            color: #222;
            background: rgba(255,255,255,0.92);
            backdrop-filter: blur(4px);
            z-index: 400;
            border-bottom: 1px solid rgba(0,0,0,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .map-title-bar span { display: flex; align-items: center; gap: 8px; }

        /* Layer switcher chips */
        .layer-chips { display: flex; gap: 6px; }
        .layer-chip {
            padding: 5px 14px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            border: 1px solid #ddd;
            background: white;
            color: #555;
            transition: all 0.2s;
        }
        .layer-chip.active { background: #1a2744; color: white; border-color: #1a2744; }
        .layer-chip:hover:not(.active) { border-color: #1a2744; color: #1a2744; }

        #map {
            width: 100%;
            height: 100%;
        }

        /* ── Leaflet overrides ── */
        .leaflet-control-zoom {
            border: none !important;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2) !important;
            border-radius: 8px !important;
            overflow: hidden;
        }
        .leaflet-control-zoom a {
            width: 32px !important;
            height: 32px !important;
            line-height: 32px !important;
            font-size: 16px !important;
            color: #333 !important;
            background: white !important;
        }
        .leaflet-control-zoom a:hover { background: #f0f0f0 !important; }

        /* ── Popup card — clean white card matching screenshot ── */
        .survey-popup {
            font-family: 'Segoe UI', sans-serif;
            min-width: 200px;
        }
        .popup-meta {
            font-size: 10px;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
            font-weight: 600;
        }
        .popup-val {
            font-size: 16px;
            font-weight: 700;
            color: #111;
            margin-bottom: 14px;
        }
        .popup-val.small {
            font-size: 14px;
            font-weight: 700;
            color: #222;
            margin-bottom: 0;
        }
        .leaflet-popup-content-wrapper {
            border-radius: 12px !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15) !important;
            padding: 0 !important;
        }
        .leaflet-popup-content {
            margin: 18px 20px !important;
        }
        .leaflet-popup-tip-container { display: none; }
        .leaflet-popup-close-button {
            top: 10px !important;
            right: 12px !important;
            font-size: 18px !important;
            color: #aaa !important;
        }
        .leaflet-popup-close-button:hover { color: #333 !important; }

        /* Popup action buttons */
        .popup-actions { display: flex; gap: 8px; margin-top: 14px; padding-top: 12px; border-top: 1px solid #f0f0f0; }
        .popup-btn {
            flex: 1;
            padding: 7px 0;
            border: none;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        .popup-edit { background: #1a2744; color: white; }
        .popup-edit:hover { background: #2d4070; }
        .popup-delete { background: #fff0f0; color: #c62828; }
        .popup-delete:hover { background: #ffcdd2; }

        /* ── Survey search panel ── */
        .search-panel {
            position: absolute;
            top: 56px; left: 14px;
            z-index: 400;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.15);
            width: 240px;
            overflow: hidden;
            display: none;
        }
        .search-panel.open { display: block; }
        .search-panel-header {
            padding: 10px 14px;
            font-size: 12px;
            font-weight: 700;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            border-bottom: 1px solid #f0f0f0;
        }
        .search-panel-list { max-height: 260px; overflow-y: auto; }
        .search-panel-item {
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f8f8f8;
            transition: background 0.15s;
        }
        .search-panel-item:hover { background: #f5f5f5; }
        .search-panel-item:last-child { border-bottom: none; }
        .spi-lsn { font-size: 13px; font-weight: 700; color: #1a2744; }
        .spi-owner { font-size: 11px; color: #888; }

        /* ── Stats overlay (bottom-left) ── */
        .map-stats {
            position: absolute;
            bottom: 22px; left: 14px;
            z-index: 400;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(6px);
            border-radius: 10px;
            padding: 10px 16px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.12);
            display: flex;
            gap: 20px;
        }
        .stat-item { text-align: center; }
        .stat-item-num { font-size: 18px; font-weight: 800; color: #1a2744; }
        .stat-item-lbl { font-size: 10px; color: #aaa; text-transform: uppercase; font-weight: 600; letter-spacing: 0.3px; }

        /* ── Legend ── */
        .map-legend {
            position: absolute;
            bottom: 22px; right: 14px;
            z-index: 400;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(6px);
            border-radius: 10px;
            padding: 10px 14px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.12);
        }
        .legend-title { font-size: 10px; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 7px; }
        .legend-row { display: flex; align-items: center; gap: 7px; font-size: 11px; color: #444; margin-bottom: 4px; }
        .legend-row:last-child { margin-bottom: 0; }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }

        /* ── Loading spinner ── */
        .map-loader {
            position: absolute;
            top: 56px; left: 50%; transform: translateX(-50%);
            z-index: 800;
            background: white;
            border-radius: 20px;
            padding: 7px 18px;
            font-size: 12px;
            color: #555;
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
            display: flex; align-items: center; gap: 8px;
        }
        .spinner {
            width: 14px; height: 14px;
            border: 2px solid #e0e0e0;
            border-top-color: #1a2744;
            border-radius: 50%;
            animation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* ── Edit modal ── */
        .overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 9000;
            align-items: center; justify-content: center;
        }
        .overlay.show { display: flex; }
        .modal-card {
            background: white; border-radius: 14px; padding: 28px;
            width: 90%; max-width: 460px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-height: 90vh; overflow-y: auto;
        }
        .modal-card h3 { font-size: 17px; font-weight: 700; color: #222; margin-bottom: 4px; }
        .modal-card .sub { font-size: 12px; color: #aaa; margin-bottom: 20px; }
        .form-row { margin-bottom: 12px; }
        .form-row label { font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 4px; }
        .form-row input, .form-row select, .form-row textarea {
            width: 100%; padding: 9px 12px;
            border: 1px solid #e0e0e0; border-radius: 8px;
            font-size: 13px; color: #333;
        }
        .form-row input:focus, .form-row select:focus { outline: none; border-color: #1a2744; }
        .form-row textarea { resize: vertical; min-height: 65px; font-family: inherit; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 16px; }
        .btn-primary { padding: 10px 24px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-primary:hover { background: #2d4070; }
        .btn-ghost { padding: 10px 18px; background: #f5f5f5; color: #555; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-ghost:hover { background: #eee; }
        .btn-danger-ghost { padding: 10px 18px; background: #fff0f0; color: #c62828; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .alert-error {
            background: #fff0f0; color: #c62828; border: 1px solid #ffcdd2;
            border-radius: 8px; padding: 9px 13px; font-size: 12px; margin-bottom: 14px;
        }

        /* Delete confirm */
        .confirm-card {
            background: white; border-radius: 14px; padding: 32px 28px;
            width: 90%; max-width: 380px; text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .confirm-icon { font-size: 38px; color: #c62828; margin-bottom: 12px; }
        .confirm-card h3 { font-size: 17px; font-weight: 700; color: #222; margin-bottom: 8px; }
        .confirm-card p { font-size: 13px; color: #666; margin-bottom: 22px; line-height: 1.5; }
        .confirm-btns { display: flex; gap: 10px; justify-content: center; }
        .btn-del { padding: 11px 26px; background: #c62828; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-del:hover { background: #b71c1c; }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="Land GIS">
        </div>
        <nav class="nav-menu">
            <a href="/dashboard" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer" class="nav-item active"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item"><i class="fas fa-file-alt"></i><span>Land Records</span></a>
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.users') }}" class="nav-item"><i class="fas fa-users"></i><span>Users</span></a>
            @endif
        </nav>
        <div class="logout-section">
            <a href="/logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i><span>Log Out</span>
            </a>
        </div>
    </aside>

    <!-- Main -->
    <div class="main-content">

        <!-- Top bar -->
        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" id="topSearch"
                       placeholder="Search surveys or owners..."
                       oninput="handleTopSearch(this.value)"
                       autocomplete="off">
            </div>
            <div class="top-right">
                <i class="fas fa-bell bell-icon"></i>
                <div class="user-chip">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div>
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role-label">{{ strtoupper(auth()->user()->role) }}</div>
                    </div>
                    <i class="fas fa-chevron-down chevron"></i>
                </div>
            </div>
        </div>

        <!-- Map area -->
        <div class="map-area">

            <!-- Title + layer switcher bar -->
            <div class="map-title-bar">
                <span><i class="fas fa-map" style="color:#1a2744;font-size:15px"></i> Mapa Viewer</span>
                <div class="layer-chips">
                    <div class="layer-chip active" id="chip-satellite" onclick="switchLayer('satellite')">Satellite</div>
                    <div class="layer-chip" id="chip-street" onclick="switchLayer('street')">Street</div>
                    <div class="layer-chip" id="chip-topo" onclick="switchLayer('topo')">Topo</div>
                </div>
            </div>

            <!-- Survey search dropdown -->
            <div class="search-panel" id="searchPanel">
                <div class="search-panel-header">Surveys</div>
                <div class="search-panel-list" id="searchList"></div>
            </div>

            <!-- Map -->
            <div id="map"></div>

            <!-- Loading indicator -->
            <div class="map-loader" id="mapLoader">
                <div class="spinner"></div> Loading surveys…
            </div>

            <!-- Bottom stats -->
            <div class="map-stats" id="mapStats" style="display:none">
                <div class="stat-item">
                    <div class="stat-item-num" id="statSurveys">0</div>
                    <div class="stat-item-lbl">Surveys</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item-num" id="statLots">0</div>
                    <div class="stat-item-lbl">Lots</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item-num" id="statHa">0</div>
                    <div class="stat-item-lbl">Hectares</div>
                </div>
            </div>

            <!-- Legend -->
            <div class="map-legend">
                <div class="legend-title">Survey Lots</div>
                <div class="legend-row"><div class="legend-dot" style="background:#2979ff"></div>Survey boundary</div>
                <div class="legend-row"><div class="legend-dot" style="background:#43a047"></div>Individual lot</div>
            </div>

        </div><!-- /map-area -->
    </div><!-- /main-content -->

    <!-- Edit lot modal -->
    <div class="overlay" id="editModal">
        <div class="modal-card">
            <h3>Edit Lot</h3>
            <div class="sub" id="editSubtitle">Update lot information</div>
            <div class="alert-error" id="editError" style="display:none"></div>
            <form id="editForm" onsubmit="submitEdit(event)">
                <input type="hidden" id="editLotId">
                <div class="form-grid">
                    <div class="form-row">
                        <label>Lot Number</label>
                        <input type="number" id="editLotNumber" min="1" required>
                    </div>
                    <div class="form-row">
                        <label>Owner Name</label>
                        <input type="text" id="editOwnerName" required>
                    </div>
                </div>
                <div class="form-row">
                    <label>Notes</label>
                    <textarea id="editNotes" placeholder="Additional notes…"></textarea>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-ghost" onclick="closeEdit()">Cancel</button>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete confirm modal -->
    <div class="overlay" id="deleteModal">
        <div class="confirm-card">
            <div class="confirm-icon"><i class="fas fa-trash-alt"></i></div>
            <h3>Remove This Lot?</h3>
            <p>This will permanently delete the lot. This action cannot be undone.</p>
            <div class="confirm-btns">
                <button class="btn-ghost" onclick="closeDelete()">Cancel</button>
                <button class="btn-del" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const CSRF       = '{{ csrf_token() }}';
        const IS_ADMIN   = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};
        const USER_ID    = {{ auth()->id() }};

        // ── Tile layers ──────────────────────────────────────────
        const layers = {
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

        // ── Map init ─────────────────────────────────────────────
        const map = L.map('map', {
            center: [15.9754, 120.5701],
            zoom: 15,
            zoomControl: false,
            layers: [layers.satellite],
        });

        // Put zoom control top-right
        L.control.zoom({ position: 'topright' }).addTo(map);

        let currentLayer = 'satellite';

        function switchLayer(name) {
            map.removeLayer(layers[currentLayer]);
            map.addLayer(layers[name]);
            currentLayer = name;
            document.querySelectorAll('.layer-chip').forEach(c => c.classList.remove('active'));
            document.getElementById('chip-' + name).classList.add('active');
        }

        // ── Survey data ──────────────────────────────────────────
        let allSurveys = [];
        const surveyLayers = {};   // id → { boundary, lots: [layer...], data }

        // Palette for surveys (cycle through)
        const SURVEY_COLORS = [
            '#2979ff','#00897b','#e53935','#f57c00','#6d4c41',
            '#1e88e5','#43a047','#8e24aa','#00acc1','#d81b60'
        ];

        function surveyColor(index) {
            return SURVEY_COLORS[index % SURVEY_COLORS.length];
        }

        async function loadSurveys() {
            try {
                const res = await fetch('/api/surveys', {
                    headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF }
                });
                allSurveys = await res.json();
                renderSurveys();
            } catch (e) {
                console.error('Failed to load surveys:', e);
            } finally {
                document.getElementById('mapLoader').style.display = 'none';
                document.getElementById('mapStats').style.display = 'flex';
            }
        }

        function renderSurveys() {
            // Clear existing
            Object.values(surveyLayers).forEach(s => {
                s.lots.forEach(l => map.removeLayer(l));
                if (s.boundary) map.removeLayer(s.boundary);
                if (s.label) map.removeLayer(s.label);
            });
            Object.keys(surveyLayers).forEach(k => delete surveyLayers[k]);

            let totalLots = 0;
            let totalArea = 0;

            allSurveys.forEach((survey, si) => {
                const color = surveyColor(si);
                const lotLayers = [];

                (survey.lots || []).forEach((lot, li) => {
                    let polygons;
                    try { polygons = JSON.parse(lot.polygons); } catch(e) { return; }
                    if (!polygons || !polygons.length) return;

                    polygons.forEach(poly => {
                        if (!poly || poly.length < 3) return;
                        const latlngs = poly.map(p => Array.isArray(p) ? [p[0], p[1]] : [p.lat, p.lng]);

                        const layer = L.polygon(latlngs, {
                            color:       color,
                            fillColor:   color,
                            fillOpacity: 0.18,
                            weight:      1.5,
                        });

                        // Build popup HTML
                        const canEdit = IS_ADMIN;
                        layer.bindPopup(buildPopup(survey, lot, canEdit), {
                            maxWidth: 260,
                            minWidth: 200,
                            closeButton: true,
                        });

                        layer.on('mouseover', function() {
                            this.setStyle({ fillOpacity: 0.36, weight: 2.5 });
                        });
                        layer.on('mouseout', function() {
                            this.setStyle({ fillOpacity: 0.18, weight: 1.5 });
                        });

                        layer.addTo(map);
                        lotLayers.push(layer);
                    });

                    totalLots++;
                    totalArea += parseFloat(lot.area || 0);
                });

                // Survey boundary (convex hull approximation — just bounding box of all lot points)
                if (lotLayers.length > 0) {
                    try {
                        const bounds = L.featureGroup(lotLayers).getBounds();
                        const boundary = L.rectangle(bounds, {
                            color: color,
                            fillOpacity: 0,
                            weight: 2,
                            dashArray: '5 4',
                        }).addTo(map);

                        // LSN label at center
                        const center = bounds.getCenter();
                        const label = L.marker(center, {
                            icon: L.divIcon({
                                className: '',
                                html: `<div style="
                                    background: ${color};
                                    color: white;
                                    font-size: 10px;
                                    font-weight: 700;
                                    padding: 2px 7px;
                                    border-radius: 4px;
                                    white-space: nowrap;
                                    box-shadow: 0 1px 4px rgba(0,0,0,0.25);
                                ">${survey.lsn}</div>`,
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

            // Update stats
            document.getElementById('statSurveys').textContent = allSurveys.length;
            document.getElementById('statLots').textContent = totalLots;
            document.getElementById('statHa').textContent = (totalArea / 10000).toFixed(1);

            // Fit map to all layers if we have data
            if (Object.keys(surveyLayers).length > 0) {
                const allLayers = Object.values(surveyLayers).flatMap(s => s.lots);
                if (allLayers.length > 0) {
                    try { map.fitBounds(L.featureGroup(allLayers).getBounds(), { padding: [40, 40] }); } catch(e) {}
                }
            }
        }

        function buildPopup(survey, lot, canEdit) {
            return `<div class="survey-popup">
                <div class="popup-meta">Land ID</div>
                <div class="popup-val">${escHtml(survey.lsn)} – Lot ${lot.lot_number}</div>
                <div class="popup-meta">Owner on</div>
                <div class="popup-val small">${escHtml(lot.owner_name)}</div>
                ${canEdit ? `<div class="popup-actions">
                    <button class="popup-btn popup-edit" onclick="openEdit(${lot.id}, ${lot.lot_number}, '${escJs(lot.owner_name)}', '${escJs(lot.notes || '')}', '${escJs(survey.lsn)}')">Edit</button>
                    <button class="popup-btn popup-delete" onclick="openDelete(${lot.id})">Delete</button>
                </div>` : ''}
            </div>`;
        }

        function escHtml(s) {
            return String(s || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
        }
        function escJs(s) {
            return String(s || '').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"');
        }

        // ── Top search ───────────────────────────────────────────
        function handleTopSearch(q) {
            const panel = document.getElementById('searchPanel');
            const list  = document.getElementById('searchList');
            if (!q.trim()) { panel.classList.remove('open'); return; }

            const term = q.toLowerCase();
            const matches = allSurveys.filter(s =>
                s.lsn.toLowerCase().includes(term) ||
                s.original_owner.toLowerCase().includes(term) ||
                (s.lots || []).some(l => l.owner_name.toLowerCase().includes(term))
            );

            if (!matches.length) { panel.classList.remove('open'); return; }

            list.innerHTML = matches.map(s => `
                <div class="search-panel-item" onclick="zoomToSurvey(${s.id})">
                    <div class="spi-lsn">${escHtml(s.lsn)}</div>
                    <div class="spi-owner">${escHtml(s.original_owner)} · ${s.lot_count} lots</div>
                </div>
            `).join('');
            panel.classList.add('open');
        }

        function zoomToSurvey(id) {
            const entry = surveyLayers[id];
            if (!entry || !entry.lots.length) return;
            document.getElementById('searchPanel').classList.remove('open');
            document.getElementById('topSearch').value = '';
            try {
                map.fitBounds(L.featureGroup(entry.lots).getBounds(), { padding: [60, 60], maxZoom: 18 });
            } catch(e) {}
        }

        // Close search panel on outside click
        document.addEventListener('click', e => {
            if (!e.target.closest('#searchPanel') && !e.target.closest('#topSearch')) {
                document.getElementById('searchPanel').classList.remove('open');
            }
        });

        // ── Edit lot ─────────────────────────────────────────────
        let _deleteLotId = null;

        function openEdit(lotId, lotNum, ownerName, notes, lsn) {
            map.closePopup();
            document.getElementById('editLotId').value    = lotId;
            document.getElementById('editLotNumber').value = lotNum;
            document.getElementById('editOwnerName').value = ownerName;
            document.getElementById('editNotes').value     = notes;
            document.getElementById('editSubtitle').textContent = `Survey: ${lsn}`;
            document.getElementById('editError').style.display = 'none';
            document.getElementById('editModal').classList.add('show');
        }

        function closeEdit() {
            document.getElementById('editModal').classList.remove('show');
        }

        async function submitEdit(e) {
            e.preventDefault();
            const id    = document.getElementById('editLotId').value;
            const num   = document.getElementById('editLotNumber').value;
            const owner = document.getElementById('editOwnerName').value.trim();
            const notes = document.getElementById('editNotes').value.trim();
            const errDiv = document.getElementById('editError');

            try {
                const res  = await fetch(`/api/lots/${id}`, {
                    method: 'PUT',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: JSON.stringify({ lot_number: num, owner_name: owner, notes }),
                });
                const data = await res.json();
                if (res.ok && data.success) {
                    closeEdit();
                    await loadSurveys();
                } else {
                    errDiv.textContent = data.message || 'Failed to save.';
                    errDiv.style.display = 'block';
                }
            } catch(err) {
                errDiv.textContent = 'Network error. Please try again.';
                errDiv.style.display = 'block';
            }
        }

        // ── Delete lot ───────────────────────────────────────────
        function openDelete(lotId) {
            map.closePopup();
            _deleteLotId = lotId;
            document.getElementById('deleteModal').classList.add('show');
        }

        function closeDelete() {
            document.getElementById('deleteModal').classList.remove('show');
            _deleteLotId = null;
        }

        async function confirmDelete() {
            if (!_deleteLotId) return;
            try {
                const res = await fetch(`/api/lots/${_deleteLotId}`, {
                    method: 'DELETE',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                });
                closeDelete();
                if (res.ok) await loadSurveys();
            } catch(e) {}
        }

        // ── Boot ─────────────────────────────────────────────────
        loadSurveys();
    </script>
</body>
</html>
