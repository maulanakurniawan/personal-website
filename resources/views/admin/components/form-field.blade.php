@php
    $name = $field['name'] ?? $field['key'];
    $type = $field['type'] ?? 'text';
    $value = old($name, $value ?? '');
    $required = (bool)($field['required'] ?? false);
    $readonly = (bool)($field['readonly'] ?? $field['disabled'] ?? false);
    $classes = 'mt-1 w-full rounded border-slate-300 p-2 shadow-sm';
@endphp
<label class="block text-sm font-medium text-slate-700">
    {{ $field['label'] ?? \Illuminate\Support\Str::headline($name) }} @if($required)<span class="text-red-600">*</span>@endif
    @if(in_array($type, ['textarea','json'], true))
        <textarea name="{{ $name }}" @required($required) @readonly($readonly) class="{{ $classes }}" rows="{{ $type === 'json' ? 8 : 4 }}">{{ is_array($value) ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : $value }}</textarea>
    @elseif($type === 'boolean')
        <input type="hidden" name="{{ $name }}" value="0"><label class="mt-2 inline-flex items-center gap-2"><input type="checkbox" name="{{ $name }}" value="1" @checked((bool)$value) @disabled($readonly) class="rounded border-slate-300"> Yes</label>
    @elseif($type === 'select')
        <select name="{{ $name }}" @required($required) @disabled($readonly) class="{{ $classes }}"><option value="">Select...</option>@foreach(($field['options'] ?? []) as $optionKey => $option)<option value="{{ is_array($option) ? ($option['value'] ?? $optionKey) : (is_string($optionKey) ? $optionKey : $option) }}" @selected((string)$value === (string)(is_array($option) ? ($option['value'] ?? $optionKey) : (is_string($optionKey) ? $optionKey : $option)))>{{ is_array($option) ? ($option['label'] ?? $option['value'] ?? $optionKey) : $option }}</option>@endforeach</select>
    @else
        <input type="{{ match($type){'email'=>'email','number','money'=>'number','date'=>'date','datetime'=>'datetime-local','url'=>'url','hidden'=>'hidden',default=>'text'} }}" name="{{ $name }}" value="{{ $value }}" @required($required) @readonly($readonly) class="{{ $classes }}" @if($type === 'money') step="0.01" @endif>
    @endif
</label>
@error($name)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
@if($field['help'] ?? $field['description'] ?? null)<p class="mt-1 text-xs text-slate-500">{{ $field['help'] ?? $field['description'] }}</p>@endif
