@props(['type', 'name', 'value'])
<input type="{{ $type }}" name="{{ $name }}" class="form-control @error($name) is-invalid @enderror">
@error($name)
<div class="invalid-feedback">
    {{ $message }}
</div>
@enderror