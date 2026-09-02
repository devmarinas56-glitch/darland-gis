<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Land Records - DarLand GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #e8e8e8; display: flex; min-height: 100vh; }

        /* Sidebar */
        .sidebar { width: 200px; background: #1a2744; display: flex; flex-direction: column; position: fixed; height: 100vh; left: 0; top: 0; z-index: 1000; }
        .logo-section { padding: 20px 15px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-section img { width: 50px; height: 50px; }
        .nav-menu { flex: 1; padding: 15px 0; }
        .nav-item { display: flex; align-items: center; padding: 14px 20px; color: rgba(255,255,255,0.7); text-decoration: none; gap: 12px; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.3s; }
        .nav-item i { font-size: 16px; width: 18px; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.15); color: white; }
        .logout-section { padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn { width: 100%; padding: 11px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7); border-radius: 6px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; font-weight: 500; text-transform: uppercase; transition: all 0.3s; text-decoration: none; }
        .logout-btn:hover { background: rgba(255,255,255,0.2); color: white; }

        /* Layout */
        .main-content { flex: 1; margin-left: 200px; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        .top-bar { background: white; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08); flex-shrink: 0; }
        .page-title { font-size: 16px; font-weight: 700; color: #1a2744; }
        .top-right { display: flex; align-items: center; gap: 14px; }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: #1a2744; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 12px; }
        .user-name { font-size: 13px; font-weight: 600; color: #333; }
        .user-role-label { font-size: 11px; color: #999; }

        /* Main workspace */
        .workspace { display: grid; grid-template-columns: 1fr 320px; flex: 1; overflow: hidden; gap: 0; }

        /* Map */
        #map { width: 100%; height: 100%; cursor: crosshair; }
        .map-wrap { position: relative; height: 100%; }
        .map-hint { position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); background: rgba(26,39,68,0.85); color: white; padding: 7px 16px; border-radius: 20px; font-size: 12px; z-index: 800; pointer-events: none; transition: opacity 0.3s; }
        .map-hint.hide { opacity: 0; }

        /* Right panel */
        .right-panel { background: #f4f5f7; border-left: 1px solid #e0e0e0; display: flex; flex-direction: column; overflow: hidden; }
        .panel-tabs { display: flex; border-bottom: 2px solid #e0e0e0; background: white; flex-shrink: 0; }
        .tab-btn { flex: 1; padding: 12px 6px; font-size: 12px; font-weight: 600; color: #888; border: none; background: none; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.3px; }
        .tab-btn.active { color: #1a2744; border-bottom-color: #1a2744; }
        .tab-content { flex: 1; overflow-y: auto; padding: 16px; display: none; }
        .tab-content.active { display: block; }

        /* LDC Panel */
        .ldc-section { background: white; border-radius: 10px; padding: 14px; margin-bottom: 12px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .ldc-section-title { font-size: 11px; font-weight: 700; color: #1a2744; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: flex; align-items: center; gap: 6px; }
        .form-row { margin-bottom: 10px; }
        .form-label { font-size: 11px; font-weight: 600; color: #555; display: block; margin-bottom: 4px; }
        .form-input { width: 100%; padding: 8px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 13px; color: #333; }
        .form-input:focus { outline: none; border-color: #1a2744; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .coord-row { display: flex; gap: 6px; align-items: flex-end; }
        .coord-row .form-input { flex: 1; }
        .pick-btn { padding: 8px 10px; background: #1a2744; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; white-space: nowrap; display: flex; align-items: center; gap: 4px; }
        .pick-btn:hover { background: #2d4070; }
        .pick-btn.active { background: #e53935; }

        /* Lot rows in form */
        .lot-rows { max-height: 220px; overflow-y: auto; }
        .lot-row { display: grid; grid-template-columns: 40px 1fr; gap: 6px; margin-bottom: 6px; align-items: center; }
        .lot-num-label { font-size: 12px; font-weight: 700; color: #1a2744; text-align: center; background: #e8edf5; border-radius: 4px; padding: 7px 4px; }

        .btn-primary { width: 100%; padding: 10px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; }
        .btn-primary:hover { background: #2d4070; }
        .btn-secondary { width: 100%; padding: 9px; background: white; color: #1a2744; border: 1px solid #1a2744; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; margin-top: 6px; }
        .btn-secondary:hover { background: #f0f4ff; }
        .btn-danger { background: #e53935; color: white; border: none; border-radius: 6px; padding: 6px 10px; font-size: 12px; cursor: pointer; }
        .btn-danger:hover { background: #c62828; }
        .btn-sm { padding: 5px 10px; border-radius: 6px; font-size: 12px; cursor: pointer; border: none; }
        .btn-edit-sm { background: #1976d2; color: white; }
        .btn-edit-sm:hover { background: #1565c0; }

        /* Stats */
        .stat-highlight { background: #1a2744; color: white; border-radius: 10px; padding: 14px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .stat-highlight-val { font-size: 22px; font-weight: 800; }
        .stat-highlight-lbl { font-size: 11px; opacity: 0.8; }
        .stat-row { display: flex; justify-content: space-between; padding: 7px 0; border-bottom: 1px solid #f0f0f0; font-size: 13px; }
        .stat-row:last-child { border-bottom: none; }
        .stat-lbl { color: #666; }
        .stat-val { font-weight: 700; color: #333; }

        /* LSN list */
        .lsn-item { background: white; border-radius: 8px; padding: 12px; margin-bottom: 8px; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
        .lsn-item:hover { border-color: #1a2744; }
        .lsn-item.selected { border-color: #1a2744; background: #f0f4ff; }
        .lsn-item-title { font-size: 13px; font-weight: 700; color: #1a2744; }
        .lsn-item-sub { font-size: 11px; color: #888; margin-top: 2px; }
        .lsn-item-badges { display: flex; gap: 6px; margin-top: 6px; flex-wrap: wrap; }
        .badge { padding: 2px 8px; border-radius: 10px; font-size: 11px; font-weight: 600; background: #e8edf5; color: #1a2744; }

        /* Lot detail panel */
        .lot-detail { background: white; border-radius: 10px; padding: 14px; }
        .lot-detail-title { font-size: 14px; font-weight: 700; color: #1a2744; margin-bottom: 12px; display: flex; justify-content: space-between; align-items: center; }
        .lot-list-item { display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; border-radius: 6px; margin-bottom: 5px; background: #f9f9f9; border: 1px solid #eee; }
        .lot-list-item:hover { background: #f0f4ff; }
        .lot-list-num { font-size: 12px; font-weight: 700; color: #1a2744; background: #e8edf5; padding: 3px 8px; border-radius: 4px; }
        .lot-list-owner { font-size: 12px; color: #444; flex: 1; padding: 0 10px; }
        .lot-list-area { font-size: 11px; color: #888; }

        /* Alerts */
        .alert { padding: 10px 14px; border-radius: 8px; font-size: 13px; margin-bottom: 10px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }

        /* Map popup override */
        .leaflet-popup-content { font-family: 'Segoe UI', sans-serif; min-width: 180px; }
        .popup-title { font-size: 13px; font-weight: 700; color: #1a2744; margin-bottom: 6px; }
        .popup-row { font-size: 12px; color: #555; margin-bottom: 3px; }
        .popup-row span { font-weight: 600; color: #333; }
        .popup-edit-btn { margin-top: 8px; width: 100%; padding: 6px; background: #1a2744; color: white; border: none; border-radius: 5px; font-size: 12px; cursor: pointer; font-weight: 600; }
        .popup-edit-btn:hover { background: #2d4070; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo-section"><img src="{{ asset('images/Darlandicon.png') }}" alt="Logo"></div>
    <nav class="nav-menu">
        <a href="/dashboard" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="/map-viewer" class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
        <a href="/land-records" class="nav-item active"><i class="fas fa-layer-group"></i><span>Land Records</span></a>
        <a href="/add-record" class="nav-item"><i class="fas fa-plus-square"></i><span>Add Record</span></a>
        @if(auth()->user()->role === 'admin')
        <a href="{{ route('admin.users') }}" class="nav-item"><i class="fas fa-users"></i><span>Users</span></a>
        @endif
    </nav>
    <div class="logout-section">
        <a href="/logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
    </div>
</aside>

<div class="main-content">
    <div class="top-bar">
        <div class="page-title"><i class="fas fa-layer-group" style="margin-right:6px;color:#1a2744"></i>Land Survey Records</div>
        <div class="top-right">
            <i class="fas fa-bell" style="font-size:17px;color:#999;cursor:pointer"></i>
            <a href="/profile" style="display:flex;align-items:center;gap:8px;text-decoration:none;cursor:pointer">
                <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                <div>
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role-label">{{ strtoupper(auth()->user()->role) }}</div>
                </div>
            </a>
        </div>
    </div>

    <div class="workspace">
        <!-- Map -->
        <div class="map-wrap">
            <div id="map"></div>
            <div class="map-hint" id="mapHint"><i class="fas fa-mouse-pointer"></i> Click on the map to place LSN center</div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <div class="panel-tabs">
                <button class="tab-btn active" onclick="switchTab('ldc')"><i class="fas fa-calculator"></i> LDC</button>
                <button class="tab-btn" onclick="switchTab('surveys')"><i class="fas fa-list"></i> Surveys</button>
                <button class="tab-btn" onclick="switchTab('stats')"><i class="fas fa-chart-bar"></i> Stats</button>
            </div>

            <!-- LDC Tab -->
            <div class="tab-content active" id="tab-ldc">
                <div id="ldcAlert"></div>

                <div class="ldc-section">
                    <div class="ldc-section-title"><i class="fas fa-map-marker-alt"></i> Land Survey Number</div>
                    <div class="form-row">
                        <label class="form-label">LSN (Survey Number)</label>
                        <input type="text" id="lsnInput" class="form-input" placeholder="e.g. Psd-123456">
                    </div>
                    <div class="form-row">
                        <label class="form-label">Original Owner</label>
                        <input type="text" id="originalOwner" class="form-input" placeholder="Full name">
                    </div>
                </div>

                <div class="ldc-section">
                    <div class="ldc-section-title"><i class="fas fa-map-pin"></i> Map Location</div>
                    <div class="form-row">
                        <label class="form-label">Center Coordinates</label>
                        <div class="coord-row">
                            <input type="number" id="centerLat" class="form-input" placeholder="Latitude" step="any" readonly>
                            <input type="number" id="centerLng" class="form-input" placeholder="Longitude" step="any" readonly>
                            <button class="pick-btn" id="pickBtn" onclick="togglePick()"><i class="fas fa-crosshairs"></i> Pick</button>
                        </div>
                        <div style="font-size:11px;color:#999;margin-top:4px">Click "Pick" then click on the map</div>
                    </div>
                </div>

                <div class="ldc-section">
                    <div class="ldc-section-title"><i class="fas fa-ruler-combined"></i> Area & Division</div>
                    <div class="form-grid-2">
                        <div class="form-row">
                            <label class="form-label">Total Area (sqm)</label>
                            <input type="number" id="totalArea" class="form-input" placeholder="e.g. 50000" min="1" oninput="updateLotRows()">
                        </div>
                        <div class="form-row">
                            <label class="form-label">No. of Lots</label>
                            <input type="number" id="lotCount" class="form-input" placeholder="e.g. 45" min="1" max="500" oninput="updateLotRows()">
                        </div>
                    </div>
                    <div id="areaPerLotDisplay" style="font-size:12px;color:#666;padding:6px 8px;background:#f0f4ff;border-radius:6px;display:none">
                        Area per lot: <strong id="areaPerLotVal"></strong> sqm &nbsp;·&nbsp; <strong id="areaPerLotHa"></strong> ha
                    </div>
                </div>

                <div class="ldc-section">
                    <div class="ldc-section-title"><i class="fas fa-users"></i> Lot Owners
                        <span style="font-size:10px;color:#999;font-weight:400;margin-left:4px">(assign owner per lot)</span>
                    </div>
                    <div class="lot-rows" id="lotOwnerRows">
                        <div style="font-size:12px;color:#aaa;text-align:center;padding:20px">Enter number of lots above</div>
                    </div>
                </div>

                <button class="btn-primary" onclick="submitLSN()">
                    <i class="fas fa-save"></i> Save Land Survey
                </button>
                <button class="btn-secondary" onclick="resetForm()">
                    <i class="fas fa-redo"></i> Reset
                </button>
            </div>

            <!-- Surveys Tab -->
            <div class="tab-content" id="tab-surveys">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px">
                    <div style="font-size:13px;font-weight:700;color:#333">All Land Surveys</div>
                    <input type="text" id="lsnSearch" class="form-input" style="width:140px;font-size:12px" placeholder="Search LSN..." oninput="filterSurveys(this.value)">
                </div>
                <div id="surveyList">
                    <div style="text-align:center;color:#aaa;padding:30px;font-size:13px">Loading...</div>
                </div>
            </div>

            <!-- Stats Tab -->
            <div class="tab-content" id="tab-stats">
                <div id="statsPanel">
                    <div style="text-align:center;color:#aaa;padding:30px;font-size:13px">Loading...</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Lot Modal -->
<div id="editLotModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:white;border-radius:14px;padding:28px;width:360px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:16px;font-weight:700;color:#1a2744;margin-bottom:18px"><i class="fas fa-pencil-alt" style="margin-right:6px"></i>Edit Lot</h3>
        <input type="hidden" id="editLotId">
        <div class="form-row">
            <label class="form-label">Lot Number</label>
            <input type="number" id="editLotNumber" class="form-input" min="1">
        </div>
        <div class="form-row">
            <label class="form-label">Owner Name</label>
            <input type="text" id="editOwnerName" class="form-input">
        </div>
        <div class="form-row">
            <label class="form-label">Notes</label>
            <input type="text" id="editLotNotes" class="form-input" placeholder="Optional">
        </div>
        <div style="display:flex;gap:10px;margin-top:18px">
            <button onclick="closeEditModal()" style="flex:1;padding:10px;background:#f5f5f5;color:#555;border:none;border-radius:8px;cursor:pointer;font-size:13px">Cancel</button>
            <button onclick="saveLotEdit()" style="flex:1;padding:10px;background:#1a2744;color:white;border:none;border-radius:8px;cursor:pointer;font-size:13px;font-weight:700">Save</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const CSRF = document.querySelector('meta[name="csrf-token"]').content;
const isAdmin = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};

// ── Map Setup ──────────────────────────────────────────────────
const map = L.map('map').setView([15.9754, 120.5701], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
}).addTo(map);

let pickingMode = false;
let pickedMarker = null;
let surveyLayers = {}; // surveyId → { boundary, lots: {lotId → [layers]} }
let allSurveys = [];

// ── Coordinate picker ──────────────────────────────────────────
function togglePick() {
    pickingMode = !pickingMode;
    const btn = document.getElementById('pickBtn');
    const hint = document.getElementById('mapHint');
    if (pickingMode) {
        btn.classList.add('active');
        btn.innerHTML = '<i class="fas fa-times"></i> Cancel';
        map.getContainer().style.cursor = 'crosshair';
        hint.classList.remove('hide');
    } else {
        btn.classList.remove('active');
        btn.innerHTML = '<i class="fas fa-crosshairs"></i> Pick';
        map.getContainer().style.cursor = '';
        hint.classList.add('hide');
    }
}

map.on('click', function(e) {
    if (!pickingMode) return;
    const { lat, lng } = e.latlng;
    document.getElementById('centerLat').value = lat.toFixed(7);
    document.getElementById('centerLng').value = lng.toFixed(7);
    if (pickedMarker) map.removeLayer(pickedMarker);
    pickedMarker = L.marker([lat, lng], {
        icon: L.divIcon({ className: '', html: '<div style="background:#1a2744;color:white;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;white-space:nowrap">LSN Center</div>', iconAnchor: [40, 10] })
    }).addTo(map);
    togglePick();
});

// Hide hint after 4 seconds
setTimeout(() => document.getElementById('mapHint').classList.add('hide'), 4000);

// ── Lot owner rows ─────────────────────────────────────────────
function updateLotRows() {
    const count = parseInt(document.getElementById('lotCount').value) || 0;
    const area  = parseFloat(document.getElementById('totalArea').value) || 0;
    const container = document.getElementById('lotOwnerRows');
    const display   = document.getElementById('areaPerLotDisplay');

    if (count > 0 && area > 0) {
        const perLot = (area / count).toFixed(2);
        document.getElementById('areaPerLotVal').textContent = Number(perLot).toLocaleString();
        document.getElementById('areaPerLotHa').textContent  = (perLot / 10000).toFixed(4);
        display.style.display = 'block';
    } else {
        display.style.display = 'none';
    }

    if (count < 1 || count > 500) {
        container.innerHTML = '<div style="font-size:12px;color:#aaa;text-align:center;padding:20px">Enter a valid number of lots (1–500)</div>';
        return;
    }

    let html = '';
    for (let i = 1; i <= count; i++) {
        html += `<div class="lot-row">
            <div class="lot-num-label">${i}</div>
            <input type="text" class="form-input" id="owner_${i}" placeholder="Owner name" style="font-size:12px">
        </div>`;
    }
    container.innerHTML = html;
}

// ── Submit LSN ────────────────────────────────────────────────
async function submitLSN() {
    const lsn   = document.getElementById('lsnInput').value.trim();
    const owner = document.getElementById('originalOwner').value.trim();
    const lat   = parseFloat(document.getElementById('centerLat').value);
    const lng   = parseFloat(document.getElementById('centerLng').value);
    const area  = parseFloat(document.getElementById('totalArea').value);
    const count = parseInt(document.getElementById('lotCount').value);

    if (!lsn || !owner || !lat || !lng || !area || !count) {
        showAlert('error', 'Please fill in all fields and pick a map location.');
        return;
    }

    const lots = [];
    for (let i = 1; i <= count; i++) {
        const ownerVal = document.getElementById(`owner_${i}`)?.value.trim() || `Owner ${i}`;
        lots.push({ lot_number: i, owner_name: ownerVal });
    }

    try {
        const res = await apiFetch('/api/surveys', 'POST', { lsn, original_owner: owner, total_area: area, center_lat: lat, center_lng: lng, lot_count: count, lots });
        if (res.success) {
            showAlert('success', `LSN "${lsn}" saved with ${count} lots.`);
            resetForm();
            await loadSurveys();
            renderSurveyOnMap(res.survey);
        } else {
            showAlert('error', res.message || 'Failed to save.');
        }
    } catch(e) {
        showAlert('error', 'Error: ' + e.message);
    }
}

function resetForm() {
    ['lsnInput','originalOwner','centerLat','centerLng','totalArea','lotCount'].forEach(id => {
        document.getElementById(id).value = '';
    });
    document.getElementById('lotOwnerRows').innerHTML = '<div style="font-size:12px;color:#aaa;text-align:center;padding:20px">Enter number of lots above</div>';
    document.getElementById('areaPerLotDisplay').style.display = 'none';
    if (pickedMarker) { map.removeLayer(pickedMarker); pickedMarker = null; }
}

// ── Render a survey on map ────────────────────────────────────
function renderSurveyOnMap(survey) {
    if (surveyLayers[survey.id]) {
        Object.values(surveyLayers[survey.id].lots).forEach(layers => layers.forEach(l => map.removeLayer(l)));
        if (surveyLayers[survey.id].boundary) map.removeLayer(surveyLayers[survey.id].boundary);
    }
    surveyLayers[survey.id] = { boundary: null, lots: {} };

    const allCoords = [];

    (survey.lots || []).forEach(lot => {
        const polygons = JSON.parse(lot.polygons || '[]');
        surveyLayers[survey.id].lots[lot.id] = [];

        polygons.forEach(poly => {
            const layer = L.polygon(poly, {
                color: '#1a2744', fillColor: '#3a5a9a', fillOpacity: 0.35, weight: 1.5
            }).addTo(map);

            layer.on('click', () => openLotPopup(lot, layer));

            // Lot number label at center
            const center = layer.getBounds().getCenter();
            const label = L.marker(center, {
                icon: L.divIcon({
                    className: '',
                    html: `<div style="background:#1a2744;color:white;padding:2px 6px;border-radius:3px;font-size:10px;font-weight:700;cursor:pointer">${lot.lot_number}</div>`,
                    iconAnchor: [12, 8]
                })
            }).addTo(map);
            label.on('click', () => openLotPopup(lot, layer));

            surveyLayers[survey.id].lots[lot.id].push(layer, label);
            poly.forEach(c => allCoords.push(c));
        });
    });

    // LSN boundary (bounding box of all lots)
    if (allCoords.length > 0) {
        const lats = allCoords.map(c => c[0]);
        const lngs = allCoords.map(c => c[1]);
        const bounds = [[Math.min(...lats), Math.min(...lngs)], [Math.max(...lats), Math.max(...lngs)]];
        const boundary = L.rectangle(bounds, { color: '#e53935', weight: 2, fill: false, dashArray: '6,4' }).addTo(map);

        // LSN label at top-left
        const lsnLabel = L.marker([Math.max(...lats), (Math.min(...lngs) + Math.max(...lngs)) / 2], {
            icon: L.divIcon({
                className: '',
                html: `<div style="background:#e53935;color:white;padding:3px 8px;border-radius:4px;font-size:11px;font-weight:700;cursor:pointer">${survey.lsn}</div>`,
                iconAnchor: [30, 20]
            })
        }).addTo(map);
        lsnLabel.on('click', () => { map.fitBounds(bounds, { padding: [30, 30] }); selectSurvey(survey.id); });

        surveyLayers[survey.id].boundary = boundary;
        surveyLayers[survey.id].lots['_lsnLabel'] = [lsnLabel];
    }
}

// ── Lot popup ─────────────────────────────────────────────────
function openLotPopup(lot, layer) {
    const areaHa = (lot.area / 10000).toFixed(4);
    const editBtn = isAdmin ? `<button class="popup-edit-btn" onclick="openEditModal(${lot.id}, ${lot.lot_number}, '${escJs(lot.owner_name)}', '${escJs(lot.notes||'')}')">Edit Lot</button>` : '';
    const popup = L.popup({ maxWidth: 220 })
        .setLatLng(layer.getBounds().getCenter())
        .setContent(`
            <div class="popup-title">Lot ${lot.lot_number}</div>
            <div class="popup-row">Owner: <span>${lot.owner_name}</span></div>
            <div class="popup-row">Area: <span>${Number(lot.area).toLocaleString()} sqm</span></div>
            <div class="popup-row">Area: <span>${areaHa} ha</span></div>
            ${lot.notes ? `<div class="popup-row">Notes: <span>${lot.notes}</span></div>` : ''}
            ${editBtn}
        `)
        .openOn(map);
}

// ── Edit modal ────────────────────────────────────────────────
function openEditModal(lotId, lotNumber, ownerName, notes) {
    document.getElementById('editLotId').value = lotId;
    document.getElementById('editLotNumber').value = lotNumber;
    document.getElementById('editOwnerName').value = ownerName;
    document.getElementById('editLotNotes').value = notes;
    document.getElementById('editLotModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editLotModal').style.display = 'none';
}

async function saveLotEdit() {
    const id    = document.getElementById('editLotId').value;
    const num   = document.getElementById('editLotNumber').value;
    const owner = document.getElementById('editOwnerName').value.trim();
    const notes = document.getElementById('editLotNotes').value.trim();

    if (!owner) { alert('Owner name is required.'); return; }

    try {
        const res = await apiFetch(`/api/lots/${id}`, 'PUT', { lot_number: num, owner_name: owner, notes });
        if (res.success) {
            closeEditModal();
            map.closePopup();
            await loadSurveys();
        }
    } catch(e) {
        alert('Error saving lot: ' + e.message);
    }
}

// ── Load surveys ──────────────────────────────────────────────
async function loadSurveys() {
    try {
        const res = await apiFetch('/api/surveys', 'GET');
        allSurveys = res;

    // Clear old map layers
    Object.values(surveyLayers).forEach(s => {
        Object.values(s.lots).forEach(layers => layers.forEach(l => map.removeLayer(l)));
        if (s.boundary) map.removeLayer(s.boundary);
    });
    surveyLayers = {};

    allSurveys.forEach(s => renderSurveyOnMap(s));
    renderSurveyList(allSurveys);
    renderStats(allSurveys);
    } catch(e) {
        console.error('Failed to load surveys:', e.message);
    }
}

// ── Survey list ───────────────────────────────────────────────
function renderSurveyList(surveys) {
    const container = document.getElementById('surveyList');
    if (!surveys.length) {
        container.innerHTML = '<div style="text-align:center;color:#aaa;padding:30px;font-size:13px">No surveys yet.<br>Use the LDC tab to add one.</div>';
        return;
    }
    container.innerHTML = surveys.map(s => `
        <div class="lsn-item" onclick="selectSurvey(${s.id})" id="lsn-item-${s.id}">
            <div class="lsn-item-title">${s.lsn}</div>
            <div class="lsn-item-sub">${s.original_owner}</div>
            <div class="lsn-item-badges">
                <span class="badge">${s.lot_count} lots</span>
                <span class="badge">${Number(s.total_area).toLocaleString()} sqm</span>
                <span class="badge">${(s.total_area/10000).toFixed(3)} ha</span>
            </div>
        </div>
    `).join('');
}

function filterSurveys(q) {
    const filtered = allSurveys.filter(s =>
        s.lsn.toLowerCase().includes(q.toLowerCase()) ||
        s.original_owner.toLowerCase().includes(q.toLowerCase())
    );
    renderSurveyList(filtered);
}

function selectSurvey(id) {
    document.querySelectorAll('.lsn-item').forEach(el => el.classList.remove('selected'));
    const el = document.getElementById(`lsn-item-${id}`);
    if (el) el.classList.add('selected');

    // Zoom map to survey
    const layers = surveyLayers[id];
    if (layers) {
        const bounds = [];
        Object.values(layers.lots).forEach(ls => ls.forEach(l => {
            if (l.getBounds) bounds.push(...Object.values(l.getBounds()));
        }));
        // Fit to boundary
        if (layers.boundary) map.fitBounds(layers.boundary.getBounds(), { padding: [40, 40] });
    }

    // Show lot detail below
    const survey = allSurveys.find(s => s.id === id);
    if (!survey) return;
    showSurveyDetail(survey);
    switchTab('surveys');
}

function showSurveyDetail(survey) {
    const container = document.getElementById('surveyList');
    const areaPerLot = (survey.total_area / survey.lot_count).toFixed(2);
    container.innerHTML = `
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px">
            <button onclick="renderSurveyList(allSurveys)" style="background:#f0f0f0;border:none;border-radius:6px;padding:5px 10px;cursor:pointer;font-size:12px"><i class="fas fa-arrow-left"></i></button>
            <div style="font-size:14px;font-weight:700;color:#1a2744">${survey.lsn}</div>
        </div>
        <div class="ldc-section" style="margin-bottom:10px">
            <div class="stat-row"><span class="stat-lbl">Original Owner</span><span class="stat-val">${survey.original_owner}</span></div>
            <div class="stat-row"><span class="stat-lbl">Total Area</span><span class="stat-val">${Number(survey.total_area).toLocaleString()} sqm</span></div>
            <div class="stat-row"><span class="stat-lbl">Hectares</span><span class="stat-val">${(survey.total_area/10000).toFixed(4)} ha</span></div>
            <div class="stat-row"><span class="stat-lbl">Lots</span><span class="stat-val">${survey.lot_count}</span></div>
            <div class="stat-row"><span class="stat-lbl">Area/Lot</span><span class="stat-val">${Number(areaPerLot).toLocaleString()} sqm</span></div>
        </div>
        <div style="font-size:11px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px">Lots</div>
        ${(survey.lots || []).map(lot => `
        <div class="lot-list-item">
            <span class="lot-list-num">Lot ${lot.lot_number}</span>
            <span class="lot-list-owner">${lot.owner_name}</span>
            <span class="lot-list-area">${Number(lot.area).toLocaleString()} sqm</span>
            ${isAdmin ? `<button class="btn-sm btn-edit-sm" onclick="openEditModal(${lot.id}, ${lot.lot_number}, '${escJs(lot.owner_name)}', '${escJs(lot.notes||'')}')"><i class="fas fa-pencil-alt"></i></button>` : ''}
        </div>`).join('')}
        ${isAdmin ? `
        <button class="btn-secondary" style="margin-top:10px" onclick="deleteSurvey(${survey.id}, '${escJs(survey.lsn)}')">
            <i class="fas fa-trash" style="color:#e53935"></i> Delete Survey
        </button>` : ''}
    `;
}

async function deleteSurvey(id, lsn) {
    if (!confirm(`Delete survey "${lsn}" and all its lots?`)) return;
    const res = await apiFetch(`/api/surveys/${id}`, 'DELETE');
    if (res.success) await loadSurveys();
}

// ── Stats ─────────────────────────────────────────────────────
function renderStats(surveys) {
    const total     = surveys.length;
    const totalLots = surveys.reduce((s, v) => s + v.lot_count, 0);
    const totalArea = surveys.reduce((s, v) => s + parseFloat(v.total_area), 0);
    const totalHa   = (totalArea / 10000).toFixed(4);
    const avgLots   = total > 0 ? (totalLots / total).toFixed(1) : 0;
    const avgArea   = total > 0 ? Math.round(totalArea / totalLots || 0) : 0;

    document.getElementById('statsPanel').innerHTML = `
        <div class="stat-highlight">
            <div><div class="stat-highlight-lbl">Total Surveys</div><div class="stat-highlight-val">${total}</div></div>
            <div style="text-align:right"><div class="stat-highlight-lbl">Total Lots</div><div class="stat-highlight-val">${totalLots.toLocaleString()}</div></div>
        </div>
        <div class="ldc-section">
            <div class="ldc-section-title"><i class="fas fa-ruler-combined"></i> Area Computation</div>
            <div class="stat-row"><span class="stat-lbl">Total Area (sqm)</span><span class="stat-val">${Math.round(totalArea).toLocaleString()}</span></div>
            <div class="stat-row"><span class="stat-lbl">Total Area (ha)</span><span class="stat-val">${totalHa}</span></div>
            <div class="stat-row"><span class="stat-lbl">Avg lots per LSN</span><span class="stat-val">${avgLots}</span></div>
            <div class="stat-row"><span class="stat-lbl">Avg area per lot</span><span class="stat-val">${avgArea.toLocaleString()} sqm</span></div>
        </div>
        <div class="ldc-section">
            <div class="ldc-section-title"><i class="fas fa-list"></i> Per Survey</div>
            ${surveys.map(s => `
            <div class="stat-row" style="cursor:pointer" onclick="selectSurvey(${s.id});switchTab('surveys')">
                <span class="stat-lbl">${s.lsn}</span>
                <span class="stat-val">${s.lot_count} lots · ${(s.total_area/10000).toFixed(2)}ha</span>
            </div>`).join('')}
        </div>
    `;
}

// ── Tab switching ─────────────────────────────────────────────
function switchTab(name) {
    document.querySelectorAll('.tab-btn').forEach((b, i) => {
        const names = ['ldc', 'surveys', 'stats'];
        b.classList.toggle('active', names[i] === name);
    });
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.getElementById(`tab-${name}`).classList.add('active');
}

// ── Alert ─────────────────────────────────────────────────────
function showAlert(type, msg) {
    const el = document.getElementById('ldcAlert');
    el.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
    setTimeout(() => el.innerHTML = '', 4000);
}

// ── Helpers ───────────────────────────────────────────────────
function escJs(str) {
    return String(str || '').replace(/'/g, "\\'").replace(/"/g, '\\"');
}

async function apiFetch(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    };
    if (body) opts.body = JSON.stringify(body);
    const res = await fetch(url, opts);
    const data = await res.json();

    // Surface Laravel validation errors (422) as a readable message
    if (!res.ok) {
        if (data.errors) {
            // Flatten all field error messages into one string
            const messages = Object.values(data.errors).flat();
            const err = new Error(messages.join('\n'));
            err.validationErrors = data.errors;
            throw err;
        }
        throw new Error(data.message || `Request failed (${res.status})`);
    }
    return data;
}

// ── Init ──────────────────────────────────────────────────────
loadSurveys();
</script>
</body>
</html>
