<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StudentController extends Controller
{
    public function dashboard(Request $request)
    {
        $student = $request->user();
        $tab = $request->query('tab', 'assigned');

        $assignments = $this->getAssignedTasksFor($student);
        $mySubmissions = Submission::with('assignment')
            ->where('student_id', $student->id)
            ->latest()
            ->get();

        // Điểm cao nhất cho mỗi bài tập
        $bestByAssignment = $mySubmissions
            ->groupBy('assignment_id')
            ->map(fn ($group) => $group->sortByDesc('score')->first());

        // Bảng vàng thi đua trong lớp
        $leaderboard = collect();
        if ($student->school_class_id) {
            $classmates = User::where('role', 'student')
                ->where('school_class_id', $student->school_class_id)
                ->with('submissions')
                ->get();

            $leaderboard = $classmates->map(function (User $mate) {
                $count = $mate->submissions->count();
                $avg = $count > 0 ? round($mate->submissions->avg('score')) : 0;
                return (object) [
                    'id' => $mate->id,
                    'name' => $mate->name,
                    'sub_count' => $count,
                    'avg_score' => $avg,
                ];
            })->sortByDesc('avg_score')->values();
        }

        return view('student.dashboard', compact(
            'student',
            'tab',
            'assignments',
            'mySubmissions',
            'bestByAssignment',
            'leaderboard'
        ));
    }

    public function practice(Request $request, Assignment $assignment)
    {
        $student = $request->user();
        if (!$this->studentCanAccessAssignment($student, $assignment)) {
            abort(403, 'Em chưa được phân quyền làm bài tập này.');
        }

        $assignment->load(['vocabSet.words', 'passage']);
        return view('student.practice', compact('student', 'assignment'));
    }

    public function submitPractice(Request $request, Assignment $assignment)
    {
        $student = $request->user();
        if (!$this->studentCanAccessAssignment($student, $assignment)) {
            return response()->json(['message' => 'Không có quyền nộp bài tập này.'], 403);
        }

        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'recognized_text' => ['nullable', 'string'],
            'word_details' => ['nullable', 'array'],
            'audio_data_url' => ['nullable', 'string'],
        ]);

        // Lưu file ghi âm thật (.webm / .mp4) vào storage/app/public/recordings/
        $audioPath = null;
        if (!empty($data['audio_data_url']) && str_starts_with($data['audio_data_url'], 'data:audio/')) {
            if (preg_match('/^data:audio\/([a-zA-Z0-9.+-]+)(?:;[^,]*)?,(.*)$/s', $data['audio_data_url'], $matches)) {
                $ext = str_contains($matches[1], 'mp4') ? 'm4a' : (str_contains($matches[1], 'ogg') ? 'ogg' : 'webm');
                $decoded = base64_decode($matches[2]);
                if ($decoded !== false && strlen($decoded) <= 15 * 1024 * 1024) {
                    $fileName = 'recordings/' . date('Y/m') . '/stu_' . $student->id . '_' . time() . '_' . Str::random(6) . '.' . $ext;
                    Storage::disk('public')->put($fileName, $decoded);
                    $audioPath = $fileName;
                }
            }
        }

        $contentTitle = $assignment->content_type === 'vocab'
            ? ($assignment->vocabSet->title ?? $assignment->title)
            : ($assignment->passage->title ?? $assignment->title);

        $submission = Submission::create([
            'assignment_id' => $assignment->id,
            'student_id' => $student->id,
            'school_class_id' => $student->school_class_id,
            'content_type' => $assignment->content_type,
            'content_title' => $contentTitle,
            'score' => $data['score'],
            'recognized_text' => $data['recognized_text'] ?? '',
            'word_details' => $data['word_details'] ?? [],
            'audio_path' => $audioPath,
        ]);

        return response()->json([
            'ok' => true,
            'submission_id' => $submission->id,
            'redirect_url' => route('student.dashboard', ['tab' => 'history']),
            'message' => 'Đã nộp điểm (' . number_format($submission->score / 10, 1) . '/10đ) và bản ghi âm về cho Thầy/Cô!',
        ]);
    }

    private function getAssignedTasksFor(User $student)
    {
        return Assignment::with(['vocabSet', 'passage'])
            ->where(function ($query) use ($student) {
                $query->where(function ($q) use ($student) {
                    $q->where('target_type', 'class')
                        ->where('school_class_id', $student->school_class_id);
                })->orWhere(function ($q) use ($student) {
                    $q->where('target_type', 'students')
                        ->whereHas('targetStudents', fn ($sq) => $sq->where('users.id', $student->id));
                });
            })
            ->latest()
            ->get();
    }

    private function studentCanAccessAssignment(User $student, Assignment $assignment): bool
    {
        if ($assignment->target_type === 'class') {
            return (int) $assignment->school_class_id === (int) $student->school_class_id;
        }
        return $assignment->targetStudents()->where('users.id', $student->id)->exists();
    }
}
