// --- 1. 全域資料中心 ---
const AppData = window.scheduleConfig.data;
const csrfToken = window.scheduleConfig.csrfToken;
const AppRoutes = window.scheduleConfig.routes;

// --- 2. 核心 Modal 控制函式 ---
function openAddModal(date) {
    const form = document.getElementById('course-form');
    form.reset();
    form.querySelector('#form-method').value = 'POST';
    form.action = AppRoutes.courses_store;
    document.getElementById('modal-title').textContent = `新增課程於 ${date}`;
    document.getElementById('course_date').value = date;
    document.getElementById('delete-button').classList.add('hidden');
    updateLocationsForCampus(null);
    openModal('course-modal');
}

function openEditModal(course) {
    const form = document.getElementById('course-form');
    form.reset();
    form.querySelector('#form-method').value = 'PUT';
    form.action = `${AppRoutes.course_base}${course.id}`;
    document.getElementById('modal-title').textContent = `編輯課程: ${course.course_template.name}`;
    document.getElementById('course_template_id').value = course.course_template_id;
    document.getElementById('teacher_id').value = course.teacher_id;
    const campusId = course.location.campus_id;
    document.getElementById('campus_id').value = campusId;
    updateLocationsForCampus(campusId, course.location_id);
    const startTime = course.start_time.substring(11, 16).split(':');
    const endTime = course.end_time.substring(11, 16).split(':');
    document.getElementById('start_hour').value = startTime[0];
    document.getElementById('start_minute').value = startTime[1];
    document.getElementById('end_hour').value = endTime[0];
    document.getElementById('end_minute').value = endTime[1];
    const deleteBtn = document.getElementById('delete-button');
    deleteBtn.classList.remove('hidden');
    deleteBtn.onclick = () => confirmDeleteItem(course.id, course.course_template.name, `${AppRoutes.course_base}${course.id}`, 'course');
    openModal('course-modal');
}

function openManagementModal(modalId) {
    event.stopPropagation();
    resetForm(modalId);
    openModal(modalId);
}

function editItem(modalId, item) {
    const form = document.getElementById(`${modalId}-form`);
    form.querySelector('h3').textContent = `編輯: ${item.name}`;
    form.action = form.dataset.updateRoute + item.id;
    form.querySelector('[name="_method"]').value = "PUT";
    form.querySelectorAll('input, select').forEach(input => {
        const key = input.name;
        if (item.hasOwnProperty(key)) {
            input.value = item[key];
        }
    });
    const deleteBtn = document.getElementById(`${modalId}-delete-btn`);
    if (deleteBtn) {
        deleteBtn.classList.remove('hidden');
        const modelType = form.dataset.modelType;
        deleteBtn.onclick = () => confirmDeleteItem(item.id, item.name, form.dataset.deleteRoute + item.id, modelType);
    }
}

function resetForm(modalId) {
    const form = document.getElementById(`${modalId}-form`);
    form.reset();
    form.querySelector('h3').textContent = "新增項目";
    form.action = form.dataset.storeRoute;
    form.querySelector('[name="_method"]').value = "POST";
    const deleteBtn = document.getElementById(`${modalId}-delete-btn`);
    if (deleteBtn) deleteBtn.classList.add('hidden');
}


// --- 3. 核心互動與表單處理 ---
function updateLocationsForCampus(campusId, selectedLocationId = null) {
    const locationSelect = document.getElementById('location_id');
    if (!campusId) {
        locationSelect.innerHTML = '<option value="">請先選擇校區</option>';
        return;
    }
    const filteredLocations = AppData.locations.filter(loc => loc.campus_id == campusId);
    let options = '<option value="">-- 請選擇 --</option>';
    if (filteredLocations.length > 0) {
        filteredLocations.forEach(loc => {
            const selected = (selectedLocationId && loc.id == selectedLocationId) ? 'selected' : '';
            options += `<option value="${loc.id}" ${selected}>${loc.name}</option>`;
        });
    } else {
        options = '<option value="">此校區尚無地點</option>';
    }
    locationSelect.innerHTML = options;
}

function combineTimeFields() {
    const form = document.getElementById('course-form');
    const startTime = document.getElementById('start_hour').value + ':' + document.getElementById('start_minute').value;
    const endTime = document.getElementById('end_hour').value + ':' + document.getElementById('end_minute').value;
    if (startTime >= endTime) {
        document.getElementById('time-error').classList.remove('hidden');
        return false;
    }
    document.getElementById('time-error').classList.add('hidden');
    form.querySelector('#start_time').value = startTime;
    form.querySelector('#end_time').value = endTime;
    return true;
}

async function handleFormSubmit(event) {
    event.preventDefault();
    const form = event.target;
    const modelType = form.dataset.modelType;
    if (modelType === 'course' && !combineTimeFields()) return;
    
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: new FormData(form),
        });

        const responseText = await response.text();
        let result = {};
        try { result = JSON.parse(responseText); } catch (e) { /* 允許空的 response body */ }

        if (response.ok) {
            showFeedbackModal(result.message || '操作成功！', 'success');
            updateAppDataAndUI(result.data, modelType, form.querySelector('[name="_method"]').value);
        } else {
            const errorMessage = result.message || `操作失敗 (${response.status})，請檢查後再試。`;
            showFeedbackModal(errorMessage, 'error');
        }
    } catch (error) {
        console.error('Submit Error:', error);
        showFeedbackModal('發生無法預期的網路錯誤', 'error');
    }
}

function confirmDeleteItem(itemId, itemName, deleteUrl, modelType) {
    showFeedbackModal(`您確定要刪除「${itemName}」嗎？`, 'confirm', async () => {
        try {
            const response = await fetch(deleteUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: new URLSearchParams({ '_method': 'DELETE' })
            });

            const result = await response.json(); // 假設所有 destroy 都會回傳 JSON

            if (response.ok) {
                showFeedbackModal(result.message || '刪除成功！', 'success');
                // 【關鍵修正】確保 item.id 存在
                const dataToUpdate = result.data || { id: itemId };
                updateAppDataAndUI(dataToUpdate, modelType, 'DELETE');
            } else {
                const errorMessage = result.message || '刪除失敗';
                showFeedbackModal(errorMessage, 'error');
            }
        } catch (error) {
            console.error('Delete Error:', error);
            showFeedbackModal('發生無法預期的網路錯誤', 'error');
        }
    });
}

function updateAppDataAndUI(item, modelType, method) {
    // 【關鍵修正】增加對 item 的有效性檢查，防止JS錯誤
    if (!item || typeof item.id === 'undefined') {
        console.error('Update failed: item or item.id is undefined.', item);
        return;
    }
    
    const dataKey = modelType.replace(/-(\w)/g, (m, c) => c.toUpperCase()) + 's';

    if (method === 'DELETE') {
        if (AppData[dataKey]) {
            AppData[dataKey] = AppData[dataKey].filter(d => d.id !== item.id);
        }
    } else if (method === 'POST') {
        if (AppData[dataKey]) AppData[dataKey].push(item);
    } else if (method === 'PUT') {
        if (AppData[dataKey]) {
            const index = AppData[dataKey].findIndex(d => d.id === item.id);
            if (index > -1) AppData[dataKey][index] = item;
        }
    }

    if (modelType === 'course') {
        updateCourseOnCalendar(item, method.toLowerCase());
        closeModal('course-modal');
    } else {
        redrawManagementList(modelType);
        
        if(modelType === 'campus') updateAllSelectOptions('campus_id', AppData.campuses);
        if(modelType === 'location') updateAllSelectOptions('location_id', AppData.locations, true);
        if(modelType === 'course-template') updateAllSelectOptions('course_template_id', AppData.courseTemplates);
        if(modelType === 'teacher') updateAllSelectOptions('teacher_id', AppData.teachers);
        
        const modalId = modelType.replace('-template','').split('-')[0] + '-modal';
        resetForm(modalId);
    }
}

function updateCourseOnCalendar(course, action) {
    if (action === 'delete') {
        const el = document.querySelector(`.course-item[data-course-id='${course.id}']`);
        if (el) el.remove();
        return;
    }
    const date = course.start_time.substring(0, 10);
    const dayCell = document.querySelector(`.day-cell-content[data-date='${date}']`);
    if (!dayCell) return;
    let courseEl = dayCell.querySelector(`.course-item[data-course-id='${course.id}']`);
    if (action === 'post' && !courseEl) {
        courseEl = document.createElement('div');
        courseEl.dataset.courseId = course.id;
        dayCell.querySelector('.space-y-1').appendChild(courseEl);
    }
    if (courseEl) {
        courseEl.className = 'course-item text-xs p-1.5 rounded-md';
        courseEl.style.borderLeftColor = course.location?.campus?.color ?? '#A9A9A9';
        courseEl.innerText = course.course_template.name;
        courseEl.onclick = (event) => {
            event.stopPropagation();
            openEditModal(JSON.parse(JSON.stringify(course)));
        };
    }
}

function redrawManagementList(modelType) {
    const modalId = modelType.replace('-template','').split('-')[0] + '-modal';
    const listElement = document.querySelector(`#${modalId} .management-item-list ul`);
    if(!listElement) return;
    const dataKey = modelType.replace(/-(\w)/g, (m, c) => c.toUpperCase()) + 's';
    const items = AppData[dataKey] || [];
    listElement.innerHTML = '';
    if (items.length === 0) {
        listElement.innerHTML = '<li class="py-2 text-gray-500">- 尚無項目</li>';
        return;
    }
    items.sort((a, b) => a.id - b.id).forEach(item => {
        const li = document.createElement('li');
        li.className = 'py-2 px-2 flex justify-between items-center hover:bg-gray-100 rounded-md cursor-pointer';
        li.onclick = () => editItem(modalId, item);
        let content = `<span>${item.name}</span>`;
        if (item.color) {
            content += `<span class="w-4 h-4 rounded-full inline-block border" style="background-color: ${item.color};"></span>`;
        }
        li.innerHTML = content;
        listElement.appendChild(li);
    });
}

function updateAllSelectOptions(selectId, items, isLocation = false) {
     document.querySelectorAll(`select[id*='${selectId}']`).forEach(select => {
        const currentValue = select.value;
        let options = '<option value="">-- 請選擇 --</option>';
        if(isLocation) options = '<option value="">請先選擇校區</option>';
        items.forEach(item => {
            options += `<option value="${item.id}">${item.name}</option>`;
        });
        select.innerHTML = options;
        select.value = currentValue;
    });
}

// --- 5. 輔助函式 ---
function openModal(modalId) { document.getElementById(modalId)?.classList.remove('hidden'); }
function closeModal(modalId) { document.getElementById(modalId)?.classList.add('hidden'); }
function showFeedbackModal(message, type = 'success', onConfirm = null) {
    const modal = document.getElementById('feedback-modal');
    const iconContainer = modal.querySelector('#feedback-icon');
    const buttonsContainer = modal.querySelector('#feedback-buttons');
    modal.querySelector('#feedback-message').innerHTML = message;
    const icons = { success: `<svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`, error: `<svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`, confirm: `<svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>` };
    iconContainer.innerHTML = icons[type] || '';
    buttonsContainer.innerHTML = '';
    if (type === 'confirm') {
        const cancelButton = document.createElement('button'); cancelButton.className = 'btn btn-secondary'; cancelButton.textContent = '取消'; cancelButton.onclick = () => closeModal('feedback-modal'); buttonsContainer.appendChild(cancelButton);
        const confirmButton = document.createElement('button'); confirmButton.className = 'btn bg-red-600 text-white hover:bg-red-700'; confirmButton.textContent = '確定刪除'; confirmButton.onclick = () => { if (onConfirm) onConfirm(); closeModal('feedback-modal'); }; buttonsContainer.appendChild(confirmButton);
    } else {
        // 【修正】將自動關閉時間調整為 1.5 秒
        setTimeout(() => closeModal('feedback-modal'), 1500);
    }
    openModal('feedback-modal');
}

function navigateToMonth() {
    const year = document.querySelector('#month-select-form [name="year"]').value;
    const month = document.querySelector('#month-select-form [name="month_val"]').value;
    window.location.href = `${AppRoutes.schedule_index}?month=${year}-${month}`;
}

function applyFilters() {
    const url = new URL(window.location.href);
    url.searchParams.delete('campus_id'); url.searchParams.delete('course_template_id');
    const campusId = document.getElementById('filter_campus').value;
    const courseTemplateId = document.getElementById('filter_course_template').value;
    if (campusId) url.searchParams.set('campus_id', campusId);
    if (courseTemplateId) url.searchParams.set('course_template_id', courseTemplateId);
    window.location.href = url.toString();
}

// --- 6. 事件監聽綁定 ---
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('campus_id').addEventListener('change', (e) => updateLocationsForCampus(e.target.value));

    const forms = { 'course-form': 'course', 'campus-modal-form': 'campus', 'location-modal-form': 'location', 'template-modal-form': 'course-template', 'teacher-modal-form': 'teacher' };
    for (const [formId, modelType] of Object.entries(forms)) {
        const form = document.getElementById(formId);
        if(form) {
            form.dataset.modelType = modelType;
            form.addEventListener('submit', handleFormSubmit);
        }
    }
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', function() { closeModal(this.id); });
    });
});