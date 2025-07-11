// --- 1. 全域資料中心 ---
const AppData = window.scheduleConfig.data;
const csrfToken = window.scheduleConfig.csrfToken;
const AppRoutes = window.scheduleConfig.routes;
let currentlyEditingItemId = null;
let currentlyEditingCampusId = null;

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

function openLinkedManagementModal(type) {
    closeModal('course-modal');
    openManagementModal(type + '-modal');
}

function editItem(modalId, item) {
    currentlyEditingItemId = item.id;
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
    deleteBtn.classList.remove('hidden');
    deleteBtn.onclick = () => confirmDeleteItem(item.id, item.name, form.dataset.deleteRoute + item.id, modalId.replace('-modal', ''));

    if (modalId === 'campus-modal') {
        currentlyEditingCampusId = item.id;
        document.getElementById('location-management-section').classList.remove('hidden');
        document.getElementById('location-campus-id').value = item.id;
        displayCampusLocations(item.id);
    }
}

function resetForm(modalId) {
    const form = document.getElementById(`${modalId}-form`);
    form.reset();
    const title = document.querySelector(`#${modalId} .management-list-title`).textContent.replace('列表', '');
    form.querySelector('h3').textContent = `新增${title}`;
    form.action = form.dataset.storeRoute;
    form.querySelector('[name="_method"]').value = "POST";
    document.getElementById(`${modalId}-delete-btn`).classList.add('hidden');
    
    if (modalId === 'campus-modal') {
        document.getElementById('location-management-section').classList.add('hidden');
    }
    currentlyEditingItemId = null;
    currentlyEditingCampusId = null;
}

// --- 3. 核心互動與表單處理 ---
function updateLocationsForCampus(campusId, selectedLocationId = null) {
    const locationSelect = document.getElementById('location_id');
    locationSelect.innerHTML = '';
    if (!campusId) {
        locationSelect.innerHTML = '<option value="">請先選擇校區</option>';
        return;
    }
    const campus = AppData.campuses.find(c => c.id == campusId);
    const locations = campus ? campus.locations : [];
    let options = '<option value="">-- 請選擇 --</option>';
    if (locations.length > 0) {
        locations.forEach(loc => {
            options += `<option value="${loc.id}" ${loc.id == selectedLocationId ? 'selected' : ''}>${loc.name}</option>`;
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
    const modalId = form.closest('.modal-backdrop').id;
    const modelType = modalId.replace('-modal', '');

    if (modalId === 'course-modal' && !combineTimeFields()) return;

    try {
        const response = await fetch(form.action, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const result = await response.json();
        if (response.ok) {
            showFeedbackModal(result.message || '操作成功！', 'success');
            updateAppDataAndUI(result.data, modelType, form.querySelector('[name="_method"]').value);
        } else {
            showFeedbackModal(result.message || `操作失敗 (${response.status})`, 'error');
        }
    } catch (error) {
        console.error('Submit Error:', error);
        showFeedbackModal('發生網路錯誤', 'error');
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
            const result = await response.json();
            if (response.ok) {
                showFeedbackModal(result.message || '刪除成功！', 'success');
                updateAppDataAndUI(result.data, modelType, 'DELETE');
            } else {
                showFeedbackModal(result.message || '刪除失敗', 'error');
            }
        } catch (error) {
            console.error('Delete Error:', error);
            showFeedbackModal('發生網路錯誤', 'error');
        }
    });
}

function updateAppDataAndUI(item, modelType, method) {
    const dataKeyMap = { campus: 'campuses', location: 'locations', template: 'courseTemplates', teacher: 'teachers', course: 'courses' };
    const dataKey = dataKeyMap[modelType];
    
    // 1. Update main data array
    if (dataKey && AppData[dataKey]) {
        if (method === 'DELETE') {
            const index = AppData[dataKey].findIndex(d => d.id === item.id);
            if (index > -1) AppData[dataKey].splice(index, 1);
        } else if (method === 'POST') {
            if (!AppData[dataKey].some(d => d.id === item.id)) {
                AppData[dataKey].push(item);
            }
        } else if (method === 'PUT') {
            const index = AppData[dataKey].findIndex(d => d.id === item.id);
            if (index > -1) AppData[dataKey][index] = item;
        }
    }

    // 2. Handle UI updates
    if (modelType === 'location') {
        let parentCampusId;
        if (method === 'DELETE') {
             for (const campus of AppData.campuses) {
                const locIndex = campus.locations.findIndex(l => l.id === item.id);
                if (locIndex > -1) {
                    campus.locations.splice(locIndex, 1);
                    parentCampusId = campus.id;
                    break;
                }
            }
        } else {
            const campus = AppData.campuses.find(c => c.id == item.campus_id);
            if (campus) {
                const locIndex = campus.locations.findIndex(l => l.id === item.id);
                if (method === 'POST' && !campus.locations.some(l => l.id === item.id)) {
                    campus.locations.push(item);
                } else if (method === 'PUT' && locIndex > -1) {
                    campus.locations[locIndex] = item;
                }
                parentCampusId = campus.id;
            }
        }
        
        if (parentCampusId && parentCampusId === currentlyEditingCampusId) {
            displayCampusLocations(parentCampusId);
        }
        updateAllSelectOptions('location_id');

    } else if (modelType === 'course') {
        location.reload(); 
    } else {
        const modalId = `${modelType}-modal`;
        redrawManagementList(modalId);
        
        if (modelType === 'campus') updateAllSelectOptions('campus_id');
        if (modelType === 'template') updateAllSelectOptions('course_template_id');
        if (modelType === 'teacher') updateAllSelectOptions('teacher_id');

        const idToKeep = (modelType === 'campus') ? currentlyEditingCampusId : currentlyEditingItemId;

        if (method === 'DELETE' && item.id === idToKeep) {
            resetForm(modalId);
        } else if (idToKeep) {
            const currentItem = AppData[dataKey]?.find(i => i.id === idToKeep);
            if (currentItem) {
                editItem(modalId, currentItem);
            } else {
                resetForm(modalId);
            }
        }
    }
}

// --- 地點管理 ---
function displayCampusLocations(campusId) {
    const locationList = document.getElementById('location-list');
    const campus = AppData.campuses.find(c => c.id === campusId);
    locationList.innerHTML = '';
    if (campus && campus.locations.length > 0) {
        campus.locations.forEach(location => {
            const div = document.createElement('div');
            div.className = 'py-2 flex justify-between items-center text-sm';
            div.innerHTML = `<span>${location.name}</span>
                             <button type="button" class="text-red-500 hover:text-red-700 bg-red-100 hover:bg-red-200 p-1 rounded-full w-6 h-6 flex items-center justify-center" onclick="confirmDeleteItem(${location.id}, '${location.name}', '${AppRoutes.location_base}${location.id}', 'location')">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                             </button>`;
            locationList.appendChild(div);
        });
    } else {
        locationList.innerHTML = '<p class="text-gray-500 text-sm py-2">- 此校區尚無地點 -</p>';
    }
}

async function handleAddLocation() {
    const container = document.getElementById('location-add-container');
    const campusId = container.querySelector('#location-campus-id').value;
    const nameInput = container.querySelector('#new-location-name');
    const name = nameInput.value;

    if (!name.trim()) return showFeedbackModal('地點名稱不能為空！', 'error');

    const formData = new FormData();
    formData.append('campus_id', campusId);
    formData.append('name', name);
    
    try {
        const response = await fetch(AppRoutes.location_store, {
            method: 'POST',
            body: formData,
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        });
        const result = await response.json();
        if (response.ok) {
            showFeedbackModal('地點新增成功！', 'success');
            updateAppDataAndUI(result.data, 'location', 'POST');
            nameInput.value = '';
        } else {
            showFeedbackModal(result.message || '新增失敗', 'error');
        }
    } catch (error) {
        console.error('Add Location Error:', error);
        showFeedbackModal('發生網路錯誤', 'error');
    }
}


// --- 輔助 & UI 更新函式 ---
function redrawManagementList(modalId) {
    const listElement = document.querySelector(`#${modalId} .management-item-list ul`);
    if (!listElement) return;
    const modelType = modalId.replace('-modal', '');
    const dataKey = { campus: 'campuses', template: 'courseTemplates', teacher: 'teachers' }[modelType];
    const items = AppData[dataKey] || [];
    listElement.innerHTML = items.length === 0 ? '<li class="py-2 text-gray-500">- 尚無項目</li>' : '';
    items.sort((a, b) => a.id - b.id).forEach(item => {
        const li = document.createElement('li');
        li.className = 'py-2 px-2 hover:bg-gray-100 rounded-md cursor-pointer';
        li.onclick = () => editItem(modalId, JSON.parse(JSON.stringify(item)));
        let colorIndicator = item.color ? `<div class="w-6 h-4 rounded" style="background-color: ${item.color}; border: 1px solid rgba(0,0,0,0.1);"></div>` : '';
        li.innerHTML = `<div class="flex items-center justify-between"><span>${item.name}</span>${colorIndicator}</div>`;
        listElement.appendChild(li);
    });
}

function updateAllSelectOptions(selectName) {
    const dataKey = { campus_id: 'campuses', course_template_id: 'courseTemplates', teacher_id: 'teachers' }[selectName];
    if (dataKey) {
        document.querySelectorAll(`select[name="${selectName}"]`).forEach(select => {
            const currentValue = select.value;
            let optionsHtml = '<option value="">-- 請選擇 --</option>';
            (AppData[dataKey] || []).forEach(item => {
                optionsHtml += `<option value="${item.id}">${item.name}</option>`;
            });
            select.innerHTML = optionsHtml;
            select.value = currentValue;
        });
    } else if (selectName === 'location_id') {
        // This is now handled by updateLocationsForCampus
    }
}

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
        const confirmButton = document.createElement('button'); confirmButton.className = 'btn bg-red-600 text-white hover:bg-red-700'; confirmButton.textContent = '確定刪除'; confirmButton.onclick = () => { onConfirm && onConfirm(); closeModal('feedback-modal'); }; buttonsContainer.appendChild(confirmButton);
    } else {
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
    document.querySelectorAll('form[id$="-form"]').forEach(form => form.addEventListener('submit', handleFormSubmit));
    document.getElementById('add-location-button')?.addEventListener('click', handleAddLocation);
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', function(e) { 
            if (e.target.id === this.id) closeModal(this.id);
        });
    });
    
    document.querySelector('label[for="campus_id"]').addEventListener('click', () => openLinkedManagementModal('campus'));
    document.querySelector('label[for="course_template_id"]').addEventListener('click', () => openLinkedManagementModal('template'));
    document.querySelector('label[for="teacher_id"]').addEventListener('click', () => openLinkedManagementModal('teacher'));
});