<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Land Records - DarLand GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, sans-serif; background: #e8e8e8; display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar { width: 200px; background: #1a2744; display: flex; flex-direction: column; position: fixed; height: 100vh; left: 0; top: 0; z-index: 1000; }
        .logo-section { padding: 20px 15px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-section img { width: 50px; height: 50px; }
        .nav-menu { flex: 1; padding: 15px 0; }
        .nav-item { display: flex; align-items: center; padding: 14px 20px; color: rgba(255,255,255,0.7); text-decoration: none; gap: 12px; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s; }
        .nav-item i { font-size: 16px; width: 18px; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.15); color: white; }
        .logout-section { padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn { width: 100%; padding: 11px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7); border-radius: 6px; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; text-transform: uppercase; text-decoration: none; transition: all 0.2s; }
        .logout-btn:hover { background: rgba(255,255,255,0.2); color: white; }

        /* ── Layout ── */
        .main-content { flex: 1; margin-left: 200px; display: flex; flex-direction: column; height: 100vh; overflow: hidden; }
        .top-bar { background: white; padding: 12px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08); flex-shrink: 0; }
        .page-title { font-size: 16px; font-weight: 700; color: #1a2744; }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; background: #1a2744; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 12px; }
        .user-name { font-size: 13px; font-weight: 600; color: #333; }
        .user-role-label { font-size: 11px; color: #999; }

        /* ── Workspace ── */
        .workspace { display: grid; grid-template-columns: 1fr 340px; flex: 1; overflow: hidden; }
        #map { width: 100%; height: 100%; }
        .map-wrap { position: relative; height: 100%; }
        .map-hint { position: absolute; bottom: 12px; left: 50%; transform: translateX(-50%); background: rgba(26,39,68,0.85); color: white; padding: 7px 16px; border-radius: 20px; font-size: 12px; z-index: 800; pointer-events: none; transition: opacity 0.3s; }
        .map-hint.hide { opacity: 0; }

        /* ── Right panel ── */
        .right-panel { background: #f4f5f7; border-left: 1px solid #e0e0e0; display: flex; flex-direction: column; overflow: hidden; }
        .panel-tabs { display: flex; border-bottom: 2px solid #e0e0e0; background: white; flex-shrink: 0; }
        .tab-btn { flex: 1; padding: 11px 4px; font-size: 11px; font-weight: 600; color: #888; border: none; background: none; cursor: pointer; border-bottom: 2px solid transparent; margin-bottom: -2px; transition: all 0.2s; text-transform: uppercase; letter-spacing: 0.3px; }
        .tab-btn.active { color: #1a2744; border-bottom-color: #1a2744; }
        .tab-content { flex: 1; overflow-y: auto; padding: 14px; display: none; }
        .tab-content.active { display: block; }

        /* ── Mode toggle ── */
        .mode-toggle { display: flex; background: #e8edf5; border-radius: 8px; padding: 3px; margin-bottom: 12px; }
        .mode-btn { flex: 1; padding: 7px 6px; border: none; background: none; border-radius: 6px; font-size: 11.5px; font-weight: 600; color: #666; cursor: pointer; transition: all 0.15s; display: flex; align-items: center; justify-content: center; gap: 5px; }
        .mode-btn.active { background: #1a2744; color: white; box-shadow: 0 1px 4px rgba(0,0,0,0.2); }

        /* ── Draw mode panel ── */
        .draw-steps { display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; }
        .draw-step { background: white; border-radius: 8px; padding: 10px 12px; border: 2px solid #e8edf5; transition: border-color 0.2s; }
        .draw-step.active-step { border-color: #1a2744; }
        .draw-step.done-step { border-color: #4caf50; opacity: 0.7; }
        .step-header { display: flex; align-items: center; gap: 8px; }
        .step-num { width: 22px; height: 22px; border-radius: 50%; background: #e8edf5; color: #1a2744; font-size: 11px; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .draw-step.active-step .step-num { background: #1a2744; color: white; }
        .draw-step.done-step .step-num { background: #4caf50; color: white; }
        .step-title { font-size: 12px; font-weight: 700; color: #1a2744; }
        .step-desc { font-size: 11px; color: #888; margin-top: 3px; margin-left: 30px; }

        /* drawn lots list */
        .drawn-lots { margin-top: 8px; display: flex; flex-direction: column; gap: 6px; max-height: 180px; overflow-y: auto; }
        .drawn-lot-row { background: #f9fafb; border: 1px solid #e8edf5; border-radius: 7px; padding: 8px 10px; display: flex; align-items: center; gap: 8px; }
        .drawn-lot-num { width: 26px; height: 26px; background: #1a2744; color: white; border-radius: 5px; font-size: 11px; font-weight: 700; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .drawn-lot-input { flex: 1; padding: 5px 8px; border: 1px solid #ddd; border-radius: 5px; font-size: 12px; color: #333; }
        .drawn-lot-input:focus { outline: none; border-color: #1a2744; }
        .drawn-lot-area { font-size: 10.5px; color: #aaa; white-space: nowrap; }
        .drawn-lot-del { background: none; border: none; color: #ddd; cursor: pointer; font-size: 13px; padding: 0 2px; }
        .drawn-lot-del:hover { color: #e53935; }

        /* ── Shared form styles ── */
        .ldc-section { background: white; border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; box-shadow: 0 1px 4px rgba(0,0,0,0.06); }
        .ldc-section-title { font-size: 11px; font-weight: 700; color: #1a2744; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; display: flex; align-items: center; gap: 5px; }
        .form-row { margin-bottom: 8px; }
        .form-label { font-size: 11px; font-weight: 600; color: #555; display: block; margin-bottom: 3px; }
        .form-input { width: 100%; padding: 7px 10px; border: 1px solid #ddd; border-radius: 6px; font-size: 12.5px; color: #333; }
        .form-input:focus { outline: none; border-color: #1a2744; }
        .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .coord-row { display: flex; gap: 6px; align-items: flex-end; }
        .coord-row .form-input { flex: 1; }
        .pick-btn { padding: 7px 10px; background: #1a2744; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 12px; white-space: nowrap; display: flex; align-items: center; gap: 4px; }
        .pick-btn:hover { background: #2d4070; }
        .pick-btn.active { background: #e53935; }
        .lot-rows { max-height: 200px; overflow-y: auto; }
        .lot-row { display: grid; grid-template-columns: 36px 1fr; gap: 6px; margin-bottom: 6px; align-items: center; }
        .lot-num-label { font-size: 11px; font-weight: 700; color: #1a2744; text-align: center; background: #e8edf5; border-radius: 4px; padding: 6px 2px; }
        .area-display { background: #f0f4ff; border-radius: 6px; padding: 6px 10px; font-size: 11.5px; color: #1a2744; margin-top: 6px; display: none; }
        .btn-primary { width: 100%; padding: 10px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: background 0.15s; }
        .btn-primary:hover { background: #2d4070; }
        .btn-secondary { width: 100%; padding: 8px; background: white; color: #1a2744; border: 1px solid #1a2744; border-radius: 8px; font-size: 12.5px; font-weight: 600; cursor: pointer; margin-top: 6px; }
        .btn-secondary:hover { background: #f0f4ff; }
        .btn-draw { width: 100%; padding: 10px; background: #2e7d32; color: white; border: none; border-radius: 8px; font-size: 12.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px; transition: background 0.15s; margin-bottom: 6px; }
        .btn-draw:hover { background: #1b5e20; }
        .btn-draw.drawing { background: #e53935; }
        .btn-draw.drawing:hover { background: #c62828; }

        /* ── Survey list / stats ── */
        .stat-highlight { background: #1a2744; color: white; border-radius: 10px; padding: 12px 14px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .stat-highlight-val { font-size: 20px; font-weight: 800; }
        .stat-highlight-lbl { font-size: 11px; opacity: 0.8; }
        .stat-row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f0f0f0; font-size: 12.5px; }
        .stat-row:last-child { border-bottom: none; }
        .stat-lbl { color: #666; }
        .stat-val { font-weight: 700; color: #333; }
        .lsn-item { background: white; border-radius: 8px; padding: 10px 12px; margin-bottom: 8px; cursor: pointer; border: 2px solid transparent; transition: all 0.15s; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
        .lsn-item:hover { border-color: #1a2744; }
        .lsn-item.selected { border-color: #1a2744; background: #f0f4ff; }
        .lsn-item-title { font-size: 13px; font-weight: 700; color: #1a2744; }
        .lsn-item-sub { font-size: 11px; color: #888; margin-top: 2px; }
        .lsn-item-badges { display: flex; gap: 5px; margin-top: 5px; flex-wrap: wrap; }
        .badge { padding: 2px 7px; border-radius: 10px; font-size: 10.5px; font-weight: 600; background: #e8edf5; color: #1a2744; }
        .lot-list-item { display: flex; justify-content: space-between; align-items: center; padding: 7px 10px; border-radius: 6px; margin-bottom: 4px; background: #f9f9f9; border: 1px solid #eee; }
        .lot-list-num { font-size: 11.5px; font-weight: 700; color: #1a2744; background: #e8edf5; padding: 2px 7px; border-radius: 4px; }
        .lot-list-owner { font-size: 12px; color: #444; flex: 1; padding: 0 8px; }
        .lot-list-area { font-size: 11px; color: #888; }
        .btn-sm { padding: 4px 9px; border-radius: 5px; font-size: 11px; cursor: pointer; border: none; }
        .btn-edit-sm { background: #1976d2; color: white; }
        .btn-edit-sm:hover { background: #1565c0; }
        .alert { padding: 8px 12px; border-radius: 7px; font-size: 12.5px; margin-bottom: 8px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }

        /* leaflet popup */
        .leaflet-popup-content { font-family: 'Segoe UI', sans-serif; min-width: 180px; }
        .popup-title { font-size: 13px; font-weight: 700; color: #1a2744; margin-bottom: 6px; }
        .popup-row { font-size: 12px; color: #555; margin-bottom: 3px; }
        .popup-row span { font-weight: 600; color: #333; }
        .popup-edit-btn { margin-top: 8px; width: 100%; padding: 5px; background: #1a2744; color: white; border: none; border-radius: 5px; font-size: 12px; cursor: pointer; font-weight: 600; }

        /* draw mode color legend */
        .draw-legend { display: flex; gap: 10px; flex-wrap: wrap; font-size: 11px; color: #666; margin-top: 6px; }
        .draw-legend-item { display: flex; align-items: center; gap: 4px; }
        .draw-legend-dot { width: 10px; height: 10px; border-radius: 2px; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo-section"><img src="{{ asset('images/Darlandicon.png') }}" alt="Logo"></div>
    <nav class="nav-menu">
        <a href="/dashboard"    class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="/map-viewer"   class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
        <a href="/land-records" class="nav-item active"><i class="fas fa-layer-group"></i><span>Land Records</span></a>
        <a href="/add-record"   class="nav-item"><i class="fas fa-plus-square"></i><span>Add Record</span></a>
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
        <div style="display:flex;align-items:center;gap:14px">
            <i class="fas fa-bell" style="font-size:17px;color:#999;cursor:pointer"></i>
            <a href="/profile" style="display:flex;align-items:center;gap:8px;text-decoration:none">
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
            <div class="map-hint" id="mapHint"><i class="fas fa-draw-polygon"></i> Draw mode: click points to trace a lot boundary. Double-click to finish.</div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <div class="panel-tabs">
                <button class="tab-btn active" onclick="switchTab('ldc')"><i class="fas fa-calculator"></i> LDC</button>
                <button class="tab-btn" onclick="switchTab('surveys')"><i class="fas fa-list"></i> Surveys</button>
                <button class="tab-btn" onclick="switchTab('stats')"><i class="fas fa-chart-bar"></i> Stats</button>
            </div>

            <!-- ── LDC Tab ── -->
            <div class="tab-content active" id="tab-ldc">
                <div id="ldcAlert"></div>

                <!-- Mode toggle -->
                <div class="mode-toggle">
                    <button class="mode-btn active" id="modeDrawBtn" onclick="setMode('draw')">
                        <i class="fas fa-draw-polygon"></i> Draw Lots
                    </button>
                    <button class="mode-btn" id="modeGridBtn" onclick="setMode('grid')">
                        <i class="fas fa-th"></i> Auto Grid
                    </button>
                </div>

                <!-- Shared: LSN info (both modes) -->
                <div class="ldc-section">
                    <div class="ldc-section-title"><i class="fas fa-map-marker-alt"></i> Survey Info</div>
                    <div class="form-row">
                        <label class="form-label">LSN (Survey Number)</label>
                        <input type="text" id="lsnInput" class="form-input" placeholder="e.g. Psd-123456">
                    </div>
                    <div class="form-row">
                        <label class="form-label">Original Owner</label>
                        <input type="text" id="originalOwner" class="form-input" placeholder="Full name">
                    </div>
                    <div class="form-row">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" id="surveyNotes" class="form-input" placeholder="e.g. Brgy. Anonas, Urdaneta">
                    </div>
                </div>

                <!-- ══ DRAW MODE ══ -->
                <div id="drawModePanel">
                    <div class="draw-steps">
                        <!-- Step 1 -->
                        <div class="draw-step active-step" id="step1">
                            <div class="step-header">
                                <div class="step-num">1</div>
                                <div class="step-title">Draw each lot on the map</div>
                            </div>
                            <div class="step-desc">Click the button below, trace the lot boundary by clicking points, double-click to finish. Repeat for every lot.</div>
                            <div style="margin-top:8px">
                                <button class="btn-draw" id="drawBtn" onclick="toggleDrawing()">
                                    <i class="fas fa-draw-polygon"></i> <span id="drawBtnText">Start Drawing Lot</span>
                                </button>
                                <div class="draw-legend">
                                    <div class="draw-legend-item"><div class="draw-legend-dot" style="background:#2e7d32"></div> Completed lot</div>
                                    <div class="draw-legend-item"><div class="draw-legend-dot" style="background:#ff9800;opacity:0.6"></div> Drawing…</div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: drawn lots list -->
                        <div class="draw-step" id="step2">
                            <div class="step-header">
                                <div class="step-num">2</div>
                                <div class="step-title">Assign owners to lots</div>
                            </div>
                            <div class="step-desc">Each drawn lot appears below. Enter the owner name.</div>
                            <div class="drawn-lots" id="drawnLotsList">
                                <div style="font-size:11.5px;color:#bbb;text-align:center;padding:12px">No lots drawn yet</div>
                            </div>
                        </div>

                        <!-- Step 3 -->
                        <div class="draw-step" id="step3">
                            <div class="step-header">
                                <div class="step-num">3</div>
                                <div class="step-title">Save the survey</div>
                            </div>
                            <div class="step-desc">Fill in LSN + owner above, then save.</div>
                        </div>
                    </div>

                    <button class="btn-primary" onclick="saveLSNDraw()">
                        <i class="fas fa-save"></i> Save Land Survey
                    </button>
                    <button class="btn-secondary" onclick="resetDrawMode()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>

                <!-- ══ GRID MODE ══ -->
                <div id="gridModePanel" style="display:none">
                    <div class="ldc-section">
                        <div class="ldc-section-title"><i class="fas fa-map-pin"></i> Center Location</div>
                        <div class="form-row">
                            <label class="form-label">Click map to set center</label>
                            <div class="coord-row">
                                <input type="number" id="centerLat" class="form-input" placeholder="Latitude"  step="any" readonly>
                                <input type="number" id="centerLng" class="form-input" placeholder="Longitude" step="any" readonly>
                                <button class="pick-btn" id="pickBtn" onclick="togglePick()"><i class="fas fa-crosshairs"></i> Pick</button>
                            </div>
                        </div>
                    </div>

                    <div class="ldc-section">
                        <div class="ldc-section-title"><i class="fas fa-ruler-combined"></i> Area &amp; Division</div>
                        <div class="form-grid-2">
                            <div class="form-row">
                                <label class="form-label">Total Area (sqm)</label>
                                <input type="number" id="totalArea" class="form-input" placeholder="e.g. 50000" min="1" oninput="updateLotRows()">
                            </div>
                            <div class="form-row">
                                <label class="form-label">No. of Lots</label>
                                <input type="number" id="lotCount" class="form-input" placeholder="e.g. 10" min="1" max="500" oninput="updateLotRows()">
                            </div>
                        </div>
                        <div class="area-display" id="areaPerLotDisplay">
                            Area per lot: <strong id="areaPerLotVal"></strong> sqm &nbsp;·&nbsp; <strong id="areaPerLotHa"></strong> ha
                        </div>
                    </div>

                    <div class="ldc-section">
                        <div class="ldc-section-title"><i class="fas fa-users"></i> Lot Owners</div>
                        <div class="lot-rows" id="lotOwnerRows">
                            <div style="font-size:11.5px;color:#aaa;text-align:center;padding:16px">Enter number of lots above</div>
                        </div>
                    </div>

                    <button class="btn-primary" onclick="saveLSNGrid()">
                        <i class="fas fa-save"></i> Save Land Survey
                    </button>
                    <button class="btn-secondary" onclick="resetGridMode()">
                        <i class="fas fa-redo"></i> Reset
                    </button>
                </div>
            </div>

            <!-- ── Surveys Tab ── -->
            <div class="tab-content" id="tab-surveys">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px">
                    <div style="font-size:13px;font-weight:700;color:#333">All Surveys</div>
                    <input type="text" id="lsnSearch" class="form-input" style="width:130px;font-size:11.5px" placeholder="Search…" oninput="filterSurveys(this.value)">
                </div>
                <div id="surveyList"><div style="text-align:center;color:#aaa;padding:30px;font-size:13px">Loading…</div></div>
            </div>

            <!-- ── Stats Tab ── -->
            <div class="tab-content" id="tab-stats">
                <div id="statsPanel"><div style="text-align:center;color:#aaa;padding:30px;font-size:13px">Loading…</div></div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Lot Modal -->
<div id="editLotModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:white;border-radius:14px;padding:26px;width:340px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:15px;font-weight:700;color:#1a2744;margin-bottom:16px"><i class="fas fa-pencil-alt" style="margin-right:6px"></i>Edit Lot</h3>
        <input type="hidden" id="editLotId">
        <div class="form-row"><label class="form-label">Lot Number</label><input type="number" id="editLotNumber" class="form-input" min="1"></div>
        <div class="form-row"><label class="form-label">Owner Name</label><input type="text" id="editOwnerName" class="form-input"></div>
        <div class="form-row"><label class="form-label">Notes</label><input type="text" id="editLotNotes" class="form-input" placeholder="Optional"></div>
        <div style="display:flex;gap:10px;margin-top:16px">
            <button onclick="closeEditModal()" style="flex:1;padding:9px;background:#f5f5f5;color:#555;border:none;border-radius:7px;cursor:pointer;font-size:13px">Cancel</button>
            <button onclick="saveLotEdit()"    style="flex:1;padding:9px;background:#1a2744;color:white;border:none;border-radius:7px;cursor:pointer;font-size:13px;font-weight:700">Save</button>
        </div>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet.draw/1.0.4/leaflet.draw.js"></script>
<script>
const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
const isAdmin = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};

// ── Map ───────────────────────────────────────────────────────
const map = L.map('map').setView([15.9754, 120.5701], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
}).addTo(map);

let surveyLayers = {};
let allSurveys   = [];

// ── Mode state ───────────────────────────────────────────────
let currentMode = 'draw';

function setMode(mode) {
    currentMode = mode;
    document.getElementById('modeDrawBtn').classList.toggle('active', mode === 'draw');
    document.getElementById('modeGridBtn').classList.toggle('active', mode === 'grid');
    document.getElementById('drawModePanel').style.display = mode === 'draw' ? 'block' : 'none';
    document.getElementById('gridModePanel').style.display = mode === 'grid' ? 'block' : 'none';
    const hint = document.getElementById('mapHint');
    if (mode === 'draw') {
        hint.innerHTML = '<i class="fas fa-draw-polygon"></i> Draw mode: click the button then trace each lot on the map.';
    } else {
        hint.innerHTML = '<i class="fas fa-mouse-pointer"></i> Grid mode: click "Pick" then click the map to set center.';
    }
    hint.classList.remove('hide');
    setTimeout(() => hint.classList.add('hide'), 5000);
}

// ══════════════════════════════════════════════════════════════
// DRAW MODE
// ══════════════════════════════════════════════════════════════
let drawingActive  = false;
let drawnPolygons  = []; // [{latlngs, area, layer, labelMarker}]
let currentPoints  = []; // points being clicked
let previewLine    = null;
let previewLayerGroup = L.layerGroup().addTo(map);
let drawnLayerGroup   = L.layerGroup().addTo(map);

// Shoelace formula to compute area from latlngs (metres²)
function polygonAreaSqm(latlngs) {
    const R = 6371000; // earth radius metres
    const n = latlngs.length;
    if (n < 3) return 0;
    let area = 0;
    for (let i = 0; i < n; i++) {
        const j = (i + 1) % n;
        const xi = latlngs[i].lng * Math.PI / 180 * R * Math.cos(latlngs[i].lat * Math.PI / 180);
        const yi = latlngs[i].lat * Math.PI / 180 * R;
        const xj = latlngs[j].lng * Math.PI / 180 * R * Math.cos(latlngs[j].lat * Math.PI / 180);
        const yj = latlngs[j].lat * Math.PI / 180 * R;
        area += xi * yj - xj * yi;
    }
    return Math.abs(area / 2);
}

function toggleDrawing() {
    if (drawingActive) {
        // Finish current polygon if has enough points
        if (currentPoints.length >= 3) finishCurrentPolygon();
        else cancelCurrentDraw();
    } else {
        startDrawing();
    }
}

function startDrawing() {
    drawingActive = true;
    currentPoints = [];
    previewLayerGroup.clearLayers();
    document.getElementById('drawBtn').classList.add('drawing');
    document.getElementById('drawBtnText').textContent = 'Finish Lot (or double-click)';
    map.getContainer().style.cursor = 'crosshair';
    document.getElementById('mapHint').innerHTML = '<i class="fas fa-draw-polygon"></i> Click to place points. Double-click to close the lot.';
    document.getElementById('mapHint').classList.remove('hide');
    document.getElementById('step1').classList.add('active-step');
}

function cancelCurrentDraw() {
    drawingActive = false;
    currentPoints = [];
    previewLayerGroup.clearLayers();
    previewLine = null;
    document.getElementById('drawBtn').classList.remove('drawing');
    document.getElementById('drawBtnText').textContent = 'Start Drawing Lot';
    map.getContainer().style.cursor = '';
    document.getElementById('mapHint').classList.add('hide');
}

// Map click — add point
map.on('click', function(e) {
    if (!drawingActive || currentMode !== 'draw') return;
    currentPoints.push(e.latlng);
    updatePreview();
});

// Map dblclick — finish polygon
map.on('dblclick', function(e) {
    if (!drawingActive || currentMode !== 'draw') return;
    // Remove the last point added by the click that preceded dblclick
    if (currentPoints.length > 0) currentPoints.pop();
    if (currentPoints.length >= 3) finishCurrentPolygon();
    else { showAlert('error', 'Need at least 3 points to form a lot.'); cancelCurrentDraw(); }
    L.DomEvent.stop(e);
});

function updatePreview() {
    previewLayerGroup.clearLayers();
    if (currentPoints.length === 0) return;
    // Dots
    currentPoints.forEach((p, i) => {
        L.circleMarker(p, { radius: 5, color: '#ff9800', fillColor: '#ff9800', fillOpacity: 1, weight: 2 }).addTo(previewLayerGroup);
    });
    // Lines between points
    if (currentPoints.length >= 2) {
        L.polyline(currentPoints, { color: '#ff9800', weight: 2, dashArray: '5,4' }).addTo(previewLayerGroup);
    }
    // Closing line
    if (currentPoints.length >= 3) {
        L.polyline([currentPoints[currentPoints.length-1], currentPoints[0]], { color: '#ff9800', weight: 1.5, dashArray: '3,4', opacity: 0.5 }).addTo(previewLayerGroup);
    }
}

function finishCurrentPolygon() {
    const latlngs  = [...currentPoints];
    const areaSqm  = polygonAreaSqm(latlngs);
    const lotNum   = drawnPolygons.length + 1;

    // Draw the completed lot
    const poly = L.polygon(latlngs, {
        color: '#1a2744', fillColor: '#2e7d32', fillOpacity: 0.3, weight: 2
    }).addTo(drawnLayerGroup);

    // Lot number label
    const center = poly.getBounds().getCenter();
    const label  = L.marker(center, {
        icon: L.divIcon({
            className: '',
            html: `<div style="background:#1a2744;color:white;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:700">${lotNum}</div>`,
            iconAnchor: [12, 8]
        }),
        interactive: false
    }).addTo(drawnLayerGroup);

    drawnPolygons.push({ latlngs, areaSqm, layer: poly, labelMarker: label, lotNum });

    // Reset for next polygon
    currentPoints = [];
    previewLayerGroup.clearLayers();
    drawingActive = false;
    document.getElementById('drawBtn').classList.remove('drawing');
    document.getElementById('drawBtnText').textContent = 'Draw Next Lot';
    map.getContainer().style.cursor = '';
    document.getElementById('mapHint').classList.add('hide');

    renderDrawnLotsList();
    updateStep2();
}

function renderDrawnLotsList() {
    const container = document.getElementById('drawnLotsList');
    if (drawnPolygons.length === 0) {
        container.innerHTML = '<div style="font-size:11.5px;color:#bbb;text-align:center;padding:12px">No lots drawn yet</div>';
        return;
    }
    container.innerHTML = drawnPolygons.map((p, i) => `
        <div class="drawn-lot-row" id="drawn-row-${i}">
            <div class="drawn-lot-num">${p.lotNum}</div>
            <input type="text" class="drawn-lot-input" id="drawn-owner-${i}"
                   placeholder="Owner name" value="">
            <span class="drawn-lot-area">${Math.round(p.areaSqm).toLocaleString()} sqm</span>
            <button class="drawn-lot-del" onclick="removeDrawnLot(${i})" title="Remove"><i class="fas fa-times"></i></button>
        </div>
    `).join('');
}

function updateStep2() {
    const s2 = document.getElementById('step2');
    if (drawnPolygons.length > 0) {
        s2.classList.add('active-step');
        s2.classList.remove('done-step');
        document.getElementById('step3').classList.add('active-step');
    }
}

function removeDrawnLot(index) {
    // Remove from map
    drawnLayerGroup.removeLayer(drawnPolygons[index].layer);
    drawnLayerGroup.removeLayer(drawnPolygons[index].labelMarker);
    drawnPolygons.splice(index, 1);
    // Re-number
    drawnPolygons.forEach((p, i) => {
        p.lotNum = i + 1;
        // Update label
        drawnLayerGroup.removeLayer(p.labelMarker);
        const center = p.layer.getBounds().getCenter();
        const lbl = L.marker(center, {
            icon: L.divIcon({ className: '', html: `<div style="background:#1a2744;color:white;padding:2px 7px;border-radius:4px;font-size:11px;font-weight:700">${i+1}</div>`, iconAnchor: [12, 8] }),
            interactive: false
        }).addTo(drawnLayerGroup);
        p.labelMarker = lbl;
    });
    renderDrawnLotsList();
}

async function saveLSNDraw() {
    const lsn   = document.getElementById('lsnInput').value.trim();
    const owner = document.getElementById('originalOwner').value.trim();
    const notes = document.getElementById('surveyNotes').value.trim();

    if (!lsn)   { showAlert('error', 'Please enter an LSN.'); return; }
    if (!owner) { showAlert('error', 'Please enter the original owner.'); return; }
    if (drawnPolygons.length === 0) { showAlert('error', 'Draw at least one lot on the map.'); return; }

    // Build lots array
    const lots = drawnPolygons.map((p, i) => {
        const ownerName = document.getElementById(`drawn-owner-${i}`)?.value.trim() || owner;
        return {
            lot_number: p.lotNum,
            owner_name: ownerName,
            area:       Math.round(p.areaSqm * 100) / 100,
            polygon:    p.latlngs.map(ll => [ll.lat, ll.lng]),
        };
    });

    // Compute centroid of all lots
    const allLats = drawnPolygons.flatMap(p => p.latlngs.map(ll => ll.lat));
    const allLngs = drawnPolygons.flatMap(p => p.latlngs.map(ll => ll.lng));
    const centerLat = allLats.reduce((a,b)=>a+b,0) / allLats.length;
    const centerLng = allLngs.reduce((a,b)=>a+b,0) / allLngs.length;

    try {
        const res = await apiFetch('/api/surveys', 'POST', {
            lsn, original_owner: owner, notes,
            center_lat: centerLat, center_lng: centerLng,
            lot_count: lots.length,
            mode: 'draw',
            lots,
        });
        if (res.success) {
            showAlert('success', `LSN "${lsn}" saved with ${lots.length} lot${lots.length>1?'s':''}.`);
            resetDrawMode();
            await loadSurveys();
        } else {
            showAlert('error', res.message || 'Failed to save.');
        }
    } catch(e) {
        showAlert('error', e.message);
    }
}

function resetDrawMode() {
    cancelCurrentDraw();
    drawnPolygons.forEach(p => { drawnLayerGroup.removeLayer(p.layer); drawnLayerGroup.removeLayer(p.labelMarker); });
    drawnPolygons = [];
    renderDrawnLotsList();
    ['lsnInput','originalOwner','surveyNotes'].forEach(id => document.getElementById(id).value='');
    document.getElementById('step2').classList.remove('active-step','done-step');
    document.getElementById('step3').classList.remove('active-step','done-step');
    document.getElementById('drawBtnText').textContent = 'Start Drawing Lot';
}

// ══════════════════════════════════════════════════════════════
// GRID MODE
// ══════════════════════════════════════════════════════════════
let pickingMode  = false;
let pickedMarker = null;

function togglePick() {
    pickingMode = !pickingMode;
    const btn = document.getElementById('pickBtn');
    if (pickingMode) {
        btn.classList.add('active'); btn.innerHTML = '<i class="fas fa-times"></i> Cancel';
        map.getContainer().style.cursor = 'crosshair';
        document.getElementById('mapHint').innerHTML = '<i class="fas fa-crosshairs"></i> Click the map to set the survey center.';
        document.getElementById('mapHint').classList.remove('hide');
    } else {
        btn.classList.remove('active'); btn.innerHTML = '<i class="fas fa-crosshairs"></i> Pick';
        map.getContainer().style.cursor = '';
        document.getElementById('mapHint').classList.add('hide');
    }
}

map.on('click', function(e) {
    if (!pickingMode || currentMode !== 'grid') return;
    const {lat, lng} = e.latlng;
    document.getElementById('centerLat').value = lat.toFixed(7);
    document.getElementById('centerLng').value = lng.toFixed(7);
    if (pickedMarker) map.removeLayer(pickedMarker);
    pickedMarker = L.marker([lat, lng], {
        icon: L.divIcon({ className: '', html: '<div style="background:#1a2744;color:white;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;white-space:nowrap">Center</div>', iconAnchor: [28,10] })
    }).addTo(map);
    togglePick();
});

function updateLotRows() {
    const count = parseInt(document.getElementById('lotCount').value) || 0;
    const area  = parseFloat(document.getElementById('totalArea').value) || 0;
    const container = document.getElementById('lotOwnerRows');
    const display   = document.getElementById('areaPerLotDisplay');

    if (count > 0 && area > 0) {
        const perLot = (area / count).toFixed(2);
        document.getElementById('areaPerLotVal').textContent = Number(perLot).toLocaleString();
        document.getElementById('areaPerLotHa').textContent  = (perLot/10000).toFixed(4);
        display.style.display = 'block';
    } else { display.style.display = 'none'; }

    if (count < 1 || count > 500) {
        container.innerHTML = '<div style="font-size:11.5px;color:#aaa;text-align:center;padding:14px">Enter 1–500 lots</div>';
        return;
    }
    let html = '';
    for (let i = 1; i <= count; i++) {
        html += `<div class="lot-row"><div class="lot-num-label">${i}</div><input type="text" class="form-input" id="owner_${i}" placeholder="Owner" style="font-size:12px"></div>`;
    }
    container.innerHTML = html;
}

async function saveLSNGrid() {
    const lsn   = document.getElementById('lsnInput').value.trim();
    const owner = document.getElementById('originalOwner').value.trim();
    const notes = document.getElementById('surveyNotes').value.trim();
    const lat   = parseFloat(document.getElementById('centerLat').value);
    const lng   = parseFloat(document.getElementById('centerLng').value);
    const area  = parseFloat(document.getElementById('totalArea').value);
    const count = parseInt(document.getElementById('lotCount').value);

    if (!lsn || !owner || isNaN(lat) || isNaN(lng) || !area || !count) {
        showAlert('error', 'Fill in all fields and pick a center location.'); return;
    }
    const lots = [];
    for (let i = 1; i <= count; i++) {
        lots.push({ lot_number: i, owner_name: document.getElementById(`owner_${i}`)?.value.trim() || `Owner ${i}` });
    }
    try {
        const res = await apiFetch('/api/surveys', 'POST', {
            lsn, original_owner: owner, notes,
            total_area: area, center_lat: lat, center_lng: lng,
            lot_count: count, mode: 'grid', lots,
        });
        if (res.success) {
            showAlert('success', `LSN "${lsn}" saved with ${count} lots.`);
            resetGridMode();
            await loadSurveys();
            renderSurveyOnMap(res.survey);
        } else { showAlert('error', res.message || 'Failed.'); }
    } catch(e) { showAlert('error', e.message); }
}

function resetGridMode() {
    ['lsnInput','originalOwner','surveyNotes','centerLat','centerLng','totalArea','lotCount']
        .forEach(id => document.getElementById(id).value='');
    document.getElementById('lotOwnerRows').innerHTML = '<div style="font-size:11.5px;color:#aaa;text-align:center;padding:14px">Enter number of lots above</div>';
    document.getElementById('areaPerLotDisplay').style.display = 'none';
    if (pickedMarker) { map.removeLayer(pickedMarker); pickedMarker = null; }
}

// ══════════════════════════════════════════════════════════════
// SURVEY RENDERING (shared)
// ══════════════════════════════════════════════════════════════
function renderSurveyOnMap(survey) {
    if (surveyLayers[survey.id]) {
        Object.values(surveyLayers[survey.id].lots).forEach(ls => ls.forEach(l => map.removeLayer(l)));
        if (surveyLayers[survey.id].boundary) map.removeLayer(surveyLayers[survey.id].boundary);
    }
    surveyLayers[survey.id] = { boundary: null, lots: {} };
    const allCoords = [];

    (survey.lots || []).forEach(lot => {
        const polygons = JSON.parse(lot.polygons || '[]');
        surveyLayers[survey.id].lots[lot.id] = [];

        polygons.forEach(poly => {
            const layer = L.polygon(poly, {
                color: '#1a2744', fillColor: '#3a5a9a', fillOpacity: 0.3, weight: 1.5
            }).addTo(map);
            layer.on('click', () => openLotPopup(lot, layer));

            const center = layer.getBounds().getCenter();
            const label  = L.marker(center, {
                icon: L.divIcon({
                    className: '',
                    html: `<div style="background:#1a2744;color:white;padding:2px 6px;border-radius:3px;font-size:10px;font-weight:700;cursor:pointer">${lot.lot_number}</div>`,
                    iconAnchor: [10, 8]
                })
            }).addTo(map);
            label.on('click', () => openLotPopup(lot, layer));

            surveyLayers[survey.id].lots[lot.id].push(layer, label);
            poly.forEach(c => allCoords.push(Array.isArray(c) ? c : [c.lat, c.lng]));
        });
    });

    if (allCoords.length > 0) {
        const lats   = allCoords.map(c => c[0]);
        const lngs   = allCoords.map(c => c[1]);
        const bounds = [[Math.min(...lats), Math.min(...lngs)], [Math.max(...lats), Math.max(...lngs)]];
        const boundary = L.rectangle(bounds, { color: '#e53935', weight: 2, fill: false, dashArray: '6,4' }).addTo(map);

        const lsnLabel = L.marker([(Math.max(...lats)), (Math.min(...lngs)+Math.max(...lngs))/2], {
            icon: L.divIcon({ className: '', html: `<div style="background:#e53935;color:white;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;cursor:pointer">${survey.lsn}</div>`, iconAnchor: [28,20] })
        }).addTo(map);
        lsnLabel.on('click', () => { map.fitBounds(bounds, {padding:[30,30]}); selectSurvey(survey.id); });

        surveyLayers[survey.id].boundary = boundary;
        surveyLayers[survey.id].lots['_lbl'] = [lsnLabel];
    }
}

// ── Lot popup ─────────────────────────────────────────────────
function openLotPopup(lot, layer) {
    const areaHa  = (lot.area / 10000).toFixed(4);
    const editBtn = isAdmin
        ? `<button class="popup-edit-btn" onclick="openEditModal(${lot.id},${lot.lot_number},'${escJs(lot.owner_name)}','${escJs(lot.notes||'')}')">Edit Lot</button>`
        : '';
    L.popup({ maxWidth: 220 })
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
function openEditModal(lotId, lotNum, ownerName, notes) {
    document.getElementById('editLotId').value = lotId;
    document.getElementById('editLotNumber').value = lotNum;
    document.getElementById('editOwnerName').value = ownerName;
    document.getElementById('editLotNotes').value = notes;
    document.getElementById('editLotModal').style.display = 'flex';
}
function closeEditModal() { document.getElementById('editLotModal').style.display = 'none'; }

async function saveLotEdit() {
    const id    = document.getElementById('editLotId').value;
    const num   = document.getElementById('editLotNumber').value;
    const owner = document.getElementById('editOwnerName').value.trim();
    const notes = document.getElementById('editLotNotes').value.trim();
    if (!owner) { alert('Owner name is required.'); return; }
    try {
        const res = await apiFetch(`/api/lots/${id}`, 'PUT', { lot_number: num, owner_name: owner, notes });
        if (res.success) { closeEditModal(); map.closePopup(); await loadSurveys(); }
    } catch(e) { alert('Error: ' + e.message); }
}

// ── Load & render all surveys ─────────────────────────────────
async function loadSurveys() {
    try {
        const res = await apiFetch('/api/surveys', 'GET');
        allSurveys = res;
        Object.values(surveyLayers).forEach(s => {
            Object.values(s.lots).forEach(ls => ls.forEach(l => map.removeLayer(l)));
            if (s.boundary) map.removeLayer(s.boundary);
        });
        surveyLayers = {};
        allSurveys.forEach(s => renderSurveyOnMap(s));
        renderSurveyList(allSurveys);
        renderStats(allSurveys);
    } catch(e) { console.error('Load surveys failed:', e.message); }
}

function renderSurveyList(surveys) {
    const c = document.getElementById('surveyList');
    if (!surveys.length) { c.innerHTML = '<div style="text-align:center;color:#aaa;padding:26px;font-size:12.5px">No surveys yet. Use the LDC tab to add one.</div>'; return; }
    c.innerHTML = surveys.map(s => `
        <div class="lsn-item" onclick="selectSurvey(${s.id})" id="lsn-item-${s.id}">
            <div class="lsn-item-title">${s.lsn}</div>
            <div class="lsn-item-sub">${s.original_owner}</div>
            <div class="lsn-item-badges">
                <span class="badge">${s.lot_count} lots</span>
                <span class="badge">${Number(s.total_area).toLocaleString()} sqm</span>
                <span class="badge">${(s.total_area/10000).toFixed(3)} ha</span>
            </div>
        </div>`).join('');
}

function filterSurveys(q) {
    renderSurveyList(allSurveys.filter(s =>
        s.lsn.toLowerCase().includes(q.toLowerCase()) ||
        s.original_owner.toLowerCase().includes(q.toLowerCase())
    ));
}

function selectSurvey(id) {
    document.querySelectorAll('.lsn-item').forEach(el => el.classList.remove('selected'));
    const el = document.getElementById(`lsn-item-${id}`);
    if (el) el.classList.add('selected');
    const layers = surveyLayers[id];
    if (layers?.boundary) map.fitBounds(layers.boundary.getBounds(), { padding: [40,40] });
    const survey = allSurveys.find(s => s.id === id);
    if (survey) showSurveyDetail(survey);
    switchTab('surveys');
}

function showSurveyDetail(survey) {
    const c = document.getElementById('surveyList');
    const areaPerLot = (survey.total_area / survey.lot_count).toFixed(2);
    c.innerHTML = `
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
            <button onclick="renderSurveyList(allSurveys)" style="background:#f0f0f0;border:none;border-radius:6px;padding:4px 9px;cursor:pointer;font-size:12px"><i class="fas fa-arrow-left"></i></button>
            <div style="font-size:13px;font-weight:700;color:#1a2744">${survey.lsn}</div>
        </div>
        <div class="ldc-section" style="margin-bottom:8px">
            <div class="stat-row"><span class="stat-lbl">Owner</span><span class="stat-val">${survey.original_owner}</span></div>
            <div class="stat-row"><span class="stat-lbl">Total Area</span><span class="stat-val">${Number(survey.total_area).toLocaleString()} sqm</span></div>
            <div class="stat-row"><span class="stat-lbl">Hectares</span><span class="stat-val">${(survey.total_area/10000).toFixed(4)} ha</span></div>
            <div class="stat-row"><span class="stat-lbl">Lots</span><span class="stat-val">${survey.lot_count}</span></div>
            <div class="stat-row"><span class="stat-lbl">Area/Lot</span><span class="stat-val">${Number(areaPerLot).toLocaleString()} sqm</span></div>
        </div>
        <div style="font-size:11px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Lots</div>
        ${(survey.lots||[]).map(lot => `
        <div class="lot-list-item">
            <span class="lot-list-num">Lot ${lot.lot_number}</span>
            <span class="lot-list-owner">${lot.owner_name}</span>
            <span class="lot-list-area">${Number(lot.area).toLocaleString()} sqm</span>
            ${isAdmin ? `<button class="btn-sm btn-edit-sm" onclick="openEditModal(${lot.id},${lot.lot_number},'${escJs(lot.owner_name)}','${escJs(lot.notes||'')}')"><i class="fas fa-pencil-alt"></i></button>` : ''}
        </div>`).join('')}
        ${isAdmin ? `<button class="btn-secondary" style="margin-top:8px" onclick="deleteSurvey(${survey.id},'${escJs(survey.lsn)}')"><i class="fas fa-trash" style="color:#e53935"></i> Delete Survey</button>` : ''}
    `;
}

async function deleteSurvey(id, lsn) {
    if (!confirm(`Delete survey "${lsn}" and all its lots?`)) return;
    const res = await apiFetch(`/api/surveys/${id}`, 'DELETE');
    if (res.success) await loadSurveys();
}

function renderStats(surveys) {
    const total     = surveys.length;
    const totalLots = surveys.reduce((s,v) => s+v.lot_count, 0);
    const totalArea = surveys.reduce((s,v) => s+parseFloat(v.total_area), 0);
    document.getElementById('statsPanel').innerHTML = `
        <div class="stat-highlight">
            <div><div class="stat-highlight-lbl">Total Surveys</div><div class="stat-highlight-val">${total}</div></div>
            <div style="text-align:right"><div class="stat-highlight-lbl">Total Lots</div><div class="stat-highlight-val">${totalLots.toLocaleString()}</div></div>
        </div>
        <div class="ldc-section">
            <div class="ldc-section-title"><i class="fas fa-ruler-combined"></i> Area</div>
            <div class="stat-row"><span class="stat-lbl">Total (sqm)</span><span class="stat-val">${Math.round(totalArea).toLocaleString()}</span></div>
            <div class="stat-row"><span class="stat-lbl">Total (ha)</span><span class="stat-val">${(totalArea/10000).toFixed(4)}</span></div>
            <div class="stat-row"><span class="stat-lbl">Avg lots/survey</span><span class="stat-val">${total>0?(totalLots/total).toFixed(1):0}</span></div>
            <div class="stat-row"><span class="stat-lbl">Avg area/lot</span><span class="stat-val">${total>0?Math.round(totalArea/(totalLots||1)).toLocaleString():0} sqm</span></div>
        </div>
        <div class="ldc-section">
            <div class="ldc-section-title"><i class="fas fa-list"></i> Per Survey</div>
            ${surveys.map(s=>`
            <div class="stat-row" style="cursor:pointer" onclick="selectSurvey(${s.id});switchTab('surveys')">
                <span class="stat-lbl">${s.lsn}</span>
                <span class="stat-val">${s.lot_count} lots · ${(s.total_area/10000).toFixed(2)}ha</span>
            </div>`).join('')}
        </div>`;
}

function switchTab(name) {
    ['ldc','surveys','stats'].forEach((n,i) => {
        document.querySelectorAll('.tab-btn')[i].classList.toggle('active', n===name);
    });
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.getElementById(`tab-${name}`).classList.add('active');
}

function showAlert(type, msg) {
    const el = document.getElementById('ldcAlert');
    el.innerHTML = `<div class="alert alert-${type}">${msg}</div>`;
    setTimeout(() => el.innerHTML='', 5000);
}

function escJs(s) { return String(s||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'").replace(/"/g,'\\"'); }

async function apiFetch(url, method='GET', body=null) {
    const opts = { method, headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':CSRF, 'Accept':'application/json' } };
    if (body) opts.body = JSON.stringify(body);
    const res  = await fetch(url, opts);
    const data = await res.json();
    if (!res.ok) {
        const msgs = data.errors ? Object.values(data.errors).flat() : [data.message || `Error ${res.status}`];
        throw new Error(msgs.join('\n'));
    }
    return data;
}

// ── Boot ─────────────────────────────────────────────────────
setTimeout(() => document.getElementById('mapHint').classList.add('hide'), 5000);
loadSurveys();
</script>
</body>
</html>
