<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return Auth::user()->isTeacher()
                ? redirect()->route('teacher.dashboard')
                : redirect()->route('student.dashboard');
        }

        $sampleStudents = User::with('schoolClass')
            ->where('role', 'student')
            ->orderBy('id')
            ->take(6)
            ->get();

        $googleConfigured = !empty(config('services.google.client_id')) && !empty(config('services.google.client_secret'));

        return view('auth.login', compact('sampleStudents', 'googleConfigured'));
    }

    public function studentLogin(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'Vui lòng nhập tên đăng nhập do thầy/cô cấp.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $student = User::where('role', 'student')
            ->where('username', strtolower(trim($credentials['username'])))
            ->first();

        if (!$student || !Hash::check($credentials['password'], $student->password)) {
            return back()
                ->withInput($request->only('username'))
                ->with('error', 'Sai tên đăng nhập hoặc mật khẩu! Em hãy kiểm tra lại tài khoản thầy/cô đã cấp nhé.');
        }

        Auth::login($student, true);
        $request->session()->regenerate();

        return redirect()->route('student.dashboard')->with('success', 'Chào mừng em ' . $student->name . ' đã quay trở lại lớp học!');
    }

    public function redirectToGoogle()
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (!empty($clientId) && !empty($clientSecret)) {
            return Socialite::driver('google')->redirect();
        }

        // Chế độ chạy thử trực tiếp trên máy Local khi chưa điền Google OAuth Client ID trong .env
        $demoTeacher = User::firstOrCreate(
            ['email' => 'giaovien.thcs@gmail.com'],
            [
                'name' => 'Cô giáo Tiếng Anh THCS',
                'username' => 'teacher',
                'password' => Hash::make('123456'),
                'plain_password' => '123456',
                'role' => 'teacher',
            ]
        );

        Auth::login($demoTeacher, true);
        return redirect()->route('teacher.dashboard')
            ->with('success', 'Đăng nhập Giáo viên thành công (Chế độ Local Demo - điền GOOGLE_CLIENT_ID trong file .env để bật Google OAuth thật).');
    }

    public function handleGoogleCallback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $teacher = User::updateOrCreate(
                ['email' => $googleUser->getEmail()],
                [
                    'name' => $googleUser->getName() ?: 'Giáo viên Tiếng Anh',
                    'google_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'role' => 'teacher',
                ]
            );

            Auth::login($teacher, true);
            $request->session()->regenerate();

            return redirect()->route('teacher.dashboard')
                ->with('success', 'Đăng nhập Giáo viên bằng tài khoản Google thành công!');
        } catch (\Throwable $e) {
            return redirect()->route('login')
                ->with('error', 'Không thể đăng nhập bằng Google: ' . $e->getMessage());
        }
    }

    public function impersonateStudent(Request $request, User $student)
    {
        if (!$request->user() || !$request->user()->isTeacher() || !$student->isStudent()) {
            abort(403);
        }

        $request->session()->put('impersonator_teacher_id', $request->user()->id);
        Auth::login($student);

        return redirect()->route('student.dashboard')
            ->with('success', 'Đang xem giao diện dưới góc nhìn học sinh: ' . $student->name);
    }

    public function leaveImpersonation(Request $request)
    {
        $teacherId = $request->session()->pull('impersonator_teacher_id');
        if ($teacherId) {
            $teacher = User::where('id', $teacherId)->where('role', 'teacher')->first();
            if ($teacher) {
                Auth::login($teacher);
                return redirect()->route('teacher.dashboard', ['tab' => 'accounts'])
                    ->with('success', 'Đã quay lại trang quản trị Giáo viên.');
            }
        }
        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('impersonator_teacher_id');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Đã đăng xuất khỏi hệ thống.');
    }
}
