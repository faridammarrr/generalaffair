document.addEventListener('DOMContentLoaded', () => {
    const dialog = document.getElementById('custom-confirm-modal');
    const messageEl = document.getElementById('confirm-modal-message');
    const cancelBtn = document.getElementById('confirm-modal-cancel');
    const acceptBtn = document.getElementById('confirm-modal-accept');

    let pendingForm = null;

    if (!dialog || !messageEl || !cancelBtn || !acceptBtn) return;

    document.addEventListener('submit', (e) => {
        const form = e.target;
        const confirmMsg = form.getAttribute('data-confirm');

        if (confirmMsg && !form.dataset.confirmed) {
            e.preventDefault();
            pendingForm = form;
            messageEl.textContent = confirmMsg;
            dialog.showModal();
        }
    });

    cancelBtn.addEventListener('click', () => {
        pendingForm = null;
        dialog.close();
    });

    acceptBtn.addEventListener('click', () => {
        if (pendingForm) {
            pendingForm.dataset.confirmed = 'true';
            pendingForm.submit();
            pendingForm = null;
        }
        dialog.close();
    });

    dialog.addEventListener('click', (e) => {
        const rect = dialog.getBoundingClientRect();
        const isInDialog = (
            rect.top <= e.clientY &&
            e.clientY <= rect.top + rect.height &&
            rect.left <= e.clientX &&
            e.clientX <= rect.left + rect.width
        );
        if (!isInDialog) {
            pendingForm = null;
            dialog.close();
        }
    });

    // IntersectionObserver fallback for scroll reveal animations
    if (!CSS.supports('(animation-timeline: view()) and (animation-range: entry)')) {
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.panel, .stats, .activity-card, .chart-grid').forEach(el => {
            el.classList.add('reveal-on-scroll');
            revealObserver.observe(el);
        });
    }

    // Animate progress track chart fill on view
    const chartObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.querySelectorAll('.progress-track > div').forEach((bar, index) => {
                    const targetWidth = bar.style.width;
                    bar.style.width = '0%';
                    setTimeout(() => {
                        bar.style.width = targetWidth;
                    }, 100 + (index * 150));
                });
                chartObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.chart-legend').forEach(el => chartObserver.observe(el));
});
