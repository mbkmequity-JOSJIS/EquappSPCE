<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
            showClass: {
                popup: 'swal2-show'
            },
            hideClass: {
                popup: 'swal2-hide'
            }
        });

        const messages = [
            {
                icon: 'success',
                title: @json(session('success'))
            },
            {
                icon: 'error',
                title: @json(session('error'))
            },
            {
                icon: 'warning',
                title: @json(session('warning'))
            },
            {
                icon: 'info',
                title: @json(session('info'))
            }
        ];

        const firstMessage = messages.find(function(message) {
            return !!message.title;
        });

        if (firstMessage) {
            toast.fire({
                icon: firstMessage.icon,
                title: firstMessage.title
            });
            return;
        }

        const firstError = @json($errors->first());

        if (firstError) {
            toast.fire({
                icon: 'error',
                title: firstError
            });
        }
    });
</script>