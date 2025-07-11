<div id="{{ $modalId }}" class="modal-backdrop fixed inset-0 z-[60] hidden" onclick="closeModal('{{ $modalId }}')">
    <div class="modal-content bg-white rounded-lg shadow-xl w-11/12 max-w-3xl flex" onclick="event.stopPropagation()">
        <div class="w-1/2 border-r border-gray-200 p-6 flex flex-col">
            <h3 class="text-lg font-bold mb-4 text-gray-800">{{ $title }}列表</h3>
            <div class="management-item-list flex-grow">
                <ul class="divide-y divide-gray-200">
                    @forelse ($items as $item)
                        <li class="py-2 px-2 flex justify-between items-center hover:bg-gray-100 rounded-md cursor-pointer" onclick="editItem('{{ $modalId }}', {{ json_encode($item) }})">
                            <span>{{ $item->name }}</span>
                            @if(isset($item->color))
                                <span class="w-4 h-4 rounded-full inline-block border" style="background-color: {{ $item->color }};"></span>
                            @endif
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
                <h3 id="{{ $modalId }}-form-title" class="text-lg font-header mb-4 text-[#5C5248]">新增項目</h3>
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
            <div class="mt-8 flex justify-between items-center">
                <button type="button" id="{{ $modalId }}-delete-btn" class="btn text-red-600 hover:bg-red-50 hidden">刪除</button>
                <div class="flex gap-3 ml-auto">
                    <button type="button" onclick="resetForm('{{ $modalId }}')" class="btn btn-secondary">重設</button>
                    <button type="submit" form="{{ $modalId }}-form" class="btn btn-primary">儲存</button>
                </div>
            </div>
        </div>
    </div>
</div>