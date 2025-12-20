<?php
// Reusable modals: delete confirmation + success notification
?>

<!-- Delete confirmation modal -->
<div id="delete-modal" class="modal">
    <div class="modal-content">
        <h3 style="margin-bottom: 15px; font-size: 16px; text-align: center;">Xác nhận xóa</h3>
        <p style="text-align: center; margin-bottom: 20px; font-size: 13px;">Bạn có chắc chắn muốn xóa nhân sự này không?</p>
        <div class="modal-actions" style="justify-content: center;">
            <button id="confirm-delete-btn" class="btn-save btn-danger" style="background-color: #ff3333; margin: 0; width: auto; padding: 10px 30px;">Xóa</button>
            <button id="cancel-delete-btn" class="btn-cancel">Hủy</button>
        </div>
    </div>
</div>

<!-- Generic success modal -->
<div id="success-modal" class="modal">
    <div class="modal-content">
        <h3 id="success-modal-title" style="margin-bottom: 12px; font-size: 16px; text-align: center;">Thành công</h3>
        <p id="success-modal-message" style="text-align: center; margin-bottom: 18px; font-size: 14px;">Thao tác đã thực hiện thành công.</p>
        <div class="modal-actions" style="justify-content: center;">
            <button id="success-ok-btn" class="btn-save" style="margin:0; width:auto; padding:8px 24px;">OK</button>
        </div>
    </div>
</div>