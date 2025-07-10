<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>互動式課程日曆</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Noto+Sans+TC:wght@400;500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { font-family: 'Noto Sans TC', sans-serif; background-color: #F8F7F5; }
        .font-header { font-family: 'Cormorant Garamond', serif; }
        .calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); border-color: #EAEAEA; }
        .day-cell { min-height: 140px; border-color: #EAEAEA; transition: background-color: 0.3s; }
        .day-cell:hover { background-color: #FFF; }
        .day-cell-content { cursor: pointer; }
        .other-month { background-color: #FDFDFD; }
        .day-number-wrapper { display: flex; justify-content: flex-end; padding-right: 0.25rem; }
        .day-number { color: #888; width: 1.75rem; height: 1.75rem; display: flex; align-items: center; justify-content: center; font-weight: 500; }
        .today .day-number { background-color: #8A705A; color: white; border-radius: 9999px; }
        .course-item { cursor: pointer; transition: all 0.2s ease; font-weight: 500; }
        .course-item:hover { transform: translateY(-2px); box-shadow: 0 6px 12px rgba(0,0,0,0.08); }
        .modal-backdrop { background-color: rgba(42, 38, 38, 0.5); transition: opacity 0.3s ease-in-out; display: flex; align-items: center; justify-content: center; padding: 1rem; }
        .modal-content { box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1); }
        .btn { padding: 0.5rem 1rem; border-radius: 0.375rem; font-weight: 500; transition: all 0.2s; }
        .btn-primary { background-color: #8A705A; color: white; }
        .btn-primary:hover { background-color: #705A49; }
        .btn-secondary { background-color: #EAEAEA; color: #555; }
        .btn-secondary:hover { background-color: #DCDCDC; }
        .form-select, .form-input { border-color: #DCDCDC; border-radius: 0.375rem; }
        .form-select:focus, .form-input:focus { border-color: #8A705A; box-shadow: 0 0 0 1px #8A705A; outline: none; }
        .management-item-list { max-height: 24rem; overflow-y: auto; }
        .course-item { background-color: #F9F9F9 !important; color: #333 !important; border-left-width: 4px; }
        .school-event-item { background-color: #FDF3E1; color: #B58428; font-weight: 500; }
    </style>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto p-4 md:p-8">
        <header class="mb-8 flex justify-between items-center">
            <h1 class="text-4xl md:text-5xl font-header text-center text-[#5C5248]">小豆芽舞蹈社課表</h1>
            <div>
                 <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="text-sm text-gray-600 hover:text-[#8A705A]">
                    登出 ({{ Auth::user()->name }})
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
            </div>
        </header>

        <main class="bg-white p-4 sm:p-6 rounded-xl shadow-lg">
            <div class="flex flex-wrap gap-4 mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <div class="flex-1 min-w-[200px]"><select id="filter_campus" class="form-select mt-1 block w-full shadow-sm" onchange="applyFilters()"><option value="">所有校區</option>@foreach ($campuses as $campus)<option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>@endforeach</select></div>
                <div class="flex-1 min-w-[200px]"><select id="filter_course_template" class="form-select mt-1 block w-full shadow-sm" onchange="applyFilters()"><option value="">所有課程</option>@foreach ($courseTemplates as $template)<option value="{{ $template->id }}" {{ request('course_template_id') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>@endforeach</select></div>
            </div>

            <div class="flex justify-center items-center mb-6 flex-wrap gap-4">
                <a href="{{ url(request()->fullUrlWithQuery(['month' => $prevMonth])) }}" class="btn btn-secondary" title="上個月">&lt;</a><div class="flex items-center gap-2"><form id="month-select-form" class="flex items-center gap-2"><select name="year" onchange="navigateToMonth()" class="form-select rounded-md shadow-sm">@for ($y = now()->year - 2; $y <= now()->year + 2; $y++) <option value="{{ $y }}" @if($y == $currentDate->year) selected @endif>{{ $y }} 年</option> @endfor</select><select name="month_val" onchange="navigateToMonth()" class="form-select rounded-md shadow-sm">@for ($m = 1; $m <= 12; $m++) <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" @if($m == $currentDate->month) selected @endif>{{ $m }} 月</option> @endfor</select></form><a href="{{ route('schedule.index') }}" class="btn btn-primary">今日</a></div><a href="{{ url(request()->fullUrlWithQuery(['month' => $nextMonth])) }}" class="btn btn-secondary" title="下個月">&gt;</a>
            </div>

            <div class="calendar-grid border-t border-l">
                @foreach (['週日', '週一', '週二', '週三', '週四', '週五', '週六'] as $day)<div class="text-center font-bold p-2 border-r border-b bg-gray-50 text-gray-500">{{ $day }}</div>@endforeach
                @php $date = $gridStartDate->copy(); @endphp
                @while ($date->lte($gridEndDate))
                    <div class="day-cell p-1 border-r border-b @if(!$date->isSameMonth($currentDate)) other-month @endif @if($date->isToday()) today @endif">
                        <div class="day-cell-content h-full" onclick="openAddModal('{{ $date->format('Y-m-d') }}')" data-date="{{ $date->format('Y-m-d') }}">
                            <div class="day-number-wrapper"><div class="day-number text-sm">{{ $date->day }}</div></div>
                            <div class="mt-1 space-y-1">
                                @if(isset($groupedEvents[$date->format('Y-m-d')]))@foreach($groupedEvents[$date->format('Y-m-d')] as $event)<div class="school-event-item text-xs text-center p-1 rounded" title="{{ $event->title }}"><i class="fa fa-calendar-times mr-1"></i>{{ $event->title }}</div>@endforeach @endif
                                @if(isset($groupedCourses[$date->format('Y-m-d')]))
                                    @foreach ($groupedCourses[$date->format('Y-m-d')] as $course)
                                        <div class="course-item text-xs p-1.5 rounded-md" style="border-left-color: {{ $course->location->campus->color ?? '#A9A9A9' }};" data-course-id="{{ $course->id }}" onclick="event.stopPropagation(); openEditModal({{ json_encode($course) }})">
                                            {{ $course->courseTemplate->name }}
                                        </div>
                                    @endforeach
                                @endif
                            </div>
                        </div>
                    </div>
                    @php $date->addDay(); @endphp
                @endwhile
            </div>
        </main>
    </div>

    {{-- Modals HTML --}}
    <div id="course-modal" class="modal-backdrop fixed inset-0 z-50 hidden" onclick="closeModal('course-modal')"><div class="modal-content bg-white rounded-lg shadow-xl p-8 w-11/12 max-w-lg" onclick="event.stopPropagation()"><h2 id="modal-title" class="text-2xl font-header mb-6 text-[#5C5248]"></h2><form id="course-form" method="POST"><input type="hidden" id="form-method" name="_method" value="POST"><input type="hidden" id="course_date" name="course_date"><input type="hidden" id="start_time" name="start_time"><input type="hidden" id="end_time" name="end_time"><div class="space-y-6"><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label for="campus_id" class="block text-sm font-medium text-gray-700">校區</label><div class="flex items-center gap-2 mt-1"><select id="campus_id" name="campus_id" class="form-select block w-full" required><option value="">-- 請選擇 --</option>@foreach ($campuses as $campus)<option value="{{ $campus->id }}">{{ $campus->name }}</option>@endforeach</select><button type="button" onclick="openManagementModal('campus-modal')" class="btn btn-secondary flex-shrink-0" title="編輯">+</button></div></div><div><label for="location_id" class="block text-sm font-medium text-gray-700">地點</label><div class="flex items-center gap-2 mt-1"><select id="location_id" name="location_id" class="form-select block w-full" required><option value="">-- 請選擇 --</option></select><button type="button" onclick="openManagementModal('location-modal')" class="btn btn-secondary flex-shrink-0" title="編輯">+</button></div></div></div><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label for="course_template_id" class="block text-sm font-medium text-gray-700">課程</label><div class="flex items-center gap-2 mt-1"><select id="course_template_id" name="course_template_id" class="form-select block w-full" required><option value="">-- 請選擇 --</option>@foreach ($courseTemplates as $template)<option value="{{ $template->id }}">{{ $template->name }}</option>@endforeach</select><button type="button" onclick="openManagementModal('template-modal')" class="btn btn-secondary flex-shrink-0" title="編輯">+</button></div></div><div><label for="teacher_id" class="block text-sm font-medium text-gray-700">老師</label><div class="flex items-center gap-2 mt-1"><select id="teacher_id" name="teacher_id" class="form-select block w-full" required><option value="">-- 請選擇 --</option>@foreach ($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach</select><button type="button" onclick="openManagementModal('teacher-modal')" class="btn btn-secondary flex-shrink-0" title="編輯">+</button></div></div></div><div><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label for="start_hour" class="block text-sm font-medium text-gray-700">開始時間</label><div class="flex items-center gap-1 mt-1"><select id="start_hour" class="form-select block w-full">@for($h=8;$h<=22;$h++)<option value="{{str_pad($h,2,'0',STR_PAD_LEFT)}}">{{str_pad($h,2,'0',STR_PAD_LEFT)}}</option>@endfor</select>:<select id="start_minute" class="form-select block w-full"><option value="00">00</option><option value="30">30</option></select></div></div><div><label for="end_hour" class="block text-sm font-medium text-gray-700">結束時間</label><div class="flex items-center gap-1 mt-1"><select id="end_hour" class="form-select block w-full">@for($h=8;$h<=22;$h++)<option value="{{str_pad($h,2,'0',STR_PAD_LEFT)}}">{{str_pad($h,2,'0',STR_PAD_LEFT)}}</option>@endfor</select>:<select id="end_minute" class="form-select block w-full"><option value="00">00</option><option value="30">30</option></select></div></div></div><p id="time-error" class="text-red-500 text-sm mt-1 hidden text-center">結束時間必須在開始時間之後。</p></div></div><div class="mt-8 flex items-center"><button type="button" id="delete-button" class="btn text-red-600 hover:bg-red-50 hidden">刪除課程</button><div class="flex gap-3 ml-auto"><button type="button" onclick="closeModal('course-modal')" class="btn btn-secondary">取消</button><button type="submit" class="btn btn-primary" form="course-form">儲存</button></div></div></form></div></div>
    <div id="feedback-modal" class="modal-backdrop fixed inset-0 z-[70] hidden" onclick="closeModal('feedback-modal')"><div class="modal-content bg-white rounded-lg shadow-xl p-8 w-11/12 max-w-sm text-center" onclick="event.stopPropagation()"><div id="feedback-icon" class="mb-4 flex justify-center"></div><p id="feedback-message" class="text-xl font-medium text-gray-700"></p><div id="feedback-buttons" class="mt-6 flex justify-center gap-3"></div></div></div>
    @csrf
    @include('partials.management-modal', ['modalId' => 'campus-modal', 'title' => '管理校區', 'items' => $campuses, 'storeRoute' => route('campuses.store'), 'updateRoute' => '/campuses/', 'deleteRoute' => '/campuses/', 'fields' => [ ['label' => '校區名稱', 'name' => 'name', 'type' => 'text'], ['label' => '代表色', 'name' => 'color', 'type' => 'color'] ]])
    @include('partials.management-modal', ['modalId' => 'location-modal', 'title' => '管理地點', 'items' => $locations, 'storeRoute' => route('locations.store'), 'updateRoute' => '/locations/', 'deleteRoute' => '/locations/', 'fields' => [ ['label' => '所屬校區', 'name' => 'campus_id', 'type' => 'select', 'options' => $campuses->pluck('name', 'id')], ['label' => '地點名稱', 'name' => 'name', 'type' => 'text'] ]])
    @include('partials.management-modal', ['modalId' => 'template-modal', 'title' => '管理課程', 'items' => $courseTemplates, 'storeRoute' => route('course-templates.store'), 'updateRoute' => '/course-templates/', 'deleteRoute' => '/course-templates/', 'fields' => [ ['label' => '課程名稱', 'name' => 'name', 'type' => 'text'], ['label' => '價格', 'name' => 'price', 'type' => 'number'] ]])
    @include('partials.management-modal', ['modalId' => 'teacher-modal', 'title' => '管理老師', 'items' => $teachers, 'storeRoute' => route('teachers.store'), 'updateRoute' => '/teachers/', 'deleteRoute' => '/teachers/', 'fields' => [ ['label' => '姓名', 'name' => 'name', 'type' => 'text'], ['label' => '電話', 'name' => 'phone_number', 'type' => 'text'] ]])

    <script>
        // --- 1. 全域資料中心 ---
        const AppData = {
            campuses: @json($campuses),
            locations: @json($locations),
            courseTemplates: @json($courseTemplates),
            teachers: @json($teachers)
        };
        const csrfToken = document.querySelector('input[name="_token"]').value;

        // --- 2. 舊有功能函式 (恢復完整功能) ---
        function openAddModal(date) {
            const form = document.getElementById('course-form');
            form.reset();
            form.querySelector('#form-method').value = 'POST';
            form.action = '{{ route("courses.store") }}';
            document.getElementById('modal-title').textContent = `新增課程於 ${date}`;
            document.getElementById('course_date').value = date;
            document.getElementById('delete-button').classList.add('hidden');
            document.getElementById('location_id').innerHTML = '<option value="">-- 請選擇 --</option>';
            document.getElementById('start_hour').value = '08';
            document.getElementById('start_minute').value = '00';
            document.getElementById('end_hour').value = '08';
            document.getElementById('end_minute').value = '00';
            document.getElementById('course-modal').classList.remove('hidden');
        }

        async function openEditModal(course) {
            const form = document.getElementById('course-form');
            form.reset();
            form.querySelector('#form-method').value = 'PUT';
            form.action = `/courses/${course.id}`;
            document.getElementById('modal-title').textContent = `編輯課程: ${course.course_template.name}`;
            
            const location = AppData.locations.find(loc => loc.id === course.location_id);
            const campusId = location ? location.campus_id : null;

            document.getElementById('course_template_id').value = course.course_template_id;
            document.getElementById('teacher_id').value = course.teacher_id;
            
            if (campusId) {
                document.getElementById('campus_id').value = campusId;
                await updateLocationsForCampus(campusId, course.location_id);
            } else {
                 await updateLocationsForCampus(null, null);
            }
            
            if (course.start_time && course.end_time) {
                const startTime = course.start_time.substring(11, 16).split(':');
                const endTime = course.end_time.substring(11, 16).split(':');
                document.getElementById('start_hour').value = startTime[0];
                document.getElementById('start_minute').value = startTime[1];
                document.getElementById('end_hour').value = endTime[0];
                document.getElementById('end_minute').value = endTime[1];
            }
            
            const deleteBtn = document.getElementById('delete-button');
            deleteBtn.classList.remove('hidden');
            deleteBtn.onclick = () => confirmDeleteItem(course.id, course.course_template.name, `/courses/${course.id}`, 'course');
            document.getElementById('course-modal').classList.remove('hidden');
        }
        
        function openManagementModal(modalId) {
            event.stopPropagation();
            resetForm(modalId);
            document.getElementById(modalId).classList.remove('hidden');
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

        function combineTimeFields() {
            const form = document.getElementById('course-form');
            const startTime = document.getElementById('start_hour').value + ':' + document.getElementById('start_minute').value;
            const endTime = document.getElementById('end_hour').value + ':' + document.getElementById('end_minute').value;
            if (startTime >= endTime) {
                document.getElementById('time-error').classList.remove('hidden'); return false;
            }
            document.getElementById('time-error').classList.add('hidden');
            form.querySelector('#start_time').value = startTime;
            form.querySelector('#end_time').value = endTime;
            return true;
        }

        async function updateLocationsForCampus(campusId, selectedLocationId = null) {
            const locationSelect = document.getElementById('location_id');
            locationSelect.innerHTML = '<option value="">讀取中...</option>';
            if (!campusId) {
                locationSelect.innerHTML = '<option value="">-- 請選擇 --</option>';
                return;
            }
            try {
                const response = await fetch(`/api/campuses/${campusId}/locations`);
                if (!response.ok) throw new Error('Network response not ok');
                const locations = await response.json();
                let options = '<option value="">-- 請選擇 --</option>';
                locations.forEach(loc => {
                    const selected = (selectedLocationId && loc.id == selectedLocationId) ? 'selected' : '';
                    options += `<option value="${loc.id}" ${selected}>${loc.name}</option>`;
                });
                locationSelect.innerHTML = options;
            } catch (error) { 
                console.error('Failed to fetch locations:', error); 
                locationSelect.innerHTML = '<option value="">讀取地點失敗</option>';
            }
        }

        function navigateToMonth() {
            const year = document.querySelector('#month-select-form [name="year"]').value;
            const month = document.querySelector('#month-select-form [name="month_val"]').value;
            window.location.href = `{{ route('schedule.index') }}?month=${year}-${month}`;
        }
        function applyFilters() {
            const url = new URL(window.location.href);
            url.searchParams.delete('campus_id');
            url.searchParams.delete('course_template_id');
            const campusId = document.getElementById('filter_campus').value;
            const courseTemplateId = document.getElementById('filter_course_template').value;
            if (campusId) url.searchParams.set('campus_id', campusId);
            if (courseTemplateId) url.searchParams.set('course_template_id', courseTemplateId);
            window.location.href = url.toString();
        }

        // --- 3. UI 動態更新函式 (已修正) ---
        function redrawManagementList(modelType) {
            const modalMap = {
                'campus': 'campus-modal',
                'location': 'location-modal',
                'teacher': 'teacher-modal',
                'course-template': 'template-modal'
            };
            const modalId = modalMap[modelType];
            if (!modalId) {
                console.error('Unknown modelType for redraw:', modelType);
                return;
            }

            const listElement = document.querySelector(`#${modalId} .management-item-list ul`);
            if(!listElement) {
                console.error('Could not find list element for modal:', modalId);
                return;
            }
            
            const dataKey = modelType.replace(/-(\w)/g, (m, c) => c.toUpperCase()) + 's';
            const items = AppData[dataKey];
            
            listElement.innerHTML = '';
            if (!items || items.length === 0) {
                listElement.innerHTML = '<li class="py-2 text-gray-500">尚無項目</li>';
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

        function updateCourseOnCalendar(course, action) {
            if (action === 'delete') {
                const el = document.querySelector(`[data-course-id='${course.id}']`);
                if (el) el.remove();
                return;
            }

            const date = course.start_time.substring(0, 10);
            const dayCell = document.querySelector(`[data-date='${date}']`);
            if (!dayCell) return;

            let courseEl = dayCell.querySelector(`[data-course-id='${course.id}']`);
            if (action === 'add' && !courseEl) {
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
                    const latestCourseData = JSON.parse(JSON.stringify(course));
                    openEditModal(latestCourseData);
                };
            }
        }
        
        // --- 4. 核心邏輯：表單提交與刪除 (已修正) ---
        async function handleFormSubmit(event) {
            event.preventDefault();
            const form = event.target;
            const modelType = form.dataset.modelType;
            const method = form.querySelector('[name="_method"]').value;

            if (modelType === 'course' && !combineTimeFields()) return;
            
            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
                    body: new FormData(form),
                });
                const result = await response.json();
                if (!response.ok) {
                    showFeedbackModal(result.message || '操作失敗', 'error');
                    return;
                }
                showFeedbackModal(result.message, 'success');

                const item = result.data;
                const dataKey = modelType.replace(/-(\w)/g, (m, c) => c.toUpperCase()) + 's';

                if (method === 'POST') {
                    if (AppData[dataKey]) AppData[dataKey].push(item);
                    if (modelType === 'course') {
                        updateCourseOnCalendar(item, 'add');
                        closeModal('course-modal');
                    } else {
                        redrawManagementList(modelType);
                        resetForm(form.id.replace('-form', ''));
                    }
                } else if (method === 'PUT') {
                    if (AppData[dataKey]) {
                        const index = AppData[dataKey].findIndex(d => d.id === item.id);
                        if (index > -1) AppData[dataKey][index] = item;
                    }
                    
                    if (modelType === 'course') {
                        updateCourseOnCalendar(item, 'update');
                        document.getElementById('modal-title').textContent = `編輯課程: ${item.course_template.name}`;
                    } else {
                        redrawManagementList(modelType);
                        form.querySelector('h3').textContent = `編輯: ${item.name}`;
                    }
                }
            } catch (error) { console.error(error); showFeedbackModal('發生無法預期的錯誤', 'error'); }
        }
        
        function confirmDeleteItem(itemId, itemName, deleteUrl, modelType) {
            showFeedbackModal(`您確定要刪除「${itemName}」嗎？`, 'confirm', async () => {
                try {
                    const response = await fetch(deleteUrl, {
                        method: 'POST',
                        headers: {'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json'},
                        body: new URLSearchParams({'_method': 'DELETE'})
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        showFeedbackModal(result.message || '刪除失敗', 'error');
                        return;
                    }
                    showFeedbackModal(result.message, 'success');
                    
                    const dataKey = modelType.replace(/-(\w)/g, (m, c) => c.toUpperCase()) + 's';
                    if (AppData[dataKey]) {
                        AppData[dataKey] = AppData[dataKey].filter(d => d.id !== itemId);
                    }
                    
                    if (modelType === 'course') {
                        updateCourseOnCalendar({id: result.data.id}, 'delete');
                        closeModal('course-modal');
                    } else {
                        redrawManagementList(modelType);
                        resetForm(modelType.replace('-template','').split('-')[0] + '-modal');
                    }
                } catch (error) { showFeedbackModal('發生網路錯誤', 'error'); }
            });
        }
        
        // --- 5. 輔助函式 ---
        function closeModal(modalId) {
            document.getElementById(modalId).classList.add('hidden');
        }

        function showFeedbackModal(message, type = 'success', onConfirm = null) {
            const modal = document.getElementById('feedback-modal');
            const iconContainer = modal.querySelector('#feedback-icon');
            const buttonsContainer = modal.querySelector('#feedback-buttons');
            modal.querySelector('#feedback-message').innerHTML = message;
            
            const icons = {
                success: `<svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                error: `<svg class="w-12 h-12 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>`,
                confirm: `<svg class="w-12 h-12 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>`
            };
            iconContainer.innerHTML = icons[type] || '';
            buttonsContainer.innerHTML = '';
            
            if (type === 'confirm') {
                const cancelButton = document.createElement('button'); cancelButton.className = 'btn btn-secondary'; cancelButton.textContent = '取消'; cancelButton.onclick = () => closeModal('feedback-modal'); buttonsContainer.appendChild(cancelButton);
                const confirmButton = document.createElement('button'); confirmButton.className = 'btn bg-red-600 text-white hover:bg-red-700'; confirmButton.textContent = '確定刪除'; confirmButton.onclick = () => { if (onConfirm) onConfirm(); closeModal('feedback-modal'); }; buttonsContainer.appendChild(confirmButton);
            } else {
                 setTimeout(() => closeModal('feedback-modal'), 1000);
            }
            modal.classList.remove('hidden');
        }
        
        // --- 6. 事件監聽綁定 ---
        document.addEventListener('DOMContentLoaded', () => {
            document.getElementById('campus_id').addEventListener('change', (e) => updateLocationsForCampus(e.target.value));
            
            const forms = {
                'course-form': 'course', 'campus-modal-form': 'campus', 'location-modal-form': 'location',
                'template-modal-form': 'course-template', 'teacher-modal-form': 'teacher'
            };

            for (const [formId, modelType] of Object.entries(forms)) {
                const form = document.getElementById(formId);
                if(form) {
                    form.dataset.modelType = modelType;
                    form.addEventListener('submit', handleFormSubmit);
                } else {
                    console.error(`Form with id "${formId}" not found.`);
                }
            }
        });
    </script>
</body>
</html>