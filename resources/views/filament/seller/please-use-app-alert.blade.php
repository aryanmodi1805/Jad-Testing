@if(session('please_use_app'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        alert(@json(__('seller.please_use_app')));
    });
</script>
@php(session()->forget('please_use_app'))
@endif
