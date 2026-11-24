@extends('layouts.app')

@section('title', 'Học sinh THCS - English Smart Class')

@php
  function renderStudentScoreBadge($score) {
      $s = (int) $score;
      $s10 = number_format($s / 10, 1);
      if ($s >= 85) return '<span class="badge badge-green">🌟 ' . $s10 . 'đ (' . $s . '% - Giỏi)</span>';
      if ($s >= 70) return '<span class="badge badge-blue">👍 ' . $s10 . 'đ (' . $s . '% - Khá)</span>';
      if ($s >= 50) return '<span class="badge badge-yellow">⚡ ' . $s10 . 'đ (' . $s . '% - Đạt)</span>';
      return '<span class="badge badge-red">❗ ' . $s10 . 'đ (' . $s . '% - Cần cố gắng)</span>';
  }
@endphp

@section('content')
  <div class="nav-tabs">
    <a class="nav-tab {{ $tab === 'assigned' ? 'active' : '' }}" href="{{ route('student.dashboard', ['tab' => 'assigned']) }}">
      📚 Bài tập Thầy/Cô giao ({{ $assignments->count() }})
    </a>
    <a class="nav-tab {{ $tab === 'history' ? 'active' : '' }}" href="{{ route('student.dashboard', ['tab' => 'history']) }}">
      🏆 Sổ Điểm & Nhận xét của em ({{ $mySubmissions->count() }})
    </a>
    <a class="nav-tab {{ $tab === 'leaderboard' ? 'active' : '' }}" href="{{ route('student.dashboard', ['tab' => 'leaderboard']) }}">
      🥇 Bảng Vàng Thi đua Lớp
    </a>
  </div>

  {{-- TAB 1: BÀI TẬP ĐƯỢC PHÂN QUYỀN --}}
  @if($tab === 'assigned')
    @if($assignments->isEmpty())
      <div class="card" style="text-align: center; padding: 40px;">
        <div style="font-size: 2.5rem; margin-bottom: 10px;">🎉</div>
        <h3>Hiện tại lớp của em chưa có bài tập mới nào được giao!</h3>
        <p style="color: var(--text-muted); margin-top: 6px;">Hãy chờ thầy/cô phân quyền bài Từ vựng hoặc Đoạn văn tiếp theo nhé.</p>
      </div>
    @else
      <div class="grid-2">
        @foreach($assignments as $assign)
          @php
            $isVocab = $assign->content_type === 'vocab';
            $contentTitle = $isVocab ? ($assign->vocabSet->title ?? '') : ($assign->passage->title ?? '');
            $bestSub = $bestByAssignment->get($assign->id);
          @endphp
          <div class="card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
              <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; margin-bottom: 10px;">
                <span class="badge {{ $isVocab ? 'badge-purple' : 'badge-yellow' }}">
                  {{ $isVocab ? '📘 Bài Từ vựng SGK' : '📖 Bài Đọc Đoạn văn' }}
                </span>
                @if($bestSub)
                  {!! renderStudentScoreBadge($bestSub->score) !!}
                @else
                  <span class="badge badge-gray">⏳ Chưa làm bài</span>
                @endif
              </div>

              <h3 style="font-size: 1.15rem; margin-bottom: 6px;">{{ $assign->title }}</h3>
              <div style="font-size: 0.88rem; color: var(--text-muted); margin-bottom: 10px;">
                Bài gốc: <strong>{{ $contentTitle }}</strong>
                • Hạn nộp: <strong>{{ $assign->due_date ? $assign->due_date->format('d/m/Y') : 'Không giới hạn' }}</strong>
                • Điểm đạt: <strong>{{ number_format($assign->pass_score / 10, 1) }}/10đ</strong>
              </div>

              @if($assign->teacher_note)
                <div style="padding: 10px 12px; background: #eff6ff; border-radius: 10px; font-size: 0.86rem; color: #1e40af; margin-bottom: 14px;">
                  👩‍🏫 <strong>Lưu ý từ thầy/cô:</strong> "{{ $assign->teacher_note }}"
                </div>
              @endif

              @if($bestSub && $bestSub->teacher_feedback)
                <div style="padding: 10px 12px; background: #f0fdf4; border-radius: 10px; font-size: 0.86rem; color: #15803d; margin-bottom: 14px;">
                  💬 <strong>Lời phê của thầy/cô:</strong> "{{ $bestSub->teacher_feedback }}"
                </div>
              @endif
            </div>

            <div style="display: flex; gap: 10px; margin-top: 8px;">
              <a class="btn btn-primary" style="flex: 1;" href="{{ route('student.practice', $assign) }}">
                🎙️ {{ $bestSub ? 'Luyện đọc lại để nâng điểm' : 'Bắt đầu Đọc & Ghi âm' }}
              </a>
              @if($bestSub)
                <button type="button" class="btn btn-outline" onclick="openStudentSubmissionModal({{ $bestSub->id }})">Xem chi tiết</button>
              @endif
            </div>
          </div>
        @endforeach
      </div>
    @endif

  {{-- TAB 2: LỊCH SỬ ĐIỂM & BÀI NỘP --}}
  @elseif($tab === 'history')
    @if($mySubmissions->isEmpty())
      <div class="card" style="text-align: center; padding: 36px; color: var(--text-muted);">
        Em chưa gửi bài thu âm nào. Hãy chọn một bài tập để bắt đầu đọc nhé!
      </div>
    @else
      <div class="card">
        <div class="card-header">
          <div class="card-title">🏆 Lịch sử Điểm số & Bản ghi âm em đã gửi Giáo viên</div>
        </div>
        <div class="table-responsive">
          <table class="table">
            <thead>
              <tr>
                <th>Bài đọc</th>
                <th>Điểm đạt được (Hệ 10)</th>
                <th>Ghi âm của em</th>
                <th>Lời phê của Giáo viên</th>
                <th>Ngày nộp</th>
                <th>Chi tiết</th>
              </tr>
            </thead>
            <tbody>
              @foreach($mySubmissions as $sub)
                <tr>
                  <td><strong>{{ $sub->content_title }}</strong></td>
                  <td>{!! renderStudentScoreBadge($sub->score) !!}</td>
                  <td>
                    @if($sub->audio_url)
                      <audio controls src="{{ $sub->audio_url }}" style="height: 32px; max-width: 200px;"></audio>
                    @else
                      <span style="font-size: 0.8rem; color: var(--text-muted);">Chấm văn bản AI</span>
                    @endif
                  </td>
                  <td>{{ $sub->teacher_feedback ?: 'Đã gửi giáo viên' }}</td>
                  <td style="font-size: 0.82rem; color: var(--text-muted);">{{ $sub->created_at->format('H:i d/m/Y') }}</td>
                  <td>
                    <button type="button" class="btn btn-outline btn-sm" onclick="openStudentSubmissionModal({{ $sub->id }})">Xem lại</button>
                  </td>
                </tr>
              @endforeach
            </tbody>
          </table>
        </div>
      </div>
    @endif

  {{-- TAB 3: BẢNG VÀNG THI ĐUA LỚP --}}
  @elseif($tab === 'leaderboard')
    <div class="card">
      <div class="card-header">
        <div class="card-title">🥇 Bảng Vàng Thi đua Phát âm - {{ $student->schoolClass->name ?? 'Lớp học' }}</div>
      </div>
      <div class="table-responsive">
        <table class="table">
          <thead>
            <tr>
              <th>Thứ hạng</th>
              <th>Học sinh</th>
              <th>Số bài đã hoàn thành</th>
              <th>Điểm Trung bình</th>
            </tr>
          </thead>
          <tbody>
            @foreach($leaderboard as $idx => $row)
              @php
                $rankIcon = $idx === 0 ? '🥇 Hạng 1' : ($idx === 1 ? '🥈 Hạng 2' : ($idx === 2 ? '🥉 Hạng 3' : '#' . ($idx + 1)));
                $isMe = $row->id === $student->id;
              @endphp
              <tr style="{{ $isMe ? 'background: #eff6ff; font-weight: 600;' : '' }}">
                <td><strong>{{ $rankIcon }}</strong></td>
                <td>{{ $row->name }} @if($isMe) <span class="badge badge-blue">Bạn</span> @endif</td>
                <td>{{ $row->sub_count }} bài</td>
                <td>
                  @if($row->sub_count > 0)
                    {!! renderStudentScoreBadge($row->avg_score) !!}
                  @else
                    <span class="badge badge-gray">Chưa nộp bài</span>
                  @endif
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  @endif

@php
  $mySubsMap = $mySubmissions->map(function ($s) {
      return [
          'id' => $s->id,
          'contentTitle' => $s->content_title,
          'score' => (int) $s->score,
          'recognizedText' => $s->recognized_text ?? '',
          'wordDetails' => $s->word_details ?? [],
          'audioUrl' => $s->audio_url,
          'teacherFeedback' => $s->teacher_feedback ?? '',
          'submittedAt' => $s->created_at->format('H:i d/m/Y'),
      ];
  })->keyBy('id');
@endphp

@push('scripts')
<script>
  const STUDENT_SUBS_MAP = @json($mySubsMap);

  function openStudentSubmissionModal(subId) {
    const sub = STUDENT_SUBS_MAP[subId];
    if (!sub) return;
    const score10 = (sub.score / 10).toFixed(1);
    const wordsHtml = (sub.wordDetails || []).map(w => {
      const cls = w.status === 'correct' ? 'word-correct' : w.status === 'close' ? 'word-close' : 'word-wrong';
      const cleanSpeech = (w.expected || '').replace(/[^a-zA-Z0-9 ]/g, '');
      return `<span class="word-token ${cls}" title="Bấm để nghe phát âm chuẩn" onclick="window.speechGrader.speakText('${escapeHtml(cleanSpeech)}')">${escapeHtml(w.expected)}</span>`;
    }).join(' ');
    const tips = window.speechGrader.analyzeSecondarySchoolTips(sub.wordDetails || []);

    const modal = document.getElementById('app-modal');
    const body = document.getElementById('app-modal-body');
    body.innerHTML = `
      <div class="modal-header">
        <div>
          <h3 style="font-size: 1.15rem;">📄 Kết quả Bài đọc: ${escapeHtml(sub.contentTitle)}</h3>
          <div style="font-size: 0.84rem; color: var(--text-muted);">Nộp lúc: ${escapeHtml(sub.submittedAt)}</div>
        </div>
        <button type="button" class="btn btn-outline btn-sm" onclick="closeAppModal()">✖ Đóng</button>
      </div>

      <div style="display: flex; align-items: center; gap: 20px; padding: 16px; background: #f8fafc; border-radius: 14px; margin-bottom: 16px; flex-wrap: wrap;">
        <div class="score-circle" style="border-color: ${sub.score >= 75 ? 'var(--success)' : sub.score >= 50 ? 'var(--warning)' : 'var(--danger)'}">
          <span style="font-size: 1.45rem; line-height: 1.1;">${score10}đ</span>
          <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">(${sub.score}%)</span>
        </div>
        <div style="flex: 1;">
          ${sub.audioUrl
            ? `<div style="font-weight:700; margin-bottom:6px;">🎧 Bản ghi âm của em:</div><audio controls src="${escapeHtml(sub.audioUrl)}" style="width: 100%; max-width: 360px;"></audio>`
            : `<div style="font-size: 0.86rem; color: var(--text-muted);">Bài nộp không đính kèm file ghi âm.</div>`}
          <div style="margin-top: 8px; font-size: 0.84rem; color: #475569;">
            <strong>Văn bản AI nhận diện:</strong> <em>"${escapeHtml(sub.recognizedText)}"</em>
          </div>
        </div>
      </div>

      ${tips.length > 0
        ? `<div style="margin-bottom: 14px; padding: 12px 14px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 10px; font-size: 0.86rem; color: #92400e;">
            ${tips.map(t => `<div style="margin-bottom: 4px;">${t}</div>`).join('')}
           </div>`
        : ''}

      <div class="reading-passage-display" style="font-size: 1.1rem; margin: 0 0 14px;">
        ${wordsHtml || escapeHtml(sub.recognizedText)}
      </div>

      <div style="padding: 14px; background: #eff6ff; border-radius: 12px; font-size: 0.92rem;">
        <strong>👩‍🏫 Nhận xét của Giáo viên:</strong>
        <p style="margin-top: 4px;">${escapeHtml(sub.teacherFeedback || 'Giáo viên đã nhận được bài đọc của em. Hãy tiếp tục phát huy nhé!')}</p>
      </div>
    `;
    modal.classList.remove('hidden');
  }
</script>
@endpush
@endsection
