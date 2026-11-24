@extends('layouts.app')

@section('title', 'Phòng Luyện đọc AI - ' . $assignment->title)

@php
  $isVocab = $assignment->content_type === 'vocab';
  $contentObj = $isVocab ? $assignment->vocabSet : $assignment->passage;
  $targetText = $isVocab
      ? ($contentObj ? $contentObj->words->pluck('word')->join(' ') : '')
      : ($contentObj ? $contentObj->content : '');
@endphp

@section('content')
  <div style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
    <a class="btn btn-outline" href="{{ route('student.dashboard') }}">⬅ Quay lại danh sách bài tập</a>
    <div>
      <span class="badge badge-purple">{{ $contentObj->level ?? 'THCS' }}</span>
      <span class="badge badge-blue">Điểm chuẩn: {{ number_format($assignment->pass_score / 10, 1) }}/10đ ({{ $assignment->pass_score }}%)</span>
      <span class="badge badge-gray">Hạn nộp: {{ $assignment->due_date ? $assignment->due_date->format('d/m/Y') : 'Không giới hạn' }}</span>
    </div>
  </div>

  <div class="studio-box">
    <span class="badge {{ $isVocab ? 'badge-purple' : 'badge-yellow' }}">
      {{ $isVocab ? '📘 Luyện đọc Từ vựng THCS' : '📖 Luyện đọc Đoạn văn THCS' }}
    </span>
    <h2 style="font-size: 1.4rem; margin-top: 6px;">{{ $assignment->title }}</h2>
    <div style="color: var(--text-muted); font-size: 0.92rem;">Bài học: {{ $contentObj->title ?? '' }}</div>

    @if($assignment->teacher_note)
      <div style="margin-top: 12px; padding: 10px 14px; background: #eff6ff; border-radius: 10px; font-size: 0.88rem; color: #1e40af;">
        👩‍🏫 <strong>Lời dặn của thầy/cô:</strong> "{{ $assignment->teacher_note }}"
      </div>
    @endif

    @if($isVocab && $contentObj)
      {{-- PHÒNG LUYỆN ĐỌC TỪ VỰNG --}}
      <div style="margin-top: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
        <div style="font-size: 0.92rem; color: #334155;">
          💡 <strong>Cách làm bài:</strong> Bấm nghe phát âm mẫu và luyện đọc từng từ, sau đó bấm <strong>"🎙️ Đọc & Ghi âm Toàn bộ Từ vựng"</strong> để nộp về thầy/cô.
        </div>
        <button type="button" class="btn btn-outline btn-sm"
          onclick="window.speechGrader.speakText({{ json_encode($contentObj->words->pluck('word')->join(', ')) }}, 0.85)">
          🔊 Nghe đọc mẫu tất cả từ
        </button>
      </div>

      <div class="vocab-grid">
        @foreach($contentObj->words as $idx => $item)
          <div class="vocab-item-card" id="vocab-card-{{ $idx }}">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
              <div>
                <span class="vocab-word">{{ $item->word }}</span>
                @if($item->ipa)
                  <span class="vocab-ipa">{{ $item->ipa }}</span>
                @endif
              </div>
              <span id="vocab-badge-{{ $idx }}"></span>
            </div>
            <div class="vocab-meaning">🇻🇳 {{ $item->meaning }}</div>
            @if($item->example)
              <div class="vocab-example">"{{ $item->example }}"</div>
            @endif
            <div style="display: flex; gap: 8px; margin-top: 12px; flex-wrap: wrap;">
              <button type="button" class="btn btn-outline btn-sm" onclick="window.speechGrader.speakText({{ json_encode($item->word) }})">
                🔊 Nghe từ
              </button>
              @if($item->example)
                <button type="button" class="btn btn-outline btn-sm" onclick="window.speechGrader.speakText({{ json_encode($item->example) }}, 0.9)">
                  🔊 Nghe câu
                </button>
              @endif
              <button type="button" id="btn-vocab-single-{{ $idx }}" class="btn btn-primary btn-sm"
                onclick="toggleSingleWordMic({{ $idx }}, {{ json_encode($item->word) }})">
                🎙️ Đọc từ này
              </button>
            </div>
            <div id="vocab-heard-{{ $idx }}" style="margin-top: 8px; font-size: 0.78rem; color: var(--text-muted);"></div>
          </div>
        @endforeach
      </div>

      <div style="margin-top: 20px; background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 20px; text-align: center;">
        <div style="font-weight: 700; margin-bottom: 8px;">
          🎙️ Thu âm đọc liên tiếp toàn bộ {{ $contentObj->words->count() }} từ vựng:
          <span style="color: var(--primary);">{{ $contentObj->words->pluck('word')->join(' • ') }}</span>
        </div>
        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap; margin-top: 10px;">
          <button type="button" id="btn-main-mic" class="btn btn-primary" onclick="toggleFullRecording()">
            🎙️ Bấm để Đọc & Ghi âm Toàn bộ Từ vựng
          </button>
          <button type="button" class="btn btn-outline" onclick="openSimulatedInput()">
            ⌨️ Giả lập giọng đọc (Nếu máy không có Mic)
          </button>
        </div>
        <div id="live-transcript-box" style="margin-top: 12px; font-size: 0.92rem; color: var(--primary); font-style: italic;">
          Nhấn nút Ghi âm phía trên và đọc lần lượt các từ tiếng Anh...
        </div>
      </div>

    @elseif($contentObj)
      {{-- PHÒNG LUYỆN ĐỌC ĐOẠN VĂN --}}
      <div style="margin-top: 16px; display: flex; gap: 10px; flex-wrap: wrap; align-items: center;">
        <button type="button" class="btn btn-outline" onclick="window.speechGrader.speakText({{ json_encode($contentObj->content) }}, 0.95)">
          🔊 Nghe đọc mẫu (Tốc độ chuẩn)
        </button>
        <button type="button" class="btn btn-outline" onclick="window.speechGrader.speakText({{ json_encode($contentObj->content) }}, 0.75)">
          🐢 Nghe đọc chậm (0.75x)
        </button>
        <span style="font-size: 0.84rem; color: var(--text-muted);">💡 Bấm trực tiếp vào bất kỳ từ nào trong đoạn văn để nghe phát âm riêng từ đó!</span>
      </div>

      @if($contentObj->focus_words)
        <div style="margin-top: 12px; font-size: 0.85rem;">
          <strong>🎯 Từ trọng tâm & âm đuôi cần đọc rõ:</strong>
          @foreach(array_filter(array_map('trim', explode(',', $contentObj->focus_words))) as $fw)
            <button type="button" class="btn btn-outline btn-sm" style="margin: 2px 4px;" onclick="window.speechGrader.speakText({{ json_encode($fw) }})">
              🔊 {{ $fw }}
            </button>
          @endforeach
        </div>
      @endif

      <div class="reading-passage-display" id="passage-display-box">
        @foreach(preg_split('/\s+/', trim($contentObj->content)) as $word)
          <span class="word-token" title="Bấm để nghe phát âm"
            onclick="window.speechGrader.speakText({{ json_encode(preg_replace('/[^a-zA-Z0-9 ]/', '', $word)) }})">{{ $word }}</span>
        @endforeach
      </div>

      @if($contentObj->translation)
        <div style="padding: 12px 16px; background: #fff; border-radius: 10px; border: 1px solid var(--border); font-size: 0.9rem; color: #475569; margin-bottom: 18px;">
          <strong>📖 Dịch nghĩa tiếng Việt:</strong> {{ $contentObj->translation }}
        </div>
      @endif

      <div style="background: #fff; border: 1px solid var(--border); border-radius: 14px; padding: 20px; text-align: center;">
        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
          <button type="button" id="btn-main-mic" class="btn btn-primary" style="padding: 14px 28px; font-size: 1.02rem;" onclick="toggleFullRecording()">
            🎙️ Bấm để Bắt đầu Đọc & Ghi âm
          </button>
          <button type="button" class="btn btn-outline" onclick="openSimulatedInput()">
            ⌨️ Giả lập giọng đọc (Nếu máy không có Mic)
          </button>
        </div>
        <div id="live-transcript-box" style="margin-top: 14px; min-height: 28px; font-size: 0.95rem; color: var(--primary); font-style: italic;">
          Nhấn nút Ghi âm phía trên và đọc to, rõ ràng đoạn văn bằng tiếng Anh...
        </div>
      </div>
    @endif

    <!-- Khung Kết quả Chấm điểm & Gửi về Giáo viên -->
    <div id="result-panel-container" class="hidden"></div>
  </div>

@push('scripts')
<script>
  const IS_VOCAB = @json($isVocab);
  const TARGET_TEXT = @json($targetText);
  const PASS_SCORE = @json((int) $assignment->pass_score);
  const SUBMIT_URL = @json(route('student.practice.submit', $assignment));
  const VOCAB_WORDS = @json($isVocab && $contentObj ? $contentObj->words->pluck('word')->values() : []);
  const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  let currentGradedResult = null;
  let singleVocabScores = {};
  let latestVocabAudio = '';
  let activeSingleIdx = null;

  async function toggleFullRecording() {
    const btn = document.getElementById('btn-main-mic');
    const liveBox = document.getElementById('live-transcript-box');

    if (!window.speechGrader.isRecording) {
      const started = await window.speechGrader.startRecording({
        onInterimResult: text => {
          liveBox.innerHTML = `🎙️ Đang nghe em đọc: <strong>"${escapeHtml(text)}"</strong>`;
        },
        onError: msg => showToast(msg, 'error')
      });
      if (started) {
        btn.classList.add('mic-btn-active');
        btn.innerHTML = '⏹️ Đang ghi âm... Bấm vào đây khi em đọc xong!';
      }
    } else {
      btn.classList.remove('mic-btn-active');
      btn.innerHTML = '⏳ Đang chấm điểm...';
      btn.disabled = true;

      const { transcript, audioDataUrl } = await window.speechGrader.stopRecording();
      btn.disabled = false;
      btn.innerHTML = IS_VOCAB ? '🎙️ Đọc lại Toàn bộ Từ vựng' : '🎙️ Bấm để Đọc & Ghi âm lại';

      if (!transcript) {
        showToast('Chưa nghe rõ giọng đọc. Em hãy nói gần micro hơn hoặc dùng nút Giả lập giọng đọc nhé!', 'error');
        return;
      }

      const graded = window.speechGrader.gradePronunciation(TARGET_TEXT, transcript);
      graded.audioDataUrl = audioDataUrl;
      applyGradedResult(graded);
    }
  }

  async function toggleSingleWordMic(idx, wordText) {
    const btn = document.getElementById(`btn-vocab-single-${idx}`);

    if (!window.speechGrader.isRecording) {
      activeSingleIdx = idx;
      btn.classList.add('mic-btn-active');
      btn.innerHTML = '⏹️ Dừng & Chấm';
      const ok = await window.speechGrader.startRecording({
        onInterimResult: () => {},
        onError: msg => {
          btn.classList.remove('mic-btn-active');
          btn.innerHTML = '🎙️ Đọc từ này';
          activeSingleIdx = null;
          showToast(msg, 'error');
        }
      });
      if (!ok) {
        btn.classList.remove('mic-btn-active');
        btn.innerHTML = '🎙️ Đọc từ này';
        activeSingleIdx = null;
      }
    } else {
      const { transcript, audioDataUrl } = await window.speechGrader.stopRecording();
      const targetIdx = activeSingleIdx !== null ? activeSingleIdx : idx;
      const oldBtn = document.getElementById(`btn-vocab-single-${targetIdx}`);
      if (oldBtn) {
        oldBtn.classList.remove('mic-btn-active');
        oldBtn.innerHTML = '🎙️ Đọc từ này';
      }
      activeSingleIdx = null;

      if (audioDataUrl) latestVocabAudio = audioDataUrl;
      if (!transcript) {
        showToast('Chưa nghe rõ từ em đọc, thử lại nhé!', 'error');
        return;
      }

      const graded = window.speechGrader.gradePronunciation(wordText, transcript);
      singleVocabScores[idx] = {
        score: graded.score,
        recognizedText: transcript,
        status: graded.score >= 85 ? 'correct' : graded.score >= 60 ? 'close' : 'wrong'
      };

      const card = document.getElementById(`vocab-card-${idx}`);
      if (card) card.classList.toggle('scored-high', graded.score >= 80);
      const badgeEl = document.getElementById(`vocab-badge-${idx}`);
      if (badgeEl) {
        const s10 = (graded.score / 10).toFixed(1);
        badgeEl.innerHTML = `<span class="badge ${graded.score >= 80 ? 'badge-green' : 'badge-yellow'}">${s10}đ (${graded.score}%)</span>`;
      }
      const heardEl = document.getElementById(`vocab-heard-${idx}`);
      if (heardEl) heardEl.innerHTML = `AI nghe được: <strong>"${escapeHtml(transcript)}"</strong>`;

      // Tổng hợp kết quả toàn bộ bài từ vựng
      const wordDetails = VOCAB_WORDS.map((w, i) => ({
        expected: w,
        actual: singleVocabScores[i] ? singleVocabScores[i].recognizedText : '',
        status: singleVocabScores[i] ? singleVocabScores[i].status : 'wrong'
      }));
      const avgScore = Math.round(
        VOCAB_WORDS.reduce((sum, _, i) => sum + (singleVocabScores[i]?.score || 0), 0) / Math.max(1, VOCAB_WORDS.length)
      );
      const recognizedAll = VOCAB_WORDS.map((_, i) => singleVocabScores[i]?.recognizedText || '').filter(Boolean).join(' | ');

      applyGradedResult({
        score: avgScore,
        wordDetails,
        recognizedText: recognizedAll,
        audioDataUrl: latestVocabAudio,
        tips: window.speechGrader.analyzeSecondarySchoolTips(wordDetails)
      });
    }
  }

  function openSimulatedInput() {
    const spoken = prompt(
      'Chế độ Giả lập Giọng đọc AI (Dùng khi máy không có Mic):\nBạn có thể sửa/xóa bớt đuôi -s hoặc -ed ở vài từ để kiểm tra hệ thống chấm điểm tự động:',
      TARGET_TEXT
    );
    if (spoken === null) return;
    const graded = window.speechGrader.gradePronunciation(TARGET_TEXT, spoken);
    graded.audioDataUrl = '';
    applyGradedResult(graded);
  }

  function applyGradedResult(graded) {
    currentGradedResult = graded;
    const score10 = (graded.score / 10).toFixed(1);
    showToast(`Chấm điểm tự động hoàn tất: ${score10}/10 điểm (${graded.score}%)!`, 'success');

    // Tô màu từng từ trên đoạn văn
    const passageBox = document.getElementById('passage-display-box');
    if (passageBox && !IS_VOCAB && Array.isArray(graded.wordDetails)) {
      passageBox.innerHTML = graded.wordDetails.map(w => {
        const cls = w.status === 'correct' ? 'word-correct' : w.status === 'close' ? 'word-close' : 'word-wrong';
        const cleanSpeech = (w.expected || '').replace(/[^a-zA-Z0-9 ]/g, '');
        return `<span class="word-token ${cls}" title="Bấm để nghe phát âm chuẩn" onclick="window.speechGrader.speakText('${escapeHtml(cleanSpeech)}')">${escapeHtml(w.expected)}</span>`;
      }).join(' ');
    }

    const liveBox = document.getElementById('live-transcript-box');
    if (liveBox) {
      liveBox.innerHTML = `Văn bản AI nhận diện: <strong>"${escapeHtml(graded.recognizedText)}"</strong>`;
    }

    const correctCount = (graded.wordDetails || []).filter(w => w.status === 'correct').length;
    const closeCount = (graded.wordDetails || []).filter(w => w.status === 'close').length;
    const wrongCount = (graded.wordDetails || []).filter(w => w.status === 'wrong').length;
    const tips = graded.tips || window.speechGrader.analyzeSecondarySchoolTips(graded.wordDetails || []);

    const panel = document.getElementById('result-panel-container');
    panel.classList.remove('hidden');
    panel.innerHTML = `
      <div style="margin-top: 22px; background: #f0fdf4; border: 2px solid #86efac; border-radius: 16px; padding: 22px;">
        <div style="display: flex; align-items: center; justify-content: space-between; gap: 20px; flex-wrap: wrap;">
          <div style="display: flex; align-items: center; gap: 18px;">
            <div class="score-circle" style="border-color: ${graded.score >= PASS_SCORE ? 'var(--success)' : 'var(--warning)'}">
              <span style="font-size: 1.45rem; line-height: 1.1;">${score10}đ</span>
              <span style="font-size: 0.78rem; color: var(--text-muted); font-weight: 600;">(${graded.score}%)</span>
            </div>
            <div>
              <h3 style="font-size: 1.2rem; color: #14532d;">🎉 Kết quả Chấm điểm Phát âm: ${score10} / 10 điểm (${graded.score}%)</h3>
              <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 6px;">
                <span class="badge badge-green">🟢 Đúng chuẩn: ${correctCount} từ</span>
                <span class="badge badge-yellow">🟡 Gần đúng (thiếu âm cuối): ${closeCount} từ</span>
                <span class="badge badge-red">🔴 Cần sửa: ${wrongCount} từ</span>
              </div>
              ${graded.audioDataUrl
                ? `<div style="margin-top: 10px;">
                    <div style="font-size: 0.82rem; font-weight: 600; color: #166534; margin-bottom: 4px;">🎧 Nghe lại bản ghi âm giọng đọc của em:</div>
                    <audio controls src="${graded.audioDataUrl}" style="height: 36px;"></audio>
                   </div>`
                : ''}
            </div>
          </div>

          <div>
            <button type="button" id="btn-submit-server" class="btn btn-success" style="padding: 14px 24px; font-size: 1rem;" onclick="submitToLaravelServer()">
              📤 Nộp Điểm & File Ghi âm về cho Thầy/Cô
            </button>
          </div>
        </div>

        ${tips.length > 0
          ? `<div style="margin-top: 14px; padding: 12px 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; font-size: 0.88rem; color: #92400e;">
              <div style="font-weight: 700; margin-bottom: 4px;">💡 Gợi ý sửa lỗi phát âm THCS từ AI:</div>
              ${tips.map(t => `<div style="margin-top: 4px;">${t}</div>`).join('')}
             </div>`
          : ''}
      </div>
    `;
  }

  async function submitToLaravelServer() {
    if (!currentGradedResult) return;
    const btn = document.getElementById('btn-submit-server');
    if (btn) {
      btn.disabled = true;
      btn.innerHTML = '⏳ Đang gửi điểm & bản ghi âm về máy chủ...';
    }

    try {
      const response = await fetch(SUBMIT_URL, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'X-CSRF-TOKEN': CSRF_TOKEN
        },
        body: JSON.stringify({
          score: currentGradedResult.score,
          recognized_text: currentGradedResult.recognizedText || '',
          word_details: currentGradedResult.wordDetails || [],
          audio_data_url: currentGradedResult.audioDataUrl || ''
        })
      });

      const data = await response.json();
      if (!response.ok) {
        throw new Error(data.message || 'Lỗi gửi bài về máy chủ.');
      }

      showToast(data.message || 'Đã gửi bài thành công!', 'success');
      setTimeout(() => {
        window.location.href = data.redirect_url;
      }, 700);
    } catch (err) {
      showToast(err.message, 'error');
      if (btn) {
        btn.disabled = false;
        btn.innerHTML = '📤 Nộp Điểm & File Ghi âm về cho Thầy/Cô';
      }
    }
  }
</script>
@endpush
@endsection
