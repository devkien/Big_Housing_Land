<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chỉnh sửa thông tin nội bộ</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../Public/Css/style.css">
    <script src="../Public/Js/script.js"></script>

    <style>
        .ck.ck-editor__main>.ck-editor__editable:not(.ck-focused) {
            border-color: #eee !important;
        }

        /* Chỉnh lại chiều cao CKEditor cho giống ảnh (khá dài) */
        .ck-editor__editable {
            min-height: 250px !important;
            background-color: white !important;
        }
    </style>
</head>

<body>
    <div class="app-container" style="background: #f0f4f8;">

        <div class="header-blue-solid">
            <a href="<?= BASE_URL ?>/superadmin/internal-info-list" class="back-btn-white"><i class="fa-solid fa-chevron-left"></i></a>
            Chỉnh sửa thông tin nội bộ
        </div>

        <div style="padding: 0 15px 100px 15px;">

            <label class="label-bold-black">Tiêu đề thông tin</label>
            <div style="background: white; padding: 5px; border-radius: 4px;">
                <input type="text" class="input-title-box input-title-large"
                    value="Big Housing Land Khởi Đầu Năm Mới Hứng Khởi 2025"
                    style="padding: 15px; box-shadow: none;">
            </div>

            <label class="label-bold-black">Nội dung thông tin nội bộ</label>
            <textarea id="editor-edit-content"></textarea>

            <div class="upload-box-large-center" id="upload-box" style="background: white; border-style: dashed; position: relative; cursor: pointer;">

                <div class="upload-hint-text" style="z-index: 2;">
                    <i class="fa-solid fa-circle-info"></i> Tải hình ảnh/video
                </div>

                <i class="fa-solid fa-camera icon-camera-large" id="icon-camera" style="display: none;"></i>
                <i class="fa-solid fa-plus icon-plus-absolute" id="icon-plus" style="display: none;"></i>

                <div class="upload-preview-container" id="preview-container">
                    <img src="https://images.unsplash.com/photo-1523580494863-6f3031224c94?q=80&w=600&auto=format&fit=crop" class="upload-preview-img" id="preview-img" alt="Preview">
                    <button class="btn-remove-image" id="btn-remove-img" type="button"><i class="fa-solid fa-xmark"></i></button>
                </div>
                <input type="file" id="file-upload-edit" style="display: none;" accept="image/*,video/*">
            </div>

            <button class="btn-submit-blue" style="background-color: #0033cc;">Lưu</button>

        </div>

        <div id="bottom-nav-container">
            <?php require_once __DIR__ . '/layouts/bottom-nav.php'; ?>
        </div>

    </div>

    <script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>

    <script>
        // Nội dung mẫu lấy từ ảnh (Có gạch chân <u>)
        const initialData = `
            <p>Mỗi dịp Tết đến xuân về, Big Housing land cũng như các doanh nghiệp thường tổ chức hoạt động du xuân đầu năm nhằm tạo tinh thần hứng khởi, gắn kết tập thể và khởi động cho một năm làm việc đầy nhiệt huyết. Hòa chung không khí đó, cán bộ nhân viên Big Housing Land đã có chuyến du xuân – tham quan – <u>du lịch đầu năm</u> đầy ý nghĩa tại nhiều địa điểm nổi tiếng. Đây không chỉ là hoạt động truyền thống mà còn là nét văn hóa đẹp thể hiện sự quan tâm của lãnh đạo Big Housing Land đối với đời sống tinh thần của nhân sự.</p>
        `;

        ClassicEditor
            .create(document.querySelector('#editor-edit-content'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', '|',
                        'bulletedList', 'numberedList', '|',
                        'undo', 'redo'
                    ]
                }
            })
            .then(editor => {
                // Đổ dữ liệu vào editor
                editor.setData(initialData);
                console.log('CKEditor (Edit Mode) đã sẵn sàng.');
            })
            .catch(error => {
                console.error('Lỗi CKEditor:', error);
            });

        // Logic xử lý ảnh (Xóa & Tải lại)
        document.addEventListener('DOMContentLoaded', function() {
            const uploadBox = document.getElementById('upload-box');
            const fileInput = document.getElementById('file-upload-edit');
            const previewContainer = document.getElementById('preview-container');
            const previewImg = document.getElementById('preview-img');
            const btnRemove = document.getElementById('btn-remove-img');
            const iconCamera = document.getElementById('icon-camera');
            const iconPlus = document.getElementById('icon-plus');

            // Xử lý click nút Xóa
            btnRemove.addEventListener('click', function(e) {
                e.stopPropagation(); // Ngăn sự kiện click lan ra box cha
                previewContainer.style.display = 'none';
                iconCamera.style.display = 'block';
                iconPlus.style.display = 'block';
                uploadBox.style.backgroundColor = '#f2f6ff'; // Đổi màu nền giống trạng thái thêm mới
                fileInput.value = ''; // Reset input file
            });

            // Xử lý click vào box để tải ảnh (khi đã xóa ảnh cũ)
            uploadBox.addEventListener('click', function() {
                if (previewContainer.style.display === 'none') {
                    fileInput.click();
                }
            });

            // Xử lý khi chọn file mới
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(evt) {
                        previewImg.src = evt.target.result;
                        previewContainer.style.display = 'flex';
                        iconCamera.style.display = 'none';
                        iconPlus.style.display = 'none';
                        uploadBox.style.backgroundColor = 'white';
                    }
                    reader.readAsDataURL(file);
                }
            });
        });
    </script>
</body>

</html>