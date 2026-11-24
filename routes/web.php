<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use Illuminate\Support\Facades\Route;

// Trang chủ & Đăng nhập (Học sinh & Giáo viên Google OAuth)
Route::get('/', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login/student', [AuthController::class, 'studentLogin'])->name('login.student');
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/impersonate/leave', [AuthController::class, 'leaveImpersonation'])->name('impersonate.leave');

// CỔNG GIÁO VIÊN THCS
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () {
    Route::get('/', [TeacherController::class, 'dashboard'])->name('dashboard');

    // Quản lý Lớp học (Khối 6-9)
    Route::post('/classes', [TeacherController::class, 'storeClass'])->name('classes.store');
    Route::delete('/classes/{schoolClass}', [TeacherController::class, 'destroyClass'])->name('classes.destroy');

    // Quản lý Tài khoản Học sinh
    Route::post('/students', [TeacherController::class, 'storeStudent'])->name('students.store');
    Route::delete('/students/{student}', [TeacherController::class, 'destroyStudent'])->name('students.destroy');
    Route::post('/students/{student}/impersonate', [AuthController::class, 'impersonateStudent'])->name('students.impersonate');

    // Kho Từ vựng THCS
    Route::post('/vocab-sets', [TeacherController::class, 'storeVocabSet'])->name('vocab.store');
    Route::delete('/vocab-sets/{vocabSet}', [TeacherController::class, 'destroyVocabSet'])->name('vocab.destroy');

    // Kho Đoạn văn THCS
    Route::post('/passages', [TeacherController::class, 'storePassage'])->name('passages.store');
    Route::delete('/passages/{passage}', [TeacherController::class, 'destroyPassage'])->name('passages.destroy');

    // Phân quyền & Giao bài tập
    Route::post('/assignments', [TeacherController::class, 'storeAssignment'])->name('assignments.store');
    Route::delete('/assignments/{assignment}', [TeacherController::class, 'destroyAssignment'])->name('assignments.destroy');

    // Chấm điểm & Nhận xét bài nộp của Học sinh
    Route::put('/submissions/{submission}', [TeacherController::class, 'updateSubmission'])->name('submissions.update');
    Route::delete('/submissions/{submission}', [TeacherController::class, 'destroySubmission'])->name('submissions.destroy');
});

// CỔNG HỌC SINH CẤP 2 (KHỐI 6-9)
Route::middleware(['auth', 'role:student'])->prefix('student')->name('student.')->group(function () {
    Route::get('/', [StudentController::class, 'dashboard'])->name('dashboard');
    Route::get('/practice/{assignment}', [StudentController::class, 'practice'])->name('practice');
    Route::post('/practice/{assignment}/submit', [StudentController::class, 'submitPractice'])->name('practice.submit');
});
