<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trung tâm giám sát Log - DeviceShop Admin</title>
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>

<body class="index-admin-body">
    <div class="admin-layout">
        <aside class="sidebar">
            <div class="brand">
                DeviceShop Monitor
                <span>Hệ thống theo dõi Log</span>
            </div>

            <div class="nav-label">Danh mục Log (4)</div>
            <div class="nav-item disabled">
                <span>1. Activity log khách</span>
                <span class="badge-soon">Chờ làm</span>
            </div>
            <a href="{{ route('Admin.index') }}" class="nav-item active">
                <span>2. Log trạng thái</span>
            </a>
            <div class="nav-item disabled">
                <span>3. Log hệ thống</span>
                <span class="badge-soon">Chờ làm</span>
            </div>
            <div class="nav-item disabled">
                <span>4. Log chỉ số</span>
                <span class="badge-soon">Chờ làm</span>
            </div>
        </aside>

        <div class="main-wrapper">
            <header class="topbar">
                <h1 class="topbar-title">Log trạng thái (Đơn hàng & Giao dịch QR)</h1>
                <div class="topbar-right">
                    <span>Quản trị: <strong>{{ session('admin_ten', 'Admin') }}</strong></span>
                    <form action="{{ route('admin.logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="btn-logout">Đăng xuất</button>
                    </form>
                </div>
            </header>

            <main class="content">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-title">Tổng bản ghi log_trangthai</div>
                        <div class="stat-value">{{ $tongLog }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Log đơn hàng (DON_HANG)</div>
                        <div class="stat-value">{{ $tongDonHang }}</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-title">Log giao dịch QR (GIAO_DICH_QR)</div>
                        <div class="stat-value">{{ $tongGiaoDich }}</div>
                    </div>
                </div>

                <section class="panel">
                    <form action="{{ route('Admin.index') }}" method="GET" class="filter-bar">
                        <select name="loai">
                            <option value="">-- Tất cả loại đối tượng --</option>
                            <option value="DON_HANG" {{ ($loai ?? '') === 'DON_HANG' ? 'selected' : '' }}>DON_HANG</option>
                            <option value="GIAO_DICH_QR" {{ ($loai ?? '') === 'GIAO_DICH_QR' ? 'selected' : '' }}>GIAO_DICH_QR</option>
                        </select>

                        <input
                            type="text"
                            name="ma"
                            value="{{ $ma ?? '' }}"
                            placeholder="đang load"
                        >

                        <button type="submit" class="btn-filter">Lọc dữ liệu</button>
                        <a href="{{ route('Admin.index') }}" class="btn-reset">Xóa lọc</a>

                        <a href="{{ route('logs.export', request()->only(['loai', 'ma', 'from', 'to'])) }}" class="btn-filter">Xuất CSV </a>
                    </form>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Mã Log</th>
                                    <th>Loại đối tượng</th>
                                    <th>Mã đối tượng</th>
                                    <th>Trạng thái đơn hàng</th>
                                    <th>Trạng thái thanh toán</th>
                                    <th>Thời gian tạo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($logs as $item)
                                    <tr>
                                        <td>#{{ $item->malog_tt }}</td>
                                        <td>
                                            @if ($item->loai_doituong === 'DON_HANG')
                                                <span class="pill pill-dh">DON_HANG</span>
                                            @elseif ($item->loai_doituong === 'GIAO_DICH_QR')
                                                <span class="pill pill-qr">GIAO_DICH_QR</span>
                                            @else
                                                <span>{{ $item->loai_doituong ?? '-' }}</span>
                                            @endif
                                        </td>
                                        <td><strong>{{ $item->ma_doituong ?? '-' }}</strong></td>
                                        <td>{{ $item->trangthai_dh ?? '-' }}</td>
                                        <td>{{ $item->trangthai_tt ?? '-' }}</td>
                                        <td>{{ $item->created_at }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="empty-state">Chưa có dữ liệu log trạng thái nào.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-wrap">
                        {{ $logs->links() }}
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>