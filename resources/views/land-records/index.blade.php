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
        .sidebar { width: 200px; background: #1a2744; display: flex; flex-direction: column; position: fixed; height: 100vh; left: 0; top: 0; z-index: 1000; overflow: hidden; }
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
        .lot-list-item { display: flex; justify-content: space-between; align-items: center; padding: 7px 10px; border-radius: 6px; margin-bottom: 4px; background: #f9f9f9; border: 1px solid #eee; flex-wrap: wrap; gap: 4px; }
        .lot-selected { border-color: #1a2744 !important; background: #f0f4ff !important; }
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

        /* ── Info tooltips ── */
        .info-tip {
            display: inline-flex; align-items: center; justify-content: center;
            width: 15px; height: 15px; border-radius: 50%;
            background: #c8d6f0; color: #1a2744;
            font-size: 9.5px; font-weight: 700; cursor: help;
            position: relative; flex-shrink: 0; margin-left: 4px;
            vertical-align: middle;
        }
        .info-tip::after {
            content: attr(data-tip);
            position: absolute; bottom: calc(100% + 6px); left: 50%;
            transform: translateX(-50%);
            background: #1a2744; color: white;
            padding: 7px 11px; border-radius: 8px;
            font-size: 11px; font-weight: 400; white-space: normal;
            width: 210px; max-width: 210px; z-index: 10000;
            opacity: 0; pointer-events: none; transition: opacity 0.2s;
            line-height: 1.4; box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            text-align: left;
        }
        .info-tip::before {
            content: ''; position: absolute; bottom: calc(100% + 1px); left: 50%;
            transform: translateX(-50%); border: 5px solid transparent;
            border-top-color: #1a2744; opacity: 0; transition: opacity 0.2s;
        }
        .info-tip:hover::after, .info-tip:hover::before { opacity: 1; }

        /* ── Map style toggle ── */
        .map-style-toggle {
            position: absolute; top: 10px; right: 10px; z-index: 1000;
            width: 38px; height: 38px; border-radius: 8px;
            background: white; border: none; cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; color: #1a2744; transition: background 0.15s;
        }
        .map-style-toggle:hover { background: #f0f4ff; }
        .map-style-label {
            position: absolute; top: 52px; right: 10px; z-index: 1000;
            font-size: 10px; color: #555; background: rgba(255,255,255,0.92);
            padding: 2px 8px; border-radius: 5px; pointer-events: none;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        /* ── Computed chip ── */
        .computed-chip {
            display: flex; align-items: center; gap: 6px;
            background: #e3edf5; border-radius: 6px; padding: 7px 10px;
            margin-top: 6px; font-size: 11px; color: #1a2744;
        }
        .computed-chip i { font-size: 13px; }
        .computed-chip strong { font-weight: 800; }

        /* ── Required marker ── */
        .req { color: #e53935; margin-left: 2px; }

        /* ── Pangasinan search & pins ── */
        .pangasinan-search {
            position: absolute; top: 10px; left: 10px; z-index: 1000;
            width: 220px;
        }
        .muni-input-wrap { position: relative; }
        .muni-input {
            width: 100%; padding: 8px 32px 8px 34px;
            border: none; border-radius: 8px;
            font-size: 12.5px; color: #333;
            background: white;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            outline: none;
        }
        .muni-input:focus { box-shadow: 0 2px 12px rgba(0,0,0,0.2); }
        .muni-input-icon {
            position: absolute; left: 11px; top: 50%; transform: translateY(-50%);
            color: #1a2744; font-size: 13px; pointer-events: none;
        }
        .muni-toggle-btn {
            position: absolute; right: 8px; top: 50%; transform: translateY(-50%);
            width: 24px; height: 24px; border: none; border-radius: 5px;
            background: #e8edf5; color: #1a2744; cursor: pointer;
            font-size: 11px; display: flex; align-items: center; justify-content: center;
            transition: background 0.15s;
        }
        .muni-toggle-btn:hover { background: #d0dcef; }
        .muni-toggle-btn.active { background: #1a2744; color: white; }
        .muni-dropdown {
            display: none; position: absolute; top: calc(100% + 4px); left: 0; right: 0;
            max-height: 260px; overflow-y: auto;
            background: white; border-radius: 8px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.18);
            z-index: 1001;
        }
        .muni-dropdown.show { display: block; }
        .muni-item {
            padding: 8px 12px; font-size: 12px; color: #444;
            cursor: pointer; display: flex; align-items: center; gap: 8px;
            border-bottom: 1px solid #f5f5f5; transition: background 0.1s;
        }
        .muni-item:last-child { border-bottom: none; }
        .muni-item:hover { background: #f0f4ff; color: #1a2744; }
        .muni-item i { font-size: 10px; color: #aaa; }
        .muni-item:hover i { color: #1a2744; }
        .muni-item .muni-type { font-size: 9.5px; color: #aaa; margin-left: auto; text-transform: uppercase; }

        /* Municipality pin on map */
        .muni-pin-marker {
            display: flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 50% 50% 50% 0;
            background: #1a2744; color: white; font-size: 9px; font-weight: 700;
            transform: rotate(-45deg); box-shadow: 0 2px 6px rgba(0,0,0,0.3);
            border: 2px solid white;
        }
        .muni-pin-marker span { transform: rotate(45deg); }
        .muni-pin-marker.city { background: #2e7d32; }
        .leaflet-popup-content .muni-popup-name { font-size: 13px; font-weight: 700; color: #1a2744; }
        .leaflet-popup-content .muni-popup-type { font-size: 11px; color: #888; margin-top: 2px; }

        /* ── Attachment widget ── */
        .attach-section { margin-top: 12px; }
        .attach-title { font-size: 11px; font-weight: 700; color: #1a2744; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 8px; display: flex; align-items: center; gap: 6px; }
        .attach-title .attach-count { background: #e8edf5; color: #1a2744; border-radius: 10px; padding: 1px 7px; font-size: 9.5px; font-weight: 700; }
        .attach-drop {
            border: 1.5px dashed #c8d6f0; border-radius: 8px; padding: 12px 10px;
            text-align: center; cursor: pointer; transition: all 0.15s; background: #f8faff;
        }
        .attach-drop:hover, .attach-drop.dragover { border-color: #1a2744; background: #f0f4ff; }
        .attach-drop-text { font-size: 11.5px; color: #aaa; }
        .attach-drop input[type=file] { display: none; }
        .attach-list { margin-top: 8px; display: flex; flex-direction: column; gap: 5px; }
        .attach-item {
            display: flex; align-items: center; gap: 8px;
            background: white; border: 1px solid #eee; border-radius: 7px;
            padding: 6px 10px; font-size: 12px;
        }
        .attach-icon { font-size: 14px; flex-shrink: 0; }
        .attach-icon.pdf   { color: #e53935; }
        .attach-icon.image { color: #1976d2; }
        .attach-icon.doc   { color: #2979ff; }
        .attach-icon.xls   { color: #2e7d32; }
        .attach-icon.file  { color: #888; }
        .attach-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; color: #333; }
        .attach-meta { font-size: 10.5px; color: #bbb; white-space: nowrap; }
        .attach-btn { background: none; border: none; cursor: pointer; padding: 2px 5px; border-radius: 4px; font-size: 12px; color: #bbb; transition: all 0.15s; }
        .attach-btn:hover { color: #1a2744; background: #f0f4ff; }
        .attach-btn.del:hover { color: #e53935; background: #ffebee; }
        .attach-uploading { font-size: 11.5px; color: #888; text-align: center; padding: 8px; }

        /* ── Custom barangay pins ── */
        .brgy-pin-marker {
            display: flex; align-items: center; justify-content: center;
            width: 20px; height: 20px; border-radius: 50% 50% 50% 0;
            background: #e53935; color: white; font-size: 8px; font-weight: 700;
            transform: rotate(-45deg); box-shadow: 0 2px 6px rgba(0,0,0,0.35);
            border: 2px solid white; cursor: pointer;
        }
        .brgy-pin-marker span { transform: rotate(45deg); }

        /* ── Pin-mode button on map ── */
        .pin-mode-btn {
            position: absolute; top: 56px; right: 10px; z-index: 1000;
            padding: 0 12px; height: 38px; border-radius: 8px;
            background: white; border: none; cursor: pointer;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
            display: flex; align-items: center; gap: 7px;
            font-size: 12px; font-weight: 600; color: #1a2744;
            transition: background 0.15s;
        }
        .pin-mode-btn:hover { background: #f0f4ff; }
        .pin-mode-btn.active { background: #e53935; color: white; }
        .pin-mode-btn.active:hover { background: #c62828; }

        /* ── Province / Pins tab ── */
        .province-search-wrap { position: relative; margin-bottom: 10px; }
        .province-search-input {
            width: 100%; padding: 8px 10px 8px 32px;
            border: 1px solid #ddd; border-radius: 8px; font-size: 12px; color: #333;
            background: white;
        }
        .province-search-input:focus { outline: none; border-color: #1a2744; }
        .province-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 12px; pointer-events: none; }

        .province-group { margin-bottom: 12px; }
        .province-group-header {
            display: flex; align-items: center; justify-content: space-between;
            font-size: 10.5px; font-weight: 700; color: #aaa; text-transform: uppercase;
            letter-spacing: 0.5px; padding: 4px 2px 6px; cursor: pointer; user-select: none;
        }
        .province-group-header:hover { color: #1a2744; }
        .province-group-header .pg-count { background: #e8edf5; color: #1a2744; border-radius: 10px; padding: 1px 7px; font-size: 9.5px; font-weight: 700; }
        .province-group-header .pg-arrow { transition: transform 0.2s; font-size: 10px; }
        .province-group-header.collapsed .pg-arrow { transform: rotate(-90deg); }
        .province-group-items { display: flex; flex-direction: column; gap: 3px; }
        .province-group.collapsed .province-group-items { display: none; }

        .province-item {
            display: flex; align-items: center; gap: 8px;
            padding: 7px 10px; background: white; border-radius: 7px;
            cursor: pointer; font-size: 12px; color: #444;
            border: 1px solid #f0f0f0; transition: all 0.12s;
        }
        .province-item:hover { background: #f0f4ff; color: #1a2744; border-color: #d0dcef; }
        .province-item .pi-icon { font-size: 11px; color: #bbb; flex-shrink: 0; }
        .province-item:hover .pi-icon { color: #1a2744; }
        .province-item .pi-name { flex: 1; font-weight: 500; }
        .province-item .pi-badge { font-size: 9.5px; color: #aaa; background: #f5f5f5; padding: 1px 6px; border-radius: 8px; white-space: nowrap; }
        .province-item .pi-badge.brgy { background: #ffeaea; color: #e53935; }
        .province-item .pi-badge.barangay { background: #ffeaea; color: #e53935; }
        .province-item .pi-badge.city { background: #e8f5e9; color: #2e7d32; }
        .province-item .pi-badge.other { background: #fff3e0; color: #e65100; }
        .province-item .pi-del { background: none; border: none; color: #ddd; cursor: pointer; font-size: 11px; padding: 2px 3px; margin-left: 2px; }
        .province-item .pi-del:hover { color: #e53935; }

        .pins-empty { text-align: center; color: #bbb; font-size: 12px; padding: 20px 0; }
        .pins-add-hint { background: #fff8e1; border: 1px solid #ffe082; border-radius: 8px; padding: 9px 12px; font-size: 11.5px; color: #6d4c00; margin-bottom: 10px; display: none; }
        .pins-add-hint.show { display: block; }

        /* ── Add Pin Modal ── */
        .add-pin-overlay {
            display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999;
            align-items: center; justify-content: center;
        }
        .add-pin-overlay.show { display: flex; }
        .add-pin-modal {
            background: white; border-radius: 14px; padding: 24px;
            width: 320px; box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .add-pin-modal h3 { font-size: 14px; font-weight: 700; color: #1a2744; margin-bottom: 14px; }
        .add-pin-coords { font-size: 11px; color: #888; background: #f5f5f5; border-radius: 6px; padding: 6px 10px; margin-bottom: 12px; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="logo-section"><img src="{{ asset('images/Darlandicon.png') }}" alt="Logo"></div>
    <nav class="nav-menu">
        <a href="/dashboard"    class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
        <a href="/map-viewer"   class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
        <a href="/land-records" class="nav-item active"><i class="fas fa-layer-group"></i><span>Land Records</span></a>
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

            <!-- Pangasinan municipality search -->
            <div class="pangasinan-search">
                <div class="muni-input-wrap">
                    <i class="fas fa-search muni-input-icon"></i>
                    <input type="text" class="muni-input" id="muniSearch" placeholder="Search Pangasinan municipalities..." oninput="filterMuni(this.value)" onfocus="showMuniDropdown()" autocomplete="off">
                    <button class="muni-toggle-btn active" id="muniToggleBtn" onclick="toggleMuniPins()" title="Show/hide municipality pins"><i class="fas fa-map-marker-alt" id="muniToggleIcon"></i></button>
                </div>
                <div class="muni-dropdown" id="muniDropdown"></div>
            </div>

            <button class="map-style-toggle" id="mapStyleToggle" onclick="toggleMapStyle()" title="Switch map style"><i class="fas fa-satellite" id="mapStyleIcon"></i></button>
            <div class="map-style-label" id="mapStyleLabel">Satellite</div>
            @if(auth()->user()->role === 'admin')
            <button class="pin-mode-btn" id="pinModeBtn" onclick="togglePinMode()" title="Drop a barangay pin on the map">
                <i class="fas fa-map-pin"></i> <span id="pinModeBtnText">Add Pin</span>
            </button>
            <div class="pins-add-hint" id="pinAddHint">
                <i class="fas fa-hand-pointer"></i> Click anywhere on the map to place a barangay pin. Press <strong>Esc</strong> or click <strong>Add Pin</strong> again to cancel.
            </div>
            @endif
            <div class="map-hint" id="mapHint"><i class="fas fa-draw-polygon"></i> Draw mode: click points to trace a lot boundary. Double-click to finish.</div>
        </div>

        <!-- Right Panel -->
        <div class="right-panel">
            <div class="panel-tabs">
                <button class="tab-btn active" onclick="switchTab('ldc')"><i class="fas fa-calculator"></i> LDC</button>
                <button class="tab-btn" onclick="switchTab('surveys')"><i class="fas fa-list"></i> Surveys</button>
                <button class="tab-btn" onclick="switchTab('pins')"><i class="fas fa-map-pin"></i> Pins</button>
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
                    <div class="ldc-section-title"><i class="fas fa-map-marker-alt"></i> Survey Info <span class="info-tip" data-tip="Enter the land survey details. Fields marked * are required. Location fields help identify the property.">?</span></div>

                    <div class="form-grid-2">
                        <div class="form-row">
                            <label class="form-label">LSN (Survey Number) <span class="req">*</span></label>
                            <input type="text" id="lsnInput" class="form-input" placeholder="e.g. Psd-123456">
                        </div>
                        <div class="form-row">
                            <label class="form-label">Date Registered</label>
                            <input type="date" id="surveyDate" class="form-input">
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Original Owner <span class="req">*</span></label>
                        <input type="text" id="originalOwner" class="form-input" placeholder="Full name">
                    </div>

                    <div class="form-grid-2">
                        <div class="form-row">
                            <label class="form-label">Province <span class="info-tip" data-tip="Province is locked to Pangasinan for this system.">?</span></label>
                            <select id="surveyProvince" class="form-input" onchange="onProvinceChange(this.value)">
                                <option value="Pangasinan" selected>Pangasinan</option>
                            </select>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Municipality/City <span class="info-tip" data-tip="Select a municipality from the list after choosing a province.">?</span></label>
                            <select id="surveyMunicipality" class="form-input" onchange="onMunicipalityChange(this.value)" disabled>
                                <option value="">— Select Municipality —</option>
                            </select>
                            <input type="text" id="surveyMunicipalityCustom" class="form-input"
                                   placeholder="Type municipality name" style="display:none;margin-top:6px"
                                   oninput="onCustomMuniInput()">
                        </div>
                    </div>

                    <div class="form-grid-2">
                        <div class="form-row">
                            <label class="form-label">Barangay <span class="info-tip" data-tip="Select a barangay after choosing a municipality.">?</span></label>
                            <select id="surveyBarangay" class="form-input" disabled>
                                <option value="">— Select Barangay —</option>
                            </select>
                            <input type="text" id="surveyBarangayCustom" class="form-input"
                                   placeholder="Type barangay name" style="display:none;margin-top:6px">
                        </div>
                        <div class="form-row">
                            <label class="form-label">Barangay No./Zone</label>
                            <input type="text" id="surveyZone" class="form-input" placeholder="e.g. Zone 3">
                        </div>
                    </div>

                    <div class="form-row">
                        <label class="form-label">Location / Address</label>
                        <input type="text" id="surveyLocation" class="form-input" placeholder="Street or full address (optional)">
                    </div>

                    <div class="form-grid-2">
                        <div class="form-row">
                            <label class="form-label">Latitude <span class="info-tip" data-tip="Decimal degrees (e.g. 15.9342). Used to auto-pin the survey on the map. You can also click Pick on the map to auto-fill.">?</span></label>
                            <div style="position:relative">
                                <input type="number" id="surveyLat" class="form-input" placeholder="e.g. 15.9342" step="any"
                                       oninput="onCoordInput()" style="padding-right:32px">
                                <i class="fas fa-crosshairs" title="Or click Pick on the map"
                                   style="position:absolute;right:9px;top:50%;transform:translateY(-50%);color:#bbb;font-size:12px;pointer-events:none"></i>
                            </div>
                        </div>
                        <div class="form-row">
                            <label class="form-label">Longitude <span class="info-tip" data-tip="Decimal degrees (e.g. 120.5567). Used to auto-pin the survey on the map.">?</span></label>
                            <div style="position:relative">
                                <input type="number" id="surveyLng" class="form-input" placeholder="e.g. 120.5567" step="any"
                                       oninput="onCoordInput()" style="padding-right:32px">
                                <i class="fas fa-crosshairs"
                                   style="position:absolute;right:9px;top:50%;transform:translateY(-50%);color:#bbb;font-size:12px;pointer-events:none"></i>
                            </div>
                        </div>
                    </div>
                    <div id="coordPinHint" style="display:none;font-size:11px;color:#2e7d32;background:#e8f5e9;border-radius:5px;padding:5px 8px;margin-top:4px">
                        <i class="fas fa-map-pin"></i> Map will pan to this location when you save.
                    </div>

                    <div class="form-row" style="margin-top:4px">
                        <label class="form-label">Notes (optional)</label>
                        <input type="text" id="surveyNotes" class="form-input" placeholder="Additional notes">
                    </div>
                </div>

                <!-- Boundary Details -->
                <div class="ldc-section">
                    <div class="ldc-section-title"><i class="fas fa-draw-polygon"></i> Boundary Details <span class="info-tip" data-tip="Boundary info describes the physical edges of the land. Line is the direction (e.g. N-S). Bearing is the compass direction. Distance is the length in meters. These are optional but help with legal documentation.">?</span></div>
                    <div class="form-grid-2">
                        <div class="form-row">
                            <label class="form-label">Line</label>
                            <input type="text" id="boundaryLine" class="form-input" placeholder="e.g. N-S">
                        </div>
                        <div class="form-row">
                            <label class="form-label">Bearing</label>
                            <input type="text" id="boundaryBearing" class="form-input" placeholder="e.g. N 45°E">
                        </div>
                    </div>
                    <div class="form-grid-2">
                        <div class="form-row">
                            <label class="form-label">Distance (m)</label>
                            <input type="number" id="boundaryDistance" class="form-input" placeholder="e.g. 120">
                        </div>
                        <div class="form-row">
                            <label class="form-label">Lot Reference No.</label>
                            <input type="text" id="boundaryLotNo" class="form-input" placeholder="e.g. 1-A">
                        </div>
                    </div>
                    <div class="form-row">
                        <label class="form-label">Tenant / Claimant</label>
                        <input type="text" id="boundaryClaimant" class="form-input" placeholder="Name (if different from owner)">
                    </div>
                </div>

                <!-- ══ DRAW MODE ══ -->
                <div id="drawModePanel">
                    <div class="draw-steps">
                        <!-- Step 1 -->
                        <div class="draw-step active-step" id="step1">
                            <div class="step-header">
                                <div class="step-num">1</div>
                                <div class="step-title">Draw each lot on the map <span class="info-tip" data-tip="Click the Start Drawing button, then click points on the map to trace each lot boundary. Double-click to finish a lot. Repeat for every lot in the survey.">?</span></div>
                            </div>
                            <div class="step-desc">Fill in LSN, Owner, and Municipality above first, then draw.</div>
                            <div style="margin-top:8px">
                                <button class="btn-draw" id="drawBtn" onclick="toggleDrawing()">
                                    <i class="fas fa-draw-polygon"></i> <span id="drawBtnText">Start Drawing Lot</span>
                                </button>
                                <div style="display:flex;gap:6px;margin-top:6px">
                                    <button id="drawUndoBtn" onclick="undoLastPoint()" style="display:none;flex:1;padding:7px 10px;background:#fff3e0;color:#e65100;border:1.5px solid #ffcc80;border-radius:7px;font-size:12px;font-weight:600;cursor:pointer;align-items:center;justify-content:center;gap:5px" title="Remove the last placed point">
                                        <i class="fas fa-undo"></i> Undo Point
                                    </button>
                                </div>
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

                    <!-- Overlap warning banner (shown when overlap blocks drawing) -->
                    <div id="overlapWarning" style="display:none;background:#ffebee;border:1px solid #e53935;border-radius:7px;padding:10px 12px;margin-bottom:8px;font-size:12px;color:#c62828">
                        <i class="fas fa-ban"></i> <strong>Overlap detected — drawing blocked!</strong>
                        <div id="overlapDetails" style="margin-top:4px;font-size:11px"></div>
                        <div style="margin-top:6px;font-size:11px;color:#888">Plots cannot overlap existing lots. Please redraw in a non-overlapping area.</div>
                    </div>

                    <button class="btn-primary" onclick="saveLSNDraw()">
                        <i class="fas fa-save"></i> Save Land Survey
                    </button>
                    <button class="btn-secondary" onclick="resetDrawMode()">
                        <i class="fas fa-redo"></i> Reset
                    </button>

                    <!-- Quick attach section — shown after save -->
                    <div id="drawSaveAttachHint" style="display:none;margin-top:12px;background:#f0f4ff;border:1px solid #d0dcef;border-radius:8px;padding:10px 12px;font-size:12px;color:#1a2744">
                        <i class="fas fa-check-circle" style="color:#2e7d32;margin-right:6px"></i>
                        Survey saved! You can now <strong>attach documents or photos</strong> from the Surveys tab.
                        <button onclick="switchTab('surveys')" style="display:block;margin-top:8px;width:100%;padding:7px;background:#1a2744;color:white;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:600">
                            <i class="fas fa-paperclip"></i> Go to Survey → Attach Files
                        </button>
                    </div>
                </div>

                <!-- ══ GRID MODE ══ -->
                <div id="gridModePanel" style="display:none">
                    <div class="ldc-section">
                        <div class="ldc-section-title"><i class="fas fa-map-pin"></i> Center Location <span class="info-tip" data-tip="Click the Pick button, then click on the map to set the center point. Grid lots will be generated around this center location.">?</span></div>
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
                        <div class="ldc-section-title"><i class="fas fa-ruler-combined"></i> Area &amp; Division <span class="info-tip" data-tip="Enter the total land area in square meters and how many lots to divide it into. The system auto-calculates the area per lot and generates equal-sized grid rectangles on the map.">?</span></div>
                        <div class="form-grid-2">
                            <div class="form-row">
                                <label class="form-label">Total Area (sqm) <span class="req">*</span></label>
                                <input type="number" id="totalArea" class="form-input" placeholder="e.g. 50000" min="1" oninput="updateLotRows()">
                            </div>
                            <div class="form-row">
                                <label class="form-label">No. of Lots <span class="req">*</span></label>
                                <input type="number" id="lotCount" class="form-input" placeholder="e.g. 10" min="1" max="500" oninput="updateLotRows()">
                            </div>
                        </div>
                        <div class="area-display" id="areaPerLotDisplay" style="display:none">
                            Area per lot: <strong id="areaPerLotVal"></strong> sqm &nbsp;·&nbsp; <strong id="areaPerLotHa"></strong> ha
                        </div>
                        <div class="computed-chip" id="computedChip" style="display:none">
                            <i class="fas fa-calculator"></i>
                            <span><strong id="computedNum">0</strong> lots &times; <strong id="computedAreaEach">0</strong> sqm = <strong id="computedTotal">0</strong> sqm (<strong id="computedHa">0</strong> ha)</span>
                        </div>
                    </div>

                    <div class="ldc-section">
                        <div class="ldc-section-title"><i class="fas fa-users"></i> Lot Owners <span class="info-tip" data-tip="Assign an owner name to each lot. If left blank, the original owner from the Survey Info above will be used automatically.">?</span></div>
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

            <!-- ── Pins Tab ── -->
            <div class="tab-content" id="tab-pins">
                <!-- Province/location search -->
                <div class="province-search-wrap">
                    <i class="fas fa-search province-search-icon"></i>
                    <input type="text" id="provinceSearch" class="province-search-input"
                           placeholder="Search municipalities, cities, barangays…"
                           oninput="filterProvinceList(this.value)" autocomplete="off">
                </div>

                <div class="pins-add-hint" id="pinsTabHint" style="display:none"></div>

                <!-- Province groups list -->
                <div id="provinceGroupList"></div>
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

<!-- Add Barangay Pin Modal -->
<div class="add-pin-overlay" id="addPinOverlay">
    <div class="add-pin-modal">
        <h3><i class="fas fa-map-pin" style="color:#e53935;margin-right:7px"></i>Add Pin</h3>
        <div class="add-pin-coords" id="addPinCoords">Lat: — · Lng: —</div>

        <!-- Pin Type -->
        <div class="form-row" style="margin-bottom:10px">
            <label class="form-label" style="font-size:11px;font-weight:700;color:#555;display:block;margin-bottom:5px">Pin Type</label>
            <div style="display:flex;gap:8px">
                <label id="addPinTypeBarangayLabel" style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;padding:7px 12px;border:1.5px solid #e53935;border-radius:7px;background:#ffeaea;color:#c62828;font-weight:600;flex:1;justify-content:center">
                    <input type="radio" name="pinTypeRadio" id="addPinTypeBarangay" value="barangay" checked onchange="onPinTypeChange('barangay')" style="accent-color:#e53935">
                    <i class="fas fa-map-pin"></i> Barangay
                </label>
                <label id="addPinTypeOtherLabel" style="display:flex;align-items:center;gap:6px;cursor:pointer;font-size:13px;padding:7px 12px;border:1.5px solid #ddd;border-radius:7px;background:#f5f5f5;color:#888;font-weight:600;flex:1;justify-content:center">
                    <input type="radio" name="pinTypeRadio" id="addPinTypeOther" value="other" onchange="onPinTypeChange('other')" style="accent-color:#ff9800">
                    <i class="fas fa-location-dot"></i> Other
                </label>
            </div>
        </div>
        <input type="hidden" id="addPinType" value="barangay">

        <!-- Step 1: Municipality (required first) -->
        <div class="form-row">
            <label style="font-size:11px;font-weight:700;color:#555;display:block;margin-bottom:4px">
                <span style="background:#1a2744;color:white;border-radius:50%;width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;font-size:9px;margin-right:4px">1</span>
                Municipality / City <span style="color:#e53935">*</span>
            </label>
            <select id="addPinMunicipality" class="form-input" style="font-size:12.5px" onchange="onAddPinMunicipalityChange(this.value)">
                <option value="">— Select Municipality first —</option>
                @foreach([
                    'Alaminos City','Dagupan City','San Carlos City','Urdaneta City',
                    'Agno','Aguilar','Alcala','Anda','Asingan','Balungao','Bani','Basista','Bautista',
                    'Bayambang','Binalonan','Binmaley','Bolinao','Bugallon','Burgos','Calasiao',
                    'Dasol','Infanta','Labrador','Laoac','Lingayen','Mabini','Malasiqui','Manaoag',
                    'Mangaldan','Mangatarem','Mapandan','Natividad','Pozorrubio','Rosales',
                    'San Fabian','San Jacinto','San Manuel','San Nicolas','San Quintin',
                    'Santa Barbara','Santa Maria','Santo Tomas','Sison','Sual','Tayug',
                    'Umingan','Urbiztondo','Villasis'
                ] as $m)
                <option value="{{ $m }}">{{ $m }}</option>
                @endforeach
            </select>
            <div id="addPinMuniPinCount" style="display:none;font-size:11px;color:#1a2744;background:#e8edf5;border-radius:5px;padding:4px 8px;margin-top:4px">
                <i class="fas fa-map-pin"></i> <span id="addPinMuniPinCountText"></span>
            </div>
            <div id="addPinAutoMuniHint" style="display:none;font-size:11px;color:#2e7d32;background:#e8f5e9;border-radius:5px;padding:4px 8px;margin-top:4px">
                <i class="fas fa-magic"></i> <span></span>
            </div>
        </div>

        <!-- Step 2: Barangay (populated after municipality, Barangay type only) -->
        <div id="addPinBarangayRow" style="display:none">
            <div class="form-row">
                <label style="font-size:11px;font-weight:700;color:#555;display:block;margin-bottom:4px">
                    <span style="background:#1a2744;color:white;border-radius:50%;width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;font-size:9px;margin-right:4px">2</span>
                    Barangay <span style="color:#e53935">*</span>
                </label>
                <select id="addPinBarangay" class="form-input" style="font-size:12.5px" onchange="onAddPinBarangayChange(this.value)">
                    <option value="">— Select Barangay —</option>
                </select>
            </div>
            <!-- Custom name (shown when "Custom" is picked or type=Other) -->
            <div id="addPinCustomRow" style="display:none" class="form-row">
                <label style="font-size:11px;font-weight:700;color:#555;display:block;margin-bottom:4px" id="addPinCustomLabel">Custom Barangay Name <span style="color:#e53935">*</span></label>
                <input type="text" id="addPinCustomName" class="form-input" placeholder="Type the barangay name" style="font-size:12.5px">
            </div>
        </div>

        <!-- Other type: just a name field (shown immediately when Other is selected) -->
        <div id="addPinOtherRow" style="display:none" class="form-row">
            <label style="font-size:11px;font-weight:700;color:#555;display:block;margin-bottom:4px">
                <span style="background:#ff9800;color:white;border-radius:50%;width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;font-size:9px;margin-right:4px">2</span>
                Location Name <span style="color:#e53935">*</span>
            </label>
            <input type="text" id="addPinOtherName" class="form-input" placeholder="e.g. DAR Office, Covered Court" style="font-size:12.5px">
        </div>

        <!-- Province — locked -->
        <div class="form-row" style="margin-top:4px">
            <label style="font-size:11px;font-weight:600;color:#aaa;display:block;margin-bottom:3px">Province</label>
            <input type="text" class="form-input" value="Pangasinan" readonly style="background:#f5f5f5;color:#aaa;font-size:12.5px;cursor:default">
        </div>

        <div style="display:flex;gap:10px;margin-top:16px">
            <button onclick="cancelAddPin()" style="flex:1;padding:9px;background:#f5f5f5;color:#555;border:none;border-radius:7px;cursor:pointer;font-size:13px">Cancel</button>
            <button onclick="saveAddPin()" style="flex:1;padding:9px;background:#e53935;color:white;border:none;border-radius:7px;cursor:pointer;font-size:13px;font-weight:700" id="savePinBtn"><i class="fas fa-map-pin"></i> Save Pin</button>
        </div>
    </div>
</div>

<!-- Edit Lot Modal -->
<div id="editLotModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:white;border-radius:14px;padding:26px;width:380px;max-height:88vh;overflow-y:auto;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:15px;font-weight:700;color:#1a2744;margin-bottom:16px"><i class="fas fa-pencil-alt" style="margin-right:6px"></i>Edit Lot</h3>
        <input type="hidden" id="editLotId">
        <div class="form-row"><label class="form-label">Lot Number</label><input type="number" id="editLotNumber" class="form-input" min="1"></div>
        <div class="form-row"><label class="form-label">Owner Name</label><input type="text" id="editOwnerName" class="form-input"></div>
        <div class="form-row"><label class="form-label">Notes</label><input type="text" id="editLotNotes" class="form-input" placeholder="Optional"></div>

        <!-- Lot attachments -->
        <div class="attach-section" id="lotAttachSection">
            <div class="attach-title">
                <i class="fas fa-paperclip"></i> Documents &amp; Photos
                <span class="attach-count" id="lotAttachCount">0</span>
            </div>
            <div class="attach-list" id="lotAttachList"></div>
            @if(auth()->user()->role === 'admin')
            <div class="attach-drop" id="lotAttachDrop"
                 onclick="document.getElementById('lotAttachInput').click()"
                 ondragover="event.preventDefault();this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="handleLotAttachDrop(event)">
                <div class="attach-drop-text"><i class="fas fa-cloud-upload-alt" style="margin-right:5px"></i>Click or drag to attach — photos, PDFs, Word, Excel</div>
                <input type="file" id="lotAttachInput" multiple
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.bmp"
                       onchange="uploadLotAttachments(this.files)">
            </div>
            @endif
        </div>

        <div style="display:flex;gap:10px;margin-top:16px">
            <button onclick="closeEditModal()" style="flex:1;padding:9px;background:#f5f5f5;color:#555;border:none;border-radius:7px;cursor:pointer;font-size:13px">Cancel</button>
            <button onclick="saveLotEdit()"    style="flex:1;padding:9px;background:#1a2744;color:white;border:none;border-radius:7px;cursor:pointer;font-size:13px;font-weight:700">Save</button>
        </div>
    </div>
</div>

<!-- Add Lot to Survey Modal -->
<div id="addLotModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:white;border-radius:14px;padding:26px;width:360px;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:15px;font-weight:700;color:#1a2744;margin-bottom:6px"><i class="fas fa-plus-circle" style="margin-right:6px;color:#2e7d32"></i>Add Lot to Survey <span class="info-tip" data-tip="Draw a new lot on the map and assign an owner. The lot is added to the existing survey and the total area is recalculated automatically.">?</span></h3>
        <p style="font-size:12px;color:#888;margin-bottom:16px">Draw the new lot on the map, then fill in details below.</p>
        <input type="hidden" id="addLotSurveyId">
        <div id="addLotOverlapWarning" style="display:none;background:#fff3e0;border:1px solid #ff9800;border-radius:6px;padding:8px 10px;margin-bottom:10px;font-size:11.5px;color:#e65100">
            <i class="fas fa-exclamation-triangle"></i> Overlap detected with existing lots.
        </div>
        <div class="form-row"><label class="form-label">Owner Name</label><input type="text" id="addLotOwner" class="form-input" placeholder="Owner of new lot"></div>
        <div class="form-row"><label class="form-label">Notes (optional)</label><input type="text" id="addLotNotes" class="form-input" placeholder="Optional"></div>
        <div style="background:#f0f4ff;border-radius:7px;padding:10px;margin-bottom:12px;font-size:12px;color:#555">
            <div id="addLotDrawStatus"><i class="fas fa-draw-polygon"></i> Click <strong>Draw Lot</strong> then trace on the map. Double-click to finish.</div>
            <div id="addLotAreaDisplay" style="display:none;margin-top:4px;color:#2e7d32;font-weight:600"></div>
        </div>
        <button id="addLotDrawBtn" onclick="startAddLotDraw()" style="width:100%;padding:9px;background:#2e7d32;color:white;border:none;border-radius:7px;cursor:pointer;font-size:13px;font-weight:700;margin-bottom:10px">
            <i class="fas fa-draw-polygon"></i> <span id="addLotDrawBtnText">Draw Lot on Map</span>
        </button>
        <div style="display:flex;gap:10px">
            <button onclick="closeAddLotModal()" style="flex:1;padding:9px;background:#f5f5f5;color:#555;border:none;border-radius:7px;cursor:pointer;font-size:13px">Cancel</button>
            <button onclick="saveAddLot()" style="flex:1;padding:9px;background:#1a2744;color:white;border:none;border-radius:7px;cursor:pointer;font-size:13px;font-weight:700">Save Lot</button>
        </div>
    </div>
</div>

<!-- Divide Lot Modal -->
<div id="divideLotModal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:9999;align-items:center;justify-content:center">
    <div style="background:white;border-radius:14px;padding:26px;width:380px;max-height:90vh;overflow-y:auto;box-shadow:0 10px 40px rgba(0,0,0,0.2)">
        <h3 style="font-size:15px;font-weight:700;color:#1a2744;margin-bottom:6px"><i class="fas fa-cut" style="margin-right:6px;color:#e53935"></i>Divide Lot <span class="info-tip" data-tip="Split a lot into two parts by drawing each half on the map. Assign owners to both Part A and Part B. The original lot becomes Part A and a new lot is created for Part B.">?</span></h3>
        <p style="font-size:12px;color:#888;margin-bottom:16px">Draw a line through the lot to divide it into two parts. Then assign owners to each half.</p>
        <input type="hidden" id="divideLotId">
        <div style="background:#f0f4ff;border-radius:7px;padding:10px;margin-bottom:12px;font-size:12px;color:#555">
            <div id="divideDrawStatus"><i class="fas fa-draw-polygon"></i> Click <strong>Draw Part A</strong> to trace the first half. Double-click to finish, then draw Part B.</div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:12px">
            <div>
                <button id="divideDrawABtn" onclick="startDivideDrawA()" style="width:100%;padding:8px;background:#1976d2;color:white;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:700;margin-bottom:6px">
                    <i class="fas fa-draw-polygon"></i> Draw Part A
                </button>
                <div class="form-row"><label class="form-label" style="color:#1976d2">Owner A</label><input type="text" id="divideOwnerA" class="form-input" placeholder="Owner"></div>
                <div id="divideAreaA" style="font-size:11px;color:#1976d2;font-weight:600"></div>
            </div>
            <div>
                <button id="divideDrawBBtn" onclick="startDivideDrawB()" style="width:100%;padding:8px;background:#e53935;color:white;border:none;border-radius:6px;cursor:pointer;font-size:12px;font-weight:700;margin-bottom:6px" disabled>
                    <i class="fas fa-draw-polygon"></i> Draw Part B
                </button>
                <div class="form-row"><label class="form-label" style="color:#e53935">Owner B</label><input type="text" id="divideOwnerB" class="form-input" placeholder="Owner"></div>
                <div id="divideAreaB" style="font-size:11px;color:#e53935;font-weight:600"></div>
            </div>
        </div>
        <div style="display:flex;gap:10px">
            <button onclick="closeDivideLotModal()" style="flex:1;padding:9px;background:#f5f5f5;color:#555;border:none;border-radius:7px;cursor:pointer;font-size:13px">Cancel</button>
            <button onclick="saveDivideLot()" style="flex:1;padding:9px;background:#1a2744;color:white;border:none;border-radius:7px;cursor:pointer;font-size:13px;font-weight:700">Save Division</button>
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

// ── Map style toggle (classic ↔ satellite) ──
let currentTileLayer = null;
let mapStyleIsSatellite = false;

const classicTiles = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap', maxZoom: 19
});
const satelliteTiles = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
    attribution: 'Tiles © Esri', maxZoom: 20
});

currentTileLayer = classicTiles;
currentTileLayer.addTo(map);

function toggleMapStyle() {
    map.removeLayer(currentTileLayer);
    mapStyleIsSatellite = !mapStyleIsSatellite;
    currentTileLayer = mapStyleIsSatellite ? satelliteTiles : classicTiles;
    currentTileLayer.addTo(map);
    const icon = document.getElementById('mapStyleIcon');
    const label = document.getElementById('mapStyleLabel');
    if (mapStyleIsSatellite) {
        icon.className = 'fas fa-map';
        label.textContent = 'Street';
    } else {
        icon.className = 'fas fa-satellite';
        label.textContent = 'Satellite';
    }
}

// ══════════════════════════════════════════════════════════════
// PANGASINAN MUNICIPALITY PINS & SEARCH
// ══════════════════════════════════════════════════════════════
const pangasinanMunis = [
    // ── 4 Cities (accurate PSGC coordinates) ──────────────────
    {name:'Alaminos City',   lat:16.1558, lng:119.9797, type:'City'},
    {name:'Dagupan City',    lat:16.0432, lng:120.3327, type:'City'},
    {name:'San Carlos City', lat:15.9269, lng:120.3493, type:'City'},
    {name:'Urdaneta City',   lat:15.9754, lng:120.5706, type:'City'},
    // ── 44 Municipalities (PSGC-verified coordinates) ─────────
    // 1st District - Western Pangasinan
    {name:'Agno',            lat:16.1192, lng:119.7978, type:'Municipality'},
    {name:'Bani',            lat:16.1931, lng:119.8669, type:'Municipality'},
    {name:'Bolinao',         lat:16.3344, lng:119.8992, type:'Municipality'},
    {name:'Burgos',          lat:16.0472, lng:119.8597, type:'Municipality'},
    {name:'Dasol',           lat:15.9792, lng:119.8828, type:'Municipality'},
    {name:'Infanta',         lat:16.0186, lng:119.9033, type:'Municipality'},
    {name:'Mabini',          lat:16.0631, lng:120.0806, type:'Municipality'},
    {name:'Sual',            lat:15.9903, lng:120.0783, type:'Municipality'},
    // 2nd District - South/Central West
    {name:'Aguilar',         lat:15.8903, lng:120.2322, type:'Municipality'},
    {name:'Bayambang',       lat:15.8169, lng:120.4511, type:'Municipality'},
    {name:'Binalonan',       lat:16.0569, lng:120.5903, type:'Municipality'},
    {name:'Binmaley',        lat:16.0294, lng:120.2697, type:'Municipality'},
    {name:'Bugallon',        lat:15.7978, lng:120.1878, type:'Municipality'},
    {name:'Burgos',          lat:15.9961, lng:120.6522, type:'Municipality'},
    {name:'Calasiao',        lat:16.0097, lng:120.3661, type:'Municipality'},
    {name:'Labrador',        lat:16.0211, lng:120.1397, type:'Municipality'},
    {name:'Lingayen',        lat:16.0167, lng:120.2311, type:'Municipality'},
    {name:'Malasiqui',       lat:15.9178, lng:120.4133, type:'Municipality'},
    {name:'Manaoag',         lat:16.0444, lng:120.4869, type:'Municipality'},
    {name:'Mangaldan',       lat:16.0658, lng:120.4019, type:'Municipality'},
    {name:'Mangatarem',      lat:15.7922, lng:120.2003, type:'Municipality'},
    {name:'Mapandan',        lat:16.0211, lng:120.4506, type:'Municipality'},
    // 3rd District
    {name:'Anda',            lat:16.2814, lng:120.4028, type:'Municipality'},
    {name:'Basista',         lat:15.8486, lng:120.3794, type:'Municipality'},
    {name:'Bautista',        lat:15.8878, lng:120.5331, type:'Municipality'},
    {name:'San Fabian',      lat:16.1197, lng:120.4178, type:'Municipality'},
    {name:'San Jacinto',     lat:16.0789, lng:120.4544, type:'Municipality'},
    {name:'San Quintin',     lat:15.9981, lng:120.7736, type:'Municipality'},
    {name:'Santa Barbara',   lat:15.8822, lng:120.4047, type:'Municipality'},
    {name:'Santa Maria',     lat:15.9969, lng:120.4697, type:'Municipality'},
    {name:'Santo Tomas',     lat:15.9036, lng:120.5553, type:'Municipality'},
    {name:'Urbiztondo',      lat:15.8278, lng:120.3192, type:'Municipality'},
    // 4th District
    {name:'Agno',            lat:16.1192, lng:119.7978, type:'Municipality'},
    {name:'Alcala',          lat:15.8622, lng:120.6356, type:'Municipality'},
    {name:'Asingan',         lat:16.0003, lng:120.6633, type:'Municipality'},
    {name:'Balungao',        lat:15.9133, lng:120.7067, type:'Municipality'},
    {name:'Laoac',           lat:16.0353, lng:120.6364, type:'Municipality'},
    {name:'Natividad',       lat:15.9889, lng:120.7414, type:'Municipality'},
    {name:'Pozorrubio',      lat:16.1183, lng:120.5422, type:'Municipality'},
    {name:'Rosales',         lat:15.8942, lng:120.6322, type:'Municipality'},
    {name:'San Manuel',      lat:16.0656, lng:120.6628, type:'Municipality'},
    {name:'San Nicolas',     lat:16.1033, lng:120.6833, type:'Municipality'},
    {name:'Sison',           lat:16.1711, lng:120.5456, type:'Municipality'},
    {name:'Tayug',           lat:16.0358, lng:120.7458, type:'Municipality'},
    {name:'Umingan',         lat:15.9289, lng:120.7819, type:'Municipality'},
    {name:'Villasis',        lat:15.9014, lng:120.5844, type:'Municipality'},
];

let muniPinsLayer = L.layerGroup().addTo(map);
let muniPinsVisible = true;
let muniMarkers = [];

// Create pins for each municipality
pangasinanMunis.forEach((m, idx) => {
    const marker = L.marker([m.lat, m.lng], {
        icon: L.divIcon({
            className: '',
            html: `<div class="muni-pin-marker ${m.type === 'City' ? 'city' : ''}"><span>${m.name.charAt(0)}</span></div>`,
            iconSize: [22, 22],
            iconAnchor: [11, 22],
            popupAnchor: [0, -22],
        })
    });
    marker.bindPopup(`
        <div class="muni-popup-name">${m.name}</div>
        <div class="muni-popup-type">${m.type} · Pangasinan</div>
        <div style="font-size:11px;color:#555;margin-top:4px">Lat: ${m.lat.toFixed(4)}, Lng: ${m.lng.toFixed(4)}</div>
    `);
    marker.addTo(muniPinsLayer);
    muniMarkers.push({marker, ...m});
});

// Search filter
function filterMuni(query) {
    const dd = document.getElementById('muniDropdown');
    const q = query.trim().toLowerCase();
    if (!q) { dd.classList.remove('show'); return; }

    // Combine built-in municipalities + custom barangay pins
    const customPins = loadCustomPins();
    const customItems = customPins.map(p => ({
        name: p.name + (p.municipality ? ' (' + p.municipality + ')' : ''),
        realName: p.name,
        lat:  p.lat,
        lng:  p.lng,
        type: 'Barangay',
    }));
    const allSearchable = [
        ...pangasinanMunis.map(m => ({ name: m.name, realName: m.name, lat: m.lat, lng: m.lng, type: m.type })),
        ...customItems,
    ];

    const matches = allSearchable.filter(m =>
        m.name.toLowerCase().includes(q)
    ).slice(0, 30);

    if (matches.length === 0) {
        dd.innerHTML = '<div class="muni-item" style="color:#aaa;cursor:default">No results found</div>';
        dd.classList.add('show');
        return;
    }
    dd.innerHTML = matches.map(m =>
        `<div class="muni-item" onclick="flyToMuni('${escJs(m.realName)}','${m.type==='Barangay'?'custom':''}')">
            <i class="fas ${m.type==='Barangay'?'fa-map-pin':'fa-map-marker-alt'}" style="color:${m.type==='City'?'#2e7d32':m.type==='Barangay'?'#e53935':'#1a2744'}"></i>
            ${m.name}
            <span class="muni-type">${m.type}</span>
        </div>`
    ).join('');
    dd.classList.add('show');
}

function showMuniDropdown() {
    const q = document.getElementById('muniSearch').value.trim();
    if (q) filterMuni(q);
}

function flyToMuni(name, pinType) {
    // First check custom pins
    if (pinType === 'custom') {
        const cm = customPinMarkers.find(x => x.name === name);
        if (cm) {
            map.flyTo([cm.lat, cm.lng], 17, {duration: 1.2});
            setTimeout(() => cm.marker.openPopup(), 1200);
            document.getElementById('muniSearch').value = name;
            document.getElementById('muniDropdown').classList.remove('show');
            return;
        }
    }
    // Built-in municipality
    const m = pangasinanMunis.find(x => x.name === name);
    if (!m) return;
    map.flyTo([m.lat, m.lng], 15, {duration: 1.2});
    const mm = muniMarkers.find(x => x.name === name);
    if (mm) setTimeout(() => mm.marker.openPopup(), 1200);
    document.getElementById('muniSearch').value = m.name;
    document.getElementById('muniDropdown').classList.remove('show');
}

function toggleMuniPins() {
    muniPinsVisible = !muniPinsVisible;
    const btn = document.getElementById('muniToggleBtn');
    const icon = document.getElementById('muniToggleIcon');
    if (muniPinsVisible) {
        muniPinsLayer.addTo(map);
        btn.classList.add('active');
        icon.className = 'fas fa-map-marker-alt';
    } else {
        map.removeLayer(muniPinsLayer);
        btn.classList.remove('active');
        icon.className = 'fas fa-map-marker-alt';
        icon.style.opacity = '0.4';
    }
    if (muniPinsVisible) icon.style.opacity = '1';
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('.pangasinan-search')) {
        document.getElementById('muniDropdown').classList.remove('show');
    }
});

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
        return;
    }

    // ── Require survey info before allowing drawing ──
    const lsn   = document.getElementById('lsnInput').value.trim();
    const owner = document.getElementById('originalOwner').value.trim();
    const muni  = getMuniValue();

    if (!lsn) {
        showAlert('error', 'Please enter a Survey Number (LSN) before drawing.');
        document.getElementById('lsnInput').focus();
        return;
    }
    if (!owner) {
        showAlert('error', 'Please enter the Original Owner before drawing.');
        document.getElementById('originalOwner').focus();
        return;
    }
    if (!muni) {
        showAlert('error', 'Please select a Municipality before drawing.');
        document.getElementById('surveyMunicipality').focus();
        return;
    }

    startDrawing();
}

function startDrawing() {
    drawingActive = true;
    currentPoints = [];
    previewLayerGroup.clearLayers();
    map.doubleClickZoom.disable(); // prevent map zoom on dblclick while drawing
    document.getElementById('drawBtn').classList.add('drawing');
    document.getElementById('drawBtnText').textContent = 'Finish Lot (or double-click)';
    map.getContainer().style.cursor = 'crosshair';
    document.getElementById('mapHint').innerHTML = '<i class="fas fa-draw-polygon"></i> Click to place points. Double-click to close the lot. Use Undo to remove last point.';
    document.getElementById('mapHint').classList.remove('hide');
    document.getElementById('step1').classList.add('active-step');
    const undoBtn = document.getElementById('drawUndoBtn');
    if (undoBtn) { undoBtn.style.display = 'flex'; }
}

// Timer for debouncing draw clicks vs. double-click
let drawClickTimer = null;

function cancelCurrentDraw() {
    drawingActive = false;
    currentPoints = [];
    previewLayerGroup.clearLayers();
    previewLine = null;
    clearTimeout(drawClickTimer);
    map.doubleClickZoom.enable(); // restore zoom
    document.getElementById('drawBtn').classList.remove('drawing');
    document.getElementById('drawBtnText').textContent = drawnPolygons.length > 0 ? 'Draw Next Lot' : 'Start Drawing Lot';
    const undoBtn = document.getElementById('drawUndoBtn');
    if (undoBtn) { undoBtn.style.display = 'none'; }
    map.getContainer().style.cursor = '';
    document.getElementById('mapHint').classList.add('hide');
}

function undoLastPoint() {
    if (!drawingActive || currentPoints.length === 0) return;
    currentPoints.pop();
    updatePreview();
    if (currentPoints.length === 0) {
        document.getElementById('mapHint').innerHTML = '<i class="fas fa-draw-polygon"></i> Click to place points. Double-click to close the lot. Use Undo to remove last point.';
    }
}

// Map click — add point
map.on('click', function(e) {
    if (!drawingActive || currentMode !== 'draw') return;
    // Debounce: cancel if a dblclick arrives within 220ms
    clearTimeout(drawClickTimer);
    drawClickTimer = setTimeout(() => {
        currentPoints.push(e.latlng);
        updatePreview();
    }, 220);
});

// Map dblclick — finish polygon
map.on('dblclick', function(e) {
    if (!drawingActive || currentMode !== 'draw') return;
    // Cancel the pending single-click
    clearTimeout(drawClickTimer);
    drawClickTimer = null;
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
    clearTimeout(drawClickTimer);
    map.doubleClickZoom.enable(); // restore zoom
    document.getElementById('drawBtn').classList.remove('drawing');
    document.getElementById('drawBtnText').textContent = 'Draw Next Lot';
    const undoBtn2 = document.getElementById('drawUndoBtn');
    if (undoBtn2) { undoBtn2.style.display = 'none'; }
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
            <select id="drawn-type-${i}" class="drawn-lot-input" style="width:110px;font-size:11px;padding:4px 5px">
                <option value="agricultural">Agricultural</option>
                <option value="residential">Residential</option>
                <option value="commercial">Commercial</option>
                <option value="industrial">Industrial</option>
                <option value="government">Government</option>
                <option value="other">Other</option>
            </select>
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

// ── Build notes from extra form fields ──
function val(id) { return (document.getElementById(id)?.value || '').trim(); }

// Read municipality — either the select value or the custom text input
function getMuniValue() {
    const sel = document.getElementById('surveyMunicipality');
    if (sel && sel.value === '__custom__') {
        return (document.getElementById('surveyMunicipalityCustom')?.value || '').trim();
    }
    return sel ? sel.value.trim() : '';
}

// Read barangay — either the select value or the custom text input
function getBrgyValue() {
    const sel = document.getElementById('surveyBarangay');
    if (sel && sel.value === '__custom_brgy__') {
        return (document.getElementById('surveyBarangayCustom')?.value || '').trim();
    }
    return sel ? sel.value.trim() : '';
}

// When user types in the custom municipality box, enable barangay only if known
function onCustomMuniInput() {
    // No barangay data for custom municipalities — keep disabled with empty
    const brgySel = document.getElementById('surveyBarangay');
    if (brgySel) {
        brgySel.innerHTML = '<option value="">— Select Barangay —</option>';
        const custom = document.createElement('option');
        custom.value = '__custom_brgy__'; custom.textContent = '— Type custom barangay —';
        brgySel.appendChild(custom);
        brgySel.disabled = false;
    }
}

// Show hint when user types lat/lng manually
function onCoordInput() {
    const lat = parseFloat(document.getElementById('surveyLat').value);
    const lng = parseFloat(document.getElementById('surveyLng').value);
    const hint = document.getElementById('coordPinHint');
    if (hint) hint.style.display = (!isNaN(lat) && !isNaN(lng)) ? 'block' : 'none';
}

function buildNotes() {
    return [
        val('surveyNotes'),
        val('surveyZone')          ? 'Zone: '      + val('surveyZone')        : '',
        getBrgyValue()             ? 'Brgy: '      + getBrgyValue()           : '',
        val('surveyLocation')      ? 'Loc: '       + val('surveyLocation')    : '',
        val('surveyProvince')      ? 'Province: '  + val('surveyProvince')    : '',
        val('boundaryLine')        ? 'Line: '      + val('boundaryLine')      : '',
        val('boundaryBearing')     ? 'Bearing: '   + val('boundaryBearing')   : '',
        val('boundaryDistance')    ? 'Distance: '  + val('boundaryDistance')  + 'm' : '',
        val('boundaryLotNo')       ? 'Lot ref: '   + val('boundaryLotNo')     : '',
        val('boundaryClaimant')    ? 'Claimant: '  + val('boundaryClaimant')  : '',
    ].filter(Boolean).join(' | ');
}

async function saveLSNDraw() {
    const lsn          = document.getElementById('lsnInput').value.trim();
    const owner        = document.getElementById('originalOwner').value.trim();
    const municipality = getMuniValue();
    const dateRegistered = document.getElementById('surveyDate').value;
    const notes = buildNotes();

    if (!lsn)          { showAlert('error', 'Please enter a Survey Number (LSN).'); return; }
    if (!owner)        { showAlert('error', 'Please enter the original owner name.'); return; }
    if (!municipality) { showAlert('error', 'Please select a municipality/city.'); return; }
    if (drawnPolygons.length === 0) { showAlert('error', 'Draw at least one lot on the map.'); return; }

    // Build lots array
    const lots = drawnPolygons.map((p, i) => {
        const ownerName = document.getElementById(`drawn-owner-${i}`)?.value.trim() || owner;
        const landType  = document.getElementById(`drawn-type-${i}`)?.value || 'agricultural';
        return {
            lot_number: p.lotNum,
            owner_name: ownerName,
            land_type:  landType,
            area:       Math.round(p.areaSqm * 100) / 100,
            polygon:    p.latlngs.map(ll => [ll.lat, ll.lng]),
        };
    });

    // Use manually entered lat/lng if provided; otherwise compute centroid from drawn lots
    const manLat = parseFloat(document.getElementById('surveyLat').value);
    const manLng = parseFloat(document.getElementById('surveyLng').value);
    let centerLat, centerLng;
    if (!isNaN(manLat) && !isNaN(manLng)) {
        centerLat = manLat;
        centerLng = manLng;
    } else {
        const allLats = drawnPolygons.flatMap(p => p.latlngs.map(ll => ll.lat));
        const allLngs = drawnPolygons.flatMap(p => p.latlngs.map(ll => ll.lng));
        centerLat = allLats.reduce((a,b)=>a+b,0) / allLats.length;
        centerLng = allLngs.reduce((a,b)=>a+b,0) / allLngs.length;
    }

    try {
        const res = await apiFetch('/api/surveys', 'POST', {
            lsn, original_owner: owner, municipality, date_registered: dateRegistered, notes,
            center_lat: centerLat, center_lng: centerLng,
            lot_count: lots.length,
            mode: 'draw',
            lots,
        });
        if (res.success) {
            showAlert('success', `LSN "${lsn}" saved with ${lots.length} lot${lots.length>1?'s':''}.`);
            resetDrawMode();
            await loadSurveys();
            // Auto-navigate to survey detail so user can attach files immediately
            const saved = allSurveys.find(s => s.lsn === lsn);
            if (saved) {
                showSurveyDetail(saved);
                switchTab('surveys');
            }
            document.getElementById('drawSaveAttachHint').style.display = 'none';
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
    ['lsnInput','originalOwner','surveyNotes','surveyMunicipality','surveyDate',
     'surveyZone','surveyBarangay','surveyLocation','surveyProvince','surveyLat','surveyLng',
     'surveyMunicipalityCustom','surveyBarangayCustom',
     'boundaryLine','boundaryBearing','boundaryDistance','boundaryLotNo','boundaryClaimant'
    ].forEach(id => { const el = document.getElementById(id); if (el) el.value=''; });
    // Reset custom input visibility
    const cm = document.getElementById('surveyMunicipalityCustom');
    const cb = document.getElementById('surveyBarangayCustom');
    if (cm) cm.style.display = 'none';
    if (cb) cb.style.display = 'none';
    // Re-populate municipality list (province stays Pangasinan)
    onProvinceChange('Pangasinan');
    const hint = document.getElementById('coordPinHint');
    if (hint) hint.style.display = 'none';
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
    if (!pickingMode || currentMode !== 'grid' || drawingActive) return;
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
        // Computed chip
        const chip = document.getElementById('computedChip');
        document.getElementById('computedNum').textContent = count;
        document.getElementById('computedAreaEach').textContent = Number(perLot).toLocaleString();
        document.getElementById('computedTotal').textContent = area.toLocaleString();
        document.getElementById('computedHa').textContent = (area/10000).toFixed(4);
        if (chip) chip.style.display = 'flex';
    } else {
        display.style.display = 'none';
        const chip = document.getElementById('computedChip');
        if (chip) chip.style.display = 'none';
    }

    if (count < 1 || count > 500) {
        container.innerHTML = '<div style="font-size:11.5px;color:#aaa;text-align:center;padding:14px">Enter 1–500 lots</div>';
        return;
    }
    let html = '';
    for (let i = 1; i <= count; i++) {
        html += `<div class="lot-row"><div class="lot-num-label">${i}</div>
            <div style="display:flex;flex-direction:column;gap:4px;flex:1">
                <input type="text" class="form-input" id="owner_${i}" placeholder="Owner" style="font-size:12px">
                <select id="type_${i}" class="form-input" style="font-size:11.5px;padding:5px 7px">
                    <option value="agricultural">Agricultural</option>
                    <option value="residential">Residential</option>
                    <option value="commercial">Commercial</option>
                    <option value="industrial">Industrial</option>
                    <option value="government">Government</option>
                    <option value="other">Other</option>
                </select>
            </div></div>`;
    }
    container.innerHTML = html;
}

async function saveLSNGrid() {
    const lsn          = document.getElementById('lsnInput').value.trim();
    const owner        = document.getElementById('originalOwner').value.trim();
    const municipality = getMuniValue();
    const dateRegistered = document.getElementById('surveyDate').value;
    const notes = buildNotes();
    // Prefer manual lat/lng from Survey Info; fall back to the picked map center
    const manLat = parseFloat(document.getElementById('surveyLat').value);
    const manLng = parseFloat(document.getElementById('surveyLng').value);
    const pickLat = parseFloat(document.getElementById('centerLat').value);
    const pickLng = parseFloat(document.getElementById('centerLng').value);
    const lat   = !isNaN(manLat) ? manLat : pickLat;
    const lng   = !isNaN(manLng) ? manLng : pickLng;
    const area  = parseFloat(document.getElementById('totalArea').value);
    const count = parseInt(document.getElementById('lotCount').value);

    if (!lsn)          { showAlert('error', 'Please enter a Survey Number (LSN).'); return; }
    if (!owner)        { showAlert('error', 'Please enter the original owner name.'); return; }
    if (!municipality) { showAlert('error', 'Please select a municipality/city.'); return; }
    if (isNaN(lat) || isNaN(lng)) {
        showAlert('error', 'Please pick a center location on the map or enter Lat/Lng.'); return;
    }
    if (!area || !count) {
        showAlert('error', 'Please enter total area and number of lots.'); return;
    }
    const lots = [];
    for (let i = 1; i <= count; i++) {
        lots.push({
            lot_number: i,
            owner_name: document.getElementById(`owner_${i}`)?.value.trim() || `Owner ${i}`,
            land_type:  document.getElementById(`type_${i}`)?.value || 'agricultural',
        });
    }
    try {
        const res = await apiFetch('/api/surveys', 'POST', {
            lsn, original_owner: owner, municipality, date_registered: dateRegistered, notes,
            total_area: area, center_lat: lat, center_lng: lng,
            lot_count: count, mode: 'grid', lots,
        });
        if (res.success) {
            showAlert('success', `LSN "${lsn}" saved with ${count} lots.`);
            resetGridMode();
            await loadSurveys();
            renderSurveyOnMap(res.survey);
            // Auto-navigate to survey detail so user can attach files immediately
            const saved = allSurveys.find(s => s.lsn === lsn);
            if (saved) { showSurveyDetail(saved); switchTab('surveys'); }
        } else { showAlert('error', res.message || 'Failed.'); }
    } catch(e) { showAlert('error', e.message); }
}

function resetGridMode() {
    ['lsnInput','originalOwner','surveyNotes','surveyMunicipality','surveyDate',
     'surveyZone','surveyBarangay','surveyLocation','surveyProvince','surveyLat','surveyLng',
     'surveyMunicipalityCustom','surveyBarangayCustom',
     'boundaryLine','boundaryBearing','boundaryDistance','boundaryLotNo','boundaryClaimant',
     'centerLat','centerLng','totalArea','lotCount'
    ].forEach(id => { const el = document.getElementById(id); if (el) el.value=''; });
    const cm2 = document.getElementById('surveyMunicipalityCustom');
    const cb2 = document.getElementById('surveyBarangayCustom');
    if (cm2) cm2.style.display = 'none';
    if (cb2) cb2.style.display = 'none';
    onProvinceChange('Pangasinan');
    const hint = document.getElementById('coordPinHint');
    if (hint) hint.style.display = 'none';
    document.getElementById('lotOwnerRows').innerHTML = '<div style="font-size:11.5px;color:#aaa;text-align:center;padding:14px">Enter number of lots above</div>';
    document.getElementById('areaPerLotDisplay').style.display = 'none';
    const chip = document.getElementById('computedChip');
    if (chip) chip.style.display = 'none';
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

// ── Lot popup — opens full detail panel in sidebar ────────────
function openLotPopup(lot, layer) {
    // Find the survey this lot belongs to
    const survey = allSurveys.find(s => (s.lots||[]).some(l => l.id === lot.id));
    if (!survey) return;

    // Highlight this layer
    layer.setStyle({ weight: 3, color: '#1a2744', fillOpacity: 0.45 });
    setTimeout(() => layer.setStyle({ weight: 1.5, color: '#1a2744', fillColor: '#3a5a9a', fillOpacity: 0.3 }), 3000);

    // Show survey detail panel with this lot pre-selected
    showSurveyDetail(survey, lot.id);
    switchTab('surveys');

    // Scroll the lot into view in the list
    setTimeout(() => {
        const lotEl = document.getElementById(`lot-row-${lot.id}`);
        if (lotEl) lotEl.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 150);
}

// ── Edit modal ────────────────────────────────────────────────
function openEditModal(lotId, lotNum, ownerName, notes) {
    document.getElementById('editLotId').value = lotId;
    document.getElementById('editLotNumber').value = lotNum;
    document.getElementById('editOwnerName').value = ownerName;
    document.getElementById('editLotNotes').value = notes;
    document.getElementById('editLotModal').style.display = 'flex';
    loadLotAttachments(lotId);
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
    const filtered = allSurveys.filter(s =>
        s.lsn.toLowerCase().includes(q.toLowerCase()) ||
        s.original_owner.toLowerCase().includes(q.toLowerCase()) ||
        (s.municipality || '').toLowerCase().includes(q.toLowerCase()) ||
        (s.lots||[]).some(l => l.owner_name.toLowerCase().includes(q.toLowerCase()))
    );
    renderSurveyList(filtered);

    // Also highlight/dim map markers based on filter
    if (q.trim()) {
        const matchIds = new Set(filtered.map(s => s.id));
        Object.entries(surveyLayers).forEach(([id, layers]) => {
            const matched = matchIds.has(parseInt(id));
            const opacity = matched ? 0.3 : 0.05;
            const weight  = matched ? 1.5 : 0.5;
            layers.lots && Object.values(layers.lots).forEach(ll =>
                ll.forEach && ll.forEach(l => {
                    if (l.setStyle) l.setStyle({ fillOpacity: opacity, weight });
                })
            );
            if (layers.boundary) layers.boundary.setStyle({ opacity: matched ? 0.7 : 0.1 });
        });
    } else {
        // Reset all to normal
        Object.values(surveyLayers).forEach(layers => {
            layers.lots && Object.values(layers.lots).forEach(ll =>
                ll.forEach && ll.forEach(l => {
                    if (l.setStyle) l.setStyle({ fillOpacity: 0.3, weight: 1.5 });
                })
            );
            if (layers.boundary) layers.boundary.setStyle({ opacity: 0.7 });
        });
    }
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

function showSurveyDetail(survey, selectedLotId = null) {
    const c = document.getElementById('surveyList');
    const areaPerLot = (survey.total_area / survey.lot_count).toFixed(2);
    c.innerHTML = `
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
            <button onclick="renderSurveyList(allSurveys)" style="background:#f0f0f0;border:none;border-radius:6px;padding:4px 9px;cursor:pointer;font-size:12px"><i class="fas fa-arrow-left"></i></button>
            <div style="font-size:13px;font-weight:700;color:#1a2744">${survey.lsn}</div>
        </div>
        <div class="ldc-section" style="margin-bottom:8px">
            <div class="stat-row"><span class="stat-lbl">Owner</span><span class="stat-val">${survey.original_owner}</span></div>
            ${survey.municipality ? `<div class="stat-row"><span class="stat-lbl">Municipality</span><span class="stat-val">${survey.municipality}</span></div>` : ''}
            ${survey.date_registered ? `<div class="stat-row"><span class="stat-lbl">Date Registered</span><span class="stat-val">${survey.date_registered}</span></div>` : ''}
            <div class="stat-row"><span class="stat-lbl">Total Area</span><span class="stat-val">${Number(survey.total_area).toLocaleString()} sqm</span></div>
            <div class="stat-row"><span class="stat-lbl">Hectares</span><span class="stat-val">${(survey.total_area/10000).toFixed(4)} ha</span></div>
            <div class="stat-row"><span class="stat-lbl">Lots</span><span class="stat-val">${survey.lot_count}</span></div>
            <div class="stat-row"><span class="stat-lbl">Area/Lot</span><span class="stat-val">${Number(areaPerLot).toLocaleString()} sqm</span></div>
        </div>
        <div style="font-size:11px;font-weight:700;color:#aaa;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Lots</div>
        ${(survey.lots||[]).map(lot => {
            const isSelected = lot.id === selectedLotId;
            const areaHa = (lot.area / 10000).toFixed(4);
            return `
            <div class="lot-list-item ${isSelected ? 'lot-selected' : ''}" id="lot-row-${lot.id}"
                 style="${isSelected ? 'border:2px solid #1a2744;background:#f0f4ff;border-radius:8px;' : ''}">
                <div style="display:flex;align-items:center;justify-content:space-between;width:100%;gap:6px">
                    <div style="display:flex;align-items:center;gap:6px;min-width:0">
                        <span class="lot-list-num">Lot ${lot.lot_number}</span>
                        <div style="min-width:0">
                            <div class="lot-list-owner">${lot.owner_name}</div>
                            <div style="font-size:10.5px;color:#aaa">${Number(lot.area).toLocaleString()} sqm · ${areaHa} ha</div>
                        </div>
                    </div>
                    <div style="display:flex;gap:4px;flex-shrink:0">
                        ${lot.attachment_count > 0 ? `<span style="font-size:9.5px;background:#e8edf5;color:#1a2744;border-radius:8px;padding:2px 6px"><i class="fas fa-paperclip"></i> ${lot.attachment_count}</span>` : ''}
                        ${isAdmin ? `<button class="btn-sm btn-edit-sm" onclick="openEditModal(${lot.id},${lot.lot_number},'${escJs(lot.owner_name)}','${escJs(lot.notes||'')}')" title="Edit lot details"><i class="fas fa-pencil-alt"></i></button>` : ''}
                        ${isAdmin ? `<button class="btn-sm" style="background:#fff3e0;color:#e65100;border:none;cursor:pointer;border-radius:4px;padding:3px 7px" onclick="openDivideLotModal(${lot.id})" title="Divide this lot"><i class="fas fa-cut"></i></button>` : ''}
                        ${isAdmin ? `<button class="btn-sm" style="background:#e8edf5;color:#1a2744;border:none;cursor:pointer;border-radius:4px;padding:3px 7px" onclick="openEditModal(${lot.id},${lot.lot_number},'${escJs(lot.owner_name)}','${escJs(lot.notes||'')}');setTimeout(()=>document.getElementById('lotAttachDrop')?.scrollIntoView({behavior:'smooth'}),300)" title="Manage attachments"><i class="fas fa-paperclip"></i></button>` : ''}
                    </div>
                </div>
                ${isSelected && lot.notes ? `<div style="font-size:11px;color:#666;margin-top:4px;padding-top:4px;border-top:1px solid #e8edf5;font-style:italic">${lot.notes}</div>` : ''}
            </div>`;
        }).join('')}
        <!-- Survey-level attachments -->
        <div class="attach-section" id="surveyAttachSection" style="margin-top:14px">
            <div class="attach-title">
                <i class="fas fa-paperclip"></i> Survey Documents &amp; Photos
                <span class="attach-count" id="surveyAttachCount">…</span>
            </div>
            <div class="attach-list" id="surveyAttachList"><div class="attach-uploading">Loading…</div></div>
            ${isAdmin ? `
            <div class="attach-drop" id="surveyAttachDrop"
                 onclick="document.getElementById('surveyAttachInput').click()"
                 ondragover="event.preventDefault();this.classList.add('dragover')"
                 ondragleave="this.classList.remove('dragover')"
                 ondrop="handleSurveyAttachDrop(event)">
                <div class="attach-drop-text"><i class="fas fa-cloud-upload-alt" style="margin-right:5px"></i>Click or drag to attach — photos, PDFs, Word, Excel</div>
            </div>
            <input type="file" id="surveyAttachInput" multiple style="display:none"
                   accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.bmp"
                   onchange="uploadSurveyAttachments(this.files)">
            ` : ''}
        </div>
        ${isAdmin ? `<button class="btn-primary" style="margin-top:10px;width:100%" onclick="openAddLotModal(${survey.id})"><i class="fas fa-plus-circle"></i> Add Lot to Survey</button>` : ''}
        ${isAdmin ? `<button class="btn-secondary" style="margin-top:8px" onclick="deleteSurvey(${survey.id},'${escJs(survey.lsn)}')"><i class="fas fa-trash" style="color:#e53935"></i> Delete Survey</button>` : ''}
    `;
    // Load survey attachments after rendering
    loadSurveyAttachments(survey.id);
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
    ['ldc','surveys','pins','stats'].forEach((n,i) => {
        document.querySelectorAll('.tab-btn')[i].classList.toggle('active', n===name);
    });
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    document.getElementById(`tab-${name}`).classList.add('active');
    if (name === 'pins') renderProvinceList();
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

// ══════════════════════════════════════════════════════════════
// OVERLAP DETECTION
// ══════════════════════════════════════════════════════════════
async function checkPolygonOverlap(polygon, excludeLotId = null) {
    try {
        const res = await apiFetch('/api/check-overlap', 'POST', {
            polygon,
            exclude_lot: excludeLotId,
        });
        return res.overlaps || [];
    } catch (e) {
        console.warn('Overlap check failed:', e.message);
        return [];
    }
}

function showOverlapWarning(overlaps) {
    const w = document.getElementById('overlapWarning');
    const d = document.getElementById('overlapDetails');
    if (overlaps.length === 0) {
        w.style.display = 'none';
        return;
    }
    w.style.display = 'block';
    d.innerHTML = overlaps.map(o =>
        `<div>• Lot ${o.lot_number} (${o.lsn}) — ${o.owner}</div>`
    ).join('');
}

// Wrap finishCurrentPolygon — check overlap BEFORE drawing, block if overlap
const _origFinish = finishCurrentPolygon;
let _overlapChecking = false;
finishCurrentPolygon = async function() {
    if (_overlapChecking) return;
    _overlapChecking = true;
    drawingActive = false; // prevent further clicks during async check

    const coords = currentPoints.map(ll => [ll.lat, ll.lng]);
    const overlaps = await checkPolygonOverlap(coords);

    if (overlaps.length > 0) {
        const overlapMsg = overlaps.map(o => `Lot ${o.lot_number} (${o.lsn})`).join(', ');
        cancelCurrentDraw();
        document.getElementById('overlapWarning').style.display = 'none';
        showAlert('error', `<strong>Overlap detected!</strong> Cannot draw here — overlaps with: ${overlapMsg}. Drawing cancelled. Please redraw in a non-overlapping area.`);
        _overlapChecking = false;
        return;
    }

    _origFinish();
    _overlapChecking = false;
};

// ══════════════════════════════════════════════════════════════
// ADD LOT TO EXISTING SURVEY
// ══════════════════════════════════════════════════════════════
let addLotDrawing    = false;
let addLotPoints     = [];
let addLotPreviewLG  = L.layerGroup().addTo(map);
let addLotFinalLayer = null;
let addLotFinalCoords= null;
let addLotSurveyId   = null;

function openAddLotModal(surveyId) {
    addLotSurveyId = surveyId;
    addLotPoints   = [];
    addLotFinalLayer = null;
    addLotFinalCoords= null;
    addLotPreviewLG.clearLayers();
    document.getElementById('addLotSurveyId').value = surveyId;
    document.getElementById('addLotOwner').value    = '';
    document.getElementById('addLotNotes').value    = '';
    document.getElementById('addLotDrawStatus').innerHTML = '<i class="fas fa-draw-polygon"></i> Click <strong>Draw Lot</strong> then trace on the map. Double-click to finish.';
    document.getElementById('addLotAreaDisplay').style.display = 'none';
    document.getElementById('addLotOverlapWarning').style.display = 'none';
    document.getElementById('addLotDrawBtnText').textContent = 'Draw Lot on Map';
    document.getElementById('addLotDrawBtn').style.background = '#2e7d32';
    document.getElementById('addLotModal').style.display = 'flex';
}

function closeAddLotModal() {
    addLotDrawing = false;
    addLotPoints  = [];
    addLotPreviewLG.clearLayers();
    if (addLotFinalLayer) { map.removeLayer(addLotFinalLayer); addLotFinalLayer = null; }
    map.getContainer().style.cursor = '';
    document.getElementById('addLotModal').style.display = 'none';
}

function startAddLotDraw() {
    if (addLotDrawing) {
        // Cancel
        addLotDrawing = false;
        addLotPoints  = [];
        addLotPreviewLG.clearLayers();
        map.getContainer().style.cursor = '';
        document.getElementById('addLotDrawBtnText').textContent = 'Draw Lot on Map';
        document.getElementById('addLotDrawBtn').style.background = '#2e7d32';
        return;
    }
    addLotDrawing = true;
    addLotPoints  = [];
    if (addLotFinalLayer) { map.removeLayer(addLotFinalLayer); addLotFinalLayer = null; }
    addLotPreviewLG.clearLayers();
    map.getContainer().style.cursor = 'crosshair';
    document.getElementById('addLotDrawBtnText').textContent = 'Cancel Drawing';
    document.getElementById('addLotDrawBtn').style.background = '#e53935';
    document.getElementById('addLotDrawStatus').innerHTML = '<i class="fas fa-hand-pointer"></i> Click points on the map. Double-click to finish.';
    document.getElementById('mapHint').innerHTML = '<i class="fas fa-draw-polygon"></i> Click to place points. Double-click to close.';
    document.getElementById('mapHint').classList.remove('hide');
}

// Hook into map click/dblclick for add-lot mode
map.on('click', function(e) {
    if (!addLotDrawing || drawingActive) return;
    addLotPoints.push(e.latlng);
    // Preview
    addLotPreviewLG.clearLayers();
    addLotPoints.forEach(p => L.circleMarker(p, { radius:4, color:'#2e7d32', fillColor:'#2e7d32', fillOpacity:1 }).addTo(addLotPreviewLG));
    if (addLotPoints.length >= 2) L.polyline(addLotPoints, { color:'#2e7d32', dashArray:'5,4' }).addTo(addLotPreviewLG);
    if (addLotPoints.length >= 3) L.polyline([addLotPoints[addLotPoints.length-1], addLotPoints[0]], { color:'#2e7d32', dashArray:'3,4', opacity:0.5 }).addTo(addLotPreviewLG);
});

map.on('dblclick', async function(e) {
    if (!addLotDrawing) return;
    if (addLotPoints.length > 0) addLotPoints.pop();
    if (addLotPoints.length < 3) { alert('Need at least 3 points.'); return; }
    L.DomEvent.stop(e);

    addLotDrawing = false;
    map.getContainer().style.cursor = '';
    addLotPreviewLG.clearLayers();

    const latlngs = [...addLotPoints];
    const coords = latlngs.map(ll => [ll.lat, ll.lng]);

    // Check overlaps BEFORE drawing the polygon
    const overlaps = await checkPolygonOverlap(coords);
    if (overlaps.length > 0) {
        const overlapMsg = overlaps.map(o => `Lot ${o.lot_number} (${o.lsn})`).join(', ');
        addLotPoints = [];
        addLotFinalCoords = null;
        document.getElementById('addLotDrawStatus').innerHTML = '<i class="fas fa-ban" style="color:#e53935"></i> <strong>Overlap detected!</strong> Cannot draw here — overlaps with: ' + overlapMsg + '. Please redraw in a different location.';
        document.getElementById('addLotDrawBtnText').textContent = 'Draw Lot on Map';
        document.getElementById('addLotDrawBtn').style.background = '#2e7d32';
        document.getElementById('addLotAreaDisplay').style.display = 'none';
        document.getElementById('addLotOverlapWarning').style.display = 'none';
        document.getElementById('mapHint').classList.add('hide');
        return;
    }

    // No overlap — show the polygon
    addLotFinalCoords = coords;
    const area = polygonAreaSqm(latlngs);
    addLotFinalLayer = L.polygon(latlngs, { color:'#2e7d32', fillColor:'#2e7d32', fillOpacity:0.3, weight:2 }).addTo(map);
    document.getElementById('addLotDrawBtnText').textContent = 'Redraw';
    document.getElementById('addLotDrawBtn').style.background = '#1976d2';
    document.getElementById('addLotAreaDisplay').textContent = `Area: ${Math.round(area).toLocaleString()} sqm (${(area/10000).toFixed(4)} ha)`;
    document.getElementById('addLotAreaDisplay').style.display = 'block';
    document.getElementById('addLotDrawStatus').innerHTML = '<i class="fas fa-check-circle" style="color:#2e7d32"></i> Lot drawn! Fill in owner and save.';
    document.getElementById('addLotOverlapWarning').style.display = 'none';
    document.getElementById('mapHint').classList.add('hide');
});

async function saveAddLot() {
    const owner = document.getElementById('addLotOwner').value.trim();
    const notes = document.getElementById('addLotNotes').value.trim();
    if (!owner) { alert('Please enter an owner name.'); return; }
    if (!addLotFinalCoords) { alert('Please draw the lot on the map first.'); return; }

    // Defense check: verify no overlaps before saving
    const checkOverlaps = await checkPolygonOverlap(addLotFinalCoords);
    if (checkOverlaps.length > 0) {
        alert('Overlap detected! This lot overlaps with existing plots and cannot be saved.');
        return;
    }

    const area = polygonAreaSqm(addLotPoints);
    try {
        const res = await apiFetch(`/api/surveys/${addLotSurveyId}/add-lot`, 'POST', {
            owner_name: owner,
            polygon:    addLotFinalCoords,
            area:       Math.round(area * 100) / 100,
            notes,
        });
        if (res.success) {
            closeAddLotModal();
            await loadSurveys();
            showAlert('success', 'New lot added to survey.');
            showSurveyDetail(res.survey);
            switchTab('surveys');
        }
    } catch (e) { alert('Error: ' + e.message); }
}

// ══════════════════════════════════════════════════════════════
// DIVIDE LOT
// ══════════════════════════════════════════════════════════════
let divideLotId     = null;
let divideDrawingA  = false;
let divideDrawingB  = false;
let dividePointsA   = [];
let dividePointsB   = [];
let divideLayerA    = null;
let divideLayerB    = null;
let dividePreviewLG = L.layerGroup().addTo(map);

function openDivideLotModal(lotId) {
    divideLotId    = lotId;
    dividePointsA  = [];
    dividePointsB  = [];
    divideLayerA   = null;
    divideLayerB   = null;
    divideDrawingA = false;
    divideDrawingB = false;
    dividePreviewLG.clearLayers();
    document.getElementById('divideLotId').value  = lotId;
    document.getElementById('divideOwnerA').value = '';
    document.getElementById('divideOwnerB').value = '';
    document.getElementById('divideAreaA').textContent = '';
    document.getElementById('divideAreaB').textContent = '';
    document.getElementById('divideDrawBBtn').disabled = true;
    document.getElementById('divideDrawStatus').innerHTML = '<i class="fas fa-draw-polygon"></i> Click <strong>Draw Part A</strong> to trace the first half. Double-click to finish, then draw Part B.';
    document.getElementById('divideLotModal').style.display = 'flex';
}

function closeDivideLotModal() {
    divideDrawingA = false; divideDrawingB = false;
    dividePreviewLG.clearLayers();
    if (divideLayerA) { map.removeLayer(divideLayerA); divideLayerA = null; }
    if (divideLayerB) { map.removeLayer(divideLayerB); divideLayerB = null; }
    map.getContainer().style.cursor = '';
    document.getElementById('divideLotModal').style.display = 'none';
}

function startDivideDrawA() {
    divideDrawingA = true; divideDrawingB = false;
    dividePointsA  = [];
    dividePreviewLG.clearLayers();
    if (divideLayerA) { map.removeLayer(divideLayerA); divideLayerA = null; }
    map.getContainer().style.cursor = 'crosshair';
    document.getElementById('divideDrawStatus').innerHTML = '<i class="fas fa-hand-pointer"></i> Drawing Part A — click points, double-click to finish.';
    document.getElementById('mapHint').innerHTML = '<i class="fas fa-draw-polygon"></i> Part A: click points, double-click to finish.';
    document.getElementById('mapHint').classList.remove('hide');
}

function startDivideDrawB() {
    divideDrawingB = true; divideDrawingA = false;
    dividePointsB  = [];
    dividePreviewLG.clearLayers();
    if (divideLayerB) { map.removeLayer(divideLayerB); divideLayerB = null; }
    map.getContainer().style.cursor = 'crosshair';
    document.getElementById('divideDrawStatus').innerHTML = '<i class="fas fa-hand-pointer"></i> Drawing Part B — click points, double-click to finish.';
    document.getElementById('mapHint').innerHTML = '<i class="fas fa-draw-polygon"></i> Part B: click points, double-click to finish.';
    document.getElementById('mapHint').classList.remove('hide');
}

map.on('click', function(e) {
    if ((!divideDrawingA && !divideDrawingB) || drawingActive || addLotDrawing) return;
    if (divideDrawingA) {
        dividePointsA.push(e.latlng);
        dividePreviewLG.clearLayers();
        if (dividePointsA.length >= 2) L.polyline(dividePointsA, { color:'#1976d2', dashArray:'5,4' }).addTo(dividePreviewLG);
    } else if (divideDrawingB) {
        dividePointsB.push(e.latlng);
        dividePreviewLG.clearLayers();
        if (dividePointsB.length >= 2) L.polyline(dividePointsB, { color:'#e53935', dashArray:'5,4' }).addTo(dividePreviewLG);
    }
});

map.on('dblclick', function(e) {
    L.DomEvent.stop(e);
    if (divideDrawingA) {
        if (dividePointsA.length > 0) dividePointsA.pop();
        if (dividePointsA.length < 3) { alert('Need at least 3 points for Part A.'); return; }
        divideDrawingA = false;
        dividePreviewLG.clearLayers();
        map.getContainer().style.cursor = '';
        const area = polygonAreaSqm(dividePointsA);
        divideLayerA = L.polygon(dividePointsA, { color:'#1976d2', fillColor:'#1976d2', fillOpacity:0.3, weight:2 }).addTo(map);
        document.getElementById('divideAreaA').textContent = `${Math.round(area).toLocaleString()} sqm`;
        document.getElementById('divideDrawBBtn').disabled = false;
        document.getElementById('divideDrawStatus').innerHTML = '<i class="fas fa-check-circle" style="color:#1976d2"></i> Part A drawn! Now click <strong>Draw Part B</strong>.';
        document.getElementById('mapHint').classList.add('hide');
    } else if (divideDrawingB) {
        if (dividePointsB.length > 0) dividePointsB.pop();
        if (dividePointsB.length < 3) { alert('Need at least 3 points for Part B.'); return; }
        divideDrawingB = false;
        dividePreviewLG.clearLayers();
        map.getContainer().style.cursor = '';
        const area = polygonAreaSqm(dividePointsB);
        divideLayerB = L.polygon(dividePointsB, { color:'#e53935', fillColor:'#e53935', fillOpacity:0.3, weight:2 }).addTo(map);
        document.getElementById('divideAreaB').textContent = `${Math.round(area).toLocaleString()} sqm`;
        document.getElementById('divideDrawStatus').innerHTML = '<i class="fas fa-check-circle" style="color:#2e7d32"></i> Both parts drawn! Enter owners and save.';
        document.getElementById('mapHint').classList.add('hide');
    }
});

async function saveDivideLot() {
    const ownerA = document.getElementById('divideOwnerA').value.trim();
    const ownerB = document.getElementById('divideOwnerB').value.trim();
    if (!ownerA || !ownerB) { alert('Please enter owners for both parts.'); return; }
    if (dividePointsA.length < 3 || dividePointsB.length < 3) { alert('Please draw both parts of the lot.'); return; }

    // Client-side overlap check for both parts (exclude the lot being divided)
    const polyA = dividePointsA.map(ll => [ll.lat, ll.lng]);
    const polyB = dividePointsB.map(ll => [ll.lat, ll.lng]);
    const overlapsA = await checkPolygonOverlap(polyA, divideLotId);
    if (overlapsA.length > 0) {
        alert('Overlap detected! Part A overlaps with existing lots: ' + overlapsA.map(o => `Lot ${o.lot_number} (${o.lsn})`).join(', ') + '.');
        return;
    }
    const overlapsB = await checkPolygonOverlap(polyB, divideLotId);
    if (overlapsB.length > 0) {
        alert('Overlap detected! Part B overlaps with existing lots: ' + overlapsB.map(o => `Lot ${o.lot_number} (${o.lsn})`).join(', ') + '.');
        return;
    }

    try {
        const res = await apiFetch(`/api/lots/${divideLotId}/divide`, 'POST', {
            polygon_a: polyA,
            polygon_b: polyB,
            area_a:    Math.round(polygonAreaSqm(dividePointsA) * 100) / 100,
            area_b:    Math.round(polygonAreaSqm(dividePointsB) * 100) / 100,
            owner_a:   ownerA,
            owner_b:   ownerB,
        });
        if (res.success) {
            closeDivideLotModal();
            await loadSurveys();
            showAlert('success', 'Lot divided successfully.');
        }
    } catch (e) { alert('Error: ' + e.message); }
}

// ══════════════════════════════════════════════════════════════
// ATTACHMENTS (surveys & lots)
// ══════════════════════════════════════════════════════════════

const ATTACH_ICONS = {
    pdf: 'fa-file-pdf',
    image: 'fa-file-image',
    doc: 'fa-file-word',
    xls: 'fa-file-excel',
    file: 'fa-file',
};

function attachIcon(fileType) {
    return ATTACH_ICONS[fileType] || 'fa-file';
}

function renderAttachList(listEl, countEl, attachments) {
    if (countEl) countEl.textContent = attachments.length;
    if (!listEl) return;
    if (attachments.length === 0) {
        listEl.innerHTML = '<div style="font-size:11.5px;color:#bbb;padding:6px 2px">No attachments yet.</div>';
        return;
    }
    listEl.innerHTML = attachments.map(a => {
        const isImg = a.mime_type && a.mime_type.startsWith('image/');
        const imgPreview = isImg
            ? `<a href="/api/attachments/${a.id}/download" target="_blank" style="display:block;margin-bottom:4px">
                 <img src="/api/attachments/${a.id}/download" alt="${escJs(a.name)}"
                      style="max-width:100%;max-height:120px;border-radius:6px;border:1px solid #e0e0e0;cursor:pointer"
                      onerror="this.style.display='none'">
               </a>`
            : '';
        return `
        <div class="attach-item" id="att-${a.id}" style="${a.needs_correction ? 'border-left:3px solid #e53935;background:#fff8f8' : ''}">
            ${imgPreview}
            <div style="display:flex;align-items:center;gap:7px;width:100%">
                <i class="fas ${attachIcon(a.file_type)} attach-icon ${a.file_type}" style="flex-shrink:0"></i>
                <div style="flex:1;min-width:0">
                    <div class="attach-name" title="${escJs(a.name)}" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">${a.name}</div>
                    ${a.remarks ? `<div style="font-size:10.5px;color:${a.needs_correction?'#e53935':'#888'};font-style:italic"><i class="fas fa-${a.needs_correction?'exclamation-triangle':'comment'}" style="margin-right:3px"></i>${a.remarks}</div>` : ''}
                </div>
                <span class="attach-meta" style="flex-shrink:0">${a.size_label}</span>
                <a href="/api/attachments/${a.id}/download" target="_blank" class="attach-btn" title="Download/View"><i class="fas fa-${isImg?'eye':'download'}"></i></a>
                ${isAdmin ? `<button class="attach-btn" title="Add remark" onclick="editAttachRemark(${a.id},'${escJs(a.remarks||'')}',${a.needs_correction})"><i class="fas fa-comment${a.remarks?'-dots':''}"></i></button>` : ''}
                ${isAdmin ? `<button class="attach-btn del" title="Delete" onclick="deleteAttachment(${a.id})"><i class="fas fa-times"></i></button>` : ''}
            </div>
        </div>`;
    }).join('');
}

// ── Survey-level attachments ──────────────────────────────────
let currentSurveyId = null;

async function loadSurveyAttachments(surveyId) {
    currentSurveyId = surveyId;
    try {
        const res = await apiFetch(`/api/surveys/${surveyId}/attachments`);
        renderAttachList(
            document.getElementById('surveyAttachList'),
            document.getElementById('surveyAttachCount'),
            res.attachments
        );
    } catch(e) {
        const el = document.getElementById('surveyAttachList');
        if (el) el.innerHTML = '<div class="attach-uploading" style="color:#e53935">Failed to load.</div>';
    }
}

function handleSurveyAttachDrop(e) {
    e.preventDefault();
    document.getElementById('surveyAttachDrop')?.classList.remove('dragover');
    uploadSurveyAttachments(e.dataTransfer.files);
}

async function uploadSurveyAttachments(files) {
    if (!currentSurveyId || !files || files.length === 0) return;
    const listEl  = document.getElementById('surveyAttachList');
    const countEl = document.getElementById('surveyAttachCount');
    if (listEl) listEl.innerHTML += '<div class="attach-uploading"><i class="fas fa-spinner fa-spin"></i> Uploading…</div>';

    for (const file of Array.from(files)) {
        const fd = new FormData();
        fd.append('file', file);
        fd.append('_token', CSRF);
        try {
            await fetch(`/api/surveys/${currentSurveyId}/attachments`, { method: 'POST', body: fd });
        } catch(e) { /* continue */ }
    }
    // Reset input
    const inp = document.getElementById('surveyAttachInput');
    if (inp) inp.value = '';
    await loadSurveyAttachments(currentSurveyId);
}

// ── Lot-level attachments ─────────────────────────────────────
let currentLotId = null;

async function loadLotAttachments(lotId) {
    currentLotId = lotId;
    try {
        const res = await apiFetch(`/api/lots/${lotId}/attachments`);
        renderAttachList(
            document.getElementById('lotAttachList'),
            document.getElementById('lotAttachCount'),
            res.attachments
        );
    } catch(e) {
        const el = document.getElementById('lotAttachList');
        if (el) el.innerHTML = '<div class="attach-uploading" style="color:#e53935">Failed to load.</div>';
    }
}

function handleLotAttachDrop(e) {
    e.preventDefault();
    document.getElementById('lotAttachDrop')?.classList.remove('dragover');
    uploadLotAttachments(e.dataTransfer.files);
}

async function uploadLotAttachments(files) {
    if (!currentLotId || !files || files.length === 0) return;
    const listEl  = document.getElementById('lotAttachList');
    const countEl = document.getElementById('lotAttachCount');
    if (listEl) listEl.innerHTML += '<div class="attach-uploading"><i class="fas fa-spinner fa-spin"></i> Uploading…</div>';

    for (const file of Array.from(files)) {
        const fd = new FormData();
        fd.append('file', file);
        fd.append('_token', CSRF);
        try {
            await fetch(`/api/lots/${currentLotId}/attachments`, { method: 'POST', body: fd });
        } catch(e) { /* continue */ }
    }
    const inp = document.getElementById('lotAttachInput');
    if (inp) inp.value = '';
    await loadLotAttachments(currentLotId);
}

// ── Delete an attachment ──────────────────────────────────────
async function deleteAttachment(id) {
    if (!confirm('Delete this attachment?')) return;
    try {
        await apiFetch(`/api/attachments/${id}`, 'DELETE');
        document.getElementById(`att-${id}`)?.remove();
        // Refresh counts
        if (currentLotId)    loadLotAttachments(currentLotId);
        if (currentSurveyId) loadSurveyAttachments(currentSurveyId);
    } catch(e) { alert('Could not delete: ' + e.message); }
}

// ══════════════════════════════════════════════════════════════
// PROVINCE FILTER & CUSTOM BARANGAY PINS
// ══════════════════════════════════════════════════════════════

// ── localStorage key ─────────────────────────────────────────
const CUSTOM_PINS_KEY = 'darland_custom_pins';

function loadCustomPins() {
    try { return JSON.parse(localStorage.getItem(CUSTOM_PINS_KEY) || '[]'); } catch(e) { return []; }
}
function saveCustomPins(pins) {
    localStorage.setItem(CUSTOM_PINS_KEY, JSON.stringify(pins));
}

// ── Custom pin layer ──────────────────────────────────────────
let customPinsLayer = L.layerGroup().addTo(map);
let customPinMarkers = []; // [{id, name, municipality, province, lat, lng, marker}]

function addCustomPinToMap(pin) {
    const isOther = (pin.pinType === 'other');
    const pinColor = isOther ? '#ff9800' : '#e53935';
    const pinLabel = isOther ? '📍' : pin.name.charAt(0).toUpperCase();
    const typeLabel = isOther ? 'Other Location' : 'Barangay';

    const marker = L.marker([pin.lat, pin.lng], {
        icon: L.divIcon({
            className: '',
            html: `<div class="brgy-pin-marker" style="background:${pinColor}"><span>${pin.name.charAt(0).toUpperCase()}</span></div>`,
            iconSize: [20, 20],
            iconAnchor: [10, 20],
            popupAnchor: [0, -22],
        })
    });
    marker.bindPopup(`
        <div class="muni-popup-name"><i class="fas fa-map-pin" style="color:${pinColor};font-size:11px;margin-right:4px"></i>${pin.name}</div>
        <div class="muni-popup-type">${typeLabel} · ${pin.municipality}</div>
        <div style="font-size:11px;color:#888;margin-top:3px">Pangasinan</div>
        <div style="font-size:11px;color:#aaa;margin-top:2px">Lat: ${pin.lat.toFixed(5)}, Lng: ${pin.lng.toFixed(5)}</div>
        ${isAdmin ? `<button onclick="deleteCustomPin('${pin.id}')" style="margin-top:8px;width:100%;padding:5px;background:#ffebee;color:#e53935;border:1px solid #ffcdd2;border-radius:5px;font-size:11.5px;cursor:pointer;font-weight:600"><i class='fas fa-trash'></i> Remove Pin</button>` : ''}
    `);
    marker.addTo(customPinsLayer);
    customPinMarkers.push({ ...pin, marker });
}

function initCustomPins() {
    const pins = loadCustomPins();
    customPinsLayer.clearLayers();
    customPinMarkers = [];
    pins.forEach(p => addCustomPinToMap(p));
}

function deleteCustomPin(id) {
    let pins = loadCustomPins();
    pins = pins.filter(p => p.id !== id);
    saveCustomPins(pins);
    map.closePopup();
    initCustomPins();
    renderProvinceList();
}

// ── Populate datalist with municipality suggestions ───────────
function populateMuniDatalist() {
    const dl = document.getElementById('muniSuggestions');
    if (!dl) return;
    pangasinanMunis.forEach(m => {
        const opt = document.createElement('option');
        opt.value = m.name;
        dl.appendChild(opt);
    });
}

// ── Auto-detect nearest municipality from coordinates ─────────
function findNearestMunicipality(lat, lng) {
    if (!pangasinanMunis.length) return null;
    let nearest = null;
    let minDist = Infinity;
    pangasinanMunis.forEach(m => {
        const dlat = m.lat - lat;
        const dlng = m.lng - lng;
        const dist = Math.sqrt(dlat*dlat + dlng*dlng);
        if (dist < minDist) { minDist = dist; nearest = { ...m, dist }; }
    });
    return (minDist < 0.22) ? nearest : null;
}

// ── Pin type toggle ───────────────────────────────────────────
function onPinTypeChange(type) {
    document.getElementById('addPinType').value = type;
    const isBarangay = (type === 'barangay');

    // Update label visual state
    const brgyLbl  = document.getElementById('addPinTypeBarangayLabel');
    const otherLbl = document.getElementById('addPinTypeOtherLabel');
    brgyLbl.style.borderColor  = isBarangay ? '#e53935' : '#ddd';
    brgyLbl.style.background   = isBarangay ? '#ffeaea' : '#f5f5f5';
    brgyLbl.style.color        = isBarangay ? '#c62828' : '#888';
    otherLbl.style.borderColor = !isBarangay ? '#ff9800' : '#ddd';
    otherLbl.style.background  = !isBarangay ? '#fff3e0' : '#f5f5f5';
    otherLbl.style.color       = !isBarangay ? '#e65100' : '#888';

    const savePinBtn = document.getElementById('savePinBtn');
    savePinBtn.style.background = isBarangay ? '#e53935' : '#ff9800';

    // Show/hide rows based on type
    const muniVal = document.getElementById('addPinMunicipality').value;
    document.getElementById('addPinOtherRow').style.display   = isBarangay ? 'none' : 'block';
    // Barangay row only shown if municipality is already selected
    document.getElementById('addPinBarangayRow').style.display =
        (isBarangay && muniVal) ? 'block' : 'none';
}

// ── Municipality selected in Add Pin modal ───────────────────
function onAddPinMunicipalityChange(muni) {
    const pinType    = document.getElementById('addPinType').value;
    const isBarangay = (pinType === 'barangay');
    const brgyRow    = document.getElementById('addPinBarangayRow');
    const countDiv   = document.getElementById('addPinMuniPinCount');
    const countText  = document.getElementById('addPinMuniPinCountText');

    // Count existing custom pins for this municipality
    if (muni) {
        const existing = loadCustomPins().filter(p => p.municipality === muni);
        countDiv.style.display = 'block';
        const plural = existing.length === 1 ? 'pin' : 'pins';
        countText.textContent = `${existing.length} existing ${plural} in ${muni}`;
    } else {
        countDiv.style.display = 'none';
    }

    // Populate barangay dropdown
    if (isBarangay && muni) {
        populateAddPinBarangaySelect(muni);
        brgyRow.style.display = 'block';
    } else {
        brgyRow.style.display = 'none';
    }
    // Reset custom name
    document.getElementById('addPinCustomName').value = '';
    document.getElementById('addPinCustomRow').style.display = 'none';
}

// ── Populate barangay select from locationData ───────────────
function populateAddPinBarangaySelect(muni) {
    const sel = document.getElementById('addPinBarangay');
    sel.innerHTML = '<option value="">— Select Barangay —</option>';
    document.getElementById('addPinCustomRow').style.display = 'none';

    const brgys = (locationData?.Pangasinan?.[muni] || []);
    // Filter out the old "— Custom —" placeholder — we handle it ourselves
    const realBrgys = brgys.filter(b => b !== '— Custom —');

    realBrgys.forEach(b => {
        const o = document.createElement('option');
        o.value = b; o.textContent = b;
        sel.appendChild(o);
    });

    // Always add a "Custom / not in list" option
    const custom = document.createElement('option');
    custom.value = '__custom__';
    custom.textContent = '— Not in list? Type custom name —';
    sel.appendChild(custom);
}

// ── Barangay select change ────────────────────────────────────
function onAddPinBarangayChange(val) {
    const customRow = document.getElementById('addPinCustomRow');
    const customLbl = document.getElementById('addPinCustomLabel');
    if (val === '__custom__') {
        customRow.style.display = 'block';
        customLbl.textContent   = 'Custom Barangay Name *';
        document.getElementById('addPinCustomName').value = '';
        document.getElementById('addPinCustomName').focus();
    } else {
        customRow.style.display = 'none';
    }
}

// ── Province list rendering ───────────────────────────────────
function buildProvinceData(query = '') {
    const q = query.trim().toLowerCase();

    // Gather all items: built-in municipalities + custom pins
    const customPins  = loadCustomPins();
    const allItems    = [];

    // Built-in Pangasinan municipalities
    pangasinanMunis.forEach(m => {
        allItems.push({
            id:         'builtin_' + m.name,
            name:       m.name,
            province:   'Pangasinan',
            type:       m.type,       // 'City' or 'Municipality'
            lat:        m.lat,
            lng:        m.lng,
            isCustom:   false,
        });
    });

    // Custom barangay pins
    customPins.forEach(p => {
        allItems.push({
            id:         p.id,
            name:       p.name,
            province:   p.province || 'Pangasinan',
            type:       p.pinType === 'other' ? 'Other' : 'Barangay',
            pinType:    p.pinType || 'barangay',
            lat:        p.lat,
            lng:        p.lng,
            municipality: p.municipality,
            isCustom:   true,
        });
    });

    // Filter
    const filtered = q
        ? allItems.filter(i =>
            i.name.toLowerCase().includes(q) ||
            (i.municipality || '').toLowerCase().includes(q) ||
            (i.province || '').toLowerCase().includes(q)
          )
        : allItems;

    // Group by province
    const groups = {};
    filtered.forEach(item => {
        const prov = item.province || 'Unknown';
        if (!groups[prov]) groups[prov] = [];
        groups[prov].push(item);
    });

    // Sort items within each group: Cities first, then Municipalities, then Barangays
    const typeOrder = { 'City': 0, 'Municipality': 1, 'Barangay': 2 };
    Object.keys(groups).forEach(prov => {
        groups[prov].sort((a,b) => (typeOrder[a.type] ?? 3) - (typeOrder[b.type] ?? 3) || a.name.localeCompare(b.name));
    });

    return groups;
}

function renderProvinceList(query = '') {
    const container = document.getElementById('provinceGroupList');
    if (!container) return;

    const groups = buildProvinceData(query);
    const provinceNames = Object.keys(groups).sort();

    if (provinceNames.length === 0) {
        container.innerHTML = '<div class="pins-empty"><i class="fas fa-search" style="font-size:20px;margin-bottom:8px;display:block"></i>No results found</div>';
        return;
    }

    container.innerHTML = provinceNames.map(prov => {
        const items = groups[prov];
        const total = items.length;
        return `
        <div class="province-group" id="pgroup-${prov.replace(/\s/g,'_')}">
            <div class="province-group-header" onclick="toggleProvinceGroup(this)">
                <span><i class="fas fa-map" style="margin-right:6px;font-size:10px"></i>${prov}</span>
                <span class="pg-count">${total}</span>
                <i class="fas fa-chevron-down pg-arrow" style="margin-left:6px"></i>
            </div>
            <div class="province-group-items">
                ${items.map(item => `
                <div class="province-item" onclick="flyToPin(${item.lat}, ${item.lng}, '${escJs(item.name)}')">
                    <i class="fas ${item.isCustom ? 'fa-map-pin' : 'fa-map-marker-alt'} pi-icon" style="color:${item.type==='Barangay'?'#e53935':item.pinType==='other'?'#ff9800':item.type==='City'?'#2e7d32':'#1a2744'}"></i>
                    <span class="pi-name">${item.name}${item.municipality && item.type==='Barangay' ? `<span style="font-size:10px;color:#aaa;display:block;margin-top:1px">${item.municipality}</span>` : ''}</span>
                    <span class="pi-badge ${item.pinType==='other'?'other':item.type.toLowerCase()}">${item.pinType==='other'?'Other':item.type}</span>
                    ${item.isCustom && isAdmin ? `<button class="pi-del" onclick="event.stopPropagation();deleteCustomPin('${item.id}')" title="Remove pin"><i class="fas fa-times"></i></button>` : ''}
                </div>`).join('')}
            </div>
        </div>`;
    }).join('');
}

function filterProvinceList(query) {
    renderProvinceList(query);
}

function toggleProvinceGroup(headerEl) {
    headerEl.parentElement.classList.toggle('collapsed');
    headerEl.classList.toggle('collapsed');
}

function flyToPin(lat, lng, name) {
    map.flyTo([lat, lng], 15, { duration: 1.2 });
    // Also open the matching marker popup
    const mm = muniMarkers.find(x => x.name === name);
    if (mm) setTimeout(() => mm.marker.openPopup(), 1200);
    const cm = customPinMarkers.find(x => x.name === name);
    if (cm) setTimeout(() => cm.marker.openPopup(), 1200);
}

// ── Pin Mode (admin only) ─────────────────────────────────────
let pinModeActive = false;
let pendingPinLatLng = null;

function togglePinMode() {
    pinModeActive = !pinModeActive;
    const btn  = document.getElementById('pinModeBtn');
    const hint = document.getElementById('pinAddHint');
    if (!btn) return;
    if (pinModeActive) {
        btn.classList.add('active');
        document.getElementById('pinModeBtnText').textContent = 'Cancel Pin';
        map.getContainer().style.cursor = 'crosshair';
        if (hint) { hint.style.display = 'block'; }
    } else {
        btn.classList.remove('active');
        document.getElementById('pinModeBtnText').textContent = 'Add Pin';
        map.getContainer().style.cursor = '';
        if (hint) { hint.style.display = 'none'; }
    }
}

// Hook into map click for pin placement
map.on('click', function(e) {
    if (!pinModeActive || drawingActive || addLotDrawing) return;
    pendingPinLatLng = e.latlng;

    // Auto-detect nearest municipality
    const nearestMuni = findNearestMunicipality(e.latlng.lat, e.latlng.lng);

    document.getElementById('addPinCoords').textContent =
        `Lat: ${e.latlng.lat.toFixed(6)} · Lng: ${e.latlng.lng.toFixed(6)}`;

    // Reset modal to clean state
    document.getElementById('addPinTypeBarangay').checked = true;
    document.getElementById('addPinType').value = 'barangay';
    onPinTypeChange('barangay');

    // Auto-fill municipality if detected
    const muniSel = document.getElementById('addPinMunicipality');
    if (nearestMuni) {
        muniSel.value = nearestMuni.name;
        onAddPinMunicipalityChange(nearestMuni.name);
        const hint = document.getElementById('addPinAutoMuniHint');
        hint.querySelector('span').textContent =
            `Auto-detected: ${nearestMuni.name} (${(nearestMuni.dist * 111.32).toFixed(1)} km)`;
        hint.style.display = 'block';
    } else {
        muniSel.value = '';
        document.getElementById('addPinAutoMuniHint').style.display = 'none';
        document.getElementById('addPinMuniPinCount').style.display = 'none';
        document.getElementById('addPinBarangayRow').style.display  = 'none';
    }

    // Reset other fields
    document.getElementById('addPinCustomName').value = '';
    document.getElementById('addPinOtherName').value  = '';

    document.getElementById('addPinOverlay').classList.add('show');
    togglePinMode(); // exit pin mode cursor
});

// Escape key cancels pin mode
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && pinModeActive) togglePinMode();
});

function cancelAddPin() {
    document.getElementById('addPinOverlay').classList.remove('show');
    pendingPinLatLng = null;
}

function saveAddPin() {
    const pinType    = document.getElementById('addPinType').value;
    const isBarangay = (pinType === 'barangay');
    const muni       = document.getElementById('addPinMunicipality').value.trim();

    if (!muni) { alert('Please select a municipality/city first.'); return; }

    let name = '';
    if (isBarangay) {
        const selVal    = document.getElementById('addPinBarangay').value;
        const customVal = document.getElementById('addPinCustomName').value.trim();
        if (selVal === '__custom__' || selVal === '') {
            name = customVal;
        } else {
            name = customVal || selVal;
        }
    } else {
        name = document.getElementById('addPinOtherName').value.trim();
    }

    if (!name) {
        alert(isBarangay ? 'Please select a barangay or type a custom name.' : 'Please enter a location name.');
        return;
    }
    if (!pendingPinLatLng) return;

    const pin = {
        id:           'pin_' + Date.now(),
        name,
        municipality: muni,
        province:     'Pangasinan',
        pinType,
        lat:          pendingPinLatLng.lat,
        lng:          pendingPinLatLng.lng,
    };

    const pins = loadCustomPins();
    pins.push(pin);
    saveCustomPins(pins);

    addCustomPinToMap(pin);
    cancelAddPin();
    renderProvinceList(document.getElementById('provinceSearch')?.value || '');
    showAlert('success', `${isBarangay ? 'Barangay' : 'Pin'} "${name}" added to ${muni}.`);
    switchTab('pins');
}

// ── Edit attachment remark ────────────────────────────────────
async function editAttachRemark(id, currentRemark, needsCorrection) {
    const remark = prompt('Remarks for this attachment (leave blank to clear):\n(Check "Needs correction" to flag it)', currentRemark || '');
    if (remark === null) return; // cancelled
    const needsCorr = confirm('Flag this attachment as "Needs Correction"?\n(OK = Yes flag, Cancel = No)');
    try {
        const res = await apiFetch(`/api/attachments/${id}/remarks`, 'PATCH', {
            remarks: remark.trim() || null,
            needs_correction: needsCorr,
        });
        if (res.success) {
            // Refresh whichever list this attachment belongs to
            if (currentSurveyId) loadSurveyAttachments(currentSurveyId);
            if (currentLotId)    loadLotAttachments(currentLotId);
        }
    } catch(e) { alert('Error: ' + e.message); }
}

// ── Boot: init custom pins & datalist ────────────────────────

// ══════════════════════════════════════════════════════════════
// CASCADING LOCATION DROPDOWNS (Province → Municipality → Barangay)
// ══════════════════════════════════════════════════════════════
const locationData = {
    'Pangasinan': {
        'Alaminos City':   ['Barangay Lucap','Barangay Poblacion','Barangay Patar','Barangay San Antonio','Barangay Telbang','Barangay Victoria','— Custom —'],
        'Dagupan City':    ['Barangay Bonuan Binloc','Barangay Bonuan Gueset','Barangay Calmay','Barangay Lucao','Barangay Poblacion','Barangay Pogo Chico','— Custom —'],
        'San Carlos City': ['Barangay Abanon','Barangay Balaya','Barangay Bolingit','Barangay Poblacion','Barangay Roxas','— Custom —'],
        'Urdaneta City':   ['Barangay Bayaoas','Barangay Bolaoen','Barangay Cabaruan','Barangay Catablan','Barangay Nancalobasaan','Barangay Poblacion','Barangay San Vicente','Barangay Tablang','— Custom —'],
        'Agno':            ['Barangay Allabon','Barangay Aloleng','Barangay Bangan-Oda','Barangay Banog Norte','Barangay Banog Sur','Barangay Poblacion','— Custom —'],
        'Aguilar':         ['Barangay Bayambang','Barangay Capandanan','Barangay Domalandan','Barangay Maasin','Barangay Poblacion','— Custom —'],
        'Alcala':          ['Barangay Alos','Barangay Ambalangan-Dalin','Barangay Baleyadaan','Barangay Poblacion','Barangay Tagudin','— Custom —'],
        'Anda':            ['Barangay Awile','Barangay Bituag','Barangay Cabungan','Barangay Calungbuyan','Barangay Poblacion','— Custom —'],
        'Asingan':         ['Barangay Bobonan','Barangay Cabalitian','Barangay Macalong','Barangay Poblacion','Barangay San Vicente','— Custom —'],
        'Balungao':        ['Barangay Artacho','Barangay Cablong','Barangay Manaois','Barangay Poblacion','— Custom —'],
        'Bani':            ['Barangay Ambabaay','Barangay Apolong','Barangay Banog Norte','Barangay Poblacion','— Custom —'],
        'Basista':         ['Barangay Bayambang','Barangay Cabilaoan','Barangay Poblacion','— Custom —'],
        'Bautista':        ['Barangay Artacho','Barangay Baluyot','Barangay Bongar','Barangay Poblacion','— Custom —'],
        'Bayambang':       ['Barangay Barangobong','Barangay Caoayan','Barangay Mapandan','Barangay Poblacion','Barangay San Fabian','— Custom —'],
        'Binalonan':       ['Barangay Balangobong','Barangay Bued','Barangay Bugallon','Barangay Calomboyan','Barangay Poblacion','— Custom —'],
        'Binmaley':        ['Barangay Alilan','Barangay Amandiego','Barangay Baybay Lopez','Barangay Poblacion','— Custom —'],
        'Bolinao':         ['Barangay Arnedo','Barangay Balingasay','Barangay Binabalian','Barangay Poblacion','Barangay Santiago','— Custom —'],
        'Bugallon':        ['Barangay Ampid','Barangay Bacabac','Barangay Bancal','Barangay Poblacion','— Custom —'],
        'Burgos':          ['Barangay Bosnit','Barangay Cato','Barangay Laoac','Barangay Poblacion','— Custom —'],
        'Calasiao':        ['Barangay Ambonao','Barangay Ambuetel','Barangay Banaoang','Barangay Bued','Barangay Buenlag','Barangay Poblacion','— Custom —'],
        'Dagupan City':    ['Barangay Bonuan Binloc','Barangay Bonuan Gueset','Barangay Calmay','Barangay Lucao','Barangay Poblacion','— Custom —'],
        'Dasol':           ['Barangay Allabon','Barangay Banog','Barangay Burgos','Barangay Poblacion','— Custom —'],
        'Infanta':         ['Barangay Batang','Barangay Beleng-Beléng','Barangay Bolaoen','Barangay Poblacion','— Custom —'],
        'Labrador':        ['Barangay Anonang Norte','Barangay Anonang Sur','Barangay Panacol','Barangay Poblacion','— Custom —'],
        'Laoac':           ['Barangay Baluyot','Barangay Bautista','Barangay Bobonan','Barangay Poblacion','— Custom —'],
        'Lingayen':        ['Barangay Aliwekwek','Barangay Baay','Barangay Balangobong','Barangay Balococ','Barangay Poblacion','— Custom —'],
        'Mabini':          ['Barangay Barlo','Barangay Calaocan','Barangay Nangalisan','Barangay Poblacion','— Custom —'],
        'Malasiqui':       ['Barangay Amamperez','Barangay Binday','Barangay Bolaoen','Barangay Boyambong','Barangay Poblacion','— Custom —'],
        'Manaoag':         ['Barangay Artacho','Barangay Bantog','Barangay Calaocan','Barangay Poblacion','Barangay Villaflor','— Custom —'],
        'Mangaldan':       ['Barangay Alitaya','Barangay Amansabina','Barangay Anolid','Barangay Barangobong','Barangay Poblacion','— Custom —'],
        'Mangatarem':      ['Barangay Bacabac','Barangay Balayang','Barangay Balut','Barangay Bancagan','Barangay Poblacion','— Custom —'],
        'Mapandan':        ['Barangay Amanoaoac','Barangay Aporao','Barangay Arellano','Barangay Poblacion','— Custom —'],
        'Natividad':       ['Barangay Barangobong','Barangay Cabalitaan','Barangay Mabini','Barangay Poblacion','— Custom —'],
        'Pozorrubio':      ['Barangay Alipangpang','Barangay Alos','Barangay Ambalangan','Barangay Barangobong','Barangay Poblacion','— Custom —'],
        'Rosales':         ['Barangay Acop','Barangay Bakbakeng','Barangay Balobooc','Barangay Bantayan','Barangay Poblacion','— Custom —'],
        'San Fabian':      ['Barangay Acao','Barangay Anonang','Barangay Aramat','Barangay Bandera','Barangay Poblacion','— Custom —'],
        'San Jacinto':     ['Barangay Basing','Barangay Estanza','Barangay Poblacion','Barangay Talogtog','— Custom —'],
        'San Manuel':      ['Barangay Aglipay','Barangay Bantog','Barangay Basing','Barangay Calaocan','Barangay Poblacion','— Custom —'],
        'San Nicolas':     ['Barangay Bacabac','Barangay Balangobong','Barangay Cabalitian','Barangay Poblacion','— Custom —'],
        'Santa Barbara':   ['Barangay Alibago','Barangay Bobonan','Barangay Poblacion','Barangay San Nicolas','— Custom —'],
        'Santa Maria':     ['Barangay Balungao','Barangay Bantog','Barangay Calomboyan','Barangay Poblacion','— Custom —'],
        'Santo Tomas':     ['Barangay Arellano','Barangay Bantog','Barangay Calomboyan','Barangay Poblacion','— Custom —'],
        'Sison':           ['Barangay Agno','Barangay Artacho','Barangay Bobonan','Barangay Poblacion','— Custom —'],
        'Sual':            ['Barangay Baquioen','Barangay Baybay Natividad','Barangay Baybay Pangalo','Barangay Poblacion','— Custom —'],
        'Tayug':           ['Barangay Agno','Barangay Artacho','Barangay Balungao','Barangay Poblacion','— Custom —'],
        'Umingan':         ['Barangay Artacho','Barangay Balangobong','Barangay Cabalitian','Barangay Poblacion','Barangay San Antonio','— Custom —'],
        'Urbiztondo':      ['Barangay Anonang','Barangay Aramal','Barangay Banaybanay','Barangay Poblacion','— Custom —'],
        'Villasis':        ['Barangay Barangobong','Barangay Baritao','Barangay Nancalobasaan','Barangay Poblacion','— Custom —'],
    }
};

function onProvinceChange(province) {
    const muniSel = document.getElementById('surveyMunicipality');
    const brgySel = document.getElementById('surveyBarangay');
    if (!muniSel) return;

    muniSel.innerHTML = '<option value="">— Select Municipality —</option>';
    brgySel.innerHTML = '<option value="">— Select Barangay —</option>';
    brgySel.disabled = true;

    if (!province || !locationData[province]) {
        muniSel.disabled = true;
        return;
    }

    muniSel.disabled = false;
    const munis = Object.keys(locationData[province]).sort();
    munis.forEach(m => {
        const o = document.createElement('option');
        o.value = m; o.textContent = m;
        muniSel.appendChild(o);
    });
    // Custom option
    const custom = document.createElement('option');
    custom.value = '__custom__'; custom.textContent = '— Type custom municipality —';
    muniSel.appendChild(custom);

    // Show/hide the custom municipality text input
    const customInput = document.getElementById('surveyMunicipalityCustom');
    if (customInput) customInput.style.display = 'none';
}

function onMunicipalityChange(muni) {
    const brgySel = document.getElementById('surveyBarangay');
    const customMuniInput = document.getElementById('surveyMunicipalityCustom');

    brgySel.innerHTML = '<option value="">— Select Barangay —</option>';
    brgySel.disabled = true;

    // Show/hide custom municipality text input
    if (customMuniInput) {
        customMuniInput.style.display = (muni === '__custom__') ? 'block' : 'none';
        if (muni === '__custom__') {
            customMuniInput.focus();
            return; // barangay stays disabled until they type
        }
    }

    const province = document.getElementById('surveyProvince').value;
    if (!province || !locationData[province] || !locationData[province][muni]) {
        return;
    }

    brgySel.disabled = false;
    locationData[province][muni].forEach(b => {
        const o = document.createElement('option');
        if (b === '— Custom —') {
            o.value = '__custom_brgy__';
            o.textContent = '— Type custom barangay —';
        } else {
            o.value = b; o.textContent = b;
        }
        brgySel.appendChild(o);
    });

    // Show/hide custom barangay text input on change
    brgySel.onchange = function() {
        const customBrgyInput = document.getElementById('surveyBarangayCustom');
        if (customBrgyInput) {
            customBrgyInput.style.display = (this.value === '__custom_brgy__') ? 'block' : 'none';
            if (this.value === '__custom_brgy__') customBrgyInput.focus();
        }
    };
}

// Auto-select Pangasinan on load — always fire since province is locked
window.addEventListener('DOMContentLoaded', function() {
    // Province is fixed to Pangasinan — populate municipalities immediately
    onProvinceChange('Pangasinan');
    // Ensure the province select shows Pangasinan
    const provSel = document.getElementById('surveyProvince');
    if (provSel) provSel.value = 'Pangasinan';
});

// ══════════════════════════════════════════════════════════════
// AUTO-PIN FROM TYPED LAT/LNG (Minor 2)
// ══════════════════════════════════════════════════════════════
(function() {
    // Make centerLat/centerLng inputs writable and auto-fly map
    const makeEditable = () => {
        const latEl = document.getElementById('centerLat');
        const lngEl = document.getElementById('centerLng');
        if (!latEl || !lngEl) return;

        // Remove readonly so users can type
        latEl.removeAttribute('readonly');
        lngEl.removeAttribute('readonly');
        latEl.placeholder = 'e.g. 15.9754';
        lngEl.placeholder = 'e.g. 120.5701';

        const flyFromInputs = () => {
            const lat = parseFloat(latEl.value);
            const lng = parseFloat(lngEl.value);
            if (isNaN(lat) || isNaN(lng)) return;
            if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return;

            // Fly map to entered coordinates
            map.flyTo([lat, lng], 16, { duration: 0.8 });

            // Drop / move pin
            if (pickedMarker) map.removeLayer(pickedMarker);
            pickedMarker = L.marker([lat, lng], {
                icon: L.divIcon({ className: '', html: '<div style="background:#1a2744;color:white;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:700;white-space:nowrap">Center</div>', iconAnchor: [28,10] })
            }).addTo(map);
        };

        // Debounce so it doesn't fire on every keypress
        let flyTimer;
        const debounce = () => { clearTimeout(flyTimer); flyTimer = setTimeout(flyFromInputs, 600); };
        latEl.addEventListener('input', debounce);
        lngEl.addEventListener('input', debounce);
    };
    // Run after page load (map must be initialised)
    window.addEventListener('DOMContentLoaded', makeEditable);
})();

// ── Boot ─────────────────────────────────────────────────────
setTimeout(() => document.getElementById('mapHint').classList.add('hide'), 5000);
populateMuniDatalist();
initCustomPins();
loadSurveys();
</script>
</body>
</html>
