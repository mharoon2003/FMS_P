document.addEventListener('DOMContentLoaded', function() {
    const deleteButtons = document.querySelectorAll('[data-confirm-delete]');
    
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });

    const forms = document.querySelectorAll('form[data-confirm-submit]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const message = form.getAttribute('data-confirm-submit');
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });

    const videoInputs = document.querySelectorAll('input[name="video_url"]');
    
    videoInputs.forEach(input => {
        input.addEventListener('blur', function() {
            const url = this.value;
            if (url) {
                let videoId = '';
                
                if (url.includes('youtube.com') || url.includes('youtu.be')) {
                    const match = url.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([^&\s]+)/);
                    if (match) {
                        videoId = match[1];
                    }
                }
                
                if (videoId) {
                    this.value = `https://www.youtube.com/embed/${videoId}`;
                }
            }
        });
    });
});

function confirmAction(message) {
    return confirm(message || 'Are you sure?');
}
