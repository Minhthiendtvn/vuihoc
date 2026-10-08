# Cập nhật dữ liệu học tập

Vào **Admin → Cập nhật dữ liệu học tập** (`/admin/learning-imports`), chọn SQL → phân tích → kiểm tra số lượng → đánh dấu xác nhận → import.

## File đã thêm/sửa

- `app/Services/LearningSqlParser.php`: parser SQL chỉ đọc literal và whitelist.
- `app/Services/LearningContentImporter.php`: kiểm tra schema/unique/FK, preview, import transaction.
- `app/Services/LearningContentMapper.php`: ánh xạ ID nguồn sang ID hosting theo nội dung và cấp cha.
- `app/Http/Controllers/Admin/LearningContentImportController.php`: upload, file riêng tư, token, xác nhận, khóa import.
- `database/migrations/2026_10_08_110000_create_learning_content_imports_table.php`: bảng lịch sử tối thiểu.
- `resources/views/admin/imports/index.blade.php`: upload, preview, xác nhận, lịch sử.
- `resources/views/admin/layout.blade.php`: mục menu Admin.
- `routes/web_admin.php`: bốn route Admin mới.
- `tests/Feature/LearningContentImportTest.php`: 15 kiểm thử độc lập với production.
- `tests/Feature/LearningContentMappingTest.php` và `tests/LearningContentTestCase.php`: kiểm thử ánh xạ qua 9 bảng, bảo toàn ID/FK và bản xuất hosting riêng tư.
- `docs/learning-content-import.md`: hướng dẫn vận hành và triển khai.

## Cách hoạt động

- Chỉ nhận UTF-8 `.sql`, tối đa 32 MB và 100.000 bản ghi học liệu. Parser nhận INSERT VALUES gồm chuỗi MySQL, số nguyên và NULL, không nhận biểu thức/hàm/INSERT SELECT/ON DUPLICATE KEY.
- Whitelist cố định: subjects → topics → skills → lessons → questions → question_options → matching_pairs → fill_answers → sort_items.
- Không thực thi bất kỳ SQL nào từ file. CREATE chỉ cung cấp thứ tự cột cho INSERT không có tên cột; cấu trúc cột phải trùng schema đã hỗ trợ. DROP/DELETE/TRUNCATE/SET/LOCK/ALTER, executable comments và INSERT của bảng khác được bỏ qua.
- Môn học ghép theo slug. Chủ đề/kỹ năng/bài học ghép theo slug trong cấp cha đã ánh xạ. Không thêm unique key vào schema: kiểm tra tính duy nhất trong file và hosting, dừng khi ghép mơ hồ. Slug phải là chữ thường ASCII, số và gạch nối.
- Câu hỏi ghép theo bài học đã ánh xạ + game_type + nguyên văn prompt. Khi prompt trùng trong cùng bài/loại game, dùng thêm sort_order để phân biệt; trùng cả vị trí thì dừng. Nếu prompt thay đổi, thêm câu mới và giữ câu cũ để không chuyển lịch sử người dùng sang câu khác. Khi đổi loại game hoặc slug cũng tạo nội dung mới theo khóa ghép mới.
- Các bảng đáp án ghép theo question_id đã ánh xạ + sort_order; fill_answers dùng thêm blank_index. Nội dung đáp án tại vị trí ghép được cập nhật theo file. Không dùng ID nguồn để ghi đè ID hosting.
- Giữ nguyên ID và FK cha của bản ghi ghép được; nội dung mới nhận ID cao hơn ID tối đa hiện có và không thấp hơn AUTO_INCREMENT hiện tại trên MySQL. File phải có đủ các bản ghi cha của dữ liệu con. Không suy đoán FK bị thiếu bằng ID hosting.
- Chặn ID trùng trong file, khóa ghép mơ hồ, thiếu FK cha, thiếu primary key id và unique indexes khác schema được hỗ trợ. Preview hiển thị thêm số bản ghi đổi ID nguồn → hosting. Khi xác nhận, tính lại ánh xạ trong transaction và so checksum kế hoạch; nếu ánh xạ/số lượng đổi thì yêu cầu preview mới.
- Bản ghi mới dùng INSERT theo batch 300 để xung đột với thao tác Admin đồng thời sẽ rollback thay vì ghi đè. Bản ghi đã ghép dùng upsert theo ID (riêng subjects có unique slug dùng UPDATE theo ID) để MySQL không ghép nhầm qua unique slug.
- Kiểm tra lại schema, FK và xung đột trong transaction, khóa bản ghi có sẵn khi import. MySQL yêu cầu InnoDB cho cả 9 bảng và bảng lịch sử. Lỗi bất kỳ sẽ rollback, kể cả lịch sử thành công.
- Không xóa dữ liệu thiếu trong file. Không xóa đáp án cũ khi file bỏ bớt đáp án; nếu cần loại bỏ nội dung/đáp án cũ, quản lý riêng trong Admin. Cờ `is_demo` được giữ theo file; chức năng “Xóa dữ liệu mẫu” hiện có vẫn áp dụng cho các bản ghi có cờ này.
- Giữ `created_at` của bản ghi có sẵn; cập nhật các cột khác từ file. Preview “Có sẵn / cập nhật” bao gồm các bản ghi không thay đổi.
- Tái sử dụng middleware `web` (session/CSRF) và `role:admin`; mọi endpoint chỉ dành cho admin. Token preview gắn với session/admin, hết hạn 30 phút, kiểm tra SHA-256 trước import, tiêu thụ sau lượt xác nhận.
- File tạm ở local private disk `storage/app/private/learning-imports`, xóa khi import/hủy/thay preview. File bỏ quên quá 1 giờ được dọn ở lượt upload tiếp theo. Không tạo public storage link cho thư mục này.
- Khóa file cache chặn import đồng thời trên **một server**. Nếu chạy nhiều server, cần chuyển sang shared lock/storage trước khi dùng module.
- Lịch sử lưu 20 lượt thành công gần nhất trên UI (database giữ toàn bộ), gồm thời gian, admin ID, tên file, checksum và số lượng từng bảng. Lỗi kỹ thuật ghi vào log Laravel.

## Triển khai Vietnix

Thay đổi hiện được lưu trong checkout local; cần commit và push lên nhánh triển khai trước khi hosting `git pull` nhận được chúng. Không chạy dump bằng mysql/phpMyAdmin và không chạy migrate:fresh.

Backup database hiện tại qua phpMyAdmin Export. Sau khi mã đã được đưa lên nhánh hosting đang sử dụng:

```bash
cd /home/huynhvin/vuichoi
git pull --ff-only
composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Migration mới chỉ tạo `learning_content_imports`; `migrate` cũng chạy các migration cũ chưa áp dụng, vì vậy kiểm tra `php artisan migrate:status` trước triển khai. Module yêu cầu các cột grade và summary từ migration hiện có.

**Lưu ý tương thích có sẵn của repo:** composer.lock hiện chứa Symfony 8.1, yêu cầu PHP >= 8.4.1 dù composer.json ghi PHP ^8.2. Kiểm tra PHP CLI và PHP web hosting trước `composer install`; không dùng `--ignore-platform-reqs`. Thay đổi này không sửa composer.lock hoặc .env.

Thiết lập giới hạn PHP/web server phù hợp trong panel hosting: `upload_max_filesize >= 32M`, `post_max_size >= 40M`, `memory_limit >= 512M`, timeout PHP-FPM/proxy >= 180 giây. Import đặt PHP time limit 180 giây; proxy có thể áp dụng timeout riêng. `storage` và `bootstrap/cache` phải ghi được bởi PHP. Không cần queue hoặc frontend build.

Sau triển khai, hủy preview cũ và upload lại `database/vuihoc-database.sql`, kiểm tra preview trước xác nhận. Bản cập nhật ánh xạ không thêm migration/dependency. Không sửa ID hosting. Bản xuất hosting gửi để kiểm chứng không được commit/push vào repo.

## Kiểm thử

```bash
php -d memory_limit=512M vendor/phpunit/phpunit/phpunit tests/Feature/LearningContentImportTest.php tests/Feature/LearningContentMappingTest.php
vendor/bin/pint --test app/Services/LearningSqlParser.php app/Services/LearningContentImporter.php app/Services/LearningContentMapper.php app/Http/Controllers/Admin/LearningContentImportController.php database/migrations/2026_10_08_110000_create_learning_content_imports_table.php tests/Feature/LearningContentImportTest.php tests/Feature/LearningContentMappingTest.php tests/LearningContentTestCase.php routes/web_admin.php
```

Test dùng SQLite trong RAM, không đọc/ghi database production và không cần .env. Bao gồm full dump thật được import qua schema migration nội dung, đảo ID trên tất cả 9 bảng, repeat import, giữ users/dữ liệu thiếu, rollback khi bảng con lỗi, FK, khóa ghép mơ hồ, parser, quyền, upload, preview, file bị đổi và chống gửi lại. Test riêng tư dùng biến môi trường LEARNING_IMPORT_HOSTING_FIXTURE trỏ đến SQL đã giải nén từ bản xuất hosting; nếu không cung cấp thì test này được skip. Chưa kiểm tra trực tiếp trên MySQL hosting; nên thử một database staging cùng schema trước lượt production đầu tiên. Bộ test cũ PlayRedirectTest phụ thuộc database seed và thiếu Tests/TestCase.php trong repo nên không chạy trong quy trình này.
