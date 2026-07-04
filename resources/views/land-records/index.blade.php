<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Land Records - Land GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #e8e8e8; display: flex; min-height: 100vh; }
        .sidebar { width: 200px; background: #1a2744; display: flex; flex-direction: column; position: fixed; height: 100vh; left: 0; top: 0; z-index: 1000; }
        .logo-section { padding: 20px 15px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-section img { width: 50px; height: 50px; }
        .nav-menu { flex: 1; padding: 15px 0; }
        .nav-item { display: flex; align-items: center; padding: 14px 20px; color: rgba(255,255,255,0.7); text-decoration: none; gap: 12px; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s; }
        .nav-item i { font-size: 16px; width: 18px; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.15); color: white; }
        .logout-section { padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn { width: 100%; padding: 11px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7); border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; font-weight: 500; text-transform: uppercase; transition: all 0.3s; }
        .logout-btn:hover { background: rgba(255,255,255,0.2); color: white; }
        .main-content { flex: 1; margin-left: 200px; display: flex; flex-direction: column; }
        .top-bar { background: white; padding: 12px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08); }
        .search-box { flex: 1; max-width: 450px; position: relative; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; font-size: 14px; }
        .search-input { width: 100%; padding: 9px 15px 9px 36px; border: 1px solid #e0e0e0; border-radius: 20px; font-size: 13px; background: #f9f9f9; }
        .top-right { display: flex; align-items: center; gap: 18px; }
        .bell-icon { font-size: 18px; color: #666; cursor: pointer; }
        .user-info { display: flex; align-items: center; gap: 8px; }
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: #1a2744; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 13px; }
        .user-name { font-size: 13px; font-weight: 600; color: #333; }
        .user-role-label { font-size: 11px; color: #999; }
        .content-area { padding: 20px 25px; flex: 1; overflow-y: auto; }
        .page-title { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 15px; }
    </style>
    <style>
        .filters-row { display: flex; gap: 12px; margin-bottom: 15px; align-items: flex-end; }
        .filter-group { display: flex; flex-direction: column; gap: 3px; }
        .filter-label { font-size: 11px; color: #666; font-weight: 500; }
        .filter-select { padding: 8px 30px 8px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; color: #333; background: white; appearance: none; cursor: pointer; min-width: 150px; }
        .map-layout { display: grid; grid-template-columns: 1fr 300px; gap: 15px; margin-bottom: 15px; }
        .map-container { background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08); position: relative; }
        #recordsMap { height: 380px; width: 100%; }
        .draw-btn { position: absolute; top: 12px; right: 12px; z-index: 999; padding: 9px 16px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 7px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.3s; }
        .draw-btn:hover { background: #2d4070; }
        .draw-btn.cancel { background: #c62828; }
        .info-panel { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .info-placeholder { text-align: center; padding: 50px 15px; color: #ccc; }
        .info-placeholder i { font-size: 36px; margin-bottom: 10px; display: block; }
        .info-placeholder p { font-size: 13px; }
        .info-field { margin-bottom: 12px; }
        .info-field-label { font-size: 11px; color: #999; margin-bottom: 3px; }
        .info-field-value { font-size: 15px; font-weight: 700; color: #333; }
        .info-row { display: flex; gap: 10px; margin-bottom: 12px; }
        .info-col { flex: 1; }
        .info-col-label { font-size: 11px; color: #999; margin-bottom: 2px; }
        .info-col-value { font-size: 13px; color: #333; font-weight: 500; }
        .land-type-badge { padding: 3px 12px; border-radius: 12px; font-size: 12px; font-weight: 500; display: inline-block; }
        .badge-residential { background: #e8f5e9; color: #2e7d32; }
        .badge-commercial { background: #e3f2fd; color: #1565c0; }
        .badge-agricultural { background: #f3e5f5; color: #6a1b9a; }
        .badge-industrial { background: #fff3e0; color: #e65100; }
        .info-actions { display: flex; gap: 10px; margin-top: 15px; }
        .btn-view { flex:1; padding: 9px; background: white; border: 1px solid #1a2744; color: #1a2744; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display:flex; align-items:center; justify-content:center; gap:5px; }
        .btn-edit { flex:1; padding: 9px; background: #1976d2; color: white; border: none; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; display:flex; align-items:center; justify-content:center; gap:5px; }
        .table-card { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .table-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .table-header h3 { font-size: 15px; font-weight: 600; color: #333; }
        .table-search { position: relative; }
        .table-search i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #999; font-size: 12px; }
        .table-search input { padding: 8px 12px 8px 30px; border: 1px solid #e0e0e0; border-radius: 20px; font-size: 12px; width: 200px; }
        .records-table { width: 100%; border-collapse: collapse; }
        .records-table th { text-align: left; padding: 10px 12px; font-size: 12px; color: #888; font-weight: 600; border-bottom: 2px solid #f0f0f0; }
        .records-table td { padding: 11px 12px; font-size: 13px; color: #444; border-bottom: 1px solid #f5f5f5; cursor: pointer; }
        .records-table tr:hover td { background: #f9f9f9; }
        .records-table tr.selected td { background: #e3f2fd; }
    </style>
    <style>
        /* Modals */
        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
        .overlay.show { display: flex; }
        .modal { background: white; border-radius: 16px; padding: 35px; max-width: 430px; width: 90%; text-align: center; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .modal-icon { font-size: 44px; color: #1a2744; margin-bottom: 15px; }
        .modal h3 { font-size: 19px; font-weight: 700; color: #333; margin-bottom: 8px; }
        .modal p { font-size: 13px; color: #666; margin-bottom: 22px; line-height: 1.5; }
        .modal-btns { display: flex; gap: 12px; justify-content: center; }
        .btn-confirm { padding: 12px 28px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-confirm:hover { background: #2d4070; }
        .btn-cancel { padding: 12px 28px; background: #f5f5f5; color: #555; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .form-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
        .form-overlay.show { display: flex; }
        .form-card { background: white; border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
        .form-card h3 { font-size: 19px; font-weight: 700; color: #333; margin-bottom: 4px; }
        .form-card p { font-size: 13px; color: #888; margin-bottom: 20px; }
        .form-row { margin-bottom: 14px; }
        .form-row label { font-size: 13px; font-weight: 600; color: #444; display: block; margin-bottom: 5px; }
        .form-row input, .form-row select, .form-row textarea { width: 100%; padding: 10px 13px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; color: #333; }
        .form-row input:focus, .form-row select:focus, .form-row textarea:focus { outline: none; border-color: #1a2744; }
        .form-row textarea { resize: vertical; min-height: 70px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 18px; }
        .btn-submit { padding: 11px 28px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-discard { padding: 11px 18px; background: #ffebee; color: #c62828; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo-section"><img src="{{ asset('images/Darlandicon.png') }}" alt="Logo"></div>
        <nav class="nav-menu">
            <a href="/dashboard" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer" class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item active"><i class="fas fa-file-alt"></i><span>Land Records</span></a>
        </nav>
        <div class="logout-section">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></button>
            </form>
        </div>
    </aside>

    <div class="main-content">
        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search documents...">
            </div>
            <div class="top-right">
                <i class="fas fa-bell bell-icon"></i>
                <div class="user-info">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div>
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role-label">{{ strtoupper(auth()->user()->role) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-area">
            <h1 class="page-title">Land Records</h1>

            <form method="GET" action="/land-records">
                <div class="filters-row">
                    <div class="filter-group">
                        <label class="filter-label">Barangay</label>
                        <select name="barangay" class="filter-select" onchange="this.form.submit()">
                            <option value="all">All Barangay</option>
                            @foreach($barangays as $brgy)
                                <option value="{{ $brgy }}" {{ request('barangay') == $brgy ? 'selected' : '' }}>{{ $brgy }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Land type</label>
                        <select name="land_type" class="filter-select" onchange="this.form.submit()">
                            <option value="all">All Land type</option>
                            <option value="residential" {{ request('land_type')=='residential'?'selected':'' }}>Residential</option>
                            <option value="commercial" {{ request('land_type')=='commercial'?'selected':'' }}>Commercial</option>
                            <option value="agricultural" {{ request('land_type')=='agricultural'?'selected':'' }}>Agricultural</option>
                            <option value="industrial" {{ request('land_type')=='industrial'?'selected':'' }}>Industrial</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Status</label>
                        <select name="status" class="filter-select" onchange="this.form.submit()">
                            <option value="all">All Status</option>
                            <option value="registered" {{ request('status')=='registered'?'selected':'' }}>Registered</option>
                            <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                            <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Rejected</option>
                        </select>
                    </div>
                </div>
            </form>

            <div class="map-layout">
                <div class="map-container">
                    <div id="recordsMap"></div>
                    <button class="draw-btn" id="drawBtn" onclick="toggleDraw()">
                        <i class="fas fa-draw-polygon"></i> Mark My Land
                    </button>
                </div>

                <div class="info-panel" id="infoPanel">
                    <div id="infoPanelContent">
                        <div class="info-placeholder">
                            <i class="fas fa-map-pin"></i>
                            <p>Click a lot on the map or<br>draw to mark your land</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-card">
                <div class="table-header">
                    <h3>Land Record</h3>
                    <div class="table-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search documents..." oninput="filterTable(this.value)">
                    </div>
                </div>
                <table class="records-table" id="recordsTable">
                    <thead>
                        <tr>
                            <th>Land ID</th><th>Owner</th><th>Location</th>
                            <th>Land Type</th><th>Area (sqm)</th><th>Status</th><th>Date Registered</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($lots as $lot)
                        <tr onclick="selectLot('{{ $lot->land_id }}')" data-id="{{ $lot->land_id }}">
                            <td>{{ $lot->land_id }}</td>
                            <td>{{ $lot->owner_name }}</td>
                            <td>{{ $lot->location }}</td>
                            <td><span class="land-type-badge badge-{{ $lot->land_type }}">{{ ucfirst($lot->land_type) }}</span></td>
                            <td>{{ $lot->area }}</td>
                            <td>{{ ucfirst($lot->status) }}</td>
                            <td>{{ $lot->date_registered ? $lot->date_registered->format('M d, Y') : '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Confirm Modal -->
    <div class="overlay" id="confirmModal">
        <div class="modal">
            <div class="modal-icon"><i class="fas fa-map-marked-alt"></i></div>
            <h3>Confirm Your Plot?</h3>
            <p>Is this your land boundary? Confirm to proceed and fill in the land information.</p>
            <div class="modal-btns">
                <button class="btn-cancel" onclick="cancelDraw()">Cancel</button>
                <button class="btn-confirm" onclick="confirmDraw()">Confirm</button>
            </div>
        </div>
    </div>

    <!-- Land Info Form Modal -->
    <div class="form-overlay" id="landFormModal">
        <div class="form-card">
            <h3>Land Information</h3>
            <p>Fill in the details for your claimed plot</p>
            <form id="landForm" method="POST" action="{{ route('land-records.store') }}">
                @csrf
                <input type="hidden" name="geojson" id="formGeojson">
                <input type="hidden" name="area" id="formArea">
                <div class="form-grid">
                    <div class="form-row">
                        <label>Owner Name</label>
                        <input type="text" name="owner_name" placeholder="Full name" required>
                    </div>
                    <div class="form-row">
                        <label>Lot No. / Land ID</label>
                        <input type="text" name="land_id" placeholder="e.g. 10293" required>
                    </div>
                </div>
                <div class="form-row">
                    <label>Barangay</label>
                    <input type="text" name="barangay" placeholder="e.g. Anonas" required>
                </div>
                <div class="form-row">
                    <label>Full Location</label>
                    <input type="text" name="location" placeholder="e.g. Brgy. Anonas, Urdaneta City" required>
                </div>
                <div class="form-grid">
                    <div class="form-row">
                        <label>Land Type</label>
                        <select name="land_type">
                            <option value="residential">Residential</option>
                            <option value="commercial">Commercial</option>
                            <option value="agricultural">Agricultural</option>
                            <option value="industrial">Industrial</option>
                        </select>
                    </div>
                    <div class="form-row">
                        <label>Area (sqm)</label>
                        <input type="number" name="area_display" id="areaDisplay" placeholder="Auto-calculated" readonly>
                    </div>
                </div>
                <div class="form-row">
                    <label>Notes</label>
                    <textarea name="notes" placeholder="Additional information..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-discard" onclick="discardDraw()">Discard</button>
                    <button type="submit" class="btn-submit">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script>
        const allLots = @json($allLots);
        const colorMap = { residential:'#4caf50', commercial:'#ff9800', agricultural:'#9c27b0', industrial:'#f44336' };
        const isAdmin = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};

        const map = L.map('recordsMap').setView([15.9754, 120.5701], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(map);

        const drawnItems = new L.FeatureGroup().addTo(map);
        const lotLayers = {};
        let drawingMode = false;
        let currentLayer = null;
        let currentArea = 0;

        // Draw control
        const drawControl = new L.Control.Draw({
            draw: {
                polygon: { allowIntersection: false, showArea: true, shapeOptions: { color: '#1a2744', fillColor: '#1a2744', fillOpacity: 0.3 } },
                polyline: false, rectangle: false, circle: false, circlemarker: false, marker: false
            },
            edit: { featureGroup: drawnItems }
        });

        // Draw existing lots from DB (no hardcoded boxes)
        allLots.forEach(lot => {
            if (!lot.geojson) return;
            try {
                const coords = JSON.parse(lot.geojson);
                const color = colorMap[lot.land_type] || '#4caf50';
                const polygon = L.polygon(coords[0].map(c => [c[1], c[0]]), { color, fillColor: color, fillOpacity: 0.4, weight: 2 }).addTo(map);
                const center = polygon.getBounds().getCenter();
                L.marker(center, { icon: L.divIcon({ className: '', html: `<div style="background:${color};color:white;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700">${lot.land_id}</div>`, iconAnchor:[20,10] }) }).addTo(map);
                polygon.on('click', () => selectLot(lot.land_id));
                lotLayers[lot.land_id] = polygon;
            } catch(e) {}
        });

        function toggleDraw() {
            const btn = document.getElementById('drawBtn');
            if (!drawingMode) {
                drawingMode = true;
                btn.innerHTML = '<i class="fas fa-times"></i> Cancel';
                btn.classList.add('cancel');
                map.addControl(drawControl);
                new L.Draw.Polygon(map, drawControl.options.draw.polygon).enable();
            } else {
                drawingMode = false;
                btn.innerHTML = '<i class="fas fa-draw-polygon"></i> Mark My Land';
                btn.classList.remove('cancel');
                map.removeControl(drawControl);
            }
        }

        map.on(L.Draw.Event.CREATED, function(e) {
            currentLayer = e.layer;
            drawnItems.addLayer(currentLayer);
            const latlngs = currentLayer.getLatLngs()[0];
            currentArea = Math.round(calcArea(latlngs));
            drawingMode = false;
            const btn = document.getElementById('drawBtn');
            btn.innerHTML = '<i class="fas fa-draw-polygon"></i> Mark My Land';
            btn.classList.remove('cancel');
            map.removeControl(drawControl);
            document.getElementById('confirmModal').classList.add('show');
        });

        function calcArea(latlngs) {
            if (!latlngs || latlngs.length < 3) return 0;
            let area = 0;
            const R = 6371000;
            for (let i = 0; i < latlngs.length; i++) {
                const j = (i + 1) % latlngs.length;
                const xi = latlngs[i].lng * Math.PI / 180 * R * Math.cos(latlngs[i].lat * Math.PI / 180);
                const yi = latlngs[i].lat * Math.PI / 180 * R;
                const xj = latlngs[j].lng * Math.PI / 180 * R * Math.cos(latlngs[j].lat * Math.PI / 180);
                const yj = latlngs[j].lat * Math.PI / 180 * R;
                area += xi * yj - xj * yi;
            }
            return Math.abs(area / 2);
        }

        function cancelDraw() {
            document.getElementById('confirmModal').classList.remove('show');
            if (currentLayer) { drawnItems.removeLayer(currentLayer); currentLayer = null; }
        }

        function confirmDraw() {
            document.getElementById('confirmModal').classList.remove('show');
            const latlngs = currentLayer.getLatLngs()[0];
            const geojson = JSON.stringify([latlngs.map(ll => [ll.lng, ll.lat])]);
            document.getElementById('formGeojson').value = geojson;
            document.getElementById('formArea').value = currentArea;
            document.getElementById('areaDisplay').value = currentArea;
            document.getElementById('landFormModal').classList.add('show');
        }

        function discardDraw() {
            document.getElementById('landFormModal').classList.remove('show');
            if (currentLayer) { drawnItems.removeLayer(currentLayer); currentLayer = null; }
        }

        function selectLot(landId) {
            const lot = allLots.find(l => l.land_id === landId);
            if (!lot) return;
            Object.entries(lotLayers).forEach(([id, layer]) => {
                const c = colorMap[allLots.find(l=>l.land_id===id)?.land_type]||'#4caf50';
                layer.setStyle(id===landId ? {color:'#1a2744',fillColor:'#1a2744',fillOpacity:0.6,weight:3} : {color:c,fillColor:c,fillOpacity:0.4,weight:2});
            });
            if (lotLayers[landId]) map.fitBounds(lotLayers[landId].getBounds(), {padding:[30,30]});
            document.querySelectorAll('#recordsTable tr').forEach(tr => tr.classList.toggle('selected', tr.dataset.id===landId));
            const badgeMap = {residential:'badge-residential',commercial:'badge-commercial',agricultural:'badge-agricultural',industrial:'badge-industrial'};
            document.getElementById('infoPanelContent').innerHTML = `
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:14px">
                    <span style="font-size:15px;font-weight:700;color:#333">Land Information</span>
                    <button onclick="clearSel()" style="background:#f0f0f0;border:none;border-radius:50%;width:24px;height:24px;cursor:pointer;font-size:11px">✕</button>
                </div>
                <div class="info-field"><div class="info-field-label">Land ID</div><div class="info-field-value">${lot.land_id}</div></div>
                <div class="info-field"><div class="info-field-label">Owner</div><div class="info-field-value">${lot.owner_name}</div></div>
                <div class="info-field"><div class="info-field-label">Location</div><div class="info-field-value" style="font-size:13px">${lot.location}</div></div>
                <div class="info-row">
                    <div class="info-col"><div class="info-col-label">Land Type</div><span class="land-type-badge ${badgeMap[lot.land_type]}">${lot.land_type.charAt(0).toUpperCase()+lot.land_type.slice(1)}</span></div>
                    <div class="info-col"><div class="info-col-label">Area</div><div class="info-col-value">${lot.area??'—'}</div></div>
                </div>
                <div class="info-row">
                    <div class="info-col"><div class="info-col-label">Status</div><div class="info-col-value">${lot.status?lot.status.charAt(0).toUpperCase()+lot.status.slice(1):'—'}</div></div>
                    <div class="info-col"><div class="info-col-label">Date Registered</div><div class="info-col-value">${lot.date_registered??'—'}</div></div>
                </div>
                <div class="info-actions">
                    <button class="btn-view"><i class="fas fa-eye"></i> View details</button>
                    ${isAdmin ? `<button class="btn-edit"><i class="fas fa-pencil-alt"></i> Edit record</button>` : ''}
                </div>`;
        }

        function clearSel() {
            Object.entries(lotLayers).forEach(([id,layer]) => { const c=colorMap[allLots.find(l=>l.land_id===id)?.land_type]||'#4caf50'; layer.setStyle({color:c,fillColor:c,fillOpacity:0.4,weight:2}); });
            document.getElementById('infoPanelContent').innerHTML = `<div class="info-placeholder"><i class="fas fa-map-pin"></i><p>Click a lot on the map or<br>draw to mark your land</p></div>`;
            document.querySelectorAll('#recordsTable tr').forEach(tr=>tr.classList.remove('selected'));
        }

        function filterTable(q) {
            document.querySelectorAll('#recordsTable tbody tr').forEach(tr => { tr.style.display = tr.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none'; });
        }
    </script>
</body>
</html>

