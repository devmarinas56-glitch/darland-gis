<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Land GIS Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
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
        .content-area { padding: 25px 30px; flex: 1; overflow-y: auto; }
        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .page-title { font-size: 20px; font-weight: 700; color: #333; }
        .btn-create { padding: 10px 20px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.3s; }
        .btn-create:hover { background: #2d4070; }
        .content-card { background: white; border-radius: 12px; padding: 25px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 18px; font-size: 13px; }
        .alert-success { background: #e8f5e9; color: #2e7d32; border: 1px solid #a5d6a7; }
        .alert-error { background: #ffebee; color: #c62828; border: 1px solid #ef9a9a; }
        .users-table { width: 100%; border-collapse: collapse; }
        .users-table th { text-align: left; padding: 10px 12px; font-size: 12px; color: #888; font-weight: 600; border-bottom: 2px solid #f0f0f0; }
        .users-table td { padding: 13px 12px; font-size: 13px; color: #444; border-bottom: 1px solid #f5f5f5; }
        .users-table tr:hover td { background: #f9f9f9; }
        .role-badge { padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: uppercase; }
        .role-admin { background: #fce4ec; color: #c2185b; }
        .role-ajdp { background: #e3f2fd; color: #1565c0; }
        .role-ltsp { background: #e8f5e9; color: #2e7d32; }
        .role-arbdsp { background: #fff3e0; color: #e65100; }
        .action-btns { display: flex; gap: 8px; }
        .btn-edit { padding: 6px 12px; background: #1976d2; color: white; border: none; border-radius: 6px; font-size: 12px; cursor: pointer; }
        .btn-edit:hover { background: #1565c0; }
        .btn-delete { padding: 6px 12px; background: #e53935; color: white; border: none; border-radius: 6px; font-size: 12px; cursor: pointer; }
        .btn-delete:hover { background: #c62828; }

        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal { background: white; border-radius: 16px; padding: 30px; width: 460px; max-width: 95vw; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
        .modal h2 { font-size: 18px; font-weight: 700; color: #333; margin-bottom: 18px; }
        .form-group { margin-bottom: 14px; }
        .form-label { font-size: 12px; font-weight: 600; color: #555; display: block; margin-bottom: 5px; }
        .form-input { width: 100%; padding: 10px 13px; border: 1px solid #ddd; border-radius: 8px; font-size: 13px; color: #333; }
        .form-input:focus { outline: none; border-color: #1a2744; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 18px; }
        .btn-save { padding: 10px 24px; background: #1a2744; color: white; border: none; border-radius: 8px; font-size: 13px; font-weight: 600; cursor: pointer; }
        .btn-save:hover { background: #2d4070; }
        .btn-cancel { padding: 10px 18px; background: #f5f5f5; color: #555; border: none; border-radius: 8px; font-size: 13px; cursor: pointer; }
        .btn-cancel:hover { background: #e0e0e0; }
        .password-note { font-size: 11px; color: #999; margin-top: 3px; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="logo-section"><img src="{{ asset('images/Darlandicon.png') }}" alt="Logo"></div>
        <nav class="nav-menu">
            <a href="/dashboard" class="nav-item"><i class="fas fa-home"></i><span>Dashboard</span></a>
            <a href="/map-viewer" class="nav-item"><i class="fas fa-map"></i><span>Map Viewer</span></a>
            <a href="/land-records" class="nav-item"><i class="fas fa-file-alt"></i><span>Land Records</span></a>
            <a href="/add-record" class="nav-item"><i class="fas fa-plus-square"></i><span>Add Record</span></a>
            <a href="{{ route('admin.users') }}" class="nav-item active"><i class="fas fa-users"></i><span>Users</span></a>
        </nav>
        <div class="logout-section">
            <a href="/logout" class="logout-btn"><i class="fas fa-sign-out-alt"></i><span>Log Out</span></a>
        </div>
    </aside>

    <div class="main-content">
        <div class="top-bar">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" class="search-input" placeholder="Search users...">
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
            <div class="page-header">
                <h1 class="page-title">User Management</h1>
                <button class="btn-create" onclick="openCreateModal()">
                    <i class="fas fa-plus"></i> Create Account
                </button>
            </div>

            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error"><ul style="margin:0;padding-left:18px">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
            @endif

            <div class="content-card">
                <table class="users-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td><span class="role-badge role-{{ $user->role }}">{{ strtoupper($user->role) }}</span></td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                <div class="action-btns">
                                    <button class="btn-edit" onclick="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->username) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}')">
                                        <i class="fas fa-pencil-alt"></i> Edit
                                    </button>
                                    @if($user->id !== auth()->id())
                                    <form method="POST" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Delete account for {{ addslashes($user->name) }}?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal-overlay" id="createModal">
        <div class="modal">
            <h2><i class="fas fa-user-plus" style="color:#1a2744;margin-right:8px"></i>Create Account</h2>
            <form method="POST" action="{{ route('admin.users.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" class="form-input" placeholder="e.g. Juan Dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-input" placeholder="e.g. juan.delacruz" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-input" placeholder="e.g. juan@darland.com" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-input" required>
                        <option value="">Select role</option>
                        <option value="ajdp">AJDP</option>
                        <option value="ltsp">LTSP</option>
                        <option value="arbdsp">ARBDSP</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Min 8 characters" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Repeat password" required>
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('createModal')">Cancel</button>
                    <button type="submit" class="btn-save">Create Account</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal-overlay" id="editModal">
        <div class="modal">
            <h2><i class="fas fa-user-edit" style="color:#1976d2;margin-right:8px"></i>Edit Account</h2>
            <form method="POST" id="editForm">
                @csrf @method('PUT')
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="name" id="edit_name" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" id="edit_username" class="form-input" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" id="edit_role" class="form-input" required>
                        <option value="ajdp">AJDP</option>
                        <option value="ltsp">LTSP</option>
                        <option value="arbdsp">ARBDSP</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="password" class="form-input" placeholder="Leave blank to keep">
                        <div class="password-note">Leave blank to keep current</div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" class="form-input" placeholder="Repeat if changing">
                    </div>
                </div>
                <div class="modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal('editModal')">Cancel</button>
                    <button type="submit" class="btn-save">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openCreateModal() { document.getElementById('createModal').classList.add('show'); }
        function openEditModal(id, name, username, email, role) {
            document.getElementById('editForm').action = '/admin/users/' + id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_email').value = email;
            document.getElementById('edit_role').value = role;
            document.getElementById('editModal').classList.add('show');
        }
        function closeModal(id) { document.getElementById(id).classList.remove('show'); }
        document.querySelectorAll('.modal-overlay').forEach(o => o.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); }));
    </script>
</body>
</html>
