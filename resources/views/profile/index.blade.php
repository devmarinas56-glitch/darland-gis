<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Land GIS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #e8e8e8; display: flex; min-height: 100vh; }

        /* ── Sidebar ── */
        .sidebar { width: 200px; background: #1a2744; display: flex; flex-direction: column; position: fixed; height: 100vh; left: 0; top: 0; z-index: 1000; }
        .logo-section { padding: 20px 15px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .logo-section img { width: 50px; height: 50px; object-fit: contain; }
        .nav-menu { flex: 1; padding: 15px 0; }
        .nav-item { display: flex; align-items: center; padding: 14px 20px; color: rgba(255,255,255,0.7); text-decoration: none; gap: 12px; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; transition: all 0.2s; }
        .nav-item i { font-size: 16px; width: 18px; }
        .nav-item:hover { background: rgba(255,255,255,0.1); color: white; }
        .nav-item.active { background: rgba(255,255,255,0.15); color: white; }
        .logout-section { padding: 15px; border-top: 1px solid rgba(255,255,255,0.1); }
        .logout-btn { width: 100%; padding: 11px; background: rgba(255,255,255,0.1); color: rgba(255,255,255,0.7); border: none; border-radius: 6px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 13px; font-weight: 500; text-transform: uppercase; transition: all 0.2s; text-decoration: none; }
        .logout-btn:hover { background: rgba(255,255,255,0.2); color: white; }

        /* ── Main ── */
        .main-content { margin-left: 200px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }

        /* ── Top bar ── */
        .top-bar { background: white; padding: 11px 28px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 1px 4px rgba(0,0,0,0.08); flex-shrink: 0; }
        .search-box { flex: 1; max-width: 380px; position: relative; }
        .search-box i { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #bbb; font-size: 13px; }
        .search-input { width: 100%; padding: 8px 14px 8px 34px; border: 1px solid #e8e8e8; border-radius: 20px; font-size: 13px; background: #f7f7f7; }
        .top-right { display: flex; align-items: center; gap: 18px; }
        .bell-icon { font-size: 18px; color: #aaa; cursor: pointer; }
        .user-chip { display: flex; align-items: center; gap: 10px; text-decoration: none; cursor: pointer; }
        .user-avatar { width: 34px; height: 34px; border-radius: 50%; overflow: hidden; background: #1a2744; display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 12px; flex-shrink: 0; }
        .user-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .user-name { font-size: 13px; font-weight: 600; color: #333; }
        .user-role-label { font-size: 11px; color: #999; text-transform: uppercase; }

        /* ── Content ── */
        .content-area { padding: 28px 32px 40px; flex: 1; max-width: 760px; }
        .page-title { font-size: 19px; font-weight: 700; color: #222; margin-bottom: 20px; }

        .alert { padding: 10px 16px; border-radius: 8px; margin-bottom: 16px; font-size: 13px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .alert-error { background: #fff0f0; color: #c62828; border: 1px solid #ffcdd2; }

        /* ── Cards ── */
        .card { background: white; border-radius: 14px; padding: 24px 26px; margin-bottom: 16px; box-shadow: 0 1px 4px rgba(0,0,0,0.07); }
        .card-title { font-size: 14px; font-weight: 700; color: #222; margin-bottom: 20px; }

        /* Photo section */
        .photo-section { display: flex; align-items: center; gap: 20px; margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid #f0f0f0; }
        .photo-ring { width: 84px; height: 84px; border-radius: 50%; border: 3px solid #e0e0e0; overflow: hidden; background: #e8e8e8; display: flex; align-items: center; justify-content: center; font-size: 26px; font-weight: 700; color: #888; cursor: pointer; position: relative; flex-shrink: 0; }
        .photo-ring img { width: 100%; height: 100%; object-fit: cover; }
        .photo-overlay { position: absolute; inset: 0; background: rgba(0,0,0,0.35); display: flex; align-items: center; justify-content: center; opacity: 0; transition: opacity 0.2s; border-radius: 50%; }
        .photo-ring:hover .photo-overlay { opacity: 1; }
        .photo-overlay i { color: white; font-size: 16px; }
        .photo-meta-name { font-size: 17px; font-weight: 700; color: #222; }
        .photo-meta-role { font-size: 12px; color: #999; text-transform: uppercase; margin-top: 2px; }
        .photo-actions { display: flex; gap: 8px; margin-top: 8px; }
        .btn-save-photo { padding: 7px 16px; background: #1a2744; color: white; border: none; border-radius: 7px; font-size: 12px; font-weight: 700; cursor: pointer; }
        .btn-save-photo:disabled { background: #bbb; cursor: not-allowed; }
        .btn-save-photo:hover:not(:disabled) { background: #2d4070; }
        .btn-discard-photo { padding: 7px 14px; background: #f5f5f5; color: #888; border: 1px solid #e0e0e0; border-radius: 7px; font-size: 12px; font-weight: 600; cursor: pointer; }
        .btn-discard-photo:hover { background: #eee; }

        /* Fields */
        .fields-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .field-group { display: flex; flex-direction: column; gap: 4px; }
        .field-label { font-size: 11px; font-weight: 700; color: #666; text-transform: uppercase; letter-spacing: 0.3px; }
        .field-input { padding: 9px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; color: #333; background: white; }
        .field-input:focus { outline: none; border-color: #1a2744; }
        .btn-save-profile { padding: 10px 26px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; justify-self: end; grid-column: 1/-1; }
        .btn-save-profile:hover { background: #2d4070; }

        /* Password */
        .pw-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
        .pw-wrap { position: relative; }
        .pw-wrap .field-input { width: 100%; padding-right: 38px; }
        .pw-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); cursor: pointer; color: #bbb; font-size: 14px; }
        .pw-toggle:hover { color: #555; }
        .btn-update-pw { padding: 10px 26px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 700; cursor: pointer; }
        .btn-update-pw:hover { background: #2d4070; }
    </style>
</head>
<body>

    <aside class="sidebar">
        <div class="logo-section">
            <img src="{{ asset('images/Darlandicon.png') }}" alt="Land GIS">
        </div>
        <nav class="nav-menu">
            <a href="/dashboard"    class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer"   class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item"><i class="fas fa-file-alt"></i><span>Land Records</span></a>
            <a href="/add-record"   class="nav-item"><i class="fas fa-plus-square"></i><span>Add Record</span></a>
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

    <div class="main-content">
        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search...">
            </div>
            <div class="top-right">
                <i class="fas fa-bell bell-icon"></i>
                <a href="/profile" class="user-chip">
                    <div class="user-avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</div>
                    <div>
                        <div class="user-name">{{ auth()->user()->name }}</div>
                        <div class="user-role-label">{{ strtoupper(auth()->user()->role) }}</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="content-area">
            <h1 class="page-title">My Profile</h1>

            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error">
                    <ul style="margin:0;padding-left:16px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            <!-- Profile Card -->
            <div class="card">
                <div class="card-title">Profile Information</div>

                {{-- Photo form --}}
                <form id="photoForm" action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="first_name" id="hid_fn" value="{{ auth()->user()->first_name ?? explode(' ', auth()->user()->name)[0] }}">
                    <input type="hidden" name="last_name"  id="hid_ln" value="{{ auth()->user()->last_name  ?? (explode(' ', auth()->user()->name)[1] ?? '') }}">
                    <input type="hidden" name="email"      id="hid_em" value="{{ auth()->user()->email }}">
                    <input type="file" id="photoInput" name="profile_photo" accept="image/*" style="display:none" onchange="onPhotoSelected(this)">

                    <div class="photo-section">
                        <div class="photo-ring" onclick="document.getElementById('photoInput').click()">
                            @if(auth()->user()->profile_photo)
                                <img id="photoPreview" src="{{ asset('storage/' . auth()->user()->profile_photo) }}" alt="">
                            @else
                                <span id="photoInitials">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
                                <img id="photoPreview" src="" alt="" style="display:none">
                            @endif
                            <div class="photo-overlay"><i class="fas fa-camera"></i></div>
                        </div>
                        <div>
                            <div class="photo-meta-name">{{ auth()->user()->first_name ?? explode(' ', auth()->user()->name)[0] }}</div>
                            <div class="photo-meta-role">{{ strtoupper(auth()->user()->role) }}</div>
                            <div class="photo-actions">
                                <button type="submit" class="btn-save-photo" id="savePhotoBtn" disabled>
                                    <i class="fas fa-save"></i> Save Photo
                                </button>
                                <button type="button" class="btn-discard-photo" id="discardPhotoBtn" style="display:none" onclick="discardPhoto()">
                                    <i class="fas fa-times"></i> Discard
                                </button>
                            </div>
                            <div style="font-size:11px;color:#bbb;margin-top:6px">Click photo to change &mdash; JPG, PNG or WebP, max 4 MB</div>
                        </div>
                    </div>
                </form>

                {{-- Profile fields form --}}
                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    <div class="fields-grid">
                        <div class="field-group">
                            <label class="field-label">First Name</label>
                            <input type="text" name="first_name" class="field-input"
                                value="{{ old('first_name', auth()->user()->first_name ?? explode(' ', auth()->user()->name)[0]) }}" required
                                oninput="document.getElementById('hid_fn').value=this.value">
                        </div>
                        <div class="field-group">
                            <label class="field-label">Last Name</label>
                            <input type="text" name="last_name" class="field-input"
                                value="{{ old('last_name', auth()->user()->last_name ?? (explode(' ', auth()->user()->name)[1] ?? '')) }}" required
                                oninput="document.getElementById('hid_ln').value=this.value">
                        </div>
                        <div class="field-group" style="grid-column:1/-1">
                            <label class="field-label">Email Address</label>
                            <input type="email" name="email" class="field-input"
                                value="{{ old('email', auth()->user()->email) }}" required
                                oninput="document.getElementById('hid_em').value=this.value">
                        </div>
                        <button type="submit" class="btn-save-profile">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>

            <!-- Password Card -->
            <div class="card">
                <div class="card-title">Change Password</div>
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    <div class="pw-grid">
                        <div class="field-group">
                            <label class="field-label">Current Password</label>
                            <div class="pw-wrap">
                                <input type="password" name="current_password" class="field-input" placeholder="Current password" required>
                                <i class="fas fa-eye pw-toggle" onclick="togglePw(this)"></i>
                            </div>
                        </div>
                        <div></div>
                        <div class="field-group">
                            <label class="field-label">New Password</label>
                            <div class="pw-wrap">
                                <input type="password" name="new_password" class="field-input" placeholder="Min. 8 characters" required>
                                <i class="fas fa-eye pw-toggle" onclick="togglePw(this)"></i>
                            </div>
                        </div>
                        <div class="field-group">
                            <label class="field-label">Confirm New Password</label>
                            <div class="pw-wrap">
                                <input type="password" name="new_password_confirmation" class="field-input" placeholder="Repeat new password" required>
                                <i class="fas fa-eye pw-toggle" onclick="togglePw(this)"></i>
                            </div>
                        </div>
                    </div>
                    <div style="display:flex;justify-content:flex-end">
                        <button type="submit" class="btn-update-pw">
                            <i class="fas fa-key"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function togglePw(icon) {
            const input = icon.previousElementSibling;
            const show  = input.type === 'password';
            input.type  = show ? 'text' : 'password';
            icon.classList.toggle('fa-eye',       !show);
            icon.classList.toggle('fa-eye-slash',  show);
        }

        function onPhotoSelected(input) {
            if (!input.files || !input.files[0]) return;
            const reader = new FileReader();
            reader.onload = e => {
                const initials = document.getElementById('photoInitials');
                const preview  = document.getElementById('photoPreview');
                if (initials) initials.style.display = 'none';
                preview.src = e.target.result;
                preview.style.display = 'block';
                document.getElementById('savePhotoBtn').disabled = false;
                document.getElementById('discardPhotoBtn').style.display = '';
            };
            reader.readAsDataURL(input.files[0]);
        }

        function discardPhoto() {
            document.getElementById('photoInput').value = '';
            const preview  = document.getElementById('photoPreview');
            const initials = document.getElementById('photoInitials');
            @if(auth()->user()->profile_photo)
                preview.src = '{{ asset('storage/' . auth()->user()->profile_photo) }}';
                preview.style.display = 'block';
                if (initials) initials.style.display = 'none';
            @else
                preview.src = '';
                preview.style.display = 'none';
                if (initials) initials.style.display = 'flex';
            @endif
            document.getElementById('savePhotoBtn').disabled = true;
            document.getElementById('discardPhotoBtn').style.display = 'none';
        }
    </script>
</body>
</html>
