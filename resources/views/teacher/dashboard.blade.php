@extends('layouts.app')

@section('title', 'Quản trị Giáo viên THCS - English Smart Class')

@php
  function renderScoreBadgePhp($score) {
      $s = (int) $score;
      $s10 = number_format($s / 10, 1);
      if ($s >= 85) return '<span class="badge badge-green">🌟 ' . $s10 . 'đ (' . $s . '% - Giỏi)</span>';
      if ($s >= 70) return '<span class="badge badge-blue">👍 ' . $s10 . 'đ (' . $s . '% - Khá)</span>';
      if ($s >= 50) return '<span class="badge badge-yellow">⚡ ' . $s10 . 'đ (' . $s . '% - Đạt)</span>';
      return '<span class="badge badge-red">❗ ' . $s10 . 'đ (' . $s . '% - Cần cố gắng)</span>';
  }
@endphp

@section('content')
  <!-- Thanh điều hướng 6 mục của Giáo viên -->
  <div class="nav-tabs">
    <a class="nav-tab {{ $tab === 'dashboard' ? 'active' : '' }}" href="{{ route('teacher.dashboard', ['tab' => 'dashboard']) }}">
      📊 Tổng quan THCS
    </a>
    <a class="nav-tab {{ $tab === 'accounts' ? 'active' : '' }}" href="{{ route('teacher.dashboard', ['tab' => 'accounts']) }}">
      👥 Quản lý Lớp & Tài khoản HS ({{ $students->count() }})
    </a>
    <a class="nav-tab {{ $tab === 'vocab' ? 'active' : '' }}" href="{{ route('teacher.dashboard', ['tab' => 'vocab']) }}">
      📘 Kho Từ vựng Khối 6-9 ({{ $allVocabSets->count() }})
    </a>
    <a class="nav-tab {{ $tab === 'passages' ? 'active' : '' }}" href="{{ route('teacher.dashboard', ['tab' => 'passages']) }}">
      📖 Kho Đoạn văn Khối 6-9 ({{ $allPassages->count() }})
    </a>
    <a class="nav-tab {{ $tab === 'assignments' ? 'active' : '' }}" href="{{ route('teacher.dashboard', ['tab' => 'assignments']) }}">
      🎯 Phân quyền Giao bài ({{ $assignments->count() }})
    </a>
    <a class="nav-tab {{ $tab === 'submissions' ? 'active' : '' }}" href="{{ route('teacher.dashboard', ['tab' => 'submissions']) }}">
      🏆 Bài nộp & Chấm điểm ({{ $allSubmissionsCount }})
    </a>
  </div>

  {{-- ================= TAB 1: TỔNG QUAN ================= --}}
  @if($tab === 'dashboard')
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon badge-blue">👥</div>
        <div>
          <div class="stat-value">{{ $students->count() }} HS</div>
          <div class="stat-label">{{ $classes->count() }} Lớp (Khối 6, 7, 8, 9)</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon badge-purple">📘</div>
        <div>
          <div class="stat-value">{{ $allVocabSets->count() }} Unit</div>
          <div class="stat-label">Bộ Từ vựng SGK THCS</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon badge-yellow">📖</div>
        <div>
          <div class="stat-value">{{ $allPassages->count() }} Bài</div>
          <div class="stat-label">Đoạn văn Luyện đọc Khối 6-9</div>
        </div>
      </div>
      <div class="stat-card">
        <div class="stat-icon badge-green">🎙️</div>
        <div>
          <div class="stat-value">{{ number_format($avgScore / 10, 1) }}đ ({{ $avgScore }}%)</div>
          <div class="stat-label">Điểm TB ({{ $allSubmissionsCount }} bài đã nộp)</div>
        </div>
      </div>
    </div>

    <div class="grid-2">
      <div class="card">
        <div class="card-header">
          <div class="card-title">🚀 Bảng Điều khiển Dành cho Giáo viên THCS (Laravel 10)</div>
        </div>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
          <a class="btn btn-outline" href="{{ route('teacher.dashboard', ['tab' => 'accounts']) }}">➕ Cấp Tài khoản Học sinh</a>
          <a class="btn btn-outline" href="{{ route('teacher.dashboard', ['tab' => 'vocab']) }}">➕ Soạn Từ vựng theo Unit</a>
          <a class="btn btn-outline" href="{{ route('teacher.dashboard', ['tab' => 'passages']) }}">➕ Soạn Đoạn văn Khối 6-9</a>
          <a class="btn btn-primary" href="{{ route('teacher.dashboard', ['tab' => 'assignments']) }}">🎯 Phân quyền Giao bài</a>
        </div>
        <div style="margin-top: 18px; padding: 14px; background: #f8fafc; border-radius: 12px; font-size: 0.87rem; color: #334155;">
          <strong>🎓 Tính năng tối ưu riêng cho Học sinh Cấp 2 (Khối 6, 7, 8, 9):</strong>
          <ul style="margin-left: 18px; margin-top: 6px; line-height: 1.7;">
            <li><strong>Cơ sở dữ liệu MySQL (Laravel Eloquent):</strong> Lưu trữ an toàn toàn bộ Lớp học, Tài khoản Học sinh, Bộ Từ vựng, Đoạn văn, Phân quyền và file ghi âm `.webm` của học sinh trên server.</li>
            <li><strong>Phát hiện lỗi phát âm THCS tự động:</strong> Nhắc nhở khi học sinh đọc thiếu âm đuôi <strong><code>-s/-es</code></strong> hoặc <strong><code>-ed</code></strong>.</li>
            <li><strong>Chấm điểm kép:</strong> Tự động quy đổi song song <strong>Thang điểm 10 (VD: 8.8/10đ)</strong> và <strong>Tỷ lệ chính xác (%)</strong>.</li>
          </ul>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">🔔 Bài đọc Học sinh mới gửi về</div>
          <a class="btn btn-outline btn-sm" href="{{ route('teacher.dashboard', ['tab' => 'submissions']) }}">Xem tất cả</a>
        </div>
        @if($submissions->isEmpty())
          <p style="color: var(--text-muted); font-size: 0.9rem;">Chưa có bài nộp nào.</p>
        @else
          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Học sinh</th>
                  <th>Bài đọc</th>
                  <th>Điểm AI</th>
                  <th>Chi tiết</th>
                </tr>
              </thead>
              <tbody>
                @foreach($submissions->take(5) as $sub)
                  <tr>
                    <td><strong>{{ $sub->student->name ?? 'Học sinh' }}</strong></td>
                    <td>{{ $sub->content_title }}</td>
                    <td>{!! renderScoreBadgePhp($sub->score) !!}</td>
                    <td>
                      <button type="button" class="btn btn-outline btn-sm" onclick="openSubmissionModal({{ $sub->id }})">
                        🎧 Nghe & Xem
                      </button>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>

  {{-- ================= TAB 2: QUẢN LÝ LỚP & TÀI KHOẢN HS ================= --}}
  @elseif($tab === 'accounts')
    <div class="grid-2">
      <div>
        <div class="card">
          <div class="card-header">
            <div class="card-title">🏫 1. Quản lý Lớp học THCS (Khối 6, 7, 8, 9)</div>
          </div>
          <form action="{{ route('teacher.classes.store') }}" method="POST" style="display: grid; grid-template-columns: 105px 1fr 1fr auto; gap: 8px; margin-bottom: 14px;">
            @csrf
            <select name="grade" class="form-control">
              <option value="6">Khối 6</option>
              <option value="7">Khối 7</option>
              <option value="8">Khối 8</option>
              <option value="9">Khối 9</option>
            </select>
            <input type="text" name="name" class="form-control" placeholder="Tên lớp (VD: Lớp 6A2...)" required />
            <input type="text" name="description" class="form-control" placeholder="Mô tả ngắn..." />
            <button type="submit" class="btn btn-primary">➕ Thêm</button>
          </form>

          <div class="table-responsive">
            <table class="table">
              <thead>
                <tr>
                  <th>Khối</th>
                  <th>Tên Lớp / Nhóm</th>
                  <th>Sĩ số</th>
                  <th>Thao tác</th>
                </tr>
              </thead>
              <tbody>
                @foreach($classes as $cls)
                  <tr>
                    <td><span class="badge badge-purple">Khối {{ $cls->grade }}</span></td>
                    <td>
                      <strong>{{ $cls->name }}</strong>
                      @if($cls->description)
                        <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $cls->description }}</div>
                      @endif
                    </td>
                    <td><span class="badge badge-blue">{{ $cls->students_count }} HS</span></td>
                    <td>
                      <form action="{{ route('teacher.classes.destroy', $cls) }}" method="POST" onsubmit="return confirm('Xóa lớp học này?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                      </form>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>

        <div class="card">
          <div class="card-header">
            <div class="card-title">👤 2. Cấp Tài khoản Mới cho Học sinh Cấp 2</div>
          </div>
          <form action="{{ route('teacher.students.store') }}" method="POST">
            @csrf
            <div class="grid-2" style="gap: 12px; grid-template-columns: 1fr 1fr;">
              <div class="form-group">
                <label class="form-label">Họ và tên Học sinh *</label>
                <input type="text" name="name" class="form-control" placeholder="VD: Nguyễn Minh Khôi" required />
              </div>
              <div class="form-group">
                <label class="form-label">Thuộc Lớp (Khối 6-9) *</label>
                <select name="school_class_id" class="form-control" required>
                  @foreach($classes as $cls)
                    <option value="{{ $cls->id }}">{{ $cls->name }}</option>
                  @endforeach
                </select>
              </div>
            </div>
            <div class="grid-2" style="gap: 12px; grid-template-columns: 1fr 1fr;">
              <div class="form-group">
                <label class="form-label">Tên đăng nhập (Mã HS) *</label>
                <input type="text" name="username" class="form-control" placeholder="VD: minhkhoi6a1" required />
              </div>
              <div class="form-group">
                <label class="form-label">Mật khẩu cấp cho HS *</label>
                <input type="text" name="password" class="form-control" value="123" required />
              </div>
            </div>
            <div class="form-group">
              <label class="form-label">Ghi chú của Giáo viên</label>
              <input type="text" name="note" class="form-control" placeholder="Ghi chú học lực hoặc SĐT phụ huynh..." />
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">
              ✅ Tạo Tài khoản Học sinh
            </button>
          </form>
        </div>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">📋 Danh sách Tài khoản Học sinh ({{ $students->count() }})</div>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Họ tên & Lớp</th>
                <th>Tài khoản / Mật khẩu</th>
                <th>Hành động</th>
              </tr>
            </thead>
            <tbody>
              @foreach($students as $stu)
                <tr>
                  <td>
                    <strong>{{ $stu->name }}</strong>
                    <div style="margin-top: 2px;">
                      <span class="badge badge-gray">{{ $stu->schoolClass->name ?? 'Chưa xếp lớp' }}</span>
                    </div>
                    @if($stu->note)
                      <div style="font-size: 0.78rem; color: var(--text-muted); margin-top: 4px;">{{ $stu->note }}</div>
                    @endif
                  </td>
                  <td>
                    <div>TK: <code>{{ $stu->username }}</code></div>
                    <div>MK: <code>{{ $stu->plain_password ?? '***' }}</code></div>
                  </td>
                  <td>
                    <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                      <button type="button" class="btn btn-outline btn-sm"
                        onclick="copyCredentials('{{ addslashes($stu->name) }}', '{{ $stu->username }}', '{{ $stu->plain_password ?? '123' }}')">
                        📋 Copy
                      </button>
                      <form action="{{ route('teacher.students.impersonate', $stu) }}" method="POST" style="display:inline;">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm" title="Đăng nhập thử dưới góc nhìn học sinh này">
                          👁️ Vào thử
                        </button>
                      </form>
                      <form action="{{ route('teacher.students.destroy', $stu) }}" method="POST" style="display:inline;" onsubmit="return confirm('Xóa tài khoản học sinh này?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                      </form>
                    </div>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    </div>

  {{-- ================= TAB 3: KHO TỪ VỰNG KHỐI 6-9 ================= --}}
  @elseif($tab === 'vocab')
    <div class="grid-2">
      <div class="card">
        <div class="card-header">
          <div class="card-title">➕ Soạn Bộ Từ vựng Mới (SGK Tiếng Anh Cấp 2)</div>
        </div>
        <form action="{{ route('teacher.vocab.store') }}" method="POST">
          @csrf
          <div class="form-group">
            <label class="form-label">Tiêu đề Unit / Chủ đề Từ vựng *</label>
            <input type="text" name="title" class="form-control" placeholder="VD: Tiếng Anh 7 - Unit 3: Community Service" required />
          </div>
          <div class="grid-2" style="gap: 12px; grid-template-columns: 1fr 1fr;">
            <div class="form-group">
              <label class="form-label">Khối lớp (THCS) *</label>
              <select name="level" class="form-control">
                <option value="Khối 6 (Global Success)">Khối 6 (Global Success)</option>
                <option value="Khối 7 (Global Success)">Khối 7 (Global Success)</option>
                <option value="Khối 8 (Global Success)">Khối 8 (Global Success)</option>
                <option value="Khối 9 (Ôn thi vào 10)">Khối 9 (Ôn thi vào 10)</option>
                <option value="Đội tuyển HSG THCS">Đội tuyển HSG THCS</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Ghi chú trọng tâm</label>
              <input type="text" name="description" class="form-control" placeholder="Từ vựng A Closer Look 1..." />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">
              Danh sách Từ vựng (Mỗi từ 1 dòng: <code>Từ tiếng Anh | Phiên âm IPA | (Từ loại) Nghĩa TV | Câu ví dụ</code>) *
            </label>
            <textarea name="bulk_words" class="form-control" rows="7" placeholder="volunteer | /ˌvɑːlənˈtɪr/ | (n/v) tình nguyện viên | Many secondary students volunteer to clean the park.&#10;donate | /ˈdoʊneɪt/ | (v) quyên góp | We donated books to poor children." required></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width: 100%;">💾 Lưu Bộ Từ vựng vào Kho THCS</button>
        </form>
      </div>

      <div>
        <div style="margin-bottom: 14px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
          <span style="font-size: 0.86rem; font-weight: 600; color: var(--text-muted);">Lọc theo khối:</span>
          @foreach(['all' => 'Tất cả Khối 6-9', 'Khối 6' => 'Khối 6', 'Khối 7' => 'Khối 7', 'Khối 8' => 'Khối 8', 'Khối 9' => 'Khối 9'] as $gVal => $gLabel)
            <a class="btn btn-sm {{ $gradeFilter === $gVal ? 'btn-primary' : 'btn-outline' }}"
              href="{{ route('teacher.dashboard', ['tab' => 'vocab', 'grade' => $gVal]) }}">
              {{ $gLabel }}
            </a>
          @endforeach
        </div>

        @foreach($vocabSets as $set)
          <div class="card">
            <div class="card-header">
              <div>
                <span class="badge badge-purple">{{ $set->level }}</span>
                <h3 style="font-size: 1.08rem; margin-top: 4px;">{{ $set->title }}</h3>
                @if($set->description)
                  <p style="font-size: 0.84rem; color: var(--text-muted);">{{ $set->description }}</p>
                @endif
              </div>
              <div style="display: flex; gap: 6px;">
                <a class="btn btn-outline btn-sm" href="{{ route('teacher.dashboard', ['tab' => 'assignments']) }}">🎯 Giao bài</a>
                <form action="{{ route('teacher.vocab.destroy', $set) }}" method="POST" onsubmit="return confirm('Xóa bộ từ vựng này?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                </form>
              </div>
            </div>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th>Từ vựng & IPA</th>
                    <th>Nghĩa TV</th>
                    <th>Câu ví dụ</th>
                    <th>Nghe thử</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($set->words as $w)
                    <tr>
                      <td>
                        <strong style="color: var(--primary);">{{ $w->word }}</strong>
                        @if($w->ipa)
                          <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $w->ipa }}</div>
                        @endif
                      </td>
                      <td>{{ $w->meaning }}</td>
                      <td style="font-size: 0.84rem; font-style: italic;">{{ $w->example }}</td>
                      <td>
                        <button type="button" class="btn btn-outline btn-sm" onclick="window.speechGrader.speakText('{{ addslashes($w->word) }}')">
                          🔊 Phát âm
                        </button>
                      </td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        @endforeach
      </div>
    </div>

  {{-- ================= TAB 4: KHO ĐOẠN VĂN KHỐI 6-9 ================= --}}
  @elseif($tab === 'passages')
    <div class="grid-2">
      <div class="card">
        <div class="card-header">
          <div class="card-title">➕ Thêm Đoạn văn Luyện đọc Mới (Khối 6, 7, 8, 9)</div>
        </div>
        <form action="{{ route('teacher.passages.store') }}" method="POST">
          @csrf
          <div class="form-group">
            <label class="form-label">Tiêu đề Đoạn văn *</label>
            <input type="text" name="title" class="form-control" placeholder="VD: [Khối 7] A Visit to the Temple of Literature" required />
          </div>
          <div class="grid-2" style="gap: 12px; grid-template-columns: 1fr 1fr;">
            <div class="form-group">
              <label class="form-label">Khối lớp (THCS) *</label>
              <select name="level" class="form-control">
                <option value="Khối 6 (Global Success)">Khối 6 (Global Success)</option>
                <option value="Khối 7 (Global Success)">Khối 7 (Global Success)</option>
                <option value="Khối 8 (Global Success)">Khối 8 (Global Success)</option>
                <option value="Khối 9 (Ôn thi vào 10)">Khối 9 (Ôn thi vào 10)</option>
                <option value="Đội tuyển HSG THCS">Đội tuyển HSG THCS</option>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Từ vựng / Âm đuôi cần chú ý</label>
              <input type="text" name="focus_words" class="form-control" placeholder="VD: historic, founded, students..." />
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Nội dung Đoạn văn Tiếng Anh *</label>
            <textarea name="content" class="form-control" rows="6" placeholder="Nhập đoạn văn tiếng Anh phù hợp trình độ THCS..." required></textarea>
          </div>
          <div class="form-group">
            <label class="form-label">Bản dịch Nghĩa Tiếng Việt</label>
            <textarea name="translation" class="form-control" rows="4" placeholder="Nhập bản dịch tiếng Việt..."></textarea>
          </div>
          <button type="submit" class="btn btn-primary" style="width: 100%;">💾 Lưu Đoạn văn vào Kho THCS</button>
        </form>
      </div>

      <div>
        <div style="margin-bottom: 14px; display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
          <span style="font-size: 0.86rem; font-weight: 600; color: var(--text-muted);">Lọc theo khối:</span>
          @foreach(['all' => 'Tất cả Khối 6-9', 'Khối 6' => 'Khối 6', 'Khối 7' => 'Khối 7', 'Khối 8' => 'Khối 8', 'Khối 9' => 'Khối 9'] as $gVal => $gLabel)
            <a class="btn btn-sm {{ $gradeFilter === $gVal ? 'btn-primary' : 'btn-outline' }}"
              href="{{ route('teacher.dashboard', ['tab' => 'passages', 'grade' => $gVal]) }}">
              {{ $gLabel }}
            </a>
          @endforeach
        </div>

        @foreach($passages as $p)
          <div class="card">
            <div class="card-header">
              <div>
                <span class="badge badge-yellow">{{ $p->level }}</span>
                <h3 style="font-size: 1.08rem; margin-top: 4px;">{{ $p->title }}</h3>
              </div>
              <div style="display: flex; gap: 6px; flex-wrap: wrap;">
                <button type="button" class="btn btn-outline btn-sm" onclick="window.speechGrader.speakText({{ json_encode($p->content) }})">
                  🔊 Nghe đọc mẫu
                </button>
                <a class="btn btn-outline btn-sm" href="{{ route('teacher.dashboard', ['tab' => 'assignments']) }}">🎯 Giao bài</a>
                <form action="{{ route('teacher.passages.destroy', $p) }}" method="POST" onsubmit="return confirm('Xóa đoạn văn này?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                </form>
              </div>
            </div>
            <div style="padding: 14px; background: #f8fafc; border-radius: 10px; font-size: 0.96rem; line-height: 1.7; border: 1px solid var(--border);">
              {{ $p->content }}
            </div>
            @if($p->translation)
              <div style="margin-top: 10px; font-size: 0.85rem; color: var(--text-muted);">
                <strong>Dịch nghĩa:</strong> {{ $p->translation }}
              </div>
            @endif
            @if($p->focus_words)
              <div style="margin-top: 8px; font-size: 0.82rem;">
                <strong>Từ trọng tâm:</strong> <span class="badge badge-blue">{{ $p->focus_words }}</span>
              </div>
            @endif
          </div>
        @endforeach
      </div>
    </div>

  {{-- ================= TAB 5: PHÂN QUYỀN GIAO BÀI ================= --}}
  @elseif($tab === 'assignments')
    <div class="grid-2">
      <div class="card">
        <div class="card-header">
          <div class="card-title">🎯 Phân quyền Bài Từ vựng / Đoạn văn cho Học sinh THCS</div>
        </div>
        <form action="{{ route('teacher.assignments.store') }}" method="POST">
          @csrf
          <div class="form-group">
            <label class="form-label">Tên Bài giao (Hiển thị trên màn hình Học sinh) *</label>
            <input type="text" name="title" class="form-control" placeholder="VD: [Lớp 7A2] Bài tập về nhà Unit 2 - Đọc đoạn văn" required />
          </div>

          <div class="grid-2" style="gap: 12px; grid-template-columns: 1fr 1fr;">
            <div class="form-group">
              <label class="form-label">Loại nội dung *</label>
              <select id="assign-content-type" name="content_type" class="form-control" onchange="toggleAssignContentBox()">
                <option value="vocab">📘 Bài Từ vựng</option>
                <option value="passage">📖 Bài Đoạn văn</option>
              </select>
            </div>
            <div class="form-group" id="box-select-vocab">
              <label class="form-label">Chọn Bộ Từ vựng *</label>
              <select name="vocab_set_id" class="form-control">
                @foreach($allVocabSets as $v)
                  <option value="{{ $v->id }}">{{ $v->title }}</option>
                @endforeach
              </select>
            </div>
            <div class="form-group hidden" id="box-select-passage">
              <label class="form-label">Chọn Bài Đoạn văn *</label>
              <select name="passage_id" class="form-control">
                @foreach($allPassages as $p)
                  <option value="{{ $p->id }}">{{ $p->title }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Hình thức Phân quyền cho Học sinh *</label>
            <select id="assign-target-type" name="target_type" class="form-control" onchange="toggleAssignTargetBox()">
              <option value="class">🏫 Giao theo Lớp học (Khối 6, 7, 8, 9)</option>
              <option value="students">👤 Giao riêng cho từng Học sinh (Bồi dưỡng / Phụ đạo)</option>
            </select>
          </div>

          <div id="assign-target-class-box" class="form-group">
            <label class="form-label">Chọn Lớp nhận bài *</label>
            <select name="school_class_id" class="form-control">
              @foreach($classes as $cls)
                <option value="{{ $cls->id }}">{{ $cls->name }}</option>
              @endforeach
            </select>
          </div>

          <div id="assign-target-students-box" class="form-group hidden">
            <label class="form-label">Tick chọn các Học sinh được phân quyền bài này *</label>
            <div class="checkbox-list">
              @foreach($students as $stu)
                <label class="checkbox-item">
                  <input type="checkbox" name="student_ids[]" value="{{ $stu->id }}" />
                  <span><strong>{{ $stu->name }}</strong> (<code>{{ $stu->username }}</code> - {{ $stu->schoolClass->name ?? '' }})</span>
                </label>
              @endforeach
            </div>
          </div>

          <div class="grid-2" style="gap: 12px; grid-template-columns: 1fr 1fr;">
            <div class="form-group">
              <label class="form-label">Hạn nộp bài</label>
              <input type="date" name="due_date" class="form-control" value="{{ now()->addDays(7)->format('Y-m-d') }}" />
            </div>
            <div class="form-group">
              <label class="form-label">Điểm chuẩn đạt yêu cầu (70% = 7.0đ)</label>
              <input type="number" name="pass_score" class="form-control" value="75" min="10" max="100" required />
            </div>
          </div>

          <div class="form-group">
            <label class="form-label">Lời dặn của Giáo viên</label>
            <input type="text" name="teacher_note" class="form-control" placeholder="VD: Các em nghe mẫu 2 lần, chú ý phát âm rõ âm cuối -s và -ed nhé!" />
          </div>

          <button type="submit" class="btn btn-primary" style="width: 100%;">🚀 Giao Bài Tập Ngay</button>
        </form>
      </div>

      <div class="card">
        <div class="card-header">
          <div class="card-title">📋 Danh sách Bài tập Đã Phân quyền ({{ $assignments->count() }})</div>
        </div>
        @foreach($assignments as $a)
          <div style="padding: 16px; border: 1px solid var(--border); border-radius: 12px; margin-bottom: 12px; background: #fff;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 10px;">
              <div>
                <span class="badge {{ $a->content_type === 'vocab' ? 'badge-purple' : 'badge-yellow' }}">
                  {{ $a->content_type === 'vocab' ? '📘 Từ vựng' : '📖 Đoạn văn' }}
                </span>
                @if($a->target_type === 'class')
                  <span class="badge badge-blue">🏫 {{ $a->schoolClass->name ?? 'Lớp học' }}</span>
                @else
                  <span class="badge badge-blue">👤 HS riêng: {{ $a->targetStudents->pluck('name')->join(', ') }}</span>
                @endif
                <h4 style="margin-top: 6px; font-size: 1rem;">{{ $a->title }}</h4>
                <div style="font-size: 0.84rem; color: var(--text-muted); margin-top: 2px;">
                  Nội dung gốc: <strong>{{ $a->content_type === 'vocab' ? ($a->vocabSet->title ?? '') : ($a->passage->title ?? '') }}</strong>
                  • Hạn nộp: {{ $a->due_date ? $a->due_date->format('d/m/Y') : 'Không giới hạn' }}
                  • Điểm đạt: {{ number_format($a->pass_score / 10, 1) }}đ
                </div>
                @if($a->teacher_note)
                  <div style="font-size: 0.82rem; color: #475569; margin-top: 4px;">💬 Lời dặn: "{{ $a->teacher_note }}"</div>
                @endif
              </div>
              <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 6px;">
                <span class="badge badge-green">📥 {{ $a->submissions_count }} bài nộp</span>
                <form action="{{ route('teacher.assignments.destroy', $a) }}" method="POST" onsubmit="return confirm('Xóa bài tập đã giao này?')">
                  @csrf @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm">Xóa</button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    </div>

  {{-- ================= TAB 6: BÀI NỘP & CHẤM ĐIỂM ================= --}}
  @elseif($tab === 'submissions')
    <div class="card">
      <div class="card-header">
        <div class="card-title">🏆 Sổ Điểm & Ghi âm Học sinh THCS Gửi về ({{ $submissions->count() }})</div>
        <form method="GET" action="{{ route('teacher.dashboard') }}" style="display: flex; gap: 10px; flex-wrap: wrap;">
          <input type="hidden" name="tab" value="submissions" />
          <select name="class_id" class="form-control" style="width: auto;" onchange="this.form.submit()">
            <option value="all">Tất cả các Lớp (Khối 6-9)</option>
            @foreach($classes as $cls)
              <option value="{{ $cls->id }}" {{ (string) $filterClassId === (string) $cls->id ? 'selected' : '' }}>{{ $cls->name }}</option>
            @endforeach
          </select>
          <select name="assignment_id" class="form-control" style="width: auto;" onchange="this.form.submit()">
            <option value="all">Tất cả Bài tập</option>
            @foreach($assignments as $a)
              <option value="{{ $a->id }}" {{ (string) $filterAssignmentId === (string) $a->id ? 'selected' : '' }}>{{ $a->title }}</option>
            @endforeach
          </select>
        </form>
      </div>

      @if($submissions->isEmpty())
        <div style="text-align: center; padding: 36px; color: var(--text-muted);">Chưa có bài nộp nào khớp với bộ lọc.</div>
      @else
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Học sinh & Lớp</th>
                <th>Nội dung Bài đọc</th>
                <th>Điểm Số (Hệ 10 & %)</th>
                <th>Bản ghi âm giọng đọc</th>
                <th>Nhận xét của GV</th>
                <th>Thời gian nộp</th>
                <th>Chi tiết</th>
              </tr>
            </thead>
            <tbody>
              @foreach($submissions as $sub)
                <tr>
                  <td>
                    <strong>{{ $sub->student->name ?? 'Học sinh' }}</strong>
                    <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $sub->schoolClass->name ?? '' }}</div>
                  </td>
                  <td>
                    <span class="badge {{ $sub->content_type === 'vocab' ? 'badge-purple' : 'badge-yellow' }}">
                      {{ $sub->content_type === 'vocab' ? '📘 Từ vựng' : '📖 Đoạn văn' }}
                    </span>
                    <div style="font-weight: 600; margin-top: 3px;">{{ $sub->content_title }}</div>
                  </td>
                  <td>{!! renderScoreBadgePhp($sub->score) !!}</td>
                  <td>
                    @if($sub->audio_url)
                      <audio controls src="{{ $sub->audio_url }}" style="height: 34px; max-width: 210px;"></audio>
                    @else
                      <span style="font-size: 0.8rem; color: var(--text-muted);">Bản chấm văn bản AI</span>
                    @endif
                  </td>
                  <td style="max-width: 220px; font-size: 0.85rem;">
                    {{ $sub->teacher_feedback ?: 'Chưa có nhận xét' }}
                  </td>
                  <td style="font-size: 0.82rem; color: var(--text-muted);">{{ $sub->created_at->format('H:i d/m/Y') }}</td>
                  <td>
                    <button type="button" class="btn btn-primary btn-sm" onclick="openSubmissionModal({{ $sub->id }})">
                      🔍 Xem & Chấm
                    </button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      @endif
    </div>
  @endif

@php
  $submissionsJson = $submissions->map(function ($s) {
      return [
          'id' => $s->id,
          'studentName' => $s->student->name ?? 'Học sinh',
          'contentTitle' => $s->content_title,
          'score' => (int) $s->score,
          'recognizedText' => $s->recognized_text ?? '',
          'wordDetails' => $s->word_details ?? [],
          'audioUrl' => $s->audio_url,
          'teacherFeedback' => $s->teacher_feedback ?? '',
          'submittedAt' => $s->created_at->format('H:i d/m/Y'),
          'updateUrl' => route('teacher.submissions.update', $s),
          'deleteUrl' => route('teacher.submissions.destroy', $s),
      ];
  })->keyBy('id');
@endphp

@push('scripts')
<script>
  const SUBMISSIONS_MAP = @json($submissionsJson);
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  function copyCredentials(fullName, username, password) {
    const text = `Thông tin đăng nhập Luyện đọc Tiếng Anh THCS của em ${fullName}:\n- Tên đăng nhập: ${username}\n- Mật khẩu: ${password}\n- Trang web: ${window.location.origin}`;
    navigator.clipboard.writeText(text).then(() => {
      showToast(`Đã copy thông tin đăng nhập của ${fullName}!`, 'success');
    });
  }

  function toggleAssignContentBox() {
    const type = document.getElementById('assign-content-type').value;
    document.getElementById('box-select-vocab').classList.toggle('hidden', type !== 'vocab');
    document.getElementById('box-select-passage').classList.toggle('hidden', type !== 'passage');
  }

  function toggleAssignTargetBox() {
    const target = document.getElementById('assign-target-type').value;
    document.getElementById('assign-target-class-box').classList.toggle('hidden', target !== 'class');
    document.getElementById('assign-target-students-box').classList.toggle('hidden', target !== 'students');
  }

  function openSubmissionModal(subId) {
    const sub = SUBMISSIONS_MAP[subId];
    if (!sub) return;
    const score10 = (sub.score / 10).toFixed(1);
    const wordsHtml = (sub.wordDetails || []).map(w => {
      const cls = w.status === 'correct' ? 'word-correct' : w.status === 'close' ? 'word-close' : 'word-wrong';
      const tip = w.actual ? `AI nghe được: "${w.actual}"` : 'Chưa đọc rõ từ này';
      const cleanSpeech = (w.expected || '').replace(/[^a-zA-Z0-9 ]/g, '');
      return `<span class="word-token ${cls}" title="${escapeHtml(tip)}" onclick="window.speechGrader.speakText('${escapeHtml(cleanSpeech)}')">${escapeHtml(w.expected)}</span>`;
    }).join(' ');

    const tips = window.speechGrader.analyzeSecondarySchoolTips(sub.wordDetails || []);

    const modal = document.getElementById('app-modal');
    const body = document.getElementById('app-modal-body');
    body.innerHTML = `
      <div class="modal-header">
        <div>
          <h3 style="font-size: 1.15rem;">📄 Chi tiết Bài đọc của ${escapeHtml(sub.studentName)}</h3>
          <div style="font-size: 0.84rem; color: var(--text-muted);">Bài: ${escapeHtml(sub.contentTitle)} • Nộp lúc: ${escapeHtml(sub.submittedAt)}</div>
        </div>
        <button type="button" class="btn btn-outline btn-sm" onclick="closeAppModal()">✖ Đóng</button>
      </div>

      <div style="display: flex; align-items: center; gap: 20px; padding: 16px; background: #f8fafc; border-radius: 14px; margin-bottom: 16px; flex-wrap: wrap;">
        <div class="score-circle" style="border-color: ${sub.score >= 75 ? 'var(--success)' : sub.score >= 50 ? 'var(--warning)' : 'var(--danger)'}">
          <span style="font-size: 1.45rem; line-height: 1.1;">${score10}đ</span>
          <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">(${sub.score}%)</span>
        </div>
        <div style="flex: 1;">
          <div style="font-weight: 700; font-size: 1.02rem; margin-bottom: 6px;">🎧 Bản ghi âm giọng đọc của học sinh:</div>
          ${sub.audioUrl
            ? `<audio controls src="${escapeHtml(sub.audioUrl)}" style="width: 100%; max-width: 380px;"></audio>`
            : `<div style="font-size: 0.86rem; color: var(--text-muted);">Bài nộp này sử dụng chế độ nhận diện văn bản trực tiếp.</div>`}
          <div style="margin-top: 8px; font-size: 0.84rem; color: #475569;">
            <strong>Văn bản AI nhận diện được:</strong> <em>"${escapeHtml(sub.recognizedText)}"</em>
          </div>
        </div>
      </div>

      ${tips.length > 0
        ? `<div style="margin-bottom: 16px; padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; font-size: 0.86rem; color: #92400e;">
            ${tips.map(t => `<div style="margin-bottom: 4px;">${t}</div>`).join('')}
           </div>`
        : ''}

      <div style="margin-bottom: 16px;">
        <div style="font-weight: 700; font-size: 0.92rem; margin-bottom: 6px;">🔍 Phân tích Phát âm Từng từ:</div>
        <div class="reading-passage-display" style="font-size: 1.12rem; margin: 0;">
          ${wordsHtml || escapeHtml(sub.recognizedText)}
        </div>
      </div>

      <form action="${escapeHtml(sub.updateUrl)}" method="POST" style="border-top: 1px solid var(--border); padding-top: 16px;">
        <input type="hidden" name="_token" value="${CSRF_TOKEN}" />
        <input type="hidden" name="_method" value="PUT" />
        <div class="grid-2" style="gap: 12px; grid-template-columns: 150px 1fr;">
          <div class="form-group">
            <label class="form-label">Điểm (% / Hệ 10)</label>
            <input type="number" name="score" class="form-control" value="${sub.score}" min="0" max="100" required />
          </div>
          <div class="form-group">
            <label class="form-label">Lời phê của Giáo viên gửi cho Học sinh</label>
            <input type="text" name="teacher_feedback" class="form-control" value="${escapeHtml(sub.teacherFeedback)}" placeholder="Nhập nhận xét khích lệ hoặc nhắc lỗi phát âm..." />
          </div>
        </div>
        <div style="display: flex; justify-content: space-between; margin-top: 8px;">
          <button type="button" class="btn btn-danger btn-sm" onclick="submitDeleteSubmission('${escapeHtml(sub.deleteUrl)}')">🗑 Xóa bài nộp</button>
          <button type="submit" class="btn btn-primary">💾 Lưu Điểm & Lời phê</button>
        </div>
      </form>
    `;
    modal.classList.remove('hidden');
  }

  function submitDeleteSubmission(url) {
    if (!confirm('Xóa bài nộp này?')) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = url;
    form.innerHTML = `<input type="hidden" name="_token" value="${CSRF_TOKEN}" /><input type="hidden" name="_method" value="DELETE" />`;
    document.body.appendChild(form);
    form.submit();
  }
</script>
@endpush
@endsection
