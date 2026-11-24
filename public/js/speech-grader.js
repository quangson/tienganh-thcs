// Hệ thống Nhận diện Giọng nói AI, Ghi âm & Chấm điểm Phát âm Tự động (Chuyên biệt cho Học sinh THCS - Cấp 2)
const NUMBER_WORD_MAP = {
  '0': 'zero', '1': 'one', '2': 'two', '3': 'three', '4': 'four',
  '5': 'five', '6': 'six', '7': 'seven', '8': 'eight', '9': 'nine',
  '10': 'ten', '11': 'eleven', '12': 'twelve', '13': 'thirteen',
  '14': 'fourteen', '15': 'fifteen', '16': 'sixteen', '17': 'seventeen',
  '18': 'eighteen', '19': 'nineteen', '20': 'twenty', '30': 'thirty',
  '40': 'forty', '50': 'fifty', '60': 'sixty', '70': 'seventy',
  '80': 'eighty', '90': 'ninety', '100': 'hundred'
};

// Từ đồng nghĩa chính tả Anh-Anh (SGK Global Success) & Anh-Mỹ (Web Speech API)
const UK_US_SPELLING_MAP = {
  'favourite': 'favorite',
  'colour': 'color',
  'coloured': 'colored',
  'centre': 'center',
  'neighbour': 'neighbor',
  'neighbourhood': 'neighborhood',
  'travelling': 'traveling',
  'practise': 'practice',
  'organisation': 'organization',
  'programme': 'program'
};

class SpeechGraderService {
  constructor() {
    this.recognition = null;
    this.mediaRecorder = null;
    this.audioChunks = [];
    this.isRecording = false;
    this.currentTranscript = '';
    this.finalTranscript = '';
    this.stream = null;
  }

  // Phát âm mẫu (Text-to-Speech)
  speakText(text, rate = 0.95, lang = 'en-US') {
    if (!('speechSynthesis' in window)) {
      alert('Trình duyệt của bạn không hỗ trợ đọc văn bản (Text-to-Speech). Vui lòng dùng Google Chrome hoặc Microsoft Edge.');
      return;
    }
    window.speechSynthesis.cancel();
    const utterance = new SpeechSynthesisUtterance(text);
    utterance.lang = lang;
    utterance.rate = rate;

    const voices = window.speechSynthesis.getVoices();
    const preferredVoice =
      voices.find(v => v.lang === lang && (v.name.includes('Google') || v.name.includes('Natural') || v.name.includes('Microsoft'))) ||
      voices.find(v => v.lang.startsWith('en'));
    if (preferredVoice) {
      utterance.voice = preferredVoice;
    }
    window.speechSynthesis.speak(utterance);
  }

  // Chuẩn hóa từ để so sánh (Hỗ trợ cả số đếm & chính tả Anh-Anh trong SGK Global Success)
  normalizeWord(word) {
    let clean = (word || '')
      .toLowerCase()
      .replace(/[^a-z0-9']/g, '')
      .trim();
    if (NUMBER_WORD_MAP[clean]) {
      clean = NUMBER_WORD_MAP[clean];
    }
    if (UK_US_SPELLING_MAP[clean]) {
      clean = UK_US_SPELLING_MAP[clean];
    }
    return clean;
  }

  // Khoảng cách Levenshtein để tính độ giống nhau giữa 2 từ
  levenshteinSimilarity(a, b) {
    if (!a && !b) return 1;
    if (!a || !b) return 0;
    if (a === b) return 1;

    const matrix = Array.from({ length: a.length + 1 }, () =>
      Array(b.length + 1).fill(0)
    );
    for (let i = 0; i <= a.length; i++) matrix[i][0] = i;
    for (let j = 0; j <= b.length; j++) matrix[0][j] = j;

    for (let i = 1; i <= a.length; i++) {
      for (let j = 1; j <= b.length; j++) {
        const cost = a[i - 1] === b[j - 1] ? 0 : 1;
        matrix[i][j] = Math.min(
          matrix[i - 1][j] + 1,
          matrix[i][j - 1] + 1,
          matrix[i - 1][j - 1] + cost
        );
      }
    }
    const distance = matrix[a.length][b.length];
    const maxLen = Math.max(a.length, b.length);
    return (maxLen - distance) / maxLen;
  }

  // Phân tích lỗi phát âm đặc trưng của học sinh Cấp 2 (âm cuối -s/-es, đuôi -ed, từ đa âm tiết)
  analyzeSecondarySchoolTips(wordDetails) {
    const missedEndingS = [];
    const missedEndingEd = [];
    const hardWordsToPractice = [];

    (wordDetails || []).forEach(item => {
      const exp = (item.expected || '').replace(/[^a-zA-Z]/g, '').toLowerCase();
      const act = (item.actual || '').replace(/[^a-zA-Z]/g, '').toLowerCase();
      if (!exp) return;

      if (item.status !== 'correct') {
        if (exp.endsWith('s') && exp.length > 3 && (act === exp.slice(0, -1) || act === exp.slice(0, -2))) {
          missedEndingS.push(item.expected.replace(/[^a-zA-Z]/g, ''));
        } else if (exp.endsWith('ed') && exp.length > 4 && (act === exp.slice(0, -2) || act === exp.slice(0, -1))) {
          missedEndingEd.push(item.expected.replace(/[^a-zA-Z]/g, ''));
        } else if (item.status === 'wrong' && exp.length >= 6) {
          hardWordsToPractice.push(item.expected.replace(/[^a-zA-Z]/g, ''));
        }
      }
    });

    const tips = [];
    if (missedEndingS.length > 0) {
      tips.push(`📌 <strong>Lưu ý âm đuôi -s/-es (Ngữ pháp THCS):</strong> Em cần bật rõ âm gió /s/, /z/ hoặc /ɪz/ ở các từ: <code>${missedEndingS.join(', ')}</code>.`);
    }
    if (missedEndingEd.length > 0) {
      tips.push(`📌 <strong>Lưu ý âm đuôi quá khứ -ed:</strong> Em chú ý phát âm đuôi /t/, /d/ hoặc /ɪd/ ở các động từ: <code>${missedEndingEd.join(', ')}</code>.`);
    }
    if (hardWordsToPractice.length > 0) {
      tips.push(`🎯 <strong>Từ vựng nhiều âm tiết cần nghe mẫu lại:</strong> Bấm trực tiếp vào các từ <code>${hardWordsToPractice.slice(0, 6).join(', ')}</code> để nghe và đọc theo trọng âm chuẩn.`);
    }
    return tips;
  }

  // Chấm điểm chi tiết từng từ (Word-by-Word Alignment)
  gradePronunciation(targetText, spokenText) {
    const rawTargetWords = (targetText || '')
      .trim()
      .split(/\s+/)
      .filter(Boolean);

    const spokenWords = (spokenText || '')
      .trim()
      .split(/\s+/)
      .map(w => this.normalizeWord(w))
      .filter(Boolean);

    if (rawTargetWords.length === 0) {
      return { score: 0, score10: '0.0', wordDetails: [], recognizedText: spokenText, tips: [] };
    }

    const usedSpokenIndices = new Set();
    const wordDetails = [];
    let totalPoints = 0;
    let searchCursor = 0;

    rawTargetWords.forEach(rawWord => {
      const cleanTarget = this.normalizeWord(rawWord);
      if (!cleanTarget) {
        wordDetails.push({ expected: rawWord, actual: '', status: 'correct', similarity: 1 });
        totalPoints += 1;
        return;
      }

      const windowStart = Math.max(0, searchCursor - 2);
      const windowEnd = Math.min(spokenWords.length - 1, searchCursor + 6);

      let bestMatchIdx = -1;
      let bestSim = 0;

      for (let j = windowStart; j <= windowEnd; j++) {
        if (usedSpokenIndices.has(j)) continue;
        const sim = this.levenshteinSimilarity(cleanTarget, spokenWords[j]);
        if (sim > bestSim) {
          bestSim = sim;
          bestMatchIdx = j;
        }
        if (sim === 1) break;
      }

      if (bestMatchIdx !== -1 && bestSim >= 0.88) {
        usedSpokenIndices.add(bestMatchIdx);
        searchCursor = bestMatchIdx + 1;
        wordDetails.push({
          expected: rawWord,
          actual: spokenWords[bestMatchIdx],
          status: 'correct',
          similarity: bestSim
        });
        totalPoints += 1;
      } else if (bestMatchIdx !== -1 && bestSim >= 0.62) {
        usedSpokenIndices.add(bestMatchIdx);
        searchCursor = bestMatchIdx + 1;
        wordDetails.push({
          expected: rawWord,
          actual: spokenWords[bestMatchIdx],
          status: 'close',
          similarity: bestSim
        });
        totalPoints += 0.65;
      } else {
        wordDetails.push({
          expected: rawWord,
          actual: bestMatchIdx !== -1 ? spokenWords[bestMatchIdx] : '',
          status: 'wrong',
          similarity: bestSim
        });
      }
    });

    const score = Math.min(100, Math.max(0, Math.round((totalPoints / rawTargetWords.length) * 100)));
    const score10 = (score / 10).toFixed(1);
    const tips = this.analyzeSecondarySchoolTips(wordDetails);

    return {
      score,
      score10,
      wordDetails,
      recognizedText: spokenText.trim(),
      tips
    };
  }

  // Bắt đầu Ghi âm + Nhận diện giọng nói
  async startRecording({ onInterimResult, onError }) {
    if (this.isRecording) return;

    const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
    if (!SpeechRecognition) {
      if (onError) {
        onError('Trình duyệt hiện tại chưa hỗ trợ Web Speech API. Vui lòng mở bằng Google Chrome hoặc Microsoft Edge để chấm điểm giọng đọc tự động.');
      }
      return false;
    }

    this.currentTranscript = '';
    this.finalTranscript = '';
    this.audioChunks = [];

    try {
      this.stream = await navigator.mediaDevices.getUserMedia({ audio: true });
      const mimeType = MediaRecorder.isTypeSupported('audio/webm;codecs=opus')
        ? 'audio/webm;codecs=opus'
        : '';
      this.mediaRecorder = mimeType
        ? new MediaRecorder(this.stream, { mimeType, audioBitsPerSecond: 32000 })
        : new MediaRecorder(this.stream);

      this.mediaRecorder.ondataavailable = e => {
        if (e.data && e.data.size > 0) {
          this.audioChunks.push(e.data);
        }
      };
      this.mediaRecorder.start();
    } catch (err) {
      console.warn('Không thể mở MediaRecorder hoặc người dùng từ chối mic:', err);
      if (onError) {
        onError('Không thể truy cập Microphone. Vui lòng cấp quyền Microphone cho trình duyệt.');
      }
      return false;
    }

    this.recognition = new SpeechRecognition();
    this.recognition.lang = 'en-US';
    this.recognition.continuous = true;
    this.recognition.interimResults = true;

    this.recognition.onresult = event => {
      let interim = '';
      for (let i = event.resultIndex; i < event.results.length; i++) {
        const transcriptPiece = event.results[i][0].transcript;
        if (event.results[i].isFinal) {
          this.finalTranscript += transcriptPiece + ' ';
        } else {
          interim += transcriptPiece;
        }
      }
      this.currentTranscript = (this.finalTranscript + interim).trim();
      if (onInterimResult) {
        onInterimResult(this.currentTranscript);
      }
    };

    this.recognition.onerror = event => {
      console.warn('SpeechRecognition notice:', event.error);
    };

    this.recognition.onend = () => {
      if (this.isRecording && this.recognition) {
        try {
          this.recognition.start();
        } catch (_) {}
      }
    };

    try {
      this.recognition.start();
      this.isRecording = true;
      return true;
    } catch (e) {
      if (onError) onError('Không thể bắt đầu nhận diện giọng nói: ' + e.message);
      return false;
    }
  }

  // Dừng Ghi âm và trả về transcript + audioDataUrl
  stopRecording() {
    return new Promise(resolve => {
      if (!this.isRecording) {
        resolve({ transcript: this.currentTranscript, audioDataUrl: '' });
        return;
      }

      this.isRecording = false;
      if (this.recognition) {
        try {
          this.recognition.onend = null;
          this.recognition.stop();
        } catch (_) {}
      }

      const finishWithAudio = audioDataUrl => {
        if (this.stream) {
          this.stream.getTracks().forEach(track => track.stop());
          this.stream = null;
        }
        resolve({
          transcript: (this.currentTranscript || this.finalTranscript || '').trim(),
          audioDataUrl: audioDataUrl || ''
        });
      };

      if (this.mediaRecorder && this.mediaRecorder.state !== 'inactive') {
        this.mediaRecorder.onstop = () => {
          if (this.audioChunks.length === 0) {
            finishWithAudio('');
            return;
          }
          const blob = new Blob(this.audioChunks, { type: this.mediaRecorder.mimeType || 'audio/webm' });
          const reader = new FileReader();
          reader.onloadend = () => {
            finishWithAudio(reader.result || '');
          };
          reader.onerror = () => finishWithAudio('');
          reader.readAsDataURL(blob);
        };
        try {
          this.mediaRecorder.stop();
        } catch (_) {
          finishWithAudio('');
        }
      } else {
        finishWithAudio('');
      }
    });
  }
}

window.speechGrader = new SpeechGraderService();
