@props(['type' => 'text', 'value' => '', 'name'])

<input 
    type="{{ $type }}" 
    name="{{ $name }}" 
    class="form-control @error($name) is-invalid @enderror"
    value="{{ old($name, $value) }}" 
/>

@error($name)
    <div class="invalid-feedback">
        {{ $message }}
    </div>
@enderror