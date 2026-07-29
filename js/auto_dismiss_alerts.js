document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    if (alerts.length > 0) {
        setTimeout(function() {
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease, max-height 0.5s ease, margin 0.5s ease, padding 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-8px)';
                alert.style.maxHeight = '0px';
                alert.style.paddingTop = '0px';
                alert.style.paddingBottom = '0px';
                alert.style.marginTop = '0px';
                alert.style.marginBottom = '0px';
                alert.style.overflow = 'hidden';
                alert.style.border = 'none';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 500);
            });
        }, 2000);
    }
});
