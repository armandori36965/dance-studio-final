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
    
    {{-- 引用外部 CSS 檔案 --}}
    <link rel="stylesheet" href="{{ asset('css/schedule.css') }}">
    
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
            <div class="flex flex-wrap gap-4 mb-4 p-4 bg-gray-50 rounded-lg border border-gray-200 items-center">
                <div class="flex-1 min-w-[200px]"><select id="filter_campus" class="form-select mt-1 block w-full shadow-sm" onchange="applyFilters()"><option value="">所有校區</option>@foreach ($campuses as $campus)<option value="{{ $campus->id }}" {{ request('campus_id') == $campus->id ? 'selected' : '' }}>{{ $campus->name }}</option>@endforeach</select></div>
                <div class="flex-1 min-w-[200px]"><select id="filter_course_template" class="form-select mt-1 block w-full shadow-sm" onchange="applyFilters()"><option value="">所有課程</option>@foreach ($courseTemplates as $template)<option value="{{ $template->id }}" {{ request('course_template_id') == $template->id ? 'selected' : '' }}>{{ $template->name }}</option>@endforeach</select></div>
                <div class="ml-auto flex gap-2 flex-wrap">
                    <button class="btn btn-secondary" onclick="openManagementModal('campus-modal')">校區</button>
                    <button class="btn btn-secondary" onclick="openManagementModal('location-modal')">地點</button>
                    <button class="btn btn-secondary" onclick="openManagementModal('template-modal')">課程</button>
                    <button class="btn btn-secondary" onclick="openManagementModal('teacher-modal')">老師</button>
                </div>
            </div>

            <div class="flex justify-center items-center mb-6 flex-wrap gap-4">
                <a href="{{ url(request()->fullUrlWithQuery(['month' => $prevMonth])) }}" class="btn btn-secondary" title="上個月">&lt;</a>
                <div class="flex items-center gap-2">
                    <form id="month-select-form" class="flex items-center gap-2">
                        <select name="year" onchange="navigateToMonth()" class="form-select rounded-md shadow-sm">@for ($y = now()->year - 2; $y <= now()->year + 2; $y++) <option value="{{ $y }}" @if($y == $currentDate->year) selected @endif>{{ $y }} 年</option> @endfor</select>
                        <select name="month_val" onchange="navigateToMonth()" class="form-select rounded-md shadow-sm">@for ($m = 1; $m <= 12; $m++) <option value="{{ str_pad($m, 2, '0', STR_PAD_LEFT) }}" @if($m == $currentDate->month) selected @endif>{{ $m }} 月</option> @endfor</select>
                    </form>
                    <a href="{{ route('schedule.index') }}" class="btn btn-primary">今日</a>
                </div>
                <a href="{{ url(request()->fullUrlWithQuery(['month' => $nextMonth])) }}" class="btn btn-secondary" title="下個月">&gt;</a>
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

    @csrf
    <div id="course-modal" class="modal-backdrop fixed inset-0 z-50 hidden">
        <div class="modal-content bg-white rounded-lg shadow-xl p-8 w-11/12 max-w-lg" onclick="event.stopPropagation()">
            <h2 id="modal-title" class="text-2xl font-header mb-6 text-[#5C5248]"></h2>
            <form id="course-form" method="POST">
                <input type="hidden" id="form-method" name="_method" value="POST">
                <input type="hidden" id="course_date" name="course_date">
                <input type="hidden" id="start_time" name="start_time">
                <input type="hidden" id="end_time" name="end_time">
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="campus_id" class="block text-sm font-medium text-gray-700">校區</label>
                            <select id="campus_id" name="campus_id" class="form-select block w-full mt-1" required>
                                <option value="">-- 請選擇 --</option>
                                @foreach ($campuses as $campus)<option value="{{ $campus->id }}">{{ $campus->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="location_id" class="block text-sm font-medium text-gray-700">地點</label>
                            <select id="location_id" name="location_id" class="form-select block w-full mt-1" required>
                                <option value="">請先選擇校區</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="course_template_id" class="block text-sm font-medium text-gray-700">課程</label>
                            <select id="course_template_id" name="course_template_id" class="form-select block w-full mt-1" required>
                                <option value="">-- 請選擇 --</option>
                                @foreach ($courseTemplates as $template)<option value="{{ $template->id }}">{{ $template->name }}</option>@endforeach
                            </select>
                        </div>
                        <div>
                            <label for="teacher_id" class="block text-sm font-medium text-gray-700">老師</label>
                            <select id="teacher_id" name="teacher_id" class="form-select block w-full mt-1" required>
                                <option value="">-- 請選擇 --</option>
                                @foreach ($teachers as $teacher)<option value="{{ $teacher->id }}">{{ $teacher->name }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="start_hour" class="block text-sm font-medium text-gray-700">開始時間</label>
                                <div class="flex items-center gap-1 mt-1">
                                    <select id="start_hour" class="form-select block w-full">@for($h=8;$h<=22;$h++)<option value="{{str_pad($h,2,'0',STR_PAD_LEFT)}}">{{str_pad($h,2,'0',STR_PAD_LEFT)}}</option>@endfor</select>
                                    <span>:</span>
                                    <select id="start_minute" class="form-select block w-full"><option value="00">00</option><option value="30">30</option></select>
                                </div>
                            </div>
                            <div>
                                <label for="end_hour" class="block text-sm font-medium text-gray-700">結束時間</label>
                                <div class="flex items-center gap-1 mt-1">
                                    <select id="end_hour" class="form-select block w-full">@for($h=8;$h<=22;$h++)<option value="{{str_pad($h,2,'0',STR_PAD_LEFT)}}">{{str_pad($h,2,'0',STR_PAD_LEFT)}}</option>@endfor</select>
                                    <span>:</span>
                                    <select id="end_minute" class="form-select block w-full"><option value="00">00</option><option value="30">30</option></select>
                                </div>
                            </div>
                        </div>
                        <p id="time-error" class="text-red-500 text-sm mt-1 hidden text-center">結束時間必須在開始時間之後。</p>
                    </div>
                </div>
                <div class="mt-8 flex items-center">
                    <button type="button" id="delete-button" class="btn text-red-600 hover:bg-red-50 hidden">刪除課程</button>
                    <div class="flex gap-3 ml-auto">
                        <button type="button" onclick="closeModal('course-modal')" class="btn btn-secondary">取消</button>
                        <button type="submit" class="btn btn-primary" form="course-form">儲存</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @include('partials.management-modal', ['modalId' => 'campus-modal', 'title' => '校區', 'items' => $campuses, 'storeRoute' => route('campuses.store'), 'updateRoute' => '/campuses/', 'deleteRoute' => '/campuses/', 'fields' => [ ['label' => '校區名稱', 'name' => 'name', 'type' => 'text'], ['label' => '代表色', 'name' => 'color', 'type' => 'color'] ]])
    @include('partials.management-modal', ['modalId' => 'location-modal', 'title' => '地點', 'items' => $locations, 'storeRoute' => route('locations.store'), 'updateRoute' => '/locations/', 'deleteRoute' => '/locations/', 'fields' => [ ['label' => '所屬校區', 'name' => 'campus_id', 'type' => 'select', 'options' => $campuses->pluck('name', 'id')], ['label' => '地點名稱', 'name' => 'name', 'type' => 'text'] ]])
    @include('partials.management-modal', ['modalId' => 'template-modal', 'title' => '課程', 'items' => $courseTemplates, 'storeRoute' => route('course-templates.store'), 'updateRoute' => '/course-templates/', 'deleteRoute' => '/course-templates/', 'fields' => [ ['label' => '課程名稱', 'name' => 'name', 'type' => 'text'], ['label' => '價格', 'name' => 'price', 'type' => 'number'] ]])
    @include('partials.management-modal', ['modalId' => 'teacher-modal', 'title' => '老師', 'items' => $teachers, 'storeRoute' => route('teachers.store'), 'updateRoute' => '/teachers/', 'deleteRoute' => '/teachers/', 'fields' => [ ['label' => '姓名', 'name' => 'name', 'type' => 'text'], ['label' => '電話', 'name' => 'phone_number', 'type' => 'text'] ]])

    <div id="feedback-modal" class="modal-backdrop fixed inset-0 z-[70] hidden"><div class="modal-content bg-white rounded-lg shadow-xl p-8 w-11/12 max-w-sm text-center" onclick="event.stopPropagation()"><div id="feedback-icon" class="mb-4 flex justify-center"></div><p id="feedback-message" class="text-xl font-medium text-gray-700"></p><div id="feedback-buttons" class="mt-6 flex justify-center gap-3"></div></div></div>

    {{-- 建立資料橋樑，並引用外部 JS --}}
    <script>
        // 這是一個「資料橋樑」，將 Laravel 的後端資料傳遞給靜態的 JS 檔案
        window.scheduleConfig = {
            data: {
                campuses: @json($campuses),
                locations: @json($locations),
                courseTemplates: @json($courseTemplates),
                teachers: @json($teachers)
            },
            routes: {
                courses_store: "{{ route('courses.store') }}",
                course_base: "/courses/", // 用於更新與刪除
                schedule_index: "{{ route('schedule.index') }}"
            },
            csrfToken: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        };
    </script>
    <script src="{{ asset('js/schedule.js') }}" defer></script>

</body>
</html>