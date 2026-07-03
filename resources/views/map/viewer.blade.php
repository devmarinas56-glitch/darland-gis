<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Map Viewer - Land GIS</title>
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
        .main-content { flex: 1; margin-left: 200px; display: flex; flex-direction: column; height: 100vh; }
        .top-bar { background: white; padding: 12px 30px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08); flex-shrink: 0; }
        .search-box { flex: 1; max-width: 450px; position: relative; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #999; font-size: 14px; }
        .search-input { width: 100%; padding: 9px 15px 9px 36px; border: 1px solid #e0e0e0; border-radius: 20px; font-size: 13px; background: #f9f9f9; }
        .top-right { display: flex; align-items: center; gap: 18px; }
        .bell-icon { font-size: 18px; color: #666; cursor: pointer; }
        .user-info { display: flex; align-items: center; gap: 8px; }
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: #1a2744; display: flex; align-items: center; justify-content: center; color: white; font-weight: 600; font-size: 13px; }
        .user-name { font-size: 13px; font-weight: 600; color: #333; }
        .user-role-label { font-size: 11px; color: #999; }
        .content-area { flex: 1; padding: 20px 25px; display: flex; flex-direction: column; overflow: hidden; }
        .page-title { font-size: 20px; font-weight: 700; color: #333; margin-bottom: 15px; }
        .map-wrapper { flex: 1; background: white; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); position: relative; min-height: 500px; }
        #map { width: 100%; height: 100%; min-height: 500px; }
        .map-toolbar { position: absolute; top: 12px; right: 12px; z-index: 999; display: flex; gap: 8px; }
        .tool-btn { padding: 9px 16px; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 7px; box-shadow: 0 2px 8px rgba(0,0,0,0.2); transition: all 0.3s; }
        .tool-btn.draw { background: #1a2744; }
        .tool-btn.draw:hover { background: #2d4070; }
        .tool-btn.cancel { background: #c62828; }
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
        .btn-cancel { padding: 12px 28px; background: #f5f5f5; color: #555; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-delete { padding: 12px 28px; background: #c62828; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .form-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
        .form-overlay.show { display: flex; }
        .form-card { background: white; border-radius: 16px; padding: 30px; max-width: 500px; width: 90%; box-shadow: 0 10px 40px rgba(0,0,0,0.2); max-height: 90vh; overflow-y: auto; }
        .form-card h3 { font-size: 19px; font-weight: 700; color: #333; margin-bottom: 4px; }
        .form-card p { font-size: 13px; color: #888; margin-bottom: 20px; }
        .form-row { margin-bottom: 14px; }
        .form-row label { font-size: 13px; font-weight: 600; color: #444; display: block; margin-bottom: 5px; }
        .form-row input, .form-row select, .form-row textarea { width: 100%; padding: 10px 13px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; color: #333; }
        .form-row input:focus, .form-row select:focus { outline: none; border-color: #1a2744; }
        .form-row textarea { resize: vertical; min-height: 70px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-actions { display: flex; gap: 12px; justify-content: flex-end; margin-top: 18px; }
        .btn-submit { padding: 11px 28px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .btn-discard { padding: 11px 18px; background: #ffebee; color: #c62828; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; border-radius: 8px; padding: 10px 14px; font-size: 13px; margin-bottom: 15px; display: none; }
        /* Popup styles */
        .lot-popup h4 { font-size: 14px; font-weight: 700; margin-bottom: 6px; }
        .lot-popup p { font-size: 12px; color: #555; margin-bottom: 4px; }
        .popup-actions { display: flex; gap: 8px; margin-top: 10px; }
        .popup-btn { padding: 6px 14px; border: none; border-radius: 6px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .popup-edit { background: #1a2744; color: white; }
        .popup-delete { background: #ffebee; color: #c62828; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo-section"><img src="{{ asset('images/DarlandIcon.png') }}" alt="Logo"></div>
        <nav class="nav-menu">
            <a href="/dashboard" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer" class="nav-item active"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item"><i class="fas fa-file-alt"></i><span>Land Records</span></a>
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
            <div class="search-box"><i class="fas fa-search"></i><input type="text" class="search-input" placeholder="Search documents..."></div>
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
            <h1 class="page-title">Map Viewer</h1>
            <div class="map-wrapper">
                <div id="map"></div>
                <div class="map-toolbar">
                    <button class="tool-btn draw" id="drawBtn" onclick="toggleDraw()">
                        <i class="fas fa-draw-polygon"></i> Draw Plot
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Overlap Warning Modal -->
    <div class="overlay" id="overlapModal">
        <div class="modal">
            <div class="modal-icon"><i class="fas fa-exclamation-triangle" style="color:#f57c00"></i></div>
            <h3>Land Already Claimed</h3>
            <p id="overlapMsg">This area overlaps with an existing land claim. You cannot claim an already claimed spot.</p>
            <div class="modal-btns">
                <button class="btn-cancel" onclick="cancelDraw()">OK</button>
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

    <!-- Delete Confirm Modal -->
    <div class="overlay" id="deleteModal">
        <div class="modal">
            <div class="modal-icon"><i class="fas fa-trash" style="color:#c62828"></i></div>
            <h3>Delete This Plot?</h3>
            <p>This will permanently remove this land claim. This action cannot be undone.</p>
            <div class="modal-btns">
                <button class="btn-cancel" onclick="document.getElementById('deleteModal').classList.remove('show')">Cancel</button>
                <button class="btn-delete" onclick="confirmDelete()">Delete</button>
            </div>
        </div>
    </div>

    <!-- Land Info Form Modal -->
    <div class="form-overlay" id="landFormModal">
        <div class="form-card">
            <h3 id="formTitle">Land Information</h3>
            <p id="formSubtitle">Fill in the details for your claimed plot</p>
            <div class="alert-error" id="formErrors"></div>
            <form id="landInfoForm">
                @csrf
                <input type="hidden" name="geojson" id="geojsonInput">
                <input type="hidden" name="area" id="areaHidden">
                <input type="hidden" id="editLotId">
                <div class="form-grid">
                    <div class="form-row">
                        <label>Owner Name</label>
                        <input type="text" name="owner_name" id="f_owner" placeholder="Full name" required>
                    </div>
                    <div class="form-row">
                        <label>Land ID / Lot No.</label>
                        <input type="text" name="land_id" id="f_land_id" placeholder="e.g. 10293" required>
                    </div>
                </div>
                <div class="form-row">
                    <label>Barangay</label>
                    <input type="text" name="barangay" id="f_barangay" placeholder="e.g. Anonas" required>
                </div>
                <div class="form-row">
                    <label>Full Location</label>
                    <input type="text" name="location" id="f_location" placeholder="e.g. Brgy. Anonas, Urdaneta City" required>
                </div>
                <div class="form-grid">
                    <div class="form-row">
                        <label>Land Type</label>
                        <select name="land_type" id="f_type">
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
                    <textarea name="notes" id="f_notes" placeholder="Additional information..."></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-discard" onclick="closeLandForm()">Cancel</button>
                    <button type="submit" class="btn-submit" id="formSubmitBtn">Submit</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
    <script>
        const CSRF = '{{ csrf_token() }}';
        const isAdmin = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};
        const currentUserId = {{ auth()->id() }};
        const colorMap = { residential:'#4caf50', commercial:'#ff9800', agricultural:'#9c27b0', industrial:'#f44336' };

        const map = L.map('map').setView([15.9754, 120.5701], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution:'© OpenStreetMap', maxZoom:19 }).addTo(map);

        const drawnItems = new L.FeatureGroup().addTo(map);
        const existingLayers = {};
        let drawingMode = false;
        let currentLayer = null;
        let currentArea = 0;
        let editingLotId = null;
        let deleteLotId = null;

        const drawControl = new L.Control.Draw({
            draw: {
                polygon: { allowIntersection: false, showArea: true, shapeOptions: { color:'#1a2744', fillColor:'#1a2744', fillOpacity:0.3 } },
                polyline:false, rectangle:false, circle:false, circlemarker:false, marker:false
            },
            edit: { featureGroup: drawnItems }
        });

        // Load all existing lots
        function loadLots() {
            fetch('/api/lots', { headers:{ 'Accept':'application/json' } })
            .then(r => r.json())
            .then(lots => {
                // Clear existing
                Object.values(existingLayers).forEach(({polygon, marker}) => {
                    map.removeLayer(polygon);
                    if (marker) map.removeLayer(marker);
                });
                Object.keys(existingLayers).forEach(k => delete existingLayers[k]);

                lots.forEach(lot => {
                    if (!lot.geojson) return;
                    try {
                        const coords = JSON.parse(lot.geojson);
                        const color = colorMap[lot.land_type] || '#4caf50';
                        const polygon = L.polygon(coords[0].map(c => [c[1], c[0]]), {
                            color, fillColor: color, fillOpacity: 0.4, weight: 2
                        }).addTo(map);

                        const canEdit = isAdmin || lot.user_id === currentUserId;
                        const popupHtml = `
                            <div class="lot-popup">
                                <h4>Lot ${lot.land_id}</h4>
                                <p><b>Owner:</b> ${lot.owner_name}</p>
                                <p><b>Location:</b> ${lot.location}</p>
                                <p><b>Type:</b> ${lot.land_type}</p>
                                <p><b>Area:</b> ${lot.area ?? '—'} sqm</p>
                                <p><b>Status:</b> ${lot.status}</p>
                                ${canEdit ? `<div class="popup-actions">
                                    <button class="popup-btn popup-edit" onclick="openEdit('${lot.land_id}')">Edit</button>
                                    <button class="popup-btn popup-delete" onclick="openDelete('${lot.land_id}')">Delete</button>
                                </div>` : ''}
                            </div>`;

                        polygon.bindPopup(popupHtml, { maxWidth: 280 });

                        const center = polygon.getBounds().getCenter();
                        const label = L.divIcon({
                            className:'',
                            html:`<div style="background:${color};color:white;padding:2px 6px;border-radius:4px;font-size:11px;font-weight:700">${lot.land_id}</div>`,
                            iconAnchor:[20,10]
                        });
                        const marker = L.marker(center, { icon: label }).addTo(map);

                        existingLayers[lot.land_id] = { polygon, marker, lot };
                    } catch(e) {}
                });
            });
        }

        loadLots();

        // Draw toggle
        function toggleDraw() {
            const btn = document.getElementById('drawBtn');
            if (!drawingMode) {
                drawingMode = true;
                btn.innerHTML = '<i class="fas fa-times"></i> Cancel';
                btn.classList.add('cancel');
                btn.classList.remove('draw');
                map.addControl(drawControl);
                new L.Draw.Polygon(map, drawControl.options.draw.polygon).enable();
            } else {
                drawingMode = false;
                btn.innerHTML = '<i class="fas fa-draw-polygon"></i> Draw Plot';
                btn.classList.remove('cancel');
                btn.classList.add('draw');
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
            btn.innerHTML = '<i class="fas fa-draw-polygon"></i> Draw Plot';
            btn.classList.remove('cancel'); btn.classList.add('draw');
            map.removeControl(drawControl);

            // Check overlap
            const geojson = JSON.stringify([latlngs.map(ll => [ll.lng, ll.lat])]);
            fetch('/api/check-overlap', {
                method: 'POST',
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
                body: JSON.stringify({ geojson })
            })
            .then(r => r.json())
            .then(data => {
                if (data.overlaps) {
                    document.getElementById('overlapMsg').textContent =
                        `This area overlaps with Lot ${data.lot_id} owned by ${data.owner}. You cannot claim an already claimed spot.`;
                    document.getElementById('overlapModal').classList.add('show');
                    drawnItems.removeLayer(currentLayer);
                    currentLayer = null;
                } else {
                    document.getElementById('confirmModal').classList.add('show');
                }
            });
        });

        function cancelDraw() {
            document.getElementById('confirmModal').classList.remove('show');
            document.getElementById('overlapModal').classList.remove('show');
            if (currentLayer) { drawnItems.removeLayer(currentLayer); currentLayer = null; }
        }

        function confirmDraw() {
            document.getElementById('confirmModal').classList.remove('show');
            const latlngs = currentLayer.getLatLngs()[0];
            document.getElementById('geojsonInput').value = JSON.stringify([latlngs.map(ll => [ll.lng, ll.lat])]);
            document.getElementById('areaHidden').value = currentArea;
            document.getElementById('areaDisplay').value = currentArea;
            editingLotId = null;
            document.getElementById('formTitle').textContent = 'Land Information';
            document.getElementById('formSubtitle').textContent = 'Fill in the details for your claimed plot';
            document.getElementById('formSubmitBtn').textContent = 'Submit';
            document.getElementById('f_land_id').readOnly = false;
            clearForm();
            document.getElementById('landFormModal').classList.add('show');
        }

        function closeLandForm() {
            document.getElementById('landFormModal').classList.remove('show');
            if (currentLayer && !editingLotId) { drawnItems.removeLayer(currentLayer); currentLayer = null; }
        }

        // Edit
        function openEdit(landId) {
            const entry = existingLayers[landId];
            if (!entry) return;
            const lot = entry.lot;
            editingLotId = landId;
            document.getElementById('formTitle').textContent = 'Edit Land Record';
            document.getElementById('formSubtitle').textContent = 'Update the details for this plot';
            document.getElementById('formSubmitBtn').textContent = 'Update';
            document.getElementById('f_owner').value = lot.owner_name;
            document.getElementById('f_land_id').value = lot.land_id;
            document.getElementById('f_land_id').readOnly = true;
            document.getElementById('f_barangay').value = lot.barangay;
            document.getElementById('f_location').value = lot.location;
            document.getElementById('f_type').value = lot.land_type;
            document.getElementById('areaDisplay').value = lot.area || '';
            document.getElementById('f_notes').value = lot.notes || '';
            document.getElementById('geojsonInput').value = lot.geojson || '';
            document.getElementById('landFormModal').classList.add('show');
        }

        // Delete
        function openDelete(landId) {
            deleteLotId = landId;
            document.getElementById('deleteModal').classList.add('show');
        }

        function confirmDelete() {
            if (!deleteLotId) return;
            fetch(`/land-records/${deleteLotId}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
            })
            .then(r => r.json())
            .then(data => {
                document.getElementById('deleteModal').classList.remove('show');
                if (data.success) {
                    loadLots();
                    deleteLotId = null;
                }
            });
        }

        // Form submit (create or update)
        document.getElementById('landInfoForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const errDiv = document.getElementById('formErrors');
            errDiv.style.display = 'none';

            const isEdit = !!editingLotId;
            const url = isEdit ? `/land-records/${editingLotId}` : '/land-records';
            const method = isEdit ? 'PUT' : 'POST';

            const body = {
                owner_name: document.getElementById('f_owner').value,
                land_id:    document.getElementById('f_land_id').value,
                barangay:   document.getElementById('f_barangay').value,
                location:   document.getElementById('f_location').value,
                land_type:  document.getElementById('f_type').value,
                area:       document.getElementById('areaHidden').value || document.getElementById('areaDisplay').value,
                notes:      document.getElementById('f_notes').value,
                geojson:    document.getElementById('geojsonInput').value,
            };

            fetch(url, {
                method,
                headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept':'application/json' },
                body: JSON.stringify(body)
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('landFormModal').classList.remove('show');
                    if (currentLayer) { drawnItems.removeLayer(currentLayer); currentLayer = null; }
                    editingLotId = null;
                    loadLots();
                } else if (data.errors) {
                    errDiv.innerHTML = Object.values(data.errors).flat().join('<br>');
                    errDiv.style.display = 'block';
                } else {
                    errDiv.innerHTML = data.message || 'An error occurred.';
                    errDiv.style.display = 'block';
                }
            });
        });

        function clearForm() {
            ['f_owner','f_land_id','f_barangay','f_location','f_notes'].forEach(id => document.getElementById(id).value = '');
            document.getElementById('f_type').value = 'residential';
        }

        function calcArea(latlngs) {
            if (!latlngs || latlngs.length < 3) return 0;
            let area = 0; const R = 6371000;
            for (let i = 0; i < latlngs.length; i++) {
                const j = (i+1) % latlngs.length;
                const xi = latlngs[i].lng*Math.PI/180*R*Math.cos(latlngs[i].lat*Math.PI/180);
                const yi = latlngs[i].lat*Math.PI/180*R;
                const xj = latlngs[j].lng*Math.PI/180*R*Math.cos(latlngs[j].lat*Math.PI/180);
                const yj = latlngs[j].lat*Math.PI/180*R;
                area += xi*yj - xj*yi;
            }
            return Math.abs(area/2);
        }
    </script>
</body>
</html>
