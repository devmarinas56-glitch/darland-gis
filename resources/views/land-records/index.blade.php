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
        .info-panel { background: white; border-radius: 12px; padding: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); overflow-y: auto; }
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

        /* Lot Data Computation Panel */
        .comp-panel { background: white; border-radius: 12px; padding: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); display: flex; flex-direction: column; gap: 12px; }
        .comp-title { font-size: 13px; font-weight: 700; color: #1a2744; display: flex; align-items: center; gap: 6px; border-bottom: 1px solid #f0f0f0; padding-bottom: 10px; }
        .comp-section-label { font-size: 10px; font-weight: 700; color: #aaa; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; }
        .comp-stat-row { display: flex; justify-content: space-between; align-items: center; padding: 5px 0; border-bottom: 1px solid #f9f9f9; }
        .comp-stat-row:last-child { border-bottom: none; }
        .comp-stat-label { font-size: 12px; color: #555; display: flex; align-items: center; gap: 6px; }
        .comp-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
        .comp-stat-value { font-size: 13px; font-weight: 700; color: #333; }
        .comp-highlight { background: #f0f4ff; border-radius: 8px; padding: 10px 12px; display: flex; justify-content: space-between; align-items: center; }
        .comp-highlight-label { font-size: 11px; color: #666; }
        .comp-highlight-value { font-size: 18px; font-weight: 800; color: #1a2744; }
        .comp-highlight-unit { font-size: 10px; color: #999; }
        .comp-bar-wrap { margin-bottom: 2px; }
        .comp-bar-label-row { display: flex; justify-content: space-between; font-size: 11px; color: #666; margin-bottom: 3px; }
        .comp-bar-track { background: #f0f0f0; border-radius: 4px; height: 7px; overflow: hidden; }
        .comp-bar-fill { height: 100%; border-radius: 4px; transition: width 0.6s ease; }
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
        /* Layout only — modals removed */
        .overlay, .form-overlay { display: none; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo-section"><img src="{{ asset('images/Darlandicon.png') }}" alt="Logo"></div>
        <nav class="nav-menu">
            <a href="/dashboard" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer" class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item active"><i class="fas fa-file-alt"></i><span>Land Records</span></a>
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
                </div>

                <div id="compPanel">
                    <!-- Lot Data Computation Panel — built by JS -->
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

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const allLots = @json($allLots);
        const colorMap = { residential:'#4caf50', commercial:'#ff9800', agricultural:'#9c27b0', industrial:'#f44336' };
        const isAdmin = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};

        // ── Map Setup ──────────────────────────────────────────────
        const map = L.map('recordsMap').setView([15.9754, 120.5701], 15);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '© OpenStreetMap', maxZoom: 19 }).addTo(map);

        const lotLayers = {};

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

        // ── Lot Data Computation ───────────────────────────────────
        function computeStats(lots) {
            const total = lots.length;
            const totalArea = lots.reduce((s, l) => s + (parseFloat(l.area) || 0), 0);
            const totalHa = (totalArea / 10000).toFixed(4);

            // by type
            const types = { residential: 0, commercial: 0, agricultural: 0, industrial: 0 };
            const typeArea = { residential: 0, commercial: 0, agricultural: 0, industrial: 0 };
            // by status
            const statuses = { registered: 0, pending: 0, rejected: 0 };

            lots.forEach(l => {
                if (types[l.land_type] !== undefined) {
                    types[l.land_type]++;
                    typeArea[l.land_type] += parseFloat(l.area) || 0;
                }
                if (statuses[l.status] !== undefined) statuses[l.status]++;
            });

            return { total, totalArea: Math.round(totalArea), totalHa, types, typeArea, statuses };
        }

        function bar(value, max, color) {
            const pct = max > 0 ? Math.round((value / max) * 100) : 0;
            return `<div class="comp-bar-track"><div class="comp-bar-fill" style="width:${pct}%;background:${color}"></div></div>`;
        }

        function renderComputationPanel(lots) {
            const s = computeStats(lots);
            const typeColors = { residential:'#4caf50', commercial:'#ff9800', agricultural:'#9c27b0', industrial:'#f44336' };
            const statusColors = { registered:'#1976d2', pending:'#ff9800', rejected:'#e53935' };

            let html = `
            <div class="comp-panel">
                <div class="comp-title"><i class="fas fa-calculator"></i> Lot Data Computation</div>

                <div class="comp-highlight">
                    <div>
                        <div class="comp-highlight-label">Total Lots</div>
                        <div class="comp-highlight-value">${s.total.toLocaleString()}</div>
                    </div>
                    <div style="text-align:right">
                        <div class="comp-highlight-label">Total Area</div>
                        <div class="comp-highlight-value">${s.totalHa} <span class="comp-highlight-unit">ha</span></div>
                        <div class="comp-highlight-unit">${s.totalArea.toLocaleString()} sqm</div>
                    </div>
                </div>

                <div>
                    <div class="comp-section-label">By Land Type</div>
                    ${Object.entries(s.types).map(([type, count]) => `
                    <div class="comp-bar-wrap">
                        <div class="comp-bar-label-row">
                            <span style="display:flex;align-items:center;gap:5px">
                                <span class="comp-dot" style="background:${typeColors[type]}"></span>
                                ${type.charAt(0).toUpperCase()+type.slice(1)}
                            </span>
                            <span>${count} lots &nbsp;·&nbsp; ${Math.round(s.typeArea[type]).toLocaleString()} sqm</span>
                        </div>
                        ${bar(count, s.total, typeColors[type])}
                    </div>`).join('')}
                </div>

                <div>
                    <div class="comp-section-label">By Status</div>
                    ${Object.entries(s.statuses).map(([status, count]) => `
                    <div class="comp-stat-row">
                        <span class="comp-stat-label">
                            <span class="comp-dot" style="background:${statusColors[status]}"></span>
                            ${status.charAt(0).toUpperCase()+status.slice(1)}
                        </span>
                        <span class="comp-stat-value">${count}</span>
                    </div>`).join('')}
                </div>

                <div>
                    <div class="comp-section-label">Area Summary</div>
                    <div class="comp-stat-row">
                        <span class="comp-stat-label"><i class="fas fa-ruler-combined" style="color:#999;font-size:11px"></i> Total sqm</span>
                        <span class="comp-stat-value">${s.totalArea.toLocaleString()}</span>
                    </div>
                    <div class="comp-stat-row">
                        <span class="comp-stat-label"><i class="fas fa-seedling" style="color:#999;font-size:11px"></i> Total hectares</span>
                        <span class="comp-stat-value">${s.totalHa}</span>
                    </div>
                    <div class="comp-stat-row">
                        <span class="comp-stat-label"><i class="fas fa-chart-bar" style="color:#999;font-size:11px"></i> Avg area/lot</span>
                        <span class="comp-stat-value">${s.total > 0 ? Math.round(s.totalArea / s.total).toLocaleString() : 0} sqm</span>
                    </div>
                </div>
            </div>`;

            document.getElementById('compPanel').innerHTML = html;
        }

        // Initial render with all lots
        renderComputationPanel(allLots);

        // ── Lot Selection ─────────────────────────────────────────
        function selectLot(landId) {
            const lot = allLots.find(l => l.land_id === landId);
            if (!lot) return;
            Object.entries(lotLayers).forEach(([id, layer]) => {
                const c = colorMap[allLots.find(l=>l.land_id===id)?.land_type]||'#4caf50';
                layer.setStyle(id===landId ? {color:'#1a2744',fillColor:'#1a2744',fillOpacity:0.6,weight:3} : {color:c,fillColor:c,fillOpacity:0.4,weight:2});
            });
            if (lotLayers[landId]) map.fitBounds(lotLayers[landId].getBounds(), {padding:[30,30]});
            document.querySelectorAll('#recordsTable tr').forEach(tr => tr.classList.toggle('selected', tr.dataset.id===landId));

            // Update computation panel to show selected lot's computed data
            const badgeMap = {residential:'badge-residential',commercial:'badge-commercial',agricultural:'badge-agricultural',industrial:'badge-industrial'};
            const areaHa = lot.area ? (lot.area / 10000).toFixed(4) : '—';
            document.getElementById('compPanel').innerHTML = `
            <div class="comp-panel">
                <div class="comp-title" style="justify-content:space-between">
                    <span><i class="fas fa-map-pin"></i> Selected Lot</span>
                    <button onclick="clearSel()" style="background:#f0f0f0;border:none;border-radius:50%;width:22px;height:22px;cursor:pointer;font-size:11px;display:flex;align-items:center;justify-content:center">✕</button>
                </div>

                <div class="comp-highlight">
                    <div>
                        <div class="comp-highlight-label">Land ID</div>
                        <div class="comp-highlight-value" style="font-size:15px">${lot.land_id}</div>
                    </div>
                    <div style="text-align:right">
                        <span class="land-type-badge ${badgeMap[lot.land_type]}">${lot.land_type.charAt(0).toUpperCase()+lot.land_type.slice(1)}</span>
                    </div>
                </div>

                <div>
                    <div class="comp-section-label">Owner</div>
                    <div style="font-size:14px;font-weight:700;color:#333">${lot.owner_name}</div>
                    <div style="font-size:12px;color:#888;margin-top:2px">${lot.location}</div>
                </div>

                <div>
                    <div class="comp-section-label">Computed Area</div>
                    <div class="comp-stat-row">
                        <span class="comp-stat-label"><i class="fas fa-ruler-combined" style="color:#999;font-size:11px"></i> Square meters</span>
                        <span class="comp-stat-value">${lot.area ? Number(lot.area).toLocaleString() : '—'} sqm</span>
                    </div>
                    <div class="comp-stat-row">
                        <span class="comp-stat-label"><i class="fas fa-seedling" style="color:#999;font-size:11px"></i> Hectares</span>
                        <span class="comp-stat-value">${areaHa} ha</span>
                    </div>
                </div>

                <div>
                    <div class="comp-section-label">Status & Date</div>
                    <div class="comp-stat-row">
                        <span class="comp-stat-label">Status</span>
                        <span class="comp-stat-value">${lot.status ? lot.status.charAt(0).toUpperCase()+lot.status.slice(1) : '—'}</span>
                    </div>
                    <div class="comp-stat-row">
                        <span class="comp-stat-label">Date Registered</span>
                        <span class="comp-stat-value" style="font-size:12px">${lot.date_registered ?? '—'}</span>
                    </div>
                </div>

                <div class="info-actions">
                    <button class="btn-view" onclick="clearSel()"><i class="fas fa-arrow-left"></i> Back to Stats</button>
                    ${isAdmin ? `<button class="btn-edit"><i class="fas fa-pencil-alt"></i> Edit</button>` : ''}
                </div>
            </div>`;
        }

        function clearSel() {
            Object.entries(lotLayers).forEach(([id,layer]) => { const c=colorMap[allLots.find(l=>l.land_id===id)?.land_type]||'#4caf50'; layer.setStyle({color:c,fillColor:c,fillOpacity:0.4,weight:2}); });
            document.querySelectorAll('#recordsTable tr').forEach(tr=>tr.classList.remove('selected'));
            renderComputationPanel(allLots);
        }

        function filterTable(q) {
            document.querySelectorAll('#recordsTable tbody tr').forEach(tr => { tr.style.display = tr.textContent.toLowerCase().includes(q.toLowerCase()) ? '' : 'none'; });
        }
    </script>
</body>
</html>

