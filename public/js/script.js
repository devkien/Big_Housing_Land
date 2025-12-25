// --- 2. XỬ LÝ SỰ KIỆN KHI DOM ĐÃ SẴN SÀNG ---
document.addEventListener('DOMContentLoaded', function () {

    // --- 1. TẢI BOTTOM NAV ---
    const bottomNavContainer = document.getElementById('bottom-nav-container');
    if (bottomNavContainer) {
        // If server already rendered bottom nav (non-empty), don't overwrite it.
        if (bottomNavContainer.innerHTML.trim() === '') {
            fetch('../layout/bottom-nav.html')
                .then(response => response.text())
                .then(data => {
                    bottomNavContainer.innerHTML = data;
                    runBottomNavHighlight();
                })
                .catch(err => console.error('Lỗi tải Bottom Nav:', err));
        } else {
            // server-rendered: still run highlight
            runBottomNavHighlight();
        }
    }

    function runBottomNavHighlight() {
        const path = window.location.pathname;
        if (path.includes('home.html') || path.includes('report_customer.html') || path.includes('hr_management.html') || path.includes('add_personnel.html') || path.includes('update_personnel.html')) document.getElementById('nav-home')?.classList.add('active');
        if (path.includes('collection.html')) document.getElementById('nav-collection')?.classList.add('active');
        if (path.includes('info.html')) document.getElementById('nav-info')?.classList.add('active');
        if (path.includes('notification.html')) document.getElementById('nav-notify')?.classList.add('active');

        if (path.includes('profile.html') || path.includes('editprofile.html') || path.includes('changepassword.html') || path.includes('detailprofile.html')) {
            const profileNav = document.getElementById('nav-profile');
            if (profileNav) profileNav.classList.add('active');
            if (profileNav && profileNav.querySelector('img')) profileNav.querySelector('img').style.border = '2px solid #0137AE';
        }
    }

    // --- 1.1 TẢI MENU (Dành cho trang Home) ---
    const menuContainer = document.getElementById('menu-container');
    if (menuContainer) {
        fetch('../layout/menu.html')
            .then(response => response.text())
            .then(data => {
                menuContainer.innerHTML = data;
            })
            .catch(err => console.error('Lỗi tải Menu:', err));
    }

    // Xử lý đóng Modal khi click ra ngoài (Dùng chung cho cả Filter và Search)
    window.onclick = function (event) {
        if (event.target.classList.contains('modal')) {
            event.target.style.display = "none";
        }
    }

    // --- LOGIC SỬA TRẠNG THÁI (ICON GHI CHÚ) ---
    const statusModal = document.getElementById('status-modal');
    if (statusModal) {
        const btnCloseStatus = document.getElementById('close-status-modal');
        const btnSaveStatus = document.getElementById('save-status-btn');
        const statusSelectEdit = document.getElementById('edit-status-select');
        let currentRowToEdit = null;

        // Gán sự kiện click trực tiếp cho từng icon note để gọi stopPropagation
        // (delegation on body happened too late and row onclick still executed)
        const noteIcons = document.querySelectorAll('.icon-note');
        noteIcons.forEach(icon => {
            icon.addEventListener('click', function (e) {
                e.stopPropagation(); // Ngăn chặn sự kiện click của dòng (chuyển trang)
                currentRowToEdit = this.closest('tr');
                if (currentRowToEdit) {
                    const badge = currentRowToEdit.querySelector('.status-badge');
                    if (badge && statusSelectEdit) {
                        statusSelectEdit.value = badge.innerText.trim();
                    }
                    statusModal.style.display = "flex";
                }
            });
        });

        if (btnCloseStatus) btnCloseStatus.onclick = () => { statusModal.style.display = "none"; }

        if (btnSaveStatus) {
            btnSaveStatus.onclick = async () => {
                if (!currentRowToEdit || !statusSelectEdit) return;
                const badge = currentRowToEdit.querySelector('.status-badge');
                const id = currentRowToEdit.dataset.id ? parseInt(currentRowToEdit.dataset.id, 10) : null;
                const displayValue = statusSelectEdit.value;

                // Map display labels to enum values (same as server mapping)
                const map = {
                    'Bán mạnh': 'ban_manh',
                    'Tạm dừng bán': 'tam_dung_ban',
                    'Dừng bán': 'dung_ban',
                    'Đã bán': 'da_ban',
                    'Tăng chào': 'tang_chao',
                    'Hạ chào': 'ha_chao'
                };

                const payload = {
                    id: id,
                    status: map[displayValue] || displayValue,
                    _csrf: (document.querySelector('meta[name="csrf-token"]') || {}).content || ''
                };

                const urlBase = (window.BASE_PATH || '');
                const url = urlBase + '/superadmin/property-update-status';

                try {
                    const resp = await fetch(url, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        credentials: 'same-origin',
                        body: JSON.stringify(payload)
                    });
                    const data = await resp.json().catch(() => null);
                    if (resp.ok && data && data.ok) {
                        if (badge) badge.innerText = displayValue;
                        statusModal.style.display = 'none';
                    } else {
                        alert((data && data.message) ? data.message : 'Không thể cập nhật trạng thái');
                    }
                } catch (err) {
                    console.error('Status update failed', err);
                    alert('Lỗi khi cập nhật trạng thái. Vui lòng thử lại.');
                }
            };
        }
    }

    // --- LOGIC LƯU TIN (ICON BOOKMARK) ---
    const saveCollectionModal = document.getElementById('save-collection-modal');
    const btnCloseSaveCollection = document.getElementById('close-save-collection');
    const btnConfirmSaveCollection = document.getElementById('confirm-save-collection');
    let currentIconToSave = null;

    // Attach direct click handlers to each icon-save so we can stopPropagation early
    const saveIcons = document.querySelectorAll('.icon-save');
    saveIcons.forEach(icon => {
        icon.addEventListener('click', function (e) {
            e.stopPropagation(); // Ngăn chặn sự kiện click của dòng (chuyển trang)
            currentIconToSave = this;
            if (saveCollectionModal) {
                saveCollectionModal.style.display = "flex";
                // Reset checkbox khi mở lại (hoặc load trạng thái cũ nếu có backend)
                const checkboxes = saveCollectionModal.querySelectorAll('input[type="checkbox"]');
                checkboxes.forEach(cb => cb.checked = false);
            }
        });
    });

    if (btnCloseSaveCollection) {
        btnCloseSaveCollection.onclick = function () {
            if (saveCollectionModal) saveCollectionModal.style.display = "none";
            currentIconToSave = null;
        }
    }

    if (btnConfirmSaveCollection) {
        btnConfirmSaveCollection.onclick = function () {
            if (currentIconToSave) {
                // Logic: Nếu ấn Lưu thì icon sáng lên (giả lập đã lưu)
                currentIconToSave.classList.remove('fa-regular');
                currentIconToSave.classList.add('fa-solid');
                currentIconToSave.style.color = '#ffcc00';
            }
            if (saveCollectionModal) saveCollectionModal.style.display = "none";
        }
    }

    // --- LOGIC LỌC (FILTER) ---
    const filterModal = document.getElementById('filter-modal');
    const btnFilter = document.getElementById('btn-filter');
    const btnCloseFilter = document.getElementById('close-filter');
    const btnApplyFilter = document.getElementById('apply-filter');

    if (filterModal && btnFilter) {
        // Mở modal
        btnFilter.onclick = function () { filterModal.style.display = "flex"; }

        // Đóng modal
        if (btnCloseFilter) btnCloseFilter.onclick = function () { filterModal.style.display = "none"; }

        // Áp dụng lọc (server-side) - redirect với query params
        if (btnApplyFilter) {
            btnApplyFilter.onclick = function () {
                const statusVal = document.getElementById('filter-status').value;
                const addressVal = document.getElementById('filter-address').value.trim();

                const params = new URLSearchParams(window.location.search);
                if (statusVal && statusVal !== 'all') params.set('status', statusVal); else params.delete('status');
                if (addressVal) params.set('address', addressVal); else params.delete('address');
                params.delete('page');

                // Close modal and navigate
                filterModal.style.display = "none";
                const qs = params.toString();
                window.location.search = qs ? ('?' + qs) : '';
            }
        }
    }

    // --- LOGIC TÌM KIẾM (SEARCH) ---
    const searchModal = document.getElementById('search-modal');
    const btnSearch = document.getElementById('btn-search');
    const btnCloseSearch = document.getElementById('close-search');
    const btnApplySearch = document.getElementById('apply-search');
    const searchInput = document.getElementById('search-input');

    if (searchModal && btnSearch) {
        // Mở modal và focus vào ô nhập
        btnSearch.onclick = function () {
            searchModal.style.display = "flex";
            if (searchInput) searchInput.focus();
        }

        // Đóng modal
        if (btnCloseSearch) btnCloseSearch.onclick = function () { searchModal.style.display = "none"; }

        // Thực hiện tìm kiếm
        if (btnApplySearch) {
            btnApplySearch.onclick = function () {
                const keyword = searchInput ? searchInput.value.toLowerCase() : "";
                const rows = document.querySelectorAll('.resource-table tbody tr');

                rows.forEach(row => {
                    const text = row.innerText.toLowerCase();
                    row.style.display = text.includes(keyword) ? '' : 'none';
                });
                searchModal.style.display = "none";
            }
        }
    }

    // --- LOGIC ĐÓNG/MỞ CÀI ĐẶT (PROFILE) ---
    const settingHeaders = document.querySelectorAll('.setting-item-header');
    settingHeaders.forEach(header => {
        header.addEventListener('click', function () {
            this.parentElement.classList.toggle('collapsed');
        });
    });

    // --- PHÂN TRANG (PAGINATION) ---
    let updatePagination = null; // Biến global trong scope này để Sort gọi lại
    const resourceTable = document.querySelector('.resource-table');
    if (resourceTable) {
        const tbody = resourceTable.querySelector('tbody');
        let paginationContainer = resourceTable.querySelector('.pagination-container');
        if (!paginationContainer) {
            paginationContainer = document.querySelector('.pagination-container');
        }
        const rowsPerPage = 10;
        let currentPage = 1;

        updatePagination = function () {
            const rows = Array.from(tbody.querySelectorAll('tr'));
            const totalPages = Math.ceil(rows.length / rowsPerPage);

            // 1. Hiển thị dòng thuộc trang hiện tại
            rows.forEach((row, index) => {
                if (index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });

            // 2. Tạo nút phân trang
            if (paginationContainer) {
                paginationContainer.innerHTML = '';

                // Nút Prev
                const prevBtn = document.createElement('a');
                prevBtn.href = '#';
                prevBtn.className = 'page-link';
                prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
                prevBtn.onclick = (e) => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        updatePagination();
                    }
                };
                paginationContainer.appendChild(prevBtn);

                // Các trang
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('a');
                    pageBtn.href = '#';
                    pageBtn.className = `page-link ${i === currentPage ? 'active' : ''}`;
                    pageBtn.innerText = i;
                    pageBtn.onclick = (e) => {
                        e.preventDefault();
                        currentPage = i;
                        updatePagination();
                    };
                    paginationContainer.appendChild(pageBtn);
                }

                // Nút Next
                const nextBtn = document.createElement('a');
                nextBtn.href = '#';
                nextBtn.className = 'page-link';
                nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
                nextBtn.onclick = (e) => {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        updatePagination();
                    }
                };
                paginationContainer.appendChild(nextBtn);
            }
        }

        // Khởi chạy lần đầu
        updatePagination();
    }

    // --- PHÂN TRANG CHO HR MANAGEMENT (HR TABLE) ---
    const hrTable = document.querySelector('.hr-table');
    if (hrTable) {
        const tbody = hrTable.querySelector('tbody');
        // Tìm container phân trang (nằm ngoài bảng trong hr_management2.html)
        const paginationContainer = document.querySelector('.pagination-container');

        if (tbody && paginationContainer) {
            const rowsPerPage = 6; // Số dòng mỗi trang
            let currentPage = 1;

            const updateHrPagination = function () {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const totalPages = Math.ceil(rows.length / rowsPerPage);

                // 1. Hiển thị dòng thuộc trang hiện tại
                rows.forEach((row, index) => {
                    if (index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // 2. Tạo nút phân trang
                paginationContainer.innerHTML = '';

                // Nút Prev
                const prevBtn = document.createElement('button');
                prevBtn.className = 'page-btn nav-arrow';
                prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
                prevBtn.onclick = (e) => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        updateHrPagination();
                    }
                };
                paginationContainer.appendChild(prevBtn);

                // Các trang
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('button');
                    pageBtn.className = `page-btn ${i === currentPage ? 'active' : ''}`;
                    pageBtn.innerText = i;
                    pageBtn.onclick = (e) => {
                        e.preventDefault();
                        currentPage = i;
                        updateHrPagination();
                    };
                    paginationContainer.appendChild(pageBtn);
                }

                // Nút Next
                const nextBtn = document.createElement('button');
                nextBtn.className = 'page-btn nav-arrow';
                nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
                nextBtn.onclick = (e) => {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        updateHrPagination();
                    }
                };
                paginationContainer.appendChild(nextBtn);
            };

            // Khởi chạy lần đầu
            updateHrPagination();

            // --- LOGIC XÓA NHÂN SỰ (ICON TRASH) ---
            const deleteModal = document.getElementById('delete-modal');
            const btnConfirmDelete = document.getElementById('confirm-delete-btn');
            const btnCancelDelete = document.getElementById('cancel-delete-btn');
            let rowToDelete = null;
            let formToSubmit = null;

            if (deleteModal) {
                // Sử dụng Event Delegation để bắt sự kiện click cho icon thùng rác
                hrTable.addEventListener('click', function (e) {
                    const trash = e.target.classList.contains('icon-trash-red') ? e.target : (e.target.closest && e.target.closest('.icon-trash-red'));
                    if (trash) {
                        e.stopPropagation();
                        rowToDelete = e.target.closest('tr');
                        // Find the form that wraps the button/icon
                        formToSubmit = trash.closest('form');
                        deleteModal.style.display = 'flex';
                    }
                });

                if (btnCancelDelete) btnCancelDelete.onclick = () => { deleteModal.style.display = 'none'; rowToDelete = null; formToSubmit = null; };

                if (btnConfirmDelete) btnConfirmDelete.onclick = async () => {
                    if (!btnConfirmDelete) return;
                    // Disable confirm to prevent double clicks
                    btnConfirmDelete.disabled = true;

                    try {
                        if (formToSubmit) {
                            // read action and tokens from form
                            const action = formToSubmit.getAttribute('action') || (window.BASE_URL ? window.BASE_URL + '/superadmin/management-delete' : '/superadmin/management-delete');
                            const csrfInput = formToSubmit.querySelector('input[name="_csrf"]');
                            const idInput = formToSubmit.querySelector('input[name="id"]');
                            const payload = {
                                id: idInput ? idInput.value : null,
                                _csrf: csrfInput ? csrfInput.value : null
                            };

                            const resp = await fetch(action, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },
                                body: JSON.stringify(payload),
                                credentials: 'same-origin'
                            });

                            const data = await (resp.ok ? resp.json().catch(() => null) : resp.json().catch(() => null));

                            if (resp.ok && data && data.ok) {
                                if (rowToDelete) rowToDelete.remove();
                                updateHrPagination();
                                deleteModal.style.display = 'none';
                                formToSubmit = null;
                                rowToDelete = null;

                                const successModal = document.getElementById('success-modal');
                                const successTitle = document.getElementById('success-modal-title');
                                const successMsg = document.getElementById('success-modal-message');
                                const successOkBtn = document.getElementById('success-ok-btn');
                                if (successModal) {
                                    if (successTitle) successTitle.innerText = 'Thành công';
                                    if (successMsg) successMsg.innerText = data.message || 'Đã xóa.';
                                    successModal.style.display = 'flex';
                                    if (successOkBtn) successOkBtn.onclick = () => { successModal.style.display = 'none'; };
                                }
                            } else {
                                const msg = (data && data.message) ? data.message : 'Lỗi khi xóa.';
                                alert(msg);
                            }

                            btnConfirmDelete.disabled = false;
                            return;
                        }

                        // Fallback: if no form, remove row locally
                        if (rowToDelete) {
                            rowToDelete.remove();
                            updateHrPagination();
                            deleteModal.style.display = 'none';
                            rowToDelete = null;
                        }
                    } catch (err) {
                        console.error('Delete failed', err);
                        alert('Không thể xóa. Vui lòng thử lại.');
                    } finally {
                        if (btnConfirmDelete) btnConfirmDelete.disabled = false;
                    }
                };
            }

            // --- LOGIC LỌC CHO HR MANAGEMENT ---
            const hrFilterModal = document.getElementById('hr-filter-modal');
            const btnHrFilter = document.getElementById('btn-hr-filter');
            const btnCloseHrFilter = document.getElementById('close-hr-filter');
            const btnApplyHrFilter = document.getElementById('apply-hr-filter');

            if (hrFilterModal && btnHrFilter) {
                btnHrFilter.onclick = () => { hrFilterModal.style.display = "flex"; };
                if (btnCloseHrFilter) btnCloseHrFilter.onclick = () => { hrFilterModal.style.display = "none"; };

                if (btnApplyHrFilter) {
                    btnApplyHrFilter.onclick = () => {
                        const statusVal = document.getElementById('hr-filter-status').value.toLowerCase();
                        const keywordVal = document.getElementById('hr-filter-keyword').value.toLowerCase();

                        // Ẩn phân trang khi lọc
                        if (paginationContainer) paginationContainer.style.display = 'none';

                        tbody.querySelectorAll('tr').forEach(row => {
                            const statusCell = row.cells[2].innerText.toLowerCase();
                            const nameCell = row.cells[3].innerText.toLowerCase();
                            const phoneCell = row.cells[4].innerText.toLowerCase();
                            const addressCell = row.cells[5].innerText.toLowerCase();

                            let show = true;
                            if (statusVal !== 'all' && statusCell !== statusVal) {
                                show = false;
                            }
                            if (keywordVal && !(nameCell.includes(keywordVal) || phoneCell.includes(keywordVal) || addressCell.includes(keywordVal))) {
                                show = false;
                            }
                            row.style.display = show ? '' : 'none';
                        });
                        hrFilterModal.style.display = "none";
                    };
                }
            }

            // --- LOGIC TÌM KIẾM CHO HR MANAGEMENT ---
            const hrSearchModal = document.getElementById('hr-search-modal');
            const btnHrSearch = document.getElementById('btn-hr-search');
            const btnCloseHrSearch = document.getElementById('close-hr-search');
            const btnApplyHrSearch = document.getElementById('apply-hr-search');
            const hrSearchInput = document.getElementById('hr-search-input');

            if (hrSearchModal && btnHrSearch) {
                btnHrSearch.onclick = () => {
                    hrSearchModal.style.display = "flex";
                    if (hrSearchInput) hrSearchInput.focus();
                };
                if (btnCloseHrSearch) btnCloseHrSearch.onclick = () => { hrSearchModal.style.display = "none"; };

                if (btnApplyHrSearch) {
                    btnApplyHrSearch.onclick = () => {
                        const keyword = hrSearchInput.value.toLowerCase();
                        if (paginationContainer) paginationContainer.style.display = 'none';

                        tbody.querySelectorAll('tr').forEach(row => {
                            const rowText = row.innerText.toLowerCase();
                            row.style.display = rowText.includes(keyword) ? '' : 'none';
                        });
                        hrSearchModal.style.display = "none";
                    };
                }
            }
        }
    }

    // --- LOGIC SẮP XẾP (SORT) ---
    const btnSort = document.querySelector('.tool-btn.tin');
    if (btnSort) {
        let isAscending = true;

        // Định nghĩa thứ tự ưu tiên (số càng nhỏ càng ưu tiên)
        const statusPriority = {
            "bán mạnh": 1,
            "tạm dừng bán": 2, "tạm dừng": 2, // Gộp cả "Tạm dừng" nếu có
            "dừng bán": 3,
            "đã bán": 4,
            "tăng chào": 5,
            "hạ chào": 6
        };

        btnSort.onclick = function () {
            // 1. Xử lý cho bảng Resource (Kho tài nguyên)
            const tbody = document.querySelector('.resource-table tbody');
            if (tbody) {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                rows.sort((a, b) => {
                    const textA = a.cells[3].innerText.trim().toLowerCase();
                    const textB = b.cells[3].innerText.trim().toLowerCase();

                    const weightA = statusPriority[textA] || 99;
                    const weightB = statusPriority[textB] || 99;

                    return isAscending ? weightA - weightB : weightB - weightA;
                });
                rows.forEach(row => tbody.appendChild(row));
            }

            // Cập nhật lại phân trang sau khi sắp xếp (để hiển thị đúng thứ tự ưu tiên trên trang 1)
            if (typeof updatePagination === 'function') {
                updatePagination();
            }

            // 2. Xử lý cho danh sách Post (Auto Match)
            const postContainer = document.getElementById('post-list-container');
            if (postContainer) {
                const posts = Array.from(postContainer.querySelectorAll('.post-card'));

                posts.sort((a, b) => {
                    // Lấy trạng thái từ nút button
                    const statusBtnA = a.querySelector('.btn-status-outline');
                    const statusBtnB = b.querySelector('.btn-status-outline');
                    const statusA = statusBtnA ? statusBtnA.innerText.trim().toLowerCase() : '';
                    const statusB = statusBtnB ? statusBtnB.innerText.trim().toLowerCase() : '';

                    const weightA = statusPriority[statusA] || 99;
                    const weightB = statusPriority[statusB] || 99;

                    // Lấy giá (để sắp xếp phụ: Giá cao xếp trên)
                    const getPrice = (el) => {
                        const priceEl = el.querySelector('.price-text');
                        if (!priceEl) return 0;
                        const match = priceEl.innerText.match(/(\d+(\.\d+)?)\s*tỷ/i);
                        return match ? parseFloat(match[1]) : 0;
                    };
                    const priceA = getPrice(a);
                    const priceB = getPrice(b);

                    // Logic: Ưu tiên Trạng thái -> Sau đó đến Giá (Cao -> Thấp)
                    if (weightA !== weightB) {
                        return isAscending ? weightA - weightB : weightB - weightA;
                    } else {
                        return priceB - priceA; // Giá luôn giảm dần trong cùng 1 nhóm trạng thái
                    }
                });
                posts.forEach(post => postContainer.appendChild(post));
            }

            // Đảo chiều sắp xếp cho lần click tiếp theo
            isAscending = !isAscending;
        }
    }

    // --- LOGIC TOGGLE PASSWORD (ADD/EDIT PERSONNEL) ---
    const togglePassword = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password-input');
    if (togglePassword && passwordInput) {
        togglePassword.addEventListener('click', function () {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    }

    // --- LOGIC TOGGLE FILTER (AUTO MATCH) ---
    const btnFilterToggle = document.getElementById('btn-filter-toggle');
    if (btnFilterToggle) {
        btnFilterToggle.addEventListener('click', function () {
            const container = document.getElementById('filter-container');
            if (container) {
                container.style.display = (container.style.display === 'none' || !container.style.display) ? 'block' : 'none';
            }
        });
    }

    // --- LOGIC TÌM KIẾM CHO TRANG AUTO MATCH ---
    const btnSearchMatch = document.getElementById('btn-search-match');
    const searchModalMatch = document.getElementById('search-modal-match');
    const btnCloseSearchMatch = document.getElementById('close-search-match');
    const btnApplySearchMatch = document.getElementById('apply-search-match');
    const searchInputMatch = document.getElementById('search-input-match');

    if (btnSearchMatch && searchModalMatch) {
        btnSearchMatch.onclick = function () {
            searchModalMatch.style.display = "flex";
            if (searchInputMatch) searchInputMatch.focus();
        }
        if (btnCloseSearchMatch) btnCloseSearchMatch.onclick = function () { searchModalMatch.style.display = "none"; }
        if (btnApplySearchMatch) {
            btnApplySearchMatch.onclick = function () {
                const keyword = searchInputMatch ? searchInputMatch.value.toLowerCase() : "";
                const type = document.getElementById('match-type') ? document.getElementById('match-type').value.toLowerCase() : "";
                const location = document.getElementById('match-location') ? document.getElementById('match-location').value.toLowerCase() : "";
                const priceRange = document.getElementById('match-price') ? document.getElementById('match-price').value : "";
                const legal = document.getElementById('match-legal') ? document.getElementById('match-legal').value.toLowerCase() : "";
                const posts = document.querySelectorAll('.post-card');

                posts.forEach(post => {
                    let isMatch = true;
                    const text = post.innerText.toLowerCase();
                    const priceEl = post.querySelector('.price-text');
                    const priceText = priceEl ? priceEl.innerText : '';

                    if (keyword && !text.includes(keyword)) isMatch = false;
                    if (type && type !== 'tất cả' && !text.includes(type)) isMatch = false;
                    if (location && !text.includes(location)) isMatch = false;
                    if (legal && legal !== 'tất cả' && !text.includes(legal)) isMatch = false;

                    if (priceRange && priceRange !== 'Tất cả' && priceText) {
                        const priceMatch = priceText.match(/(\d+(\.\d+)?)\s*tỷ/i);
                        if (priceMatch) {
                            const price = parseFloat(priceMatch[1]);
                            if (priceRange === 'Dưới 5 tỷ' && price >= 5) isMatch = false;
                            if (priceRange === '5 - 10 tỷ' && (price < 5 || price > 10)) isMatch = false;
                            if (priceRange === '10 - 20 tỷ' && (price < 10 || price > 20)) isMatch = false;
                            if (priceRange === 'Trên 20 tỷ' && price <= 20) isMatch = false;
                        }
                    }
                    post.style.display = isMatch ? 'block' : 'none';
                });
                searchModalMatch.style.display = "none";
            }
        }
    }

    // --- TỰ ĐỘNG CẮT VĂN BẢN (AUTO TRUNCATE) ---
    const truncateElements = document.querySelectorAll('.auto-truncate-text');
    truncateElements.forEach(el => {
        const MAX_LENGTH = parseInt(el.getAttribute('data-limit')) || 50; // Lấy giới hạn từ attribute hoặc mặc định 50
        const fullHTML = el.innerHTML.trim();
        const textContent = el.innerText.trim();
        if (textContent.length > MAX_LENGTH) {
            let shortHTML = "";
            let charCount = 0;
            const tempDiv = document.createElement('div');
            tempDiv.innerHTML = fullHTML;
            function traverse(node) {
                if (charCount >= MAX_LENGTH) return;
                if (node.nodeType === 3) {
                    const remaining = MAX_LENGTH - charCount;
                    if (node.textContent.length > remaining) {
                        shortHTML += node.textContent.substring(0, remaining) + "...";
                        charCount = MAX_LENGTH;
                    } else {
                        shortHTML += node.textContent;
                        charCount += node.textContent.length;
                    }
                } else if (node.nodeType === 1) {
                    shortHTML += `<${node.tagName.toLowerCase()} ${Array.from(node.attributes).map(a => `${a.name}="${a.value}"`).join(' ')}>`;
                    Array.from(node.childNodes).forEach(child => traverse(child));
                    shortHTML += `</${node.tagName.toLowerCase()}>`;
                }
            }
            Array.from(tempDiv.childNodes).forEach(node => traverse(node));
            el.innerHTML = `
                <span class="short-view">${shortHTML}</span>
                <span class="full-view" style="display:none;">${fullHTML}</span>
                <a href="javascript:void(0)" class="link-blue toggle-text-btn" style="margin-left: 5px; font-weight: 500;">Xem thêm</a>
            `;
        }
    });

    // Xử lý sự kiện click cho nút Xem thêm/Thu gọn
    document.body.addEventListener('click', function (e) {
        if (e.target && e.target.classList.contains('toggle-text-btn')) {
            e.preventDefault();
            const btn = e.target;
            const container = btn.parentElement;
            const shortView = container.querySelector('.short-view');
            const fullView = container.querySelector('.full-view');
            if (fullView.style.display === 'none') {
                shortView.style.display = 'none';
                fullView.style.display = 'inline';
                btn.innerText = 'Thu gọn';
            } else {
                shortView.style.display = 'inline';
                fullView.style.display = 'none';
                btn.innerText = 'Xem thêm';
            }
        }
    });

    // --- XỬ LÝ TÌM KIẾM TỪ TRANG CHỦ (URL PARAMS) ---
    // Kiểm tra nếu có tham số 'keyword' trên URL (khi từ trang Home chuyển sang)
    const urlParams = new URLSearchParams(window.location.search);
    const searchKeyword = urlParams.get('keyword');

    if (searchKeyword && document.querySelector('.resource-table')) {
        const keyword = searchKeyword.toLowerCase();
        const rows = document.querySelectorAll('.resource-table tbody tr');

        // Ẩn phân trang khi đang tìm kiếm để tránh xung đột hiển thị
        const paginationContainer = document.querySelector('.pagination-container');
        if (paginationContainer) paginationContainer.style.display = 'none';

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();
            row.style.display = text.includes(keyword) ? '' : 'none';
        });

        // Điền lại từ khóa vào ô tìm kiếm trong modal (nếu có) để người dùng biết
        const searchInput = document.getElementById('search-input');
        if (searchInput) searchInput.value = searchKeyword;
    }

    // --- PHÂN TRANG CHO REPORT LIST (BÁO CÁO DẪN KHÁCH) ---
    const reportTable = document.querySelector('.report-list-table');
    if (reportTable) {
        const tbody = reportTable.querySelector('tbody');
        const paginationContainer = reportTable.querySelector('.pagination-container');

        if (tbody && paginationContainer) {
            const rowsPerPage = 6; // Số dòng mỗi trang
            let currentPage = 1;

            const updateReportPagination = function () {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const totalPages = Math.ceil(rows.length / rowsPerPage);

                // 1. Hiển thị dòng thuộc trang hiện tại
                rows.forEach((row, index) => {
                    if (index >= (currentPage - 1) * rowsPerPage && index < currentPage * rowsPerPage) {
                        row.style.display = '';
                    } else {
                        row.style.display = 'none';
                    }
                });

                // 2. Tạo nút phân trang
                paginationContainer.innerHTML = '';

                // Nút Prev
                const prevBtn = document.createElement('a');
                prevBtn.href = '#';
                prevBtn.className = 'page-link';
                prevBtn.innerHTML = '<i class="fa-solid fa-chevron-left"></i>';
                prevBtn.onclick = (e) => {
                    e.preventDefault();
                    if (currentPage > 1) {
                        currentPage--;
                        updateReportPagination();
                    }
                };
                paginationContainer.appendChild(prevBtn);

                // Các trang
                for (let i = 1; i <= totalPages; i++) {
                    const pageBtn = document.createElement('a');
                    pageBtn.href = '#';
                    pageBtn.className = `page-link ${i === currentPage ? 'active' : ''}`;
                    pageBtn.innerText = i;
                    pageBtn.onclick = (e) => {
                        e.preventDefault();
                        currentPage = i;
                        updateReportPagination();
                    };
                    paginationContainer.appendChild(pageBtn);
                }

                // Nút Next
                const nextBtn = document.createElement('a');
                nextBtn.href = '#';
                nextBtn.className = 'page-link';
                nextBtn.innerHTML = '<i class="fa-solid fa-chevron-right"></i>';
                nextBtn.onclick = (e) => {
                    e.preventDefault();
                    if (currentPage < totalPages) {
                        currentPage++;
                        updateReportPagination();
                    }
                };
                paginationContainer.appendChild(nextBtn);
            };

            // Khởi chạy lần đầu
            updateReportPagination();
        }

        // --- TÌM KIẾM CHO REPORT LIST ---
        const reportSearchInput = document.getElementById('report-search-input');
        const reportSearchBtn = document.getElementById('report-search-btn');

        if (reportSearchInput && reportSearchBtn) {
            const performReportSearch = () => {
                const keyword = reportSearchInput.value.toLowerCase();
                const rows = reportTable.querySelectorAll('tbody tr');
                const paginationContainer = reportTable.querySelector('.pagination-container');

                // Ẩn phân trang khi đang tìm kiếm để hiện hết kết quả
                if (paginationContainer) paginationContainer.style.display = keyword ? 'none' : 'flex';

                rows.forEach(row => {
                    // Tìm trong cột "Đầu chủ" (cột thứ 4 - index 3)
                    const ownerName = row.cells[3] ? row.cells[3].innerText.toLowerCase() : '';
                    row.style.display = ownerName.includes(keyword) ? '' : 'none';
                });
            };

            reportSearchBtn.addEventListener('click', performReportSearch);
            reportSearchInput.addEventListener('keyup', function (e) {
                if (e.key === 'Enter') performReportSearch();
            });
        }
    }
});

document.addEventListener('DOMContentLoaded', function () {
    // Xử lý sự kiện xóa và sửa bộ sưu tập
    const collectionModal = document.getElementById('collection-modal');
    const renameModal = document.getElementById('rename-collection-modal');
    const btnEditCollection = document.getElementById('edit-collection');
    const btnConfirmDelete = document.getElementById('confirm-delete');
    const btnCancelDelete = document.getElementById('cancel-delete');

    const btnConfirmRename = document.getElementById('confirm-rename');
    const btnCancelRename = document.getElementById('cancel-rename');
    const renameInput = document.getElementById('rename-input');

    let targetCard = null;

    // Sử dụng Event Delegation để bắt sự kiện cho cả các phần tử được tạo động
    document.body.addEventListener('click', function (e) {
        if (e.target.closest('.btn-more-dots')) {
            e.stopPropagation();
            targetCard = e.target.closest('.collection-card');
            if (collectionModal) collectionModal.style.display = 'flex';
        }
    });

    if (btnCancelDelete) btnCancelDelete.onclick = function () { if (collectionModal) collectionModal.style.display = 'none'; targetCard = null; }

    if (btnConfirmDelete) btnConfirmDelete.onclick = function () {
        if (targetCard) targetCard.remove();
        if (collectionModal) collectionModal.style.display = 'none';
    }

    if (btnEditCollection) {
        btnEditCollection.onclick = function () {
            if (collectionModal) collectionModal.style.display = 'none';
            if (renameModal && targetCard) {
                const nameEl = targetCard.querySelector('.collection-name');
                if (nameEl && renameInput) renameInput.value = nameEl.innerText;
                renameModal.style.display = 'flex';
                if (renameInput) renameInput.focus();
            }
        }
    }

    if (btnCancelRename) btnCancelRename.onclick = function () { if (renameModal) renameModal.style.display = 'none'; }

    if (btnConfirmRename) btnConfirmRename.onclick = function () {
        if (targetCard && renameInput) {
            const nameEl = targetCard.querySelector('.collection-name');
            if (nameEl) nameEl.innerText = renameInput.value;
        }
        if (renameModal) renameModal.style.display = 'none';
    }

    // Xử lý tìm kiếm bộ sưu tập
    const searchInput = document.getElementById('search-collection-input');
    const searchBtn = document.getElementById('btn-search-collection');
    const noResultMsg = document.getElementById('no-result-message');

    const performCollectionSearch = () => {
        const keyword = searchInput.value.toLowerCase();
        const cards = document.querySelectorAll('.collection-card');
        let hasResult = false;

        cards.forEach(card => {
            const name = card.querySelector('.collection-name').innerText.toLowerCase();
            const isMatch = name.includes(keyword);
            card.style.display = isMatch ? 'flex' : 'none';
            if (isMatch) hasResult = true;
        });

        if (noResultMsg) {
            noResultMsg.style.display = hasResult ? 'none' : 'block';
        }
    };

    if (searchInput) {
        searchInput.addEventListener('input', performCollectionSearch);
        searchInput.addEventListener('keyup', function (e) {
            if (e.key === 'Enter') performCollectionSearch();
        });
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', performCollectionSearch);
    }
});
function previewMediaNotify(input) {
    const container = document.getElementById('media-preview-notify');
    container.innerHTML = '';
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                const mediaElement = file.type.startsWith('video/') ? document.createElement('video') : document.createElement('img');
                mediaElement.src = e.target.result;
                mediaElement.style.width = '80px';
                mediaElement.style.height = '80px';
                mediaElement.style.objectFit = 'cover';
                mediaElement.style.borderRadius = '4px';
                mediaElement.style.border = '1px solid #ddd';
                if (file.type.startsWith('video/')) mediaElement.controls = true;
                container.appendChild(mediaElement);
            }
            reader.readAsDataURL(file);
        });
    }
}
function toggleText(btn) {
    var contentDiv = btn.parentElement;
    var dots = contentDiv.querySelector(".dots");
    var moreText = contentDiv.querySelector(".more-text");

    if (dots.style.display === "none") {
        dots.style.display = "inline";
        btn.innerHTML = "Xem thêm";
        moreText.style.display = "none";
    } else {
        dots.style.display = "none";
        btn.innerHTML = "Thu gọn";
        moreText.style.display = "inline";
    }
}

// --- XỬ LÝ TÌM KIẾM ---
const btnSearchNotify = document.getElementById('btn-search-notify');
const searchModal = document.getElementById('search-modal');
const btnApplySearchNotify = document.getElementById('apply-search-notify');
const searchInputNotify = document.getElementById('search-input-notify');
const btnCloseSearch = document.getElementById('close-search');
// Click ra ngoài đã được xử lý tự động trong script.js chung

if (btnSearchNotify) {
    btnSearchNotify.onclick = function () {
        searchModal.style.display = "flex";
        searchInputNotify.focus();
    }
}

if (btnCloseSearch) {
    btnCloseSearch.onclick = function () {
        searchModal.style.display = "none";
    }
}

if (btnApplySearchNotify) {
    btnApplySearchNotify.onclick = function () {
        const keyword = searchInputNotify.value.toLowerCase();
        const posts = document.querySelectorAll('.post-card');
        posts.forEach(post => {
            const text = post.innerText.toLowerCase();
            post.style.display = text.includes(keyword) ? '' : 'none';
        });
        searchModal.style.display = "none";
    }
}
function previewMedia(input) {
    const container = document.getElementById('media-preview-container');
    container.innerHTML = ''; // Xóa nội dung cũ
    if (input.files) {
        Array.from(input.files).forEach(file => {
            const reader = new FileReader();
            reader.onload = function (e) {
                // Kiểm tra nếu là video thì tạo thẻ video, ngược lại tạo thẻ img
                const mediaElement = file.type.startsWith('video/') ? document.createElement('video') : document.createElement('img');
                mediaElement.src = e.target.result;
                mediaElement.style.width = '80px';
                mediaElement.style.height = '80px';
                mediaElement.style.objectFit = 'cover';
                mediaElement.style.borderRadius = '4px';
                mediaElement.style.border = '1px solid #ddd';
                if (file.type.startsWith('video/')) mediaElement.controls = true; // Hiện nút play cho video
                container.appendChild(mediaElement);
            }
            reader.readAsDataURL(file);
        });
    }
}

// Initialize CKEditor only if the script is loaded and the textarea exists
if (typeof ClassicEditor !== 'undefined' && document.querySelector('#editor-content')) {
    ClassicEditor
        .create(document.querySelector('#editor-content'), {
            toolbar: {
                items: [
                    'heading', '|',
                    'bold', 'italic', 'underline', '|',
                    'bulletedList', 'numberedList', '|',
                    'undo', 'redo'
                ]
            },
            placeholder: 'Nhập văn bản thông tin...'
        })
        .then(editor => {
            console.log('CKEditor đã sẵn sàng.', editor);
        })
        .catch(error => {
            console.error('Lỗi khởi tạo CKEditor:', error);
        });
}
function previewMediaInternal(input) {
    var previewContainer = document.getElementById('media-preview-internal');

    if (input.files) {
        var filesAmount = input.files.length;
        for (let i = 0; i < filesAmount; i++) {
            let file = input.files[i];
            let reader = new FileReader();

            reader.onload = function (event) {
                var mediaElement;
                if (file.type.startsWith('image/')) {
                    mediaElement = document.createElement('img');
                } else if (file.type.startsWith('video/')) {
                    mediaElement = document.createElement('video');
                    mediaElement.controls = true;
                }

                if (mediaElement) {
                    mediaElement.src = event.target.result;
                    mediaElement.style.width = '100px';
                    mediaElement.style.height = '100px';
                    mediaElement.style.objectFit = 'cover';
                    mediaElement.style.borderRadius = '4px';
                    mediaElement.style.border = '1px solid #ddd';

                    var wrapper = document.createElement('div');
                    wrapper.style.position = 'relative';
                    wrapper.style.margin = '5px';

                    var btnDelete = document.createElement('div');
                    btnDelete.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                    btnDelete.style.position = 'absolute';
                    btnDelete.style.top = '-8px';
                    btnDelete.style.right = '-8px';
                    btnDelete.style.width = '20px';
                    btnDelete.style.height = '20px';
                    btnDelete.style.background = '#ff0000';
                    btnDelete.style.color = 'white';
                    btnDelete.style.borderRadius = '50%';
                    btnDelete.style.display = 'flex';
                    btnDelete.style.alignItems = 'center';
                    btnDelete.style.justifyContent = 'center';
                    btnDelete.style.cursor = 'pointer';
                    btnDelete.style.fontSize = '12px';
                    btnDelete.style.zIndex = '10';

                    btnDelete.onclick = function () {
                        wrapper.remove();
                    };

                    wrapper.appendChild(mediaElement);
                    wrapper.appendChild(btnDelete);
                    previewContainer.appendChild(wrapper);
                }
            }
            reader.readAsDataURL(file);
        }
    }
}