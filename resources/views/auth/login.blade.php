@extends('layouts.app')

@section('title', 'Đăng nhập - English Smart Class THCS')

@section('content')
<div class="auth-wrapper">
  <div class="auth-card">
    <div class="auth-logo">
      <div class="auth-logo-icon">🎙️</div>
      <div>
        <h1 style="font-size: 1.35rem; font-weight: 800; color: var(--text-main);">English Smart Class (THCS)</h1>
        <div style="font-size: 0.82rem; color: var(--text-muted);">Laravel 10 • Luyện đọc Từ vựng, Đoạn văn & Chấm điểm AI (Khối 6-9)</div>
      </div>
    </div>

    @if(session('error'))
      <div style="background: #fef2f2; border: 1px solid #fca5a5; color: #991b1b; padding: 10px 14px; border-radius: 10px; font-size: 0.86rem; margin-top: 12px;">
        ⚠️ {{ session('error') }}
      </div>
    @endif
    @if(session('success'))
      <div style="background: #f0fdf4; border: 1px solid #86efac; color: #166534; padding: 10px 14px; border-radius: 10px; font-size: 0.86rem; margin-top: 12px;">
        ✅ {{ session('success') }}
      </div>
    @endif

    <div class="role-tabs">
      <button type="button" id="tab-btn-student" class="role-tab active" onclick="switchLoginRole('student')">
        🎒 Học sinh THCS (Khối 6-9)
      </button>
      <button type="button" id="tab-btn-teacher" class="role-tab" onclick="switchLoginRole('teacher')">
        👩‍🏫 Giáo viên (Google)
      </button>
    </div>

    <!-- Form Học sinh -->
    <div id="panel-student">
      <form action="{{ route('login.student') }}" method="POST">
        @csrf
        <div class="form-group">
          <label class="form-label">Mã học sinh / Tên đăng nhập (Do thầy cô cấp)</label>
          <input type="text" id="stu-login-user" name="username" value="{{ old('username') }}" class="form-control" placeholder="Ví dụ: minhan6a1, hoangnam7a2, ducanh9a1..." required autocomplete="username" />
        </div>
        <div class="form-group">
          <label class="form-label">Mật khẩu</label>
          <input type="password" id="stu-login-pass" name="password" class="form-control" placeholder="Nhập mật khẩu..." required autocomplete="current-password" />
        </div>
        <button type="submit" class="btn btn-primary" style="width: 100%; padding: 12px; margin-top: 4px;">
          🚀 Đăng nhập Vào Lớp Học
        </button>
      </form>

      @if($sampleStudents->count() > 0)
        <div style="margin-top: 20px; padding: 14px; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1; font-size: 0.83rem;">
          <div style="font-weight: 700; color: #334155; margin-bottom: 6px;">💡 Tài khoản Học sinh Cấp 2 mẫu (Bấm để điền nhanh):</div>
          <div style="display: flex; flex-wrap: wrap; gap: 6px;">
            @foreach($sampleStudents as $stu)
              <button type="button" class="btn btn-outline btn-sm"
                onclick="fillStudent('{{ $stu->username }}', '{{ $stu->plain_password ?? '123' }}')">
                👤 {{ $stu->name }} {{ $stu->schoolClass ? '(K'.$stu->schoolClass->grade.')' : '' }} [<code>{{ $stu->username }}</code>]
              </button>
            @endforeach
          </div>
        </div>
      @endif
    </div>

    <!-- Form Giáo viên (Google OAuth via Laravel Socialite) -->
    <div id="panel-teacher" class="hidden">
      <div style="text-align: center; padding: 6px 0 14px;">
        <p style="font-size: 0.9rem; color: var(--text-muted); margin-bottom: 18px;">
          Giáo viên Tiếng Anh THCS đăng nhập bằng <strong>Tài khoản Google</strong> để quản lý danh sách lớp Khối 6-9, soạn bài Từ vựng & Đoạn văn, phân quyền cho học sinh và nghe lại file ghi âm chấm điểm tự động.
        </p>

        <a href="{{ route('auth.google') }}" class="btn btn-google">
          <svg width="20" height="20" viewBox="0 0 48 48">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
          </svg>
          <span>Đăng nhập Giáo viên bằng Tài khoản Google</span>
        </a>

        @if($googleConfigured)
          <div style="margin-top: 14px;">
            <span class="badge badge-green">☁️ Đã kích hoạt Google OAuth (Laravel Socialite)</span>
          </div>
        @else
          <div style="margin-top: 16px; text-align: left; background: #eff6ff; padding: 12px 14px; border-radius: 10px; font-size: 0.83rem; color: #1e40af;">
            <strong>⚡ Chế độ Chạy thử Local:</strong> Bạn có thể bấm nút Google ở trên để vào thẳng trang Quản trị Giáo viên ngay bây giờ. Khi upload lên hosting thật, chỉ cần điền <code>GOOGLE_CLIENT_ID</code> và <code>GOOGLE_CLIENT_SECRET</code> vào file <code>.env</code> để bật Google Login thật!
          </div>
        @endif
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
  function switchLoginRole(role) {
    document.getElementById('tab-btn-student').classList.toggle('active', role === 'student');
    document.getElementById('tab-btn-teacher').classList.toggle('active', role === 'teacher');
    document.getElementById('panel-student').classList.toggle('hidden', role !== 'student');
    document.getElementById('panel-teacher').classList.toggle('hidden', role !== 'teacher');
  }

  function fillStudent(username, password) {
    document.getElementById('stu-login-user').value = username;
    document.getElementById('stu-login-pass').value = password;
  }
</script>
@endpush
@endsection
