<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Passage;
use App\Models\SchoolClass;
use App\Models\Submission;
use App\Models\User;
use App\Models\VocabSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class TeacherController extends Controller
{
    public function dashboard(Request $request)
    {
        $tab = $request->query('tab', 'dashboard');
        $gradeFilter = $request->query('grade', 'all');
        $filterClassId = $request->query('class_id', 'all');
        $filterAssignmentId = $request->query('assignment_id', 'all');

        $classes = SchoolClass::withCount('students')->orderBy('grade')->orderBy('name')->get();
        $students = User::with('schoolClass')->where('role', 'student')->latest()->get();

        $vocabQuery = VocabSet::with('words')->latest();
        if ($gradeFilter !== 'all' && $tab === 'vocab') {
            $vocabQuery->where('level', 'like', '%' . $gradeFilter . '%');
        }
        $vocabSets = $vocabQuery->get();
        $allVocabSets = VocabSet::latest()->get();

        $passageQuery = Passage::latest();
        if ($gradeFilter !== 'all' && $tab === 'passages') {
            $passageQuery->where('level', 'like', '%' . $gradeFilter . '%');
        }
        $passages = $passageQuery->get();
        $allPassages = Passage::latest()->get();

        $assignments = Assignment::with(['vocabSet', 'passage', 'schoolClass', 'targetStudents'])
            ->withCount('submissions')
            ->latest()
            ->get();

        $submissionsQuery = Submission::with(['student', 'schoolClass', 'assignment'])->latest();
        if ($filterClassId !== 'all') {
            $submissionsQuery->where('school_class_id', $filterClassId);
        }
        if ($filterAssignmentId !== 'all') {
            $submissionsQuery->where('assignment_id', $filterAssignmentId);
        }
        $submissions = $submissionsQuery->get();

        $allSubmissionsCount = Submission::count();
        $avgScore = $allSubmissionsCount > 0 ? round((float) Submission::avg('score')) : 0;

        return view('teacher.dashboard', compact(
            'tab',
            'gradeFilter',
            'filterClassId',
            'filterAssignmentId',
            'classes',
            'students',
            'vocabSets',
            'allVocabSets',
            'passages',
            'allPassages',
            'assignments',
            'submissions',
            'allSubmissionsCount',
            'avgScore'
        ));
    }

    // --- 1. QUẢN LÝ LỚP HỌC THCS ---
    public function storeClass(Request $request)
    {
        $data = $request->validate([
            'grade' => ['required', 'in:6,7,8,9'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:500'],
        ]);

        SchoolClass::create([
            'grade' => $data['grade'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'teacher_id' => $request->user()->id,
        ]);

        return redirect()->route('teacher.dashboard', ['tab' => 'accounts'])
            ->with('success', 'Đã tạo lớp "' . $data['name'] . '" (Khối ' . $data['grade'] . ').');
    }

    public function destroyClass(SchoolClass $schoolClass)
    {
        $schoolClass->delete();
        return redirect()->route('teacher.dashboard', ['tab' => 'accounts'])
            ->with('success', 'Đã xóa lớp học.');
    }

    // --- 2. QUẢN LÝ TÀI KHOẢN HỌC SINH ---
    public function storeStudent(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'school_class_id' => ['required', 'exists:school_classes,id'],
            'username' => ['required', 'string', 'max:80', 'alpha_dash', 'unique:users,username'],
            'password' => ['required', 'string', 'min:3', 'max:60'],
            'note' => ['nullable', 'string', 'max:500'],
        ], [
            'username.unique' => 'Tên đăng nhập này đã tồn tại, vui lòng chọn mã học sinh khác.',
        ]);

        User::create([
            'name' => $data['name'],
            'username' => strtolower(trim($data['username'])),
            'password' => Hash::make($data['password']),
            'plain_password' => $data['password'],
            'role' => 'student',
            'school_class_id' => $data['school_class_id'],
            'note' => $data['note'] ?? null,
        ]);

        return redirect()->route('teacher.dashboard', ['tab' => 'accounts'])
            ->with('success', 'Đã cấp tài khoản cho học sinh ' . $data['name'] . '!');
    }

    public function destroyStudent(User $student)
    {
        if ($student->isStudent()) {
            $student->delete();
        }
        return redirect()->route('teacher.dashboard', ['tab' => 'accounts'])
            ->with('success', 'Đã xóa tài khoản học sinh.');
    }

    // --- 3. QUẢN LÝ KHO TỪ VỰNG ---
    public function storeVocabSet(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'bulk_words' => ['required', 'string'],
        ]);

        $lines = preg_split('/\r\n|\r|\n/', trim($data['bulk_words']));
        $parsedWords = [];
        foreach ($lines as $idx => $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $parts = array_map('trim', explode('|', $line));
            if (count($parts) === 2) {
                $parsedWords[] = [
                    'word' => $parts[0],
                    'ipa' => null,
                    'meaning' => $parts[1],
                    'example' => null,
                    'sort_order' => $idx,
                ];
            } elseif (!empty($parts[0])) {
                $parsedWords[] = [
                    'word' => $parts[0],
                    'ipa' => $parts[1] ?? null,
                    'meaning' => $parts[2] ?? null,
                    'example' => $parts[3] ?? null,
                    'sort_order' => $idx,
                ];
            }
        }

        if (empty($parsedWords)) {
            return back()->with('error', 'Vui lòng nhập ít nhất 1 dòng từ vựng hợp lệ.');
        }

        $vocabSet = VocabSet::create([
            'title' => $data['title'],
            'level' => $data['level'],
            'description' => $data['description'] ?? null,
            'teacher_id' => $request->user()->id,
        ]);

        foreach ($parsedWords as $w) {
            $vocabSet->words()->create($w);
        }

        return redirect()->route('teacher.dashboard', ['tab' => 'vocab'])
            ->with('success', 'Đã lưu bộ từ vựng "' . $vocabSet->title . '" (' . count($parsedWords) . ' từ)!');
    }

    public function destroyVocabSet(VocabSet $vocabSet)
    {
        $vocabSet->delete();
        return redirect()->route('teacher.dashboard', ['tab' => 'vocab'])
            ->with('success', 'Đã xóa bộ từ vựng.');
    }

    // --- 4. QUẢN LÝ KHO ĐOẠN VĂN ---
    public function storePassage(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'level' => ['required', 'string', 'max:100'],
            'focus_words' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'translation' => ['nullable', 'string'],
        ]);

        Passage::create(array_merge($data, [
            'teacher_id' => $request->user()->id,
        ]));

        return redirect()->route('teacher.dashboard', ['tab' => 'passages'])
            ->with('success', 'Đã thêm đoạn văn "' . $data['title'] . '" vào kho THCS!');
    }

    public function destroyPassage(Passage $passage)
    {
        $passage->delete();
        return redirect()->route('teacher.dashboard', ['tab' => 'passages'])
            ->with('success', 'Đã xóa bài đoạn văn.');
    }

    // --- 5. PHÂN QUYỀN & GIAO BÀI TẬP ---
    public function storeAssignment(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'content_type' => ['required', 'in:vocab,passage'],
            'vocab_set_id' => ['nullable', 'exists:vocab_sets,id'],
            'passage_id' => ['nullable', 'exists:passages,id'],
            'target_type' => ['required', 'in:class,students'],
            'school_class_id' => ['nullable', 'exists:school_classes,id'],
            'student_ids' => ['nullable', 'array'],
            'student_ids.*' => ['exists:users,id'],
            'due_date' => ['nullable', 'date'],
            'pass_score' => ['required', 'integer', 'min:10', 'max:100'],
            'teacher_note' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['content_type'] === 'vocab' && empty($data['vocab_set_id'])) {
            return back()->with('error', 'Vui lòng chọn một bộ Từ vựng để giao.');
        }
        if ($data['content_type'] === 'passage' && empty($data['passage_id'])) {
            return back()->with('error', 'Vui lòng chọn một bài Đoạn văn để giao.');
        }
        if ($data['target_type'] === 'students' && empty($data['student_ids'])) {
            return back()->with('error', 'Vui lòng tick chọn ít nhất 1 học sinh.');
        }

        $assignment = Assignment::create([
            'title' => $data['title'],
            'content_type' => $data['content_type'],
            'vocab_set_id' => $data['content_type'] === 'vocab' ? $data['vocab_set_id'] : null,
            'passage_id' => $data['content_type'] === 'passage' ? $data['passage_id'] : null,
            'target_type' => $data['target_type'],
            'school_class_id' => $data['target_type'] === 'class' ? $data['school_class_id'] : null,
            'due_date' => $data['due_date'] ?? null,
            'pass_score' => $data['pass_score'],
            'teacher_note' => $data['teacher_note'] ?? null,
            'teacher_id' => $request->user()->id,
        ]);

        if ($data['target_type'] === 'students' && !empty($data['student_ids'])) {
            $assignment->targetStudents()->sync($data['student_ids']);
        }

        return redirect()->route('teacher.dashboard', ['tab' => 'assignments'])
            ->with('success', 'Đã phân quyền bài tập "' . $assignment->title . '" cho học sinh!');
    }

    public function destroyAssignment(Assignment $assignment)
    {
        $assignment->delete();
        return redirect()->route('teacher.dashboard', ['tab' => 'assignments'])
            ->with('success', 'Đã xóa bài tập đã giao.');
    }

    // --- 6. XEM & CHẤM BÀI NỘP CỦA HỌC SINH ---
    public function updateSubmission(Request $request, Submission $submission)
    {
        $data = $request->validate([
            'score' => ['required', 'integer', 'min:0', 'max:100'],
            'teacher_feedback' => ['nullable', 'string', 'max:1000'],
        ]);

        $submission->update($data);

        return redirect()->route('teacher.dashboard', ['tab' => 'submissions'])
            ->with('success', 'Đã lưu điểm và lời phê gửi cho học sinh ' . ($submission->student->name ?? '') . '!');
    }

    public function destroySubmission(Submission $submission)
    {
        if ($submission->audio_path) {
            Storage::disk('public')->delete($submission->audio_path);
        }
        $submission->delete();

        return redirect()->route('teacher.dashboard', ['tab' => 'submissions'])
            ->with('success', 'Đã xóa bài nộp.');
    }
}
