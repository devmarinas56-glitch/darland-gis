<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Add Record - Land GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        html, body {
            height: 100%;
            width: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #e8e8e8;
            overflow: hidden; /* prevent body scroll — inner panels scroll */
        }

        body { display: flex; }

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
            flex-shrink: 0;
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
            width: 100%; padding: 11px;
            background: rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            border: none; border-radius: 6px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            gap: 8px; font-size: 13px; font-weight: 500;
            text-transform: uppercase; transition: all 0.2s; text-decoration: none;
        }
        .logout-btn:hover { background: rgba(255,255,255,0.2); color: white; }

        /* ── Main — fills remaining width, full viewport height ── */
        .main-content {
            margin-left: 200px;
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
            min-width: 0;
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
            z-index: 100;
        }
        .search-box { flex: 1; max-width: 380px; position: relative; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 13px; }
        .search-input {
            width: 100%; padding: 8px 14px 8px 34px;
            border: 1px solid #e8e8e8; border-radius: 20px;
            font-size: 13px; background: #f7f7f7; color: #444;
        }
        .search-input:focus { outline: none; border-color: #1a2744; background: white; }
        .top-right { display: flex; align-items: center; gap: 18px; }
        .bell-icon { font-size: 18px; color: #aaa; cursor: pointer; }
        .user-chip { display: flex; align-items: center; gap: 10px; cursor: pointer; }
        .user-avatar {
            width: 34px; height: 34px; border-radius: 50%;
            background: #1a2744;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 12px;
        }
        .user-name { font-size: 13px; font-weight: 600; color: #333; }
        .user-role-label { font-size: 11px; color: #aaa; text-transform: uppercase; }
        .chevron { font-size: 11px; color: #ccc; }

        /* ── Content area — NO scroll here, fills height, layout handles overflow ── */
        .content-area {
            flex: 1;
            overflow: hidden;
            padding: 16px 18px 16px;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .page-title {
            font-size: 17px; font-weight: 700; color: #222;
            margin-bottom: 10px;
            flex-shrink: 0;
            line-height: 1;
        }

        /* ── Alert — compact, doesn't shift layout ── */
        .alert {
            padding: 8px 14px; border-radius: 8px;
            font-size: 12px; margin-bottom: 10px;
            display: flex; align-items: flex-start; gap: 8px;
            flex-shrink: 0;
        }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .alert-error   { background: #fff0f0; color: #c62828; border: 1px solid #ffcdd2; }

        /* ── Two-panel layout — fills remaining flex space, no overflow ── */
        .form-layout {
            display: grid;
            grid-template-columns: minmax(320px, 1fr) minmax(380px, 45vw);
            gap: 14px;
            flex: 1;
            min-height: 0;
            overflow: hidden;
        }

        /* ── Left panel: LDC form — ONLY this scrolls ── */
        .ldc-panel {
            background: #ebebeb;
            border-radius: 14px;
            padding: 14px 16px 16px;
            overflow-y: auto;
            overflow-x: hidden;
            min-height: 0;
            height: 100%;
        }
        .ldc-panel-title {
            font-size: 11px; font-weight: 700;
            color: #888; text-transform: uppercase;
            letter-spacing: 0.5px; margin-bottom: 14px;
        }

        .field-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 10px;
        }
        .field-row.single { grid-template-columns: 1fr; }
        .field-row.triple { grid-template-columns: 1fr 1fr 1fr; }

        .field-group { display: flex; flex-direction: column; gap: 3px; }
        .field-label {
            font-size: 11px; font-weight: 600;
            color: #666; letter-spacing: 0.2px;
        }
        .field-label .req { color: #e53935; margin-left: 2px; }
        .field-input {
            padding: 7px 9px;
            border: 1px solid #d4d4d4;
            border-radius: 6px;
            font-size: 12px;
            color: #333;
            background: white;
            transition: border-color 0.2s;
            width: 100%;
        }
        .field-input:focus { outline: none; border-color: #1a2744; }
        .field-input:read-only { background: #f5f5f5; color: #888; cursor: default; }
        .field-input.invalid { border-color: #e53935; }

        .field-error { font-size: 10px; color: #e53935; margin-top: 2px; display: none; }
        .field-error.show { display: block; }

        /* Lot owner dynamic rows */
        .lot-owners-wrap {
            background: white;
            border: 1px solid #d4d4d4;
            border-radius: 6px;
            max-height: 180px;
            overflow-y: auto;
            padding: 4px 0;
        }
        .lot-owner-row {
            display: grid;
            grid-template-columns: 32px 1fr;
            align-items: center;
            gap: 8px;
            padding: 4px 8px;
            border-bottom: 1px solid #f5f5f5;
        }
        .lot-owner-row:last-child { border-bottom: none; }
        .lot-num-badge {
            width: 26px; height: 26px;
            background: #1a2744; color: white;
            border-radius: 5px;
            font-size: 10px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .lot-owner-input {
            padding: 5px 8px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 12px; color: #333;
            width: 100%;
        }
        .lot-owner-input:focus { outline: none; border-color: #1a2744; }
        .lot-placeholder {
            padding: 14px 12px;
            text-align: center;
            font-size: 12px; color: #bbb;
        }

        /* Area per lot computed display */
        .computed-row {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #e3edf5;
            border-radius: 6px;
            padding: 7px 10px;
            margin-top: 8px;
            font-size: 11px;
            color: #1a2744;
        }
        .computed-row i { font-size: 13px; }
        .computed-row strong { font-size: 13px; font-weight: 800; }

        /* Divider */
        .section-divider {
            font-size: 10px; font-weight: 700;
            color: #999; text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 14px 0 10px;
            display: flex; align-items: center; gap: 8px;
        }
        .section-divider::after {
            content: ''; flex: 1; height: 1px; background: #d8d8d8;
        }

        /* ── Right panel — fills height, NO scroll, locked in place ── */
        .right-panel {
            display: flex;
            flex-direction: column;
            gap: 10px;
            min-height: 0;
            height: 100%;
            overflow: hidden;
        }

        /* Map preview — grows to fill available space */
        .map-preview {
            border-radius: 12px;
            overflow: hidden;
            flex: 1;              /* ← fills remaining height */
            min-height: 300px;    /* floor so it's always useful */
            background: #ccc;
            position: relative;
        }
        #previewMap { width: 100%; height: 100%; }
        .map-pick-hint {
            position: absolute;
            bottom: 12px; left: 50%; transform: translateX(-50%);
            background: rgba(26,39,68,0.82);
            color: white; font-size: 11px;
            padding: 5px 14px; border-radius: 20px;
            white-space: nowrap; pointer-events: none;
            transition: opacity 0.3s;
            z-index: 600;
        }
        .map-pick-hint.hide { opacity: 0; }

        /* Boundary section — compact to give map more room */
        .boundary-panel {
            background: #ebebeb;
            border-radius: 14px;
            padding: 10px 14px 12px;
            flex-shrink: 0;
        }
        .boundary-title {
            font-size: 11px; font-weight: 700;
            color: #888; text-transform: uppercase;
            letter-spacing: 0.5px; margin-bottom: 8px;
        }

        /* Action buttons — compact */
        .action-row {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            flex-shrink: 0;
            padding-top: 2px;
        }
        .btn-cancel {
            padding: 10px 24px;
            background: #e0e0e0; color: #555;
            border: none; border-radius: 8px;
            font-size: 13px; font-weight: 600;
            cursor: pointer; transition: background 0.2s;
        }
        .btn-cancel:hover { background: #d0d0d0; }
        .btn-save {
            padding: 10px 28px;
            background: #1a2744; color: white;
            border: none; border-radius: 8px;
            font-size: 13px; font-weight: 700;
            cursor: pointer; transition: background 0.2s;
            display: flex; align-items: center; gap: 8px;
        }
        .btn-save:hover { background: #2d4070; }
        .btn-save:disabled { background: #8a9ab8; cursor: not-allowed; }

        /* Leaflet overrides */
        .leaflet-control-zoom {
            border: none !important;
            box-shadow: 0 2px 8px rgba(0,0,0,0.18) !important;
            border-radius: 7px !important;
            overflow: hidden;
        }
        .leaflet-control-zoom a {
            width: 28px !important; height: 28px !important;
            line-height: 28px !important; font-size: 15px !important;
            color: #333 !important; background: white !important;
        }
        .leaflet-control-zoom a:hover { background: #f0f0f0 !important; }

        /* Success overlay */
        .success-overlay {
            display: none; position: fixed; inset: 0;
            background: rgba(0,0,0,0.4); z-index: 9000;
            align-items: center; justify-content: center;
        }
        .success-overlay.show { display: flex; }
        .success-card {
            background: white; border-radius: 16px;
            padding: 36px 30px; max-width: 380px; width: 90%;
            text-align: center;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .success-icon {
            width: 58px; height: 58px; border-radius: 50%;
            background: #e8f5e9;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 14px;
            font-size: 26px; color: #2e7d32;
        }
        .success-card h3 { font-size: 17px; font-weight: 700; color: #222; margin-bottom: 8px; }
        .success-card p  { font-size: 13px; color: #888; margin-bottom: 22px; line-height: 1.5; }
        .success-btns { display: flex; gap: 10px; justify-content: center; }
        .btn-view-records {
            padding: 10px 22px; background: #1a2744; color: white;
            border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
            cursor: pointer; text-decoration: none;
        }
        .btn-add-another {
            padding: 10px 22px; background: #f5f5f5; color: #555;
            border: none; border-radius: 8px; font-size: 13px; font-weight: 600;
            cursor: pointer;
        }

        /* ── Responsive ── */

        /* Tablet: stack panels, each scrolls, map still big */
        @media (max-width: 1024px) {
            .form-layout {
                grid-template-columns: 1fr;
                overflow-y: auto;    /* stacked panels need outer scroll */
                overflow-x: hidden;
            }
            .ldc-panel {
                overflow-y: visible;
                height: auto;
            }
            .right-panel {
                height: auto;
                overflow: visible;
            }
            .map-preview {
                flex: none;
                height: 420px;
            }
        }

        /* Mobile */
        @media (max-width: 768px) {
            .sidebar { width: 56px; }
            .sidebar .nav-item span,
            .sidebar .logout-btn span { display: none; }
            .sidebar .nav-item { justify-content: center; padding: 14px; gap: 0; }
            .sidebar .logout-btn { justify-content: center; padding: 11px; gap: 0; }
            .main-content { margin-left: 56px; }
            .top-bar { padding: 10px 16px; }
            .search-box { max-width: 200px; }
            .content-area { padding: 12px 12px 14px; }
            .field-row.triple { grid-template-columns: 1fr 1fr; }
            .map-preview { height: 320px; }
            .user-name, .user-role-label, .chevron { display: none; }
        }

        @media (max-width: 480px) {
            .field-row,
            .field-row.triple { grid-template-columns: 1fr; }
            .map-preview { height: 260px; }
            .action-row { flex-direction: column; }
            .btn-cancel, .btn-save { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="Land GIS">
        </div>
        <nav class="nav-menu">
            <a href="/dashboard"    class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer"   class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item"><i class="fas fa-file-alt"></i><span>Land Records</span></a>
            <a href="/add-record"   class="nav-item active"><i class="fas fa-plus-square"></i><span>Add Record</span></a>
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
                <input type="text" class="search-input" placeholder="Search documents...">
            </div>
            <div class="top-right">
                <i class="fas fa-bell bell-icon"></i>
                <a href="/profile" class="user-chip" style="text-decoration:none;cursor:pointer">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div>
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role-label">{{ strtoupper(auth()->user()->role) }}</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content-area">
            <h1 class="page-title">Add Land Survey Record</h1>

            <div id="formAlert" style="display:none"></div>

            <div class="form-layout">

                <!-- ── LEFT: LDC Form ── -->
                <div class="ldc-panel">
                    <div class="ldc-panel-title">
                        <i class="fas fa-clipboard-list" style="margin-right:6px"></i>
                        Land Data Computation (LDC)
                    </div>

                    <!-- Row 1: LSN + Barangay/Zone -->
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Lot/Survey No. (LSN)<span class="req">*</span></label>
                            <input type="text" id="f_lsn" class="field-input" placeholder="e.g. Psd-123456">
                            <span class="field-error" id="err_lsn"></span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Barangay No./Zone</label>
                            <input type="text" id="f_zone" class="field-input" placeholder="e.g. Zone 3">
                        </div>
                    </div>

                    <!-- Row 2: Owner + Named Barangay -->
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Owner (Original Claimant)<span class="req">*</span></label>
                            <input type="text" id="f_owner" class="field-input" placeholder="Full name">
                            <span class="field-error" id="err_owner"></span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Named Barangay</label>
                            <input type="text" id="f_barangay" class="field-input" placeholder="e.g. Brgy. Anonas">
                        </div>
                    </div>

                    <!-- Row 3: Location + Municipality/City -->
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Location</label>
                            <input type="text" id="f_location" class="field-input" placeholder="Full address">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Mun./City</label>
                            <input type="text" id="f_municipality" class="field-input" placeholder="e.g. Urdaneta City">
                        </div>
                    </div>

                    <!-- Row 4: Containing Area + Province -->
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Containing An Area of (sqm)<span class="req">*</span></label>
                            <input type="number" id="f_total_area" class="field-input" placeholder="e.g. 5000" min="1" oninput="onAreaChange()">
                            <span class="field-error" id="err_area"></span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Province</label>
                            <input type="text" id="f_province" class="field-input" placeholder="e.g. Pangasinan">
                        </div>
                    </div>

                    <!-- Row 5: Date Registered + Island (notes) -->
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Date Registered</label>
                            <input type="date" id="f_date" class="field-input">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Island / Notes</label>
                            <input type="text" id="f_notes" class="field-input" placeholder="Optional notes">
                        </div>
                    </div>

                    <!-- Section: Lot Computation -->
                    <div class="section-divider">Lot Computation</div>

                    <!-- Row: Number of lots + area per lot -->
                    <div class="field-row">
                        <div class="field-group">
                            <label class="field-label">Number of Lots<span class="req">*</span></label>
                            <input type="number" id="f_lot_count" class="field-input" placeholder="e.g. 10" min="1" max="500" oninput="onLotCountChange()">
                            <span class="field-error" id="err_lots"></span>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Area Per Lot (computed, sqm)</label>
                            <input type="text" id="f_area_per_lot" class="field-input" placeholder="Auto-calculated" readonly>
                        </div>
                    </div>

                    <!-- Computed summary chip -->
                    <div class="computed-row" id="computedRow" style="display:none">
                        <i class="fas fa-calculator"></i>
                        <span>
                            <strong id="computedNum">0</strong> lots &times;
                            <strong id="computedAreaEach">0</strong> sqm =
                            <strong id="computedTotal">0</strong> sqm total
                            (<strong id="computedHa">0</strong> ha)
                        </span>
                    </div>

                    <!-- Lot owner assignment -->
                    <div class="section-divider" style="margin-top:16px">Lot Owner Assignment</div>
                    <div class="field-group">
                        <label class="field-label">Assign Owner per Lot</label>
                        <div class="lot-owners-wrap" id="lotOwnersWrap">
                            <div class="lot-placeholder" id="lotPlaceholder">Enter number of lots above to assign owners</div>
                        </div>
                    </div>
                </div>

                <!-- ── RIGHT panel ── -->
                <div class="right-panel">

                    <!-- Map preview -->
                    <div class="map-preview">
                        <div id="previewMap"></div>
                        <div class="map-pick-hint" id="mapHint">
                            <i class="fas fa-map-pin"></i> Click map to set center location
                        </div>
                    </div>

                    <!-- Boundary / coordinates section -->
                    <div class="boundary-panel">
                        <div class="boundary-title">
                            <i class="fas fa-draw-polygon" style="margin-right:6px"></i>BOUNDARY
                        </div>

                        <!-- Row: Line + Bearing + Distance -->
                        <div class="field-row triple">
                            <div class="field-group">
                                <label class="field-label">Line</label>
                                <input type="text" id="f_line" class="field-input" placeholder="e.g. N-S">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Bearing</label>
                                <input type="text" id="f_bearing" class="field-input" placeholder="e.g. N 45°E">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Distance (m)</label>
                                <input type="number" id="f_distance" class="field-input" placeholder="e.g. 120">
                            </div>
                        </div>

                        <!-- Row: Lot No. + Tenant/Owner/Claimant -->
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Lot No.</label>
                                <input type="text" id="f_lot_no" class="field-input" placeholder="e.g. 1-A">
                            </div>
                            <div class="field-group">
                                <label class="field-label">Tenant/Owner/Claimant</label>
                                <input type="text" id="f_claimant" class="field-input" placeholder="Name">
                            </div>
                        </div>

                        <!-- Coordinates (set by map click) -->
                        <div class="field-row">
                            <div class="field-group">
                                <label class="field-label">Center Latitude<span class="req">*</span></label>
                                <input type="text" id="f_lat" class="field-input" placeholder="Click map to set" readonly>
                                <span class="field-error" id="err_lat"></span>
                            </div>
                            <div class="field-group">
                                <label class="field-label">Center Longitude<span class="req">*</span></label>
                                <input type="text" id="f_lng" class="field-input" placeholder="Click map to set" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Action buttons -->
                    <div class="action-row">
                        <button type="button" class="btn-cancel" onclick="resetForm()">
                            <i class="fas fa-times"></i> Cancel
                        </button>
                        <button type="button" class="btn-save" id="saveBtn" onclick="submitRecord()">
                            <i class="fas fa-save"></i> Save Record
                        </button>
                    </div>

                </div><!-- /right-panel -->
            </div><!-- /form-layout -->
        </div><!-- /content-area -->
    </div><!-- /main-content -->

    <!-- Success modal -->
    <div class="success-overlay" id="successOverlay">
        <div class="success-card">
            <div class="success-icon"><i class="fas fa-check"></i></div>
            <h3>Record Saved!</h3>
            <p id="successMsg">The land survey record has been saved successfully and lots have been generated on the map.</p>
            <div class="success-btns">
                <a href="/land-records" class="btn-view-records">View Records</a>
                <button class="btn-add-another" onclick="addAnother()">Add Another</button>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        const CSRF = '{{ csrf_token() }}';

        // ── Map init ─────────────────────────────────────────────
        const previewMap = L.map('previewMap', {
            center: [15.9754, 120.5701],
            zoom: 14,
            zoomControl: true,
        });

        L.tileLayer(
            'https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',
            { attribution: 'Tiles © Esri', maxZoom: 20 }
        ).addTo(previewMap);

        let centerMarker = null;
        let previewPolygons = [];

        previewMap.on('click', function(e) {
            const { lat, lng } = e.latlng;
            document.getElementById('f_lat').value = lat.toFixed(7);
            document.getElementById('f_lng').value = lng.toFixed(7);
            document.getElementById('f_lat').classList.remove('invalid');
            document.getElementById('err_lat').classList.remove('show');

            // Drop/move pin
            if (centerMarker) previewMap.removeLayer(centerMarker);
            centerMarker = L.marker([lat, lng], {
                icon: L.divIcon({
                    className: '',
                    html: `<div style="
                        width:14px;height:14px;border-radius:50%;
                        background:#2979ff;border:2px solid white;
                        box-shadow:0 2px 6px rgba(0,0,0,0.4)
                    "></div>`,
                    iconAnchor: [7, 7],
                })
            }).addTo(previewMap);

            document.getElementById('mapHint').classList.add('hide');
            renderPreviewPolygons(lat, lng);
        });

        // ── LDC computation ──────────────────────────────────────
        function onAreaChange() {
            updateComputed();
            const lat = parseFloat(document.getElementById('f_lat').value);
            const lng = parseFloat(document.getElementById('f_lng').value);
            if (!isNaN(lat) && !isNaN(lng)) renderPreviewPolygons(lat, lng);
        }

        function onLotCountChange() {
            updateComputed();
            buildLotOwnerRows();
            const lat = parseFloat(document.getElementById('f_lat').value);
            const lng = parseFloat(document.getElementById('f_lng').value);
            if (!isNaN(lat) && !isNaN(lng)) renderPreviewPolygons(lat, lng);
        }

        function updateComputed() {
            const area  = parseFloat(document.getElementById('f_total_area').value) || 0;
            const count = parseInt(document.getElementById('f_lot_count').value) || 0;

            if (area > 0 && count > 0) {
                const perLot = (area / count).toFixed(2);
                document.getElementById('f_area_per_lot').value = perLot;
                document.getElementById('computedNum').textContent  = count;
                document.getElementById('computedAreaEach').textContent = Number(perLot).toLocaleString();
                document.getElementById('computedTotal').textContent = area.toLocaleString();
                document.getElementById('computedHa').textContent   = (area / 10000).toFixed(4);
                document.getElementById('computedRow').style.display = 'flex';
            } else {
                document.getElementById('f_area_per_lot').value = '';
                document.getElementById('computedRow').style.display = 'none';
            }
        }

        function buildLotOwnerRows() {
            const count  = parseInt(document.getElementById('f_lot_count').value) || 0;
            const wrap   = document.getElementById('lotOwnersWrap');
            const ph     = document.getElementById('lotPlaceholder');

            if (count < 1 || count > 500) {
                wrap.innerHTML = '';
                const p = document.createElement('div');
                p.className = 'lot-placeholder';
                p.id = 'lotPlaceholder';
                p.textContent = count > 500
                    ? 'Maximum 500 lots allowed'
                    : 'Enter number of lots above to assign owners';
                wrap.appendChild(p);
                return;
            }

            // Keep existing values
            const existing = {};
            wrap.querySelectorAll('.lot-owner-input').forEach(inp => {
                existing[inp.dataset.lot] = inp.value;
            });

            wrap.innerHTML = '';
            for (let i = 1; i <= count; i++) {
                const row = document.createElement('div');
                row.className = 'lot-owner-row';
                row.innerHTML = `
                    <div class="lot-num-badge">${i}</div>
                    <input type="text" class="lot-owner-input" data-lot="${i}"
                           placeholder="Owner name for Lot ${i}"
                           value="${existing[i] || ''}">
                `;
                wrap.appendChild(row);
            }
        }

        // ── Generate preview polygons (same logic as server) ─────
        function generateRectangles(centerLat, centerLng, totalAreaSqm, count) {
            const latPerMeter = 1 / 111000;
            const lngPerMeter = 1 / (111000 * Math.cos(centerLat * Math.PI / 180));
            const areaPerLot  = totalAreaSqm / count;
            const sideMeter   = Math.sqrt(areaPerLot);
            const halfLat     = (sideMeter * latPerMeter) / 2;
            const halfLng     = (sideMeter * lngPerMeter) / 2;

            const cols = Math.ceil(Math.sqrt(count));
            const gridWidthLng  = cols * halfLng * 2;
            const gridHeightLat = Math.ceil(count / cols) * halfLat * 2;
            const startLat = centerLat + gridHeightLat / 2;
            const startLng = centerLng - gridWidthLng / 2;

            const polys = [];
            for (let i = 0; i < count; i++) {
                const col = i % cols;
                const row = Math.floor(i / cols);
                const topLat    = startLat - row * halfLat * 2;
                const bottomLat = topLat - halfLat * 2;
                const leftLng   = startLng + col * halfLng * 2;
                const rightLng  = leftLng + halfLng * 2;
                polys.push([[topLat, leftLng],[topLat, rightLng],[bottomLat, rightLng],[bottomLat, leftLng]]);
            }
            return polys;
        }

        function renderPreviewPolygons(lat, lng) {
            // Remove old preview polygons
            previewPolygons.forEach(p => previewMap.removeLayer(p));
            previewPolygons = [];

            const area  = parseFloat(document.getElementById('f_total_area').value);
            const count = parseInt(document.getElementById('f_lot_count').value);
            if (!area || !count || area < 1 || count < 1 || count > 500) return;

            const rects = generateRectangles(lat, lng, area, count);
            rects.forEach((rect, i) => {
                const poly = L.polygon(rect, {
                    color: '#2979ff',
                    fillColor: '#2979ff',
                    fillOpacity: 0.22,
                    weight: 1.5,
                }).addTo(previewMap);
                previewPolygons.push(poly);
            });

            // Fit map to polygons
            try {
                previewMap.fitBounds(
                    L.featureGroup(previewPolygons).getBounds(),
                    { padding: [20, 20], maxZoom: 18 }
                );
            } catch(e) {}
        }

        // ── Validation ───────────────────────────────────────────
        function showErr(id, msg) {
            const el = document.getElementById(id);
            el.textContent = msg;
            el.classList.add('show');
        }
        function clearErr(id) {
            const el = document.getElementById(id);
            if (el) { el.textContent = ''; el.classList.remove('show'); }
        }

        function validate() {
            let ok = true;
            ['err_lsn','err_owner','err_area','err_lots','err_lat'].forEach(clearErr);

            const lsn   = document.getElementById('f_lsn').value.trim();
            const owner = document.getElementById('f_owner').value.trim();
            const area  = parseFloat(document.getElementById('f_total_area').value);
            const count = parseInt(document.getElementById('f_lot_count').value);
            const lat   = parseFloat(document.getElementById('f_lat').value);
            const lng   = parseFloat(document.getElementById('f_lng').value);

            if (!lsn)           { showErr('err_lsn',   'Survey number is required.');   document.getElementById('f_lsn').classList.add('invalid');   ok = false; }
            if (!owner)         { showErr('err_owner', 'Owner name is required.');       document.getElementById('f_owner').classList.add('invalid'); ok = false; }
            if (!area || area < 1) { showErr('err_area', 'Enter a valid area (≥ 1 sqm).'); document.getElementById('f_total_area').classList.add('invalid'); ok = false; }
            if (!count || count < 1 || count > 500) { showErr('err_lots', 'Enter number of lots (1–500).'); document.getElementById('f_lot_count').classList.add('invalid'); ok = false; }
            if (isNaN(lat) || isNaN(lng)) { showErr('err_lat', 'Click the map to set location.'); document.getElementById('f_lat').classList.add('invalid'); ok = false; }

            return ok;
        }

        // ── Submit ───────────────────────────────────────────────
        async function submitRecord() {
            if (!validate()) {
                document.getElementById('formAlert').style.display = 'none';
                // Scroll to first error
                const first = document.querySelector('.field-error.show');
                if (first) first.scrollIntoView({ behavior:'smooth', block:'center' });
                return;
            }

            const lsn   = document.getElementById('f_lsn').value.trim();
            const owner = document.getElementById('f_owner').value.trim();
            const area  = parseFloat(document.getElementById('f_total_area').value);
            const count = parseInt(document.getElementById('f_lot_count').value);
            const lat   = parseFloat(document.getElementById('f_lat').value);
            const lng   = parseFloat(document.getElementById('f_lng').value);
            const notes = [
                document.getElementById('f_notes').value.trim(),
                document.getElementById('f_zone').value.trim()       ? 'Zone: '     + document.getElementById('f_zone').value.trim()       : '',
                document.getElementById('f_municipality').value.trim() ? 'Mun: '    + document.getElementById('f_municipality').value.trim() : '',
                document.getElementById('f_province').value.trim()   ? 'Province: ' + document.getElementById('f_province').value.trim()   : '',
                document.getElementById('f_barangay').value.trim()   ? 'Brgy: '     + document.getElementById('f_barangay').value.trim()   : '',
                document.getElementById('f_location').value.trim()   ? 'Loc: '      + document.getElementById('f_location').value.trim()   : '',
                document.getElementById('f_date').value              ? 'Registered: '+ document.getElementById('f_date').value             : '',
                document.getElementById('f_line').value.trim()       ? 'Line: '     + document.getElementById('f_line').value.trim()       : '',
                document.getElementById('f_bearing').value.trim()    ? 'Bearing: '  + document.getElementById('f_bearing').value.trim()    : '',
                document.getElementById('f_distance').value.trim()   ? 'Distance: ' + document.getElementById('f_distance').value.trim() + 'm' : '',
                document.getElementById('f_lot_no').value.trim()     ? 'Lot ref: '  + document.getElementById('f_lot_no').value.trim()     : '',
                document.getElementById('f_claimant').value.trim()   ? 'Claimant: ' + document.getElementById('f_claimant').value.trim()   : '',
            ].filter(Boolean).join(' | ');

            // Build lots array from owner inputs
            const lots = [];
            for (let i = 1; i <= count; i++) {
                const inp = document.querySelector(`.lot-owner-input[data-lot="${i}"]`);
                lots.push({
                    lot_number: i,
                    owner_name: inp && inp.value.trim() ? inp.value.trim() : owner,
                });
            }

            const btn = document.getElementById('saveBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving…';

            try {
                const res  = await fetch('/api/surveys', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        lsn, original_owner: owner,
                        total_area: area, center_lat: lat, center_lng: lng,
                        lot_count: count, notes,
                        lots,
                    }),
                });

                const data = await res.json();

                if (res.ok && data.success) {
                    document.getElementById('successMsg').textContent =
                        `LSN "${lsn}" saved with ${count} lot${count > 1 ? 's' : ''} (${(area/10000).toFixed(4)} ha). Polygons generated on the map.`;
                    document.getElementById('successOverlay').classList.add('show');
                } else if (data.errors) {
                    // Map server errors back to fields
                    const errMap = {
                        lsn:            'err_lsn',
                        original_owner: 'err_owner',
                        total_area:     'err_area',
                        lot_count:      'err_lots',
                        center_lat:     'err_lat',
                    };
                    let msgs = [];
                    Object.entries(data.errors).forEach(([field, errs]) => {
                        const errId = errMap[field];
                        if (errId) showErr(errId, errs[0]);
                        msgs.push(...errs);
                    });
                    showFormAlert('error', msgs.join('<br>'));
                } else {
                    showFormAlert('error', data.message || 'Failed to save record. Please try again.');
                }
            } catch(e) {
                showFormAlert('error', 'Network error. Please check your connection and try again.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-save"></i> Save Record';
            }
        }

        function showFormAlert(type, html) {
            const el = document.getElementById('formAlert');
            el.className = `alert alert-${type}`;
            el.innerHTML = `<i class="fas fa-${type === 'error' ? 'exclamation-circle' : 'check-circle'}"></i><span>${html}</span>`;
            el.style.display = 'flex';
            el.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function resetForm() {
            [
                'f_lsn','f_zone','f_owner','f_barangay','f_location','f_municipality',
                'f_total_area','f_province','f_date','f_notes','f_lot_count',
                'f_area_per_lot','f_lat','f_lng',
                'f_line','f_bearing','f_distance','f_lot_no','f_claimant'
            ].forEach(id => {
                const el = document.getElementById(id);
                if (el) { el.value = ''; el.classList.remove('invalid'); }
            });

            ['err_lsn','err_owner','err_area','err_lots','err_lat'].forEach(clearErr);

            document.getElementById('computedRow').style.display = 'none';
            document.getElementById('formAlert').style.display = 'none';

            // Reset lot owners
            document.getElementById('lotOwnersWrap').innerHTML =
                '<div class="lot-placeholder" id="lotPlaceholder">Enter number of lots above to assign owners</div>';

            // Clear map preview
            previewPolygons.forEach(p => previewMap.removeLayer(p));
            previewPolygons = [];
            if (centerMarker) { previewMap.removeLayer(centerMarker); centerMarker = null; }
            document.getElementById('mapHint').classList.remove('hide');
        }

        function addAnother() {
            document.getElementById('successOverlay').classList.remove('show');
            resetForm();
        }

        // Clear invalid state on input
        ['f_lsn','f_owner','f_total_area','f_lot_count'].forEach(id => {
            document.getElementById(id).addEventListener('input', function() {
                this.classList.remove('invalid');
            });
        });
    </script>
</body>
</html>
