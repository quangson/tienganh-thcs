<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Passage;
use App\Models\SchoolClass;
use App\Models\Submission;
use App\Models\User;
use App\Models\VocabSet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Giáo viên mặc định (Hỗ trợ đăng nhập Google OAuth thật hoặc Demo)
        $teacher = User::updateOrCreate(
            ['email' => 'giaovien.thcs@gmail.com'],
            [
                'name' => 'Cô giáo Tiếng Anh THCS',
                'username' => 'teacher',
                'password' => Hash::make('123456'),
                'plain_password' => '123456',
                'role' => 'teacher',
            ]
        );

        // 2. Các Lớp học THCS (Khối 6, 7, 8, 9)
        $class6 = SchoolClass::updateOrCreate(
            ['name' => 'Lớp 6A1 (Khối 6 - Global Success)'],
            ['grade' => '6', 'description' => 'Lớp tiếng Anh khối 6 - Trọng tâm phát âm cơ bản và âm đuôi -s/-es', 'teacher_id' => $teacher->id]
        );
        $class7 = SchoolClass::updateOrCreate(
            ['name' => 'Lớp 7A2 (Khối 7 - Global Success)'],
            ['grade' => '7', 'description' => 'Lớp tiếng Anh khối 7 - Trọng tâm phát âm đuôi -ed và đọc hiểu đoạn văn', 'teacher_id' => $teacher->id]
        );
        $class8 = SchoolClass::updateOrCreate(
            ['name' => 'Lớp 8B1 (Khối 8 - Global Success)'],
            ['grade' => '8', 'description' => 'Lớp tiếng Anh khối 8 - Trọng âm từ đa âm tiết và ngữ điệu câu', 'teacher_id' => $teacher->id]
        );
        $class9 = SchoolClass::updateOrCreate(
            ['name' => 'Lớp 9A1 (Khối 9 - Ôn thi vào 10)'],
            ['grade' => '9', 'description' => 'Lớp tiếng Anh cuối cấp THCS - Từ vựng học thuật & Đọc lưu loát', 'teacher_id' => $teacher->id]
        );

        // 3. Tài khoản Học sinh mẫu (Khối 6 - 9)
        $stu1 = User::updateOrCreate(
            ['username' => 'minhan6a1'],
            [
                'name' => 'Nguyễn Minh An',
                'password' => Hash::make('123'),
                'plain_password' => '123',
                'role' => 'student',
                'school_class_id' => $class6->id,
                'note' => 'Học sinh lớp 6A1, chăm chỉ, cần chú ý bật âm cuối /s/ và /z/',
            ]
        );
        $stu2 = User::updateOrCreate(
            ['username' => 'baongoc6a1'],
            [
                'name' => 'Trần Bảo Ngọc',
                'password' => Hash::make('123'),
                'plain_password' => '123',
                'role' => 'student',
                'school_class_id' => $class6->id,
                'note' => 'Phát âm chuẩn, ngữ điệu tự nhiên, đại diện đội tuyển Anh khối 6',
            ]
        );
        $stu3 = User::updateOrCreate(
            ['username' => 'hoangnam7a2'],
            [
                'name' => 'Lê Hoàng Nam',
                'password' => Hash::make('123'),
                'plain_password' => '123',
                'role' => 'student',
                'school_class_id' => $class7->id,
                'note' => 'Học sinh lớp 7A2, cần luyện đọc chậm và chú ý đuôi -ed',
            ]
        );
        $stu4 = User::updateOrCreate(
            ['username' => 'khanhlinh8b1'],
            [
                'name' => 'Phạm Khánh Linh',
                'password' => Hash::make('123'),
                'plain_password' => '123',
                'role' => 'student',
                'school_class_id' => $class8->id,
                'note' => 'Học sinh lớp 8B1, đọc tốt các đoạn văn dài',
            ]
        );
        $stu5 = User::updateOrCreate(
            ['username' => 'ducanh9a1'],
            [
                'name' => 'Vũ Đức Anh',
                'password' => Hash::make('123'),
                'plain_password' => '123',
                'role' => 'student',
                'school_class_id' => $class9->id,
                'note' => 'Học sinh lớp 9A1, đang ôn thi tuyển sinh vào lớp 10',
            ]
        );

        // 4. Kho Từ vựng SGK THCS (Khối 6, 7, 8, 9)
        $vocabData = [
            [
                'title' => 'Tiếng Anh 6 - Unit 1: My New School (Trường học mới của em)',
                'level' => 'Khối 6 (Global Success)',
                'description' => 'Từ vựng trọng tâm Unit 1 SGK Tiếng Anh lớp 6.',
                'words' => [
                    ['word' => 'boarding school', 'ipa' => '/ˈbɔːdɪŋ skuːl/', 'meaning' => '(n) trường nội trú', 'example' => 'Sunrise is a boarding school in Sydney.'],
                    ['word' => 'calculator', 'ipa' => '/ˈkælkjuleɪtər/', 'meaning' => '(n) máy tính cầm tay', 'example' => 'I have a new calculator for my math lessons.'],
                    ['word' => 'compass', 'ipa' => '/ˈkʌmpəs/', 'meaning' => '(n) chiếc com-pa', 'example' => 'Can you lend me your compass for a minute?'],
                    ['word' => 'uniform', 'ipa' => '/ˈjuːnɪfɔːrm/', 'meaning' => '(n) đồng phục trường', 'example' => 'Students at my secondary school wear uniforms every Monday.'],
                    ['word' => 'international', 'ipa' => '/ˌɪntərˈnæʃnəl/', 'meaning' => '(adj) quốc tế', 'example' => 'She studies at an international school in Hanoi.'],
                ],
            ],
            [
                'title' => 'Tiếng Anh 7 - Unit 2: Healthy Living (Lối sống lành mạnh)',
                'level' => 'Khối 7 (Global Success)',
                'description' => 'Từ vựng SGK Tiếng Anh lớp 7 về sức khỏe và vận động.',
                'words' => [
                    ['word' => 'healthy', 'ipa' => '/ˈhelθi/', 'meaning' => '(adj) khỏe mạnh, lành mạnh', 'example' => 'Eating coloured vegetables helps us stay healthy.'],
                    ['word' => 'sunburn', 'ipa' => '/ˈsʌnbɜːrn/', 'meaning' => '(n) sự cháy nắng', 'example' => 'Wear a hat and use suncream to avoid sunburn.'],
                    ['word' => 'acne', 'ipa' => '/ˈækni/', 'meaning' => '(n) mụn trứng cá', 'example' => 'Washing your face twice a day helps reduce acne.'],
                    ['word' => 'exercise', 'ipa' => '/ˈeksərsaɪz/', 'meaning' => '(n/v) tập thể dục', 'example' => 'Secondary students should do morning exercise regularly.'],
                    ['word' => 'vitamins', 'ipa' => '/ˈvaɪtəmɪnz/', 'meaning' => '(n) các loại vi-ta-min', 'example' => 'Fresh fruit and vegetables provide a lot of vitamins.'],
                ],
            ],
            [
                'title' => 'Tiếng Anh 8 - Unit 1: Leisure Time (Thời gian rảnh rỗi)',
                'level' => 'Khối 8 (Global Success)',
                'description' => 'Từ vựng SGK Tiếng Anh lớp 8 về sở thích và giải trí.',
                'words' => [
                    ['word' => 'leisure', 'ipa' => '/ˈliːʒər/', 'meaning' => '(n) thời gian rảnh rỗi', 'example' => 'What do you usually do in your leisure time?'],
                    ['word' => 'origami', 'ipa' => '/ˌɔːrɪˈɡɑːmi/', 'meaning' => '(n) nghệ thuật gấp giấy Nhật Bản', 'example' => 'Doing origami helps me relax after school.'],
                    ['word' => 'hang out', 'ipa' => '/hæŋ aʊt/', 'meaning' => '(phr.v) đi chơi với bạn bè', 'example' => 'I often hang out with my classmates on Saturday afternoon.'],
                    ['word' => 'detest', 'ipa' => '/dɪˈtest/', 'meaning' => '(v) rất ghét', 'example' => 'She detests playing computer games all day.'],
                ],
            ],
            [
                'title' => 'Tiếng Anh 9 - Unit 1: Local Community (Cộng đồng địa phương)',
                'level' => 'Khối 9 (Ôn thi vào 10)',
                'description' => 'Từ vựng trọng tâm lớp 9 & Luyện thi vào lớp 10.',
                'words' => [
                    ['word' => 'artisan', 'ipa' => '/ˈɑːrtɪzən/', 'meaning' => '(n) nghệ nhân', 'example' => 'Skilled artisans in Bat Trang make beautiful pottery.'],
                    ['word' => 'handicraft', 'ipa' => '/ˈhændikræft/', 'meaning' => '(n) sản phẩm thủ công mỹ nghệ', 'example' => 'Foreign tourists love buying Vietnamese handicrafts.'],
                    ['word' => 'preserve', 'ipa' => '/prɪˈzɜːrv/', 'meaning' => '(v) bảo tồn, giữ gìn', 'example' => 'Local people work hard to preserve their traditional culture.'],
                    ['word' => 'speciality', 'ipa' => '/ˌspeʃiˈæləti/', 'meaning' => '(n) đặc sản địa phương', 'example' => 'Green rice flakes are a famous speciality of Hanoi.'],
                ],
            ],
        ];

        $vocabModels = [];
        foreach ($vocabData as $item) {
            $set = VocabSet::firstOrCreate(
                ['title' => $item['title']],
                ['level' => $item['level'], 'description' => $item['description'], 'teacher_id' => $teacher->id]
            );
            if ($set->words()->count() === 0) {
                foreach ($item['words'] as $idx => $w) {
                    $set->words()->create(array_merge($w, ['sort_order' => $idx]));
                }
            }
            $vocabModels[] = $set;
        }

        // 5. Kho Đoạn văn THCS (Khối 6, 7, 8, 9)
        $p6 = Passage::firstOrCreate(
            ['title' => '[Khối 6] My First Week at Secondary School'],
            [
                'level' => 'Khối 6 (Global Success)',
                'content' => 'This year, I am a grade six student at Nguyen Du Secondary School. Everything is new and exciting to me. My school has a large playground, a modern library, and twenty classrooms. Every morning, I put on my school uniform and ride my bicycle to school with my best friend. Our teachers are very kind and helpful. My favourite subject is English because we play fun speaking games in class.',
                'translation' => 'Năm nay, em là học sinh lớp 6 tại trường THCS Nguyễn Du. Mọi thứ đều mới mẻ và thú vị đối với em. Trường của em có một sân chơi rộng, một thư viện hiện đại và hai mươi phòng học. Mỗi buổi sáng, em mặc đồng phục trường và đạp xe đến trường cùng người bạn thân nhất. Các thầy cô giáo của chúng em rất tốt bụng và hay giúp đỡ. Môn học yêu thích của em là Tiếng Anh vì chúng em được chơi các trò chơi luyện nói rất vui trên lớp.',
                'focus_words' => 'secondary, exciting, playground, library, classrooms, teachers, favourite',
                'teacher_id' => $teacher->id,
            ]
        );

        $p7 = Passage::firstOrCreate(
            ['title' => '[Khối 7] Simple Habits for a Healthy Life'],
            [
                'level' => 'Khối 7 (Global Success)',
                'content' => 'Staying healthy is very important for secondary school students. Last month, our class started a healthy living project. We learned that we should eat more green vegetables and fresh fruit every day. We also avoided fast food and sweet soft drinks. Every afternoon after school, my friends and I played badminton or cycled around the park for thirty minutes. Now we feel much stronger and more active in class.',
                'translation' => 'Giữ gìn sức khỏe rất quan trọng đối với học sinh trung học cơ sở. Tháng trước, lớp chúng em đã bắt đầu một dự án sống khỏe. Chúng em học được rằng mình nên ăn nhiều rau xanh và trái cây tươi mỗi ngày. Chúng em cũng hạn chế đồ ăn nhanh và nước ngọt có ga. Mỗi buổi chiều sau giờ học, bạn bè và em đều chơi cầu lông hoặc đạp xe quanh công viên trong ba mươi phút.',
                'focus_words' => 'healthy, started, vegetables, avoided, drinks, played, cycled',
                'teacher_id' => $teacher->id,
            ]
        );

        $p8 = Passage::firstOrCreate(
            ['title' => '[Khối 8] Balancing Study and Leisure Time'],
            [
                'level' => 'Khối 8 (Global Success)',
                'content' => 'As grade eight students, we have many subjects and homework at school, so using our leisure time wisely is very helpful. Instead of spending hours scrolling on smartphones, many teenagers choose outdoor sports or creative hobbies. On weekends, I enjoy doing origami and reading English adventure stories. Outdoor activities with friends help us reduce stress and improve our communication skills.',
                'translation' => 'Là học sinh lớp 8, chúng em có nhiều môn học và bài tập ở trường, vì vậy việc sử dụng thời gian rảnh rỗi hợp lý rất hữu ích. Thay vì dành hàng giờ lướt điện thoại, nhiều bạn chọn thể thao ngoài trời hoặc sở thích sáng tạo.',
                'focus_words' => 'leisure, wisely, smartphones, teenagers, creative, communication',
                'teacher_id' => $teacher->id,
            ]
        );

        $p9 = Passage::firstOrCreate(
            ['title' => '[Khối 9] Bat Trang Traditional Pottery Village'],
            [
                'level' => 'Khối 9 (Ôn thi vào 10)',
                'content' => 'Bat Trang is one of the most famous traditional craft villages in Vietnam, located near the Red River. For centuries, skilled artisans in the village have created wonderful ceramic products by hand. When visitors come to Bat Trang, they can watch how artisans mould clay on a wheel and paint delicate patterns before baking the products in a kiln. Today, the village not only preserves cultural heritage but also exports handicrafts around the world.',
                'translation' => 'Bát Tràng là một trong những làng nghề truyền thống nổi tiếng nhất Việt Nam, nằm gần sông Hồng. Qua nhiều thế kỷ, các nghệ nhân lành nghề đã tạo ra những sản phẩm gốm sứ tuyệt đẹp bằng tay.',
                'focus_words' => 'traditional, artisans, ceramic, delicate, preserves, heritage, handicrafts',
                'teacher_id' => $teacher->id,
            ]
        );

        // 6. Phân quyền Bài tập mẫu
        $a1 = Assignment::firstOrCreate(
            ['title' => '[Khối 6] Từ vựng Unit 1: My New School (SGK Global Success 6)'],
            [
                'content_type' => 'vocab',
                'vocab_set_id' => $vocabModels[0]->id,
                'target_type' => 'class',
                'school_class_id' => $class6->id,
                'due_date' => '2026-12-10',
                'pass_score' => 75,
                'teacher_note' => 'Các em lớp 6A1 nghe kỹ trọng âm của từ "calculator" và "international" trước khi thu âm nhé!',
                'teacher_id' => $teacher->id,
            ]
        );

        $a2 = Assignment::firstOrCreate(
            ['title' => '[Khối 6] Luyện đọc Đoạn văn: My First Week at Secondary School'],
            [
                'content_type' => 'passage',
                'passage_id' => $p6->id,
                'target_type' => 'class',
                'school_class_id' => $class6->id,
                'due_date' => '2026-12-12',
                'pass_score' => 70,
                'teacher_note' => 'Chú ý bật rõ âm đuôi -s ở các từ số nhiều: classrooms, teachers, games.',
                'teacher_id' => $teacher->id,
            ]
        );

        Assignment::firstOrCreate(
            ['title' => '[Khối 7] Luyện đọc Unit 2: Simple Habits for a Healthy Life'],
            [
                'content_type' => 'passage',
                'passage_id' => $p7->id,
                'target_type' => 'class',
                'school_class_id' => $class7->id,
                'due_date' => '2026-12-15',
                'pass_score' => 75,
                'teacher_note' => 'Chú ý quy tắc phát âm đuôi -ed (started, learned, avoided, played, cycled).',
                'teacher_id' => $teacher->id,
            ]
        );

        Assignment::firstOrCreate(
            ['title' => '[Khối 8] Bài đọc: Balancing Study and Leisure Time'],
            [
                'content_type' => 'passage',
                'passage_id' => $p8->id,
                'target_type' => 'class',
                'school_class_id' => $class8->id,
                'due_date' => '2026-12-18',
                'pass_score' => 75,
                'teacher_note' => 'Đọc trôi chảy, ngắt nghỉ đúng dấu phẩy và dấu chấm câu.',
                'teacher_id' => $teacher->id,
            ]
        );

        $a5 = Assignment::firstOrCreate(
            ['title' => '[Khối 9 & Đội tuyển] Luyện đọc: Bat Trang Pottery Village'],
            [
                'content_type' => 'passage',
                'passage_id' => $p9->id,
                'target_type' => 'students',
                'due_date' => '2026-12-20',
                'pass_score' => 80,
                'teacher_note' => 'Bài giao riêng cho Đức Anh (9A1) và Bảo Ngọc (Đội tuyển HSG).',
                'teacher_id' => $teacher->id,
            ]
        );
        $a5->targetStudents()->syncWithoutDetaching([$stu2->id, $stu5->id]);

        // 7. Bài nộp mẫu
        if (Submission::count() === 0) {
            Submission::create([
                'assignment_id' => $a2->id,
                'student_id' => $stu1->id,
                'school_class_id' => $class6->id,
                'content_type' => 'passage',
                'content_title' => $p6->title,
                'score' => 88,
                'recognized_text' => 'this year i am a grade six student at nguyen du secondary school everything is new and exciting to me my school has a large playground a modern library and twenty classroom our teacher are very kind and helpful',
                'word_details' => [
                    ['expected' => 'This', 'actual' => 'this', 'status' => 'correct'],
                    ['expected' => 'year,', 'actual' => 'year', 'status' => 'correct'],
                    ['expected' => 'I', 'actual' => 'i', 'status' => 'correct'],
                    ['expected' => 'am', 'actual' => 'am', 'status' => 'correct'],
                    ['expected' => 'a', 'actual' => 'a', 'status' => 'correct'],
                    ['expected' => 'grade', 'actual' => 'grade', 'status' => 'correct'],
                    ['expected' => 'six', 'actual' => 'six', 'status' => 'correct'],
                    ['expected' => 'student', 'actual' => 'student', 'status' => 'correct'],
                    ['expected' => 'at', 'actual' => 'at', 'status' => 'correct'],
                    ['expected' => 'Nguyen', 'actual' => 'nguyen', 'status' => 'correct'],
                    ['expected' => 'Du', 'actual' => 'du', 'status' => 'correct'],
                    ['expected' => 'Secondary', 'actual' => 'secondary', 'status' => 'correct'],
                    ['expected' => 'School.', 'actual' => 'school', 'status' => 'correct'],
                    ['expected' => 'classrooms.', 'actual' => 'classroom', 'status' => 'close'],
                    ['expected' => 'teachers', 'actual' => 'teacher', 'status' => 'close'],
                ],
                'teacher_feedback' => '8.8/10 điểm - Minh An đọc rất trôi chảy! Em chú ý phát âm rõ âm cuối /z/ ở từ số nhiều "classrooms" và "teachers" nhé.',
            ]);

            Submission::create([
                'assignment_id' => $a2->id,
                'student_id' => $stu2->id,
                'school_class_id' => $class6->id,
                'content_type' => 'passage',
                'content_title' => $p6->title,
                'score' => 96,
                'recognized_text' => 'this year i am a grade six student at nguyen du secondary school everything is new and exciting to me my school has a large playground a modern library and twenty classrooms our teachers are very kind and helpful',
                'word_details' => [
                    ['expected' => 'This', 'actual' => 'this', 'status' => 'correct'],
                    ['expected' => 'year,', 'actual' => 'year', 'status' => 'correct'],
                    ['expected' => 'classrooms.', 'actual' => 'classrooms', 'status' => 'correct'],
                    ['expected' => 'teachers', 'actual' => 'teachers', 'status' => 'correct'],
                ],
                'teacher_feedback' => '9.6/10 điểm - Xuất sắc! Bảo Ngọc phát âm rất chuẩn các âm đuôi -s/-es.',
            ]);
        }
    }
}
