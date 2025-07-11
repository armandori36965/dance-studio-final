<div id="{{ $modalId }}" class="modal-backdrop fixed inset-0 z-[60] hidden" onclick="closeModal('{{ $modalId }}')">
    <div class="modal-content bg-white rounded-lg shadow-xl w-11/12 max-w-3xl flex" onclick="event.stopPropagation()">
        <div class="w-1/2 border-r border-gray-200 p-6 flex flex-col">
            <h3 class="text-lg font-bold mb-4 text-gray-800 management-list-title">{{ $title }}列表</h3>
            <div class="management-item-list flex-grow">
                <ul class="divide-y divide-gray-200">
                    @forelse ($items as $item)
                        <li class="py-2 px-2 hover:bg-gray-100 rounded-md cursor-pointer" onclick="editItem('{{ $modalId }}', {{ json_encode($item) }})">
                            <div class="flex items-center justify-between">
                                <span>{{ $item->name }}</span>
                                @if(isset($item->color))
                                    <div class="w-6 h-4 rounded" style="background-color: {{ $item->color }}; border: 1px solid rgba(0,0,0,0.1);"></div>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="py-2 text-gray-500">- 尚無項目</li>
                    @endforelse
                </ul>
            </div>
        </div>
        <div class="w-1/2 p-6 bg-gray-50 flex flex-col">
            <form id="{{ $modalId }}-form" method="POST" action="{{ $storeRoute }}" class="space-y-4 flex-grow"
                  data-store-route="{{ $storeRoute }}"
                  data-update-route="{{ $updateRoute }}"
                  data-delete-route="{{ $deleteRoute }}">
                @csrf
                <input type="hidden" name="_method" value="POST">
                <h3 id="{{ $modalId }}-form-title" class="text-lg font-header mb-4 text-[#5C5248]">新增{{ $title }}</h3>
                @foreach ($fields as $field)
                    <div>
                        <label for="{{ $modalId }}-{{ $field['name'] }}" class="block text-sm font-medium text-gray-700">{{ $field['label'] }}</label>
                        @if ($field['type'] === 'select')
                            <select name="{{ $field['name'] }}" id="{{ $modalId }}-{{ $field['name'] }}" class="form-select mt-1 block w-full">
                                <option value="">-- 請選擇 --</option>
                                @if(isset($field['options']))
                                    @foreach ($field['options'] as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                @endif
                            </select>
                        @else
                            <input type="{{ $field['type'] }}" name="{{ $field['name'] }}" id="{{ $modalId }}-{{ $field['name'] }}" class="form-input mt-1 block w-full" @if($field['type'] === 'number') min="0" @endif required>
                        @endif
                    </div>
                @endforeach
            </form>

            @if ($modalId === 'campus-modal')
            <div id="location-management-section" class="mt-6 border-t pt-4 space-y-4 hidden">
                <h4 class="text-md font-bold text-gray-700">管理地點</h4>
                <div id="location-list" class="max-h-32 overflow-y-auto divide-y divide-gray-200 pr-2">
                    </div>
                <div id="location-add-container" class="flex items-stretch gap-2">
                    <input type="hidden" id="location-campus-id" name="campus_id">
                    <input type="text" id="new-location-name" name="name" placeholder="新增地點名稱" class="form-input block w-full text-sm flex-grow">
                    <button type="button" id="add-location-button" class="inline-flex items-center justify-center p-2 border border-transparent rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                        <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                    </button>
                </div>
            </div>
            @endif

            <div class="mt-8 flex justify-between items-center">
                <button type="button" id="{{ $modalId }}-delete-btn" class="btn text-red-600 hover:bg-red-50 hidden">刪除</button>
                <div class="flex gap-3 ml-auto">
                    <button type="button" onclick="closeModal('{{ $modalId }}')" class="btn btn-secondary">返回</button>
                    <button type="button" onclick="resetForm('{{ $modalId }}')" class="btn btn-secondary">重設</button>
                    <button type="submit" form="{{ $modalId }}-form" class="btn btn-primary">儲存</button>
                </div>
            </div>
        </div>
    </div>
</div>