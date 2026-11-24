<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="csrf-token" content="{{ csrf_token() }}" />
  <title>@yield('title', 'English Smart Class THCS - Laravel 10')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/styles.css') }}" />
</head>
<body>
  @auth
    <header class="app-header">
      <div class="header-inner">
        <div class="brand">
          <div class="brand-icon">🎙️</div>
          <div>
            <div class="brand-title">English Smart Class • THCS (Khối 6 - 9)</div>
            <div class="brand-subtitle">Chủ đề tài: Trần Thị Ánh Minh</div>
          </div>
        </div>

        <div class="user-bar">
          @if(session('impersonator_teacher_id'))
            <form action="{{ route('impersonate.leave') }}" method="POST" style="display:inline;">
              @csrf
              <button type="submit" class="btn btn-primary btn-sm">⬅ Quay về trang Giáo viên</button>
            </form>
          @endif

          @if(auth()->user()->isTeacher())
            <span class="badge badge-blue">👩‍🏫 Giáo viên: {{ auth()->user()->email ?? auth()->user()->name }}</span>
          @else
            <span class="badge badge-purple">🎒 Học sinh: {{ auth()->user()->name }} ({{ auth()->user()->schoolClass->name ?? 'Chưa xếp lớp' }})</span>
          @endif

          <form action="{{ route('logout') }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-outline btn-sm">Đăng xuất</button>
          </form>
        </div>
      </div>
    </header>

    <main class="app-container">
      @if(session('success'))
        <div class="card" style="background: #f0fdf4; border-color: #86efac; color: #166534; padding: 14px 18px; margin-bottom: 18px; font-weight: 600;">
          ✅ {{ session('success') }}
        </div>
      @endif

      @if(session('error'))
        <div class="card" style="background: #fef2f2; border-color: #fca5a5; color: #991b1b; padding: 14px 18px; margin-bottom: 18px; font-weight: 600;">
          ⚠️ {{ session('error') }}
        </div>
      @endif

      @if($errors->any())
        <div class="card" style="background: #fef2f2; border-color: #fca5a5; color: #991b1b; padding: 14px 18px; margin-bottom: 18px;">
          <strong>⚠️ Vui lòng kiểm tra lại thông tin:</strong>
          <ul style="margin-left: 18px; margin-top: 4px; font-size: 0.9rem;">
            @foreach($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      @yield('content')
    </main>
  @else
    @yield('content')
  @endauth

  <!-- Modal dùng chung -->
  <div id="app-modal" class="modal-backdrop hidden" onclick="if(event.target === this) closeAppModal()">
    <div id="app-modal-body" class="modal-content"></div>
  </div>

  <!-- Toast thông báo -->
  <div id="toast-container" class="toast-container"></div>

  <script src="{{ asset('js/speech-grader.js') }}"></script>
  <script>
    function showToast(message, type = 'info') {
      const container = document.getElementById('toast-container');
      if (!container) return;
      const toast = document.createElement('div');
      toast.className = 'toast';
      const icon = type === 'success' ? '✅' : type === 'error' ? '⚠️' : '💡';
      toast.innerHTML = `<span>${icon}</span><span>${escapeHtml(message)}</span>`;
      container.appendChild(toast);
      setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 250);
      }, 3400);
    }

    function escapeHtml(str) {
      return String(str ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
    }

    function closeAppModal() {
      const modal = document.getElementById('app-modal');
      if (modal) modal.classList.add('hidden');
    }
  </script>
  @stack('scripts')
</body>
</html>
