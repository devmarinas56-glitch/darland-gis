<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Submit Report - DAR</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f0f0;
            display: flex;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: 190px;
            background: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            left: 0; top: 0;
            z-index: 100;
            border-right: 1px solid #e5e7eb;
        }

        .logo-section {
            padding: 16px 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid #f0f0f0;
        }
        .logo-section img { width: 42px; height: 42px; object-fit: contain; flex-shrink: 0; }
        .logo-text { line-height: 1.2; }
        .logo-text .dept { font-size: 8px; font-weight: 700; color: #333; text-transform: uppercase; letter-spacing: 0.3px; }
        .logo-text .name { font-size: 7px; color: #666; text-transform: uppercase; letter-spacing: 0.2px; }

        .nav-menu { flex: 1; padding: 10px 0; }

        .nav-item {
            display: flex;
            align-items: center;
            padding: 11px 18px;
            color: #555;
            text-decoration: none;
            gap: 11px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.15s;
        }
        .nav-item i { font-size: 15px; width: 17px; flex-shrink: 0; }
        .nav-item:hover { background: #f5f5f5; color: #333; }
        .nav-item.active { background: #e8f5e9; color: #2e7d32; font-weight: 600; }

        .logout-section { padding: 12px 14px; border-top: 1px solid #f0f0f0; }
        .logout-btn {
            width: 100%;
            padding: 10px 14px;
            background: #fff;
            color: #555;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.15s;
        }
        .logout-btn:hover { background: #fef2f2; color: #c62828; border-color: #f5c6c6; }

        /* ── Main ── */
        .main-content {
            flex: 1;
            margin-left: 190px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        /* ── Top bar ── */
        .top-bar {
            background: white;
            padding: 10px 28px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .top-bar-left { }
        .top-bar-title { font-size: 16px; font-weight: 700; color: #1a1a1a; }
        .breadcrumb { font-size: 12px; color: #aaa; margin-top: 1px; }
        .breadcrumb a { color: #aaa; text-decoration: none; }
        .breadcrumb a:hover { color: #555; }
        .breadcrumb span { margin: 0 5px; }

        .top-bar-right {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .bell-btn {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #f5f5f5;
            border: none;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer;
            color: #666;
            font-size: 15px;
        }
        .bell-btn:hover { background: #eee; }

        .user-chip {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            text-decoration: none;
            background: #f8f8f8;
            padding: 5px 12px 5px 5px;
            border-radius: 50px;
            border: 1px solid #e8e8e8;
        }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #4a5568;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 700; font-size: 12px;
        }
        .user-info .user-name { font-size: 13px; font-weight: 600; color: #222; line-height: 1.2; }
        .user-info .user-role { font-size: 11px; color: #888; }

        /* ── Content ── */
        .content-area { padding: 24px 28px 40px; flex: 1; }

        /* ── Form Card ── */
        .form-card {
            background: #f5f5f5;
            border-radius: 12px;
            padding: 24px 26px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.06);
        }

        .form-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
        }
        .form-card-title { font-size: 15px; font-weight: 700; color: #1a1a1a; }

        .btn-submit {
            padding: 10px 22px;
            background: #2e7d32;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
            display: flex;
            align-items: center;
            gap: 7px;
        }
        .btn-submit:hover { background: #1b5e20; }
        .btn-submit:disabled { background: #a5d6a7; cursor: not-allowed; }

        /* ── Form fields ── */
        .field-row-2 {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-bottom: 16px;
        }

        .field-group { display: flex; flex-direction: column; gap: 5px; }
        .field-label { font-size: 12px; font-weight: 600; color: #555; }
        .field-label .req { color: #e53935; margin-left: 2px; }

        .field-input,
        .field-select,
        .field-textarea {
            padding: 10px 12px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            font-size: 13px;
            color: #333;
            background: white;
            width: 100%;
            font-family: inherit;
            transition: border-color 0.15s;
        }
        .field-input:focus,
        .field-select:focus,
        .field-textarea:focus {
            outline: none;
            border-color: #2e7d32;
            box-shadow: 0 0 0 3px rgba(46,125,50,0.08);
        }
        .field-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%23999' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 34px; cursor: pointer; }

        .field-textarea { resize: vertical; min-height: 110px; }

        /* Date input icons */
        .date-wrapper { position: relative; }
        .date-wrapper .field-input { padding-right: 70px; }
        .date-actions {
            position: absolute;
            right: 8px; top: 50%;
            transform: translateY(-50%);
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .date-actions button {
            background: none;
            border: none;
            cursor: pointer;
            color: #aaa;
            font-size: 14px;
            padding: 2px;
            transition: color 0.15s;
        }
        .date-actions button:hover { color: #555; }

        /* ── Upload area ── */
        .upload-section { margin-top: 4px; }
        .upload-section-title { font-size: 13px; font-weight: 700; color: #1a1a1a; margin-bottom: 12px; }

        .upload-zone {
            border: 1.5px dashed #ccc;
            border-radius: 8px;
            background: white;
            padding: 22px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            cursor: pointer;
            transition: border-color 0.15s;
        }
        .upload-zone:hover { border-color: #2e7d32; }
        .upload-zone.drag-over { border-color: #2e7d32; background: #f0faf0; }

        .upload-left { display: flex; align-items: center; gap: 12px; color: #bbb; }
        .upload-left i { font-size: 22px; }
        .upload-left span { font-size: 13px; color: #999; }

        .btn-choose {
            padding: 8px 16px;
            background: white;
            border: 1px solid #d0d0d0;
            border-radius: 6px;
            font-size: 13px;
            color: #444;
            cursor: pointer;
            white-space: nowrap;
            transition: border-color 0.15s;
            flex-shrink: 0;
        }
        .btn-choose:hover { border-color: #888; }

        #fileInput { display: none; }

        .file-list { margin-top: 10px; }
        .file-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            background: white;
            border: 1px solid #e8e8e8;
            border-radius: 7px;
            margin-bottom: 6px;
            font-size: 12px;
            color: #444;
        }
        .file-item i { color: #1565c0; font-size: 14px; }
        .file-item .file-name { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file-item .file-size { color: #aaa; white-space: nowrap; }
        .file-item .file-remove { background: none; border: none; cursor: pointer; color: #bbb; font-size: 14px; padding: 0 2px; transition: color 0.15s; }
        .file-item .file-remove:hover { color: #e53935; }

        /* ── Alert ── */
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
            display: none;
        }
        .alert.success { background: #e8f5e9; color: #1b5e20; border: 1px solid #a5d6a7; }
        .alert.error   { background: #fdecea; color: #b71c1c; border: 1px solid #ef9a9a; }

        /* ── Responsive ── */
        @media (max-width: 700px) {
            .field-row-2 { grid-template-columns: 1fr; }
        }
        @media (max-width: 600px) {
            .sidebar { width: 54px; }
            .logo-text, .nav-item span, .logout-btn span { display: none; }
            .nav-item { justify-content: center; padding: 13px; gap: 0; }
            .main-content { margin-left: 54px; }
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="DAR">
            <div class="logo-text">
                <div class="dept">Department of</div>
                <div class="name">Agrarian Reform</div>
            </div>
        </div>
        <nav class="nav-menu">
            <a href="/dashboard"     class="nav-item"><i class="fas fa-th-large"></i><span>Dashboard</span></a>
            <a href="/submit-report" class="nav-item active"><i class="fas fa-file-alt"></i><span>Submit Report</span></a>
            <a href="/my-reports"    class="nav-item"><i class="fas fa-folder-open"></i><span>My Reports</span></a>
            @if(auth()->user()->role === 'admin')
            <a href="{{ route('admin.users') }}" class="nav-item"><i class="fas fa-users-cog"></i><span>Users</span></a>
            @endif
            <a href="/profile" class="nav-item"><i class="fas fa-cog"></i><span>Account Setting</span></a>
        </nav>
        <div class="logout-section">
            <a href="/logout" class="logout-btn">
                <i class="fas fa-sign-out-alt"></i><span>Log Out</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-content">

        <!-- Top Bar -->
        <div class="top-bar">
            <div class="top-bar-left">
                <div class="top-bar-title">Submit Accomplishment</div>
                <div class="breadcrumb">
                    <a href="/dashboard">Dashboard</a>
                    <span>›</span>
                    My Reports
                </div>
            </div>
            <div class="top-bar-right">
                <button class="bell-btn"><i class="fas fa-bell"></i></button>
                <a href="/profile" class="user-chip">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role">{{ strtoupper(auth()->user()->role ?? 'Staff') }}</div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Content -->
        <div class="content-area">

            <div id="formAlert" class="alert"></div>

            <div class="form-card">
                <div class="form-card-header">
                    <div class="form-card-title">Accomplishment Information</div>
                    <button class="btn-submit" id="submitBtn" onclick="submitReport()">
                        <i class="fas fa-paper-plane"></i> Submit Report
                    </button>
                </div>

                <!-- Program + Date -->
                <div class="field-row-2">
                    <div class="field-group">
                        <label class="field-label">Program<span class="req">*</span></label>
                        <select class="field-select" id="f_program">
                            <option value="" disabled selected>Select Program/Project</option>
                            <option value="CARP">CARP (Comprehensive Agrarian Reform Program)</option>
                            <option value="CARPER">CARPER (CARP Extension with Reforms)</option>
                            <option value="AJDP">AJDP (Agrarian Justice Delivery Program)</option>
                            <option value="LTSP">LTSP (Land Tenure Security Program)</option>
                            <option value="ARBDSP">ARBDSP (ARB Development Support Program)</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="field-label">Date of Activity<span class="req">*</span></label>
                        <div class="date-wrapper">
                            <input type="date" class="field-input" id="f_date">
                            <div class="date-actions">
                                <button type="button" onclick="document.getElementById('f_date').showPicker()" title="Open calendar"><i class="fas fa-calendar-alt"></i></button>
                                <button type="button" onclick="document.getElementById('f_date').value=''" title="Clear date"><i class="fas fa-times"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="field-group" style="margin-bottom:20px">
                    <label class="field-label">Description of Activity<span class="req">*</span></label>
                    <textarea class="field-textarea" id="f_description" placeholder="Write description here..."></textarea>
                </div>

                <!-- Upload -->
                <div class="upload-section">
                    <div class="upload-section-title">Upload Documents</div>
                    <div class="upload-zone" id="uploadZone"
                         ondragover="handleDragOver(event)"
                         ondragleave="handleDragLeave(event)"
                         ondrop="handleDrop(event)"
                         onclick="document.getElementById('fileInput').click()">
                        <div class="upload-left">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Drag and drop files here</span>
                        </div>
                        <button class="btn-choose" type="button" onclick="event.stopPropagation(); document.getElementById('fileInput').click()">Choose Files</button>
                    </div>
                    <input type="file" id="fileInput" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.jpeg,.png" onchange="handleFileSelect(this.files)">
                    <div class="file-list" id="fileList"></div>
                </div>

            </div><!-- /form-card -->

        </div><!-- /content-area -->
    </div><!-- /main-content -->

    <script>
        let selectedFiles = [];

        // ── Drag & drop ──────────────────────────────────────────
        function handleDragOver(e) {
            e.preventDefault();
            document.getElementById('uploadZone').classList.add('drag-over');
        }
        function handleDragLeave(e) {
            document.getElementById('uploadZone').classList.remove('drag-over');
        }
        function handleDrop(e) {
            e.preventDefault();
            document.getElementById('uploadZone').classList.remove('drag-over');
            handleFileSelect(e.dataTransfer.files);
        }
        function handleFileSelect(files) {
            for (const file of files) {
                if (!selectedFiles.find(f => f.name === file.name && f.size === file.size)) {
                    selectedFiles.push(file);
                }
            }
            renderFileList();
        }
        function removeFile(index) {
            selectedFiles.splice(index, 1);
            renderFileList();
        }
        function renderFileList() {
            const list = document.getElementById('fileList');
            if (!selectedFiles.length) { list.innerHTML = ''; return; }
            list.innerHTML = selectedFiles.map((f, i) => `
                <div class="file-item">
                    <i class="fas fa-file-alt"></i>
                    <span class="file-name">${f.name}</span>
                    <span class="file-size">${formatSize(f.size)}</span>
                    <button class="file-remove" onclick="removeFile(${i})" title="Remove"><i class="fas fa-times"></i></button>
                </div>
            `).join('');
        }
        function formatSize(bytes) {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        }

        // ── Submit ───────────────────────────────────────────────
        async function submitReport() {
            const program     = document.getElementById('f_program').value;
            const date        = document.getElementById('f_date').value;
            const description = document.getElementById('f_description').value.trim();
            const alertBox    = document.getElementById('formAlert');
            const btn         = document.getElementById('submitBtn');

            // Validate
            if (!program || !date || !description) {
                showAlert('error', 'Please fill in all required fields: Program, Date, and Description.');
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';

            const formData = new FormData();
            formData.append('program', program);
            formData.append('date_of_activity', date);
            formData.append('description', description);
            selectedFiles.forEach((f, i) => formData.append(`documents[${i}]`, f));

            try {
                const res = await fetch('/api/reports', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData
                });
                const json = await res.json();
                if (json.success) {
                    showAlert('success', 'Report submitted successfully!');
                    // Reset form
                    document.getElementById('f_program').value = '';
                    document.getElementById('f_date').value = '';
                    document.getElementById('f_description').value = '';
                    selectedFiles = [];
                    renderFileList();
                } else {
                    showAlert('error', json.message || 'Submission failed. Please try again.');
                }
            } catch (err) {
                showAlert('error', 'Network error. Please check your connection.');
            } finally {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Report';
            }
        }

        function showAlert(type, msg) {
            const box = document.getElementById('formAlert');
            box.className = 'alert ' + type;
            box.textContent = msg;
            box.style.display = 'block';
            window.scrollTo({ top: 0, behavior: 'smooth' });
            setTimeout(() => { box.style.display = 'none'; }, 5000);
        }
    </script>

</body>
</html>
