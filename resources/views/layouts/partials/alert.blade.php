@if (session('success'))
<script>
    let message = @json(session('success'));
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })

    Toast.fire({
        icon: 'success',
        title: message
    })
</script>
@endif

@if (session('error'))
<script>
    let messageError = @json(session('error'));
    const ToastError = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    })

    ToastError.fire({
        icon: 'error',
        title: messageError
    })
</script>
@endif

@if (session('login'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        let message = @json(session('login'));
        Swal.fire(message);
    });
</script>
@endif

